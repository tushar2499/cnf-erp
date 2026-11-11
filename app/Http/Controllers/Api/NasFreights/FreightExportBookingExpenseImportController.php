<?php

namespace App\Http\Controllers\Api\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsDepartment;
use App\Models\NasFreights\NasFreightsDesignation;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsExpenseHead;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Imports export booking expenses from the accounts workbook ("Export Job EXPENSES").
 *
 * A file can be uploaded again and again without ever booking an expense twice:
 *  - only rows whose booking already exists are imported, and each expense takes the branch of its booking;
 *  - the system holds one expense per booking, so a booking that already has an expense is skipped whole;
 *  - a booking is imported all-or-nothing: one bad row skips the booking, and the corrected file picks it up;
 *  - a line repeated in the file (same date, employee, head, amount and note) is imported once.
 */
class FreightExportBookingExpenseImportController extends Controller
{
    private const HEADER_SCAN_ROWS = 15;

    /** The accounts workbook numbers jobs NF_EXDHK-2026-800038; the booking is stored as NASEXP26038. */
    private const ACCOUNTS_SEQUENCE_BASE = 800000;

    private const REQUIRED_COLUMNS = [
        'booking_no' => 'Booking No',
        'date'       => 'Date',
        'head'       => 'Expense Head',
        'amount'     => 'Amount',
    ];

    private const HEADER_FIELDS = [
        'bookingno'       => 'booking_no',
        'bookingnumber'   => 'booking_no',
        'exportbookingno' => 'booking_no',
        'jobno'           => 'booking_no',
        'jobnumber'       => 'booking_no',
        'date'            => 'date',
        'expdate'         => 'date',
        'expensedate'     => 'date',
        'employee'        => 'employee',
        'employeename'    => 'employee',
        'expby'           => 'employee',
        'expenseby'       => 'employee',
        'expensehead'     => 'head',
        'head'            => 'head',
        'receiptable'     => 'receiptable',
        'amount'          => 'amount',
        'expenseamount'   => 'amount',
        'approvedamount'  => 'approved_amount',
        'completion'      => 'completion',
        'status'          => 'completion',
        'note'            => 'note',
        'remarks'         => 'note',
        'remark'          => 'note',
    ];

    /** Header-less workbook: SL | Booking No | Date | Employee | (Category) | Expense Head | Receiptable | Amount | Completion */
    private const FIXED_COLUMNS = [
        'booking_no'  => 1,
        'date'        => 2,
        'employee'    => 3,
        'head'        => 5,
        'receiptable' => 6,
        'amount'      => 7,
        'completion'  => 8,
    ];

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if ($response = $this->missingZipResponse($request)) {
            return $response;
        }

        try {
            $analysis = $this->analyse($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        return response()->json(['summary' => $analysis['summary'], 'preview' => $analysis['groups']]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if ($response = $this->missingZipResponse($request)) {
            return $response;
        }

        try {
            $analysis = $this->analyse($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $skipped = $this->skippedReport($analysis['groups']);
        $importable = array_values(array_filter($analysis['groups'], fn (array $group) => $group['status'] === 'new'));

        if ($importable === []) {
            return response()->json([
                'message'  => 'No new export booking expenses to import.',
                'inserted' => 0,
                'summary'  => $analysis['summary'],
                'skipped'  => $skipped,
            ]);
        }

        $result = $this->persist($importable);
        $skipped['existing'] = array_merge($skipped['existing'], $result['late_existing']);
        $skipped['not_found'] = array_merge($skipped['not_found'], $result['late_not_found']);

        return response()->json([
            'message'              => "{$result['inserted']} expense(s) imported with {$result['items']} line(s).",
            'inserted'             => $result['inserted'],
            'inserted_items'       => $result['items'],
            'total_expense_amount' => $result['amount'],
            'new_heads'            => $result['new_heads'],
            'new_employees'        => $result['new_employees'],
            'summary'              => $analysis['summary'],
            'skipped'              => $skipped,
        ]);
    }

    private function validateFile(Request $request): void
    {
        $request->validate(
            [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 5 MB.',
            ]
        );
    }

    private function missingZipResponse(Request $request): ?JsonResponse
    {
        if (extension_loaded('zip') || strtolower($request->file('file')->getClientOriginalExtension()) !== 'xlsx') {
            return null;
        }

        return response()->json([
            'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Enable it or upload a .csv file instead.',
        ], 500);
    }

    // ─── Analysis (shared by preview and import) ─────────────────────────────

    /**
     * @return array{groups: array<int, array<string, mixed>>, summary: array<string, int|float>}
     */
    private function analyse(Request $request): array
    {
        $lines = $this->parseLines($request->file('file')->getPathname());
        $lookups = $this->buildLookups();

        $groups = array_map(
            fn (array $group) => $this->finaliseGroup($group, $lookups),
            $this->groupLines($lines, $lookups)
        );

        return ['groups' => $groups, 'summary' => $this->summarise($lines, $groups)];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<string, int|float>
     */
    private function summarise(array $lines, array $groups): array
    {
        $summary = [
            'rows'              => count($lines),
            'bookings'          => count($groups),
            'new'               => 0,
            'existing'          => 0,
            'not_found'         => 0,
            'invalid'           => 0,
            'duplicate_lines'   => 0,
            'importable_lines'  => 0,
            'importable_amount' => 0.0,
            'new_heads'         => 0,
            'new_employees'     => 0,
        ];
        $newHeads = [];
        $newEmployees = [];

        foreach ($groups as $group) {
            $summary[$group['status']]++;

            if ($group['status'] !== 'new') {
                continue;
            }

            foreach ($group['lines'] as $line) {
                if ($line['duplicate_of'] !== null) {
                    $summary['duplicate_lines']++;

                    continue;
                }

                $summary['importable_lines']++;
                $summary['importable_amount'] += $line['expense_amount'];

                if ($line['expense_head_id'] === null) {
                    $newHeads[$this->key($line['expense_head'])] = true;
                }
            }

            if ($group['employee_action'] === 'create') {
                $newEmployees[$this->key($group['employee']['code'])] = true;
            }
        }

        $summary['importable_amount'] = round($summary['importable_amount'], 2);
        $summary['new_heads'] = count($newHeads);
        $summary['new_employees'] = count($newEmployees);

        return $summary;
    }

    /**
     * @param  array<int, array<string, mixed>>  $groups
     * @return array{existing: array<int, array<string, mixed>>, not_found: array<int, array<string, mixed>>, invalid: array<int, array<string, mixed>>, duplicate_lines: array<int, array<string, mixed>>}
     */
    private function skippedReport(array $groups): array
    {
        $report = ['existing' => [], 'not_found' => [], 'invalid' => [], 'duplicate_lines' => []];

        foreach ($groups as $group) {
            $entry = [
                'booking_no' => $group['booking_no'],
                'rows'       => array_column($group['lines'], 'row'),
                'reason'     => $group['reason'],
            ];

            if ($group['status'] === 'existing') {
                $report['existing'][] = $entry + ['expense_no' => $group['expense_no']];
            } elseif ($group['status'] === 'not_found') {
                $report['not_found'][] = $entry;
            } elseif ($group['status'] === 'invalid') {
                $report['invalid'][] = $entry + ['errors' => $group['errors']];
            } else {
                foreach ($group['lines'] as $line) {
                    if ($line['duplicate_of'] !== null) {
                        $report['duplicate_lines'][] = [
                            'booking_no'   => $group['booking_no'],
                            'row'          => $line['row'],
                            'duplicate_of' => $line['duplicate_of'],
                        ];
                    }
                }
            }
        }

        return $report;
    }

    // ─── Spreadsheet parsing ─────────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseLines(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $lines = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $cells = $sheet->toArray(null, true, false, false);
            [$columns, $firstDataRow] = $this->resolveLayout($cells);

            foreach ($cells as $i => $row) {
                if ($i < $firstDataRow) {
                    continue;
                }

                $line = $this->parseLine($row, $columns, $sheet->getTitle(), $i + 1);

                if ($line !== null) {
                    $lines[] = $line;
                }
            }
        }

        return $lines;
    }

    /**
     * A header row (found in the first rows) wins; a workbook without one falls back to the fixed column layout.
     *
     * @param  array<int, array<int, mixed>>  $cells
     * @return array{0: array<string, int>, 1: int}
     */
    private function resolveLayout(array $cells): array
    {
        foreach (array_slice($cells, 0, self::HEADER_SCAN_ROWS + 1, true) as $i => $row) {
            $columns = $this->headerColumns($row);

            if (! isset($columns['booking_no'])) {
                continue;
            }

            $missing = array_diff_key(self::REQUIRED_COLUMNS, $columns);
            if ($missing !== []) {
                throw new \RuntimeException('Header row '.($i + 1).' is missing column(s): '.implode(', ', $missing).'.');
            }

            return [$columns, $i + 1];
        }

        return [self::FIXED_COLUMNS, 0];
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<string, int>
     */
    private function headerColumns(array $cells): array
    {
        $columns = [];

        foreach ($cells as $col => $label) {
            $key = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $label));

            if (isset(self::HEADER_FIELDS[$key]) && ! isset($columns[self::HEADER_FIELDS[$key]])) {
                $columns[self::HEADER_FIELDS[$key]] = $col;
            }
        }

        return $columns;
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $columns
     * @return array<string, mixed>|null
     */
    private function parseLine(array $row, array $columns, string $sheet, int $rowNo): ?array
    {
        $cell = fn (string $field): mixed => isset($columns[$field]) ? ($row[$columns[$field]] ?? null) : null;

        $bookingNo = $this->clean($cell('booking_no'));
        $dateText = $this->clean($cell('date'));
        $employee = $this->clean($cell('employee'));
        $head = $this->clean($cell('head'));
        $amountText = $this->clean($cell('amount'));

        if ($bookingNo === null && $dateText === null && $employee === null && $head === null && $amountText === null) {
            return null;
        }

        $errors = [];
        $warnings = [];

        if ($bookingNo === null) {
            $errors[] = 'Booking No is empty.';
        }

        $date = $this->parseDate($dateText);
        if ($date === null) {
            $errors[] = $dateText === null ? 'Date is empty.' : "Date '{$dateText}' is not a valid date.";
        }

        if ($head === null) {
            $errors[] = 'Expense Head is empty.';
        }

        $amount = $this->amount($cell('amount'));
        if ($amount === null || $amount <= 0) {
            $errors[] = $amountText === null ? 'Amount is empty.' : "Amount '{$amountText}' is not a positive number.";
            $amount = null;
        }

        $approved = $this->amount($cell('approved_amount'));
        if ($approved === null) {
            if ($this->clean($cell('approved_amount')) !== null) {
                $warnings[] = "Approved Amount '{$this->clean($cell('approved_amount'))}' is not a number; Completion was used.";
            }

            $approved = $amount !== null && strtolower((string) $this->clean($cell('completion'))) === 'done' ? $amount : 0.0;
        }

        return [
            'sheet'           => $sheet,
            'row'             => $rowNo,
            'booking_no'      => $bookingNo !== null ? mb_substr($bookingNo, 0, 255) : null,
            'date'            => $date,
            'employee'        => $employee,
            'expense_head'    => $head !== null ? mb_substr($head, 0, 255) : null,
            'receiptable'     => strtolower((string) $this->clean($cell('receiptable'))) === 'yes' ? 'Yes' : 'No',
            'expense_amount'  => $amount,
            'approved_amount' => max(0.0, $approved),
            'note'            => $this->clean($cell('note')),
            'errors'          => $errors,
            'warnings'        => $warnings,
        ];
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value)) {
            $value = rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.');
        }

        $value = trim((string) $value);

        return in_array($value, ['', '—', '–', '-'], true) ? null : $value;
    }

    private function amount(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return round((float) $value, 2);
        }

        $clean = preg_replace('/[\s,]/', '', (string) $this->clean($value));

        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        foreach (['n/j/Y', 'n/j/y', 'd-M-Y', 'd-M-y', 'Y-m-d', 'j M Y', 'j-n-Y', 'd/M/Y'] as $format) {
            $date = \DateTime::createFromFormat('!'.$format, $value);
            $errors = \DateTime::getLastErrors();

            if ($date && (! $errors || $errors['warning_count'] + $errors['error_count'] === 0) && (int) $date->format('Y') >= 1900) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function key(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function bookingKey(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', '', trim($value)));
    }

    private function normalizeName(string $name): string
    {
        $norm = str_replace('.', '', strtolower(trim(preg_replace('/\s+/', ' ', $name))));

        foreach (['mr ', 'md ', 'mrs ', 'ms ', 'sir '] as $prefix) {
            if (str_starts_with($norm, $prefix)) {
                $norm = trim(substr($norm, strlen($prefix)));
            }
        }

        return $norm;
    }

    /**
     * Splits "CODE_Name_Designation_Department"; a cell without underscores is a bare name (or code).
     *
     * @return array{code: ?string, name: ?string, designation: ?string, department: ?string}
     */
    private function parseEmployee(string $raw): array
    {
        if (! str_contains($raw, '_')) {
            return ['code' => null, 'name' => $raw, 'designation' => null, 'department' => null];
        }

        $parts = array_map(fn (string $part) => trim($part) === '' ? null : trim($part), explode('_', $raw, 4));

        return [
            'code'        => $parts[0],
            'name'        => $parts[1] ?? null,
            'designation' => $parts[2] ?? null,
            'department'  => $parts[3] ?? null,
        ];
    }

    // ─── Lookups and matching ────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function buildLookups(): array
    {
        $bookings = [];
        foreach (NasFreightsFreightExportBooking::get(['id', 'export_booking_no', 'invoice_no', 'export_bl_no']) as $booking) {
            $bookings[$this->bookingKey($booking->export_booking_no)][] = $booking;
        }

        return [
            'bookings'  => $bookings,
            'expenses'  => NasFreightsFreightExportBookingExpense::pluck('expense_no', 'booking_id')->all(),
            'heads'     => $this->headLookup(),
            'employees' => $this->employeeLookup(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function headLookup(): array
    {
        return NasFreightsExpenseHead::get(['id', 'name'])
            ->mapWithKeys(fn ($head) => [$this->key($head->name) => $head->id])
            ->all();
    }

    /**
     * @return array{byCode: array<string, int>, byName: array<string, int>}
     */
    private function employeeLookup(): array
    {
        $employees = NasFreightsEmployee::get(['id', 'code', 'name']);

        return [
            'byCode' => $employees->mapWithKeys(fn ($e) => [$this->key($e->code) => $e->id])->all(),
            'byName' => $employees->mapWithKeys(fn ($e) => [$this->normalizeName($e->name) => $e->id])->all(),
        ];
    }

    /**
     * Exact number first, then the accounts numbering converted to the booking numbering.
     *
     * @param  array<string, array<int, NasFreightsFreightExportBooking>>  $bookings
     * @return array{booking: ?NasFreightsFreightExportBooking, matched_by: ?string, reason: ?string}
     */
    private function resolveBooking(string $bookingNo, array $bookings): array
    {
        $candidates = ['exact' => $bookingNo];

        if (($converted = $this->convertedBookingNo($bookingNo)) !== null) {
            $candidates['converted'] = $converted;
        }

        foreach ($candidates as $matchedBy => $candidate) {
            $found = $bookings[$this->bookingKey($candidate)] ?? [];

            if (count($found) === 1) {
                return ['booking' => $found[0], 'matched_by' => $matchedBy, 'reason' => null];
            }

            if (count($found) > 1) {
                return ['booking' => null, 'matched_by' => null, 'reason' => "Booking No '{$candidate}' matches several bookings."];
            }
        }

        return ['booking' => null, 'matched_by' => null, 'reason' => 'Booking not found.'];
    }

    private function convertedBookingNo(string $bookingNo): ?string
    {
        if (! preg_match('/^NF_EXDHK-\d{2}(\d{2})-(\d{6})(.*)$/i', trim($bookingNo), $m) || (int) $m[2] < self::ACCOUNTS_SEQUENCE_BASE) {
            return null;
        }

        return 'NASEXP'.$m[1].str_pad((string) ((int) $m[2] - self::ACCOUNTS_SEQUENCE_BASE), 3, '0', STR_PAD_LEFT).$m[3];
    }

    /**
     * @param  array{code: ?string, name: ?string, designation: ?string, department: ?string}  $employee
     * @param  array{byCode: array<string, int>, byName: array<string, int>}  $lookup
     */
    private function findEmployeeId(array $employee, array $lookup): ?int
    {
        $codes = [$employee['code'], $employee['code'] === null ? $employee['name'] : null];

        foreach ($codes as $code) {
            if ($code !== null && isset($lookup['byCode'][$this->key($code)])) {
                return $lookup['byCode'][$this->key($code)];
            }
        }

        $name = $this->normalizeName((string) $employee['name']);

        return $name === '' ? null : ($lookup['byName'][$name] ?? null);
    }

    // ─── Grouping ────────────────────────────────────────────────────────────

    /**
     * One group per booking; lines of the same booking written with different numbers land in one group.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<string, mixed>  $lookups
     * @return array<int, array<string, mixed>>
     */
    private function groupLines(array $lines, array $lookups): array
    {
        $groups = [];

        foreach ($lines as $line) {
            $resolved = $line['booking_no'] === null
                ? ['booking' => null, 'matched_by' => null, 'reason' => null]
                : $this->resolveBooking($line['booking_no'], $lookups['bookings']);
            $booking = $resolved['booking'];

            $key = match (true) {
                $line['booking_no'] === null => 'none',
                $booking !== null            => 'id:'.$booking->id,
                default                      => 'raw:'.$this->bookingKey($line['booking_no']),
            };

            $groups[$key] ??= [
                'booking_no'            => $line['booking_no'],
                'booking_id'            => $booking?->id,
                'matched_booking_no'    => $booking?->export_booking_no,
                'matched_by'            => $resolved['matched_by'],
                'invoice_no'            => $booking?->invoice_no,
                'bl_no'                 => $booking?->export_bl_no,
                'status'                => 'new',
                'reason'                => $resolved['reason'],
                'expense_no'            => null,
                'date'                  => null,
                'employee'              => null,
                'employee_id'           => null,
                'employee_action'       => 'none',
                'total_expense_amount'  => 0.0,
                'total_approved_amount' => 0.0,
                'warnings'              => [],
                'errors'                => [],
                'lines'                 => [],
            ];

            $groups[$key]['lines'][] = $line;
        }

        return array_values($groups);
    }

    /**
     * Drops repeated lines, totals the rest and decides whether the booking can be imported.
     *
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>  $lookups
     * @return array<string, mixed>
     */
    private function finaliseGroup(array $group, array $lookups): array
    {
        $seen = [];

        foreach ($group['lines'] as $i => $line) {
            $group['lines'][$i]['duplicate_of'] = null;
            $group['lines'][$i]['expense_head_id'] = $line['expense_head'] === null
                ? null
                : ($lookups['heads'][$this->key($line['expense_head'])] ?? null);

            if ($line['errors'] !== []) {
                continue;
            }

            $fingerprint = implode('|', [
                $line['date'],
                $this->key($line['employee']),
                $this->key($line['expense_head']),
                number_format($line['expense_amount'], 2, '.', ''),
                $this->key($line['note']),
            ]);

            if (isset($seen[$fingerprint])) {
                $group['lines'][$i]['duplicate_of'] = $seen[$fingerprint];
            } else {
                $seen[$fingerprint] = $line['row'];
            }
        }

        $kept = array_filter($group['lines'], fn (array $line) => $line['errors'] === [] && $line['duplicate_of'] === null);

        $group['total_expense_amount'] = round(array_sum(array_column($kept, 'expense_amount')), 2);
        $group['total_approved_amount'] = round(array_sum(array_column($kept, 'approved_amount')), 2);
        $group['date'] = $kept === [] ? null : min(array_column($kept, 'date'));

        $headerEmployee = null;
        foreach ($kept as $line) {
            if ($line['employee'] !== null) {
                $headerEmployee = $line['employee'];

                break;
            }
        }

        if ($headerEmployee !== null) {
            $group['employee'] = $this->parseEmployee($headerEmployee);
            $group['employee_id'] = $this->findEmployeeId($group['employee'], $lookups['employees']);
            $group['employee_action'] = match (true) {
                $group['employee_id'] !== null       => 'match',
                $group['employee']['code'] !== null  => 'create',
                default                              => 'none',
            };
        }

        $lineErrors = [];
        foreach ($group['lines'] as $line) {
            foreach ($line['errors'] as $error) {
                $lineErrors[] = "Row {$line['row']}: {$error}";
            }
        }

        $group['status'] = match (true) {
            $group['booking_no'] === null                         => 'invalid',
            $group['booking_id'] === null                         => 'not_found',
            isset($lookups['expenses'][$group['booking_id']])     => 'existing',
            $lineErrors !== []                                    => 'invalid',
            default                                               => 'new',
        };

        if ($group['status'] === 'existing') {
            $group['expense_no'] = $lookups['expenses'][$group['booking_id']];
            $group['reason'] = "Booking already has expense {$group['expense_no']}.";
        } elseif ($group['status'] === 'invalid') {
            $group['errors'] = $lineErrors;
            $group['reason'] = $group['booking_no'] === null
                ? 'Rows without a Booking No.'
                : 'Fix the row error(s) and upload again; the booking is imported as a whole.';
        }

        if ($group['status'] === 'new') {
            $group = $this->annotateNewGroup($group, $kept, $headerEmployee);
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<int, array<string, mixed>>  $kept  lines that will be saved, keyed like $group['lines']
     * @return array<string, mixed>
     */
    private function annotateNewGroup(array $group, array $kept, ?string $headerEmployee): array
    {
        $headerKey = $this->key($headerEmployee);
        $employees = [$headerKey => true];

        foreach ($kept as $i => $line) {
            if ($line['employee'] !== null && $this->key($line['employee']) !== $headerKey) {
                $other = $this->parseEmployee($line['employee']);
                $label = trim(($other['name'] ?? '').($other['code'] !== null ? ' ('.$other['code'].')' : ''));

                $group['lines'][$i]['note'] = implode(' | ', array_filter([$line['note'], 'Exp. by: '.$label]));
                $employees[$this->key($line['employee'])] = true;
            }

            if ($line['expense_head_id'] === null) {
                $group['lines'][$i]['warnings'][] = "Expense head '{$line['expense_head']}' will be created.";
            }
        }

        if ($group['matched_by'] === 'converted') {
            $group['warnings'][] = "Matched {$group['booking_no']} to booking {$group['matched_booking_no']}.";
        }

        if (count($employees) > 1) {
            $group['warnings'][] = count($employees).' employees on this booking: the expense is saved under the first one, the others are added to the line notes.';
        }

        if ($group['employee_action'] === 'create') {
            $group['warnings'][] = "Employee '{$group['employee']['code']}' will be created.";
        } elseif ($group['employee_action'] === 'none' && $group['employee'] !== null) {
            $group['warnings'][] = "Employee '{$group['employee']['name']}' not found and has no code; left blank.";
        }

        return $group;
    }

    // ─── Persistence ─────────────────────────────────────────────────────────

    /**
     * Every booking is locked and re-checked inside the transaction, so two uploads at once cannot both save an expense for it.
     *
     * @param  array<int, array<string, mixed>>  $groups  groups with status "new"
     * @return array{inserted: int, items: int, amount: float, new_heads: int, new_employees: int, late_existing: array<int, array<string, mixed>>, late_not_found: array<int, array<string, mixed>>}
     */
    private function persist(array $groups): array
    {
        return DB::transaction(function () use ($groups) {
            $context = [
                'heads'         => $this->headLookup(),
                'employees'     => $this->employeeLookup(),
                'designations'  => NasFreightsDesignation::get(['id', 'name'])->mapWithKeys(fn ($d) => [$this->key($d->name) => $d->id])->all(),
                'departments'   => NasFreightsDepartment::get(['id', 'name'])->mapWithKeys(fn ($d) => [$this->key($d->name) => $d->id])->all(),
                'new_heads'     => 0,
                'new_employees' => 0,
            ];
            $result = ['inserted' => 0, 'items' => 0, 'amount' => 0.0, 'late_existing' => [], 'late_not_found' => []];

            foreach ($groups as $group) {
                $entry = ['booking_no' => $group['booking_no'], 'rows' => array_column($group['lines'], 'row')];

                $booking = NasFreightsFreightExportBooking::lockForUpdate()->find($group['booking_id']);
                if ($booking === null) {
                    $result['late_not_found'][] = $entry + ['reason' => 'Booking no longer exists.'];

                    continue;
                }

                $existingNo = NasFreightsFreightExportBookingExpense::where('booking_id', $booking->id)->lockForUpdate()->value('expense_no');
                if ($existingNo !== null) {
                    $result['late_existing'][] = $entry + ['reason' => "Booking already has expense {$existingNo}.", 'expense_no' => $existingNo];

                    continue;
                }

                $expense = NasFreightsFreightExportBookingExpense::create([
                    'expense_no'            => NasFreightsFreightExportBookingExpense::generateExpenseNo(),
                    'booking_id'            => $booking->id,
                    'booking_no'            => mb_substr($booking->export_booking_no, 0, 100),
                    'invoice_no'            => $booking->invoice_no,
                    'bl_no'                 => $booking->export_bl_no,
                    'employee_id'           => $this->employeeId($group['employee'], $booking->branch_id, $context),
                    'branch_id'             => $booking->branch_id,
                    'date'                  => $group['date'],
                    'total_expense_amount'  => $group['total_expense_amount'],
                    'total_approved_amount' => $group['total_approved_amount'],
                    'status'                => $group['total_approved_amount'] > 0 ? 'Approved' : 'Draft',
                ]);

                foreach ($group['lines'] as $line) {
                    if ($line['duplicate_of'] !== null) {
                        continue;
                    }

                    $expense->items()->create([
                        'expense_head_id' => $this->headId($line['expense_head'], $context),
                        'receiptable'     => $line['receiptable'],
                        'expense_amount'  => $line['expense_amount'],
                        'approved_amount' => $line['approved_amount'],
                        'expense_date'    => $line['date'],
                        'note'            => $line['note'],
                    ]);
                    $result['items']++;
                }

                $result['inserted']++;
                $result['amount'] += $group['total_expense_amount'];
            }

            $result['amount'] = round($result['amount'], 2);

            return $result + ['new_heads' => $context['new_heads'], 'new_employees' => $context['new_employees']];
        });
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function headId(string $name, array &$context): int
    {
        $key = $this->key($name);

        if (! isset($context['heads'][$key])) {
            $context['heads'][$key] = NasFreightsExpenseHead::create(['name' => $name, 'type' => 'External', 'status' => 'Active'])->id;
            $context['new_heads']++;
        }

        return $context['heads'][$key];
    }

    /**
     * @param  array{code: ?string, name: ?string, designation: ?string, department: ?string}|null  $parsed
     * @param  int  $branchId  branch of the booking; a new employee is created in it
     * @param  array<string, mixed>  $context
     */
    private function employeeId(?array $parsed, int $branchId, array &$context): ?int
    {
        if ($parsed === null) {
            return null;
        }

        $id = $this->findEmployeeId($parsed, $context['employees']);
        if ($id !== null || $parsed['code'] === null) {
            return $id;
        }

        $employee = NasFreightsEmployee::create([
            'branch_id'      => $branchId,
            'designation_id' => $parsed['designation'] !== null ? ($context['designations'][$this->key($parsed['designation'])] ?? null) : null,
            'department_id'  => $parsed['department'] !== null ? ($context['departments'][$this->key($parsed['department'])] ?? null) : null,
            'code'           => $parsed['code'],
            'name'           => $parsed['name'] ?? $parsed['code'],
            'designation'    => $parsed['designation'],
            'status'         => 'Active',
            'is_active'      => true,
        ]);

        $context['employees']['byCode'][$this->key($employee->code)] = $employee->id;
        $context['employees']['byName'][$this->normalizeName($employee->name)] = $employee->id;
        $context['new_employees']++;

        return $employee->id;
    }
}

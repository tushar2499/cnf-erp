<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobType;
use App\Models\Chevron\ChevronPort;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Chittagong (branch "CGP") job importer.
 *
 * Reads the "JOB INFO" sheet when the workbook has one, otherwise the active sheet. The header row is
 * located by its "Job No" cell and every column is found by header name, so extra leading columns and
 * blank rows above the header (as in the "DUE JOB INFORMATION" export) do not matter.
 *
 * Rules:
 *   - A job number that already exists in chevron_jobs (any branch, case and spaces ignored) is skipped.
 *   - A job number repeated inside the file is imported once (first row wins); the repeats are reported.
 *   - Every chunk is saved in one transaction and re-checks the unique job_no index, so a failed or
 *     repeated upload never duplicates a job and can be re-uploaded safely.
 *   - "{job_no}_PJ" / "{job_no}_PJ1" ... rows are linked to their parent job as partial jobs. Parents are
 *     always imported before partials, so file order does not matter. A partial whose parent is unknown
 *     is still imported, unlinked, and reported.
 *   - Rows that cannot be imported are skipped and reported by reason.
 *
 * Must be run before the CTG job-expense import so expenses link up to existing jobs.
 */
class CtgJobImportController extends Controller
{
    private const SHEET_NAME = 'JOB INFO';

    private const CTG_BRANCH_ID = 6;

    private const CHUNK_SIZE = 200;

    private const PREVIEW_LIMIT = 100;

    private const SKIP_SAMPLE_LIMIT = 10;

    private const HEADER_SCAN_ROWS = 30;

    private const SERVICE_IMPORT = 1;

    private const SERVICE_EXPORT = 2;

    private const MIN_DATE_YEAR = 2000;

    private const MAX_DATE_YEAR = 2100;

    private const STATUS_MAP = [
        'submitted' => 'Pending',
        'active'    => 'Active',
        'confirmed' => 'Active',
        'pending'   => 'Pending',
        'closed'    => 'Closed',
    ];

    /** Normalized header text of every column we read. */
    private const HEADERS = [
        'job_no'               => 'jobno',
        'job_date'             => 'jobdate',
        'job_type'             => 'jobtype',
        'type'                 => 'type',
        'port'                 => 'port',
        'customer'             => 'customer',
        'goods_name'           => 'goodsname',
        'be_no'                => 'be',
        'be_date'              => 'bedate',
        'bl_no'                => 'bl',
        'lc_no'                => 'lc',
        'invoice_no'           => 'invoice',
        'mbl_mawb_no'          => 'mblmawb',
        'gross_weight'         => 'grossweight',
        'pack_quantity'        => 'qty',
        'pack_unit'            => 'unit',
        'assessable_value'     => 'assessablevalue',
        'currency_type'        => 'currencyname',
        'currency_rate'        => 'currencyrate',
        'assessable_value_bdt' => 'invoicevaluebdt',
        'total_payable_1'      => 'totalpayable',
        'remarks'              => 'remarks',
        'status'               => 'status',
    ];

    // ─── Endpoints ────────────────────────────────────────────────────────────

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(300);
        ini_set('memory_limit', '768M');

        $lookups = $this->buildLookups();

        try {
            [$parsed, $skipped] = $this->parseRows($this->loadRows($request->file('file')->getPathname()), $lookups);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $fileParents = $this->fileParentKeys($parsed);

        $summary = [
            'rows_in_file'                => count($parsed) + array_sum(array_column($skipped, 'count')),
            'new_jobs'                    => 0,
            'existing_jobs'               => 0,
            'duplicate_in_file'           => $skipped['duplicate_in_file']['count'] ?? 0,
            'partial_jobs'                => 0,
            'partial_jobs_without_parent' => 0,
            'new_ports'                   => 0,
            'new_job_types'               => 0,
            'unmatched_customers'         => 0,
        ];

        $seenPorts = [];
        $seenJobTypes = [];
        $seenCustomers = [];
        $previewRows = [];

        foreach ($parsed as $row) {
            $exists = isset($lookups['jobs'][strtolower($row['job_no'])]);
            $row['exists'] = $exists;

            if ($exists) {
                $summary['existing_jobs']++;
            } else {
                $summary['new_jobs']++;

                if ($row['parent_job_no'] !== null) {
                    $summary['partial_jobs']++;

                    if (! $this->parentKnown($row, $lookups, $fileParents)) {
                        $summary['partial_jobs_without_parent']++;
                        $row['warnings'][] = "Partial job: parent '{$row['parent_job_no']}' not found; imported without a parent link.";
                    }
                }

                $portKey = strtolower($row['_port_name']);
                if ($row['port_id'] === null && $row['_port_name'] !== '' && ! isset($seenPorts[$portKey])) {
                    $seenPorts[$portKey] = true;
                    $summary['new_ports']++;
                }

                $jobTypeKey = strtolower($row['_job_type_name']);
                if ($row['job_type_id'] === null && $row['_job_type_name'] !== '' && ! isset($seenJobTypes[$jobTypeKey])) {
                    $seenJobTypes[$jobTypeKey] = true;
                    $summary['new_job_types']++;
                }

                $customerKey = strtolower($row['party_name'] ?? '');
                if ($row['customer_id'] === null && $customerKey !== '' && ! isset($seenCustomers[$customerKey])) {
                    $seenCustomers[$customerKey] = true;
                    $summary['unmatched_customers']++;
                }
            }

            if (count($previewRows) < self::PREVIEW_LIMIT) {
                $previewRows[] = $row;
            }
        }

        return response()->json([
            'summary'      => $summary,
            'skipped_rows' => $skipped,
            'preview'      => $previewRows,
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(0);
        ini_set('memory_limit', '768M');

        $lookups = $this->buildLookups();

        try {
            [$parsed, $skipped] = $this->parseRows($this->loadRows($request->file('file')->getPathname()), $lookups);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $pending = array_values(array_filter(
            $parsed,
            fn (array $row) => ! isset($lookups['jobs'][strtolower($row['job_no'])])
        ));

        $stats = $this->persistRows($pending, $lookups);

        return response()->json([
            'message' => $stats['inserted'] === 0
                ? 'No new jobs to import. All records already exist.'
                : "{$stats['inserted']} job(s) imported successfully.",
            'inserted'          => $stats['inserted'],
            'skipped_existing'  => count($parsed) - count($pending) + $stats['skipped_existing'],
            'skipped_rows'      => $skipped,
            'linked_partials'   => $stats['linked_partials'],
            'unlinked_partials' => $stats['unlinked_partials'],
            'new_ports'         => $stats['new_ports'],
            'new_job_types'     => $stats['new_job_types'],
        ]);
    }

    // ─── Spreadsheet loading ─────────────────────────────────────────────────

    /**
     * @return array<int, array<int, mixed>>
     */
    private function loadRows(string $pathname): array
    {
        $reader = IOFactory::createReaderForFile($pathname);
        $reader->setReadDataOnly(true);

        if (method_exists($reader, 'listWorksheetNames') && in_array(self::SHEET_NAME, $reader->listWorksheetNames($pathname), true)) {
            $reader->setLoadSheetsOnly([self::SHEET_NAME]);
        }

        $spreadsheet = $reader->load($pathname);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow <= 1) {
            $spreadsheet->disconnectWorksheets();

            return [];
        }

        $rows = $sheet->rangeToArray('A1:'.$sheet->getHighestDataColumn().$highestRow, null, false, false, false);

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                if (is_string($value) && str_starts_with($value, '=')) {
                    $rows[$rowIndex][$columnIndex] = $sheet->getCell([$columnIndex + 1, $rowIndex + 1])->getOldCalculatedValue();
                }
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    // ─── Parsing ──────────────────────────────────────────────────────────────

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<string, mixed>  $lookups
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, array{count: int, rows: array<int, int>}>}
     */
    private function parseRows(array $rows, array $lookups): array
    {
        [$headerIndex, $columns] = $this->locateColumns($rows);

        $parsed = [];
        $skipped = [];
        $seenJobNos = [];

        foreach ($rows as $index => $row) {
            if ($index <= $headerIndex) {
                continue;
            }

            $sheetRow = $index + 1;
            $jobNo = $this->cellText($row, $columns['job_no']);

            if ($jobNo === '') {
                if ($this->hasData($row, $columns)) {
                    $this->recordSkip($skipped, 'missing_job_no', $sheetRow);
                }

                continue;
            }

            $jobKey = strtolower($jobNo);
            if (isset($seenJobNos[$jobKey])) {
                $this->recordSkip($skipped, 'duplicate_in_file', $sheetRow);

                continue;
            }
            $seenJobNos[$jobKey] = true;

            $parsed[] = $this->mapRow($row, $columns, $jobNo, $sheetRow, $lookups);
        }

        return [$parsed, $skipped];
    }

    /**
     * Find the header row (the first one holding a "Job No" cell) and the column index of every field.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: int, 1: array<string, int|null>}
     */
    private function locateColumns(array $rows): array
    {
        foreach (array_slice($rows, 0, self::HEADER_SCAN_ROWS, true) as $index => $row) {
            $found = [];
            foreach ($row as $column => $value) {
                $text = $this->normalizeHeader($value);
                if ($text !== '') {
                    $found[$text] ??= $column;
                }
            }

            if (isset($found['jobno'])) {
                $columns = [];
                foreach (self::HEADERS as $key => $header) {
                    $columns[$key] = $found[$header] ?? null;
                }

                return [$index, $columns];
            }
        }

        throw new \RuntimeException('Header row with a "Job No" column was not found in the first '.self::HEADER_SCAN_ROWS.' rows.');
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int|null>  $columns
     * @param  array<string, mixed>  $lookups
     * @return array<string, mixed>
     */
    private function mapRow(array $row, array $columns, string $jobNo, int $sheetRow, array $lookups): array
    {
        $portName = $this->cellText($row, $columns['port']);
        $jobTypeName = $this->cellText($row, $columns['type']) ?: $this->cellText($row, $columns['job_type']);
        $customerName = $this->cellText($row, $columns['customer']);
        $statusRaw = strtolower($this->cellText($row, $columns['status']));

        $portId = $portName !== '' ? ($lookups['ports'][strtolower($portName)] ?? null) : null;
        $jobTypeId = $jobTypeName !== '' ? ($lookups['jobTypes'][strtolower($jobTypeName)] ?? null) : null;
        $customer = $customerName !== '' ? ($lookups['customers'][strtolower($customerName)] ?? null) : null;

        $warnings = [];
        if ($portId === null && $portName !== '') {
            $warnings[] = "Port '{$portName}' will be created.";
        }
        if ($jobTypeId === null && $jobTypeName !== '') {
            $warnings[] = "Job type '{$jobTypeName}' will be created.";
        }
        if ($customer === null && $customerName !== '') {
            $warnings[] = "Customer '{$customerName}' not found; saved as party name only.";
        }

        $jobDateRaw = $this->cellValue($row, $columns['job_date']);
        $beDateRaw = $this->cellValue($row, $columns['be_date']);
        $jobDate = $this->parseDate($jobDateRaw);
        $beDate = $this->parseDate($beDateRaw);

        if ($jobDate === null && $this->hasValue($jobDateRaw)) {
            $warnings[] = "Job date '{$jobDateRaw}' not recognised; saved empty.";
        }
        if ($beDate === null && $this->hasValue($beDateRaw)) {
            $warnings[] = "B/E date '{$beDateRaw}' not recognised; saved empty.";
        }

        $parentJobNo = null;
        $partialSeq = null;
        if (preg_match('/^(.+)_PJ(\d*)$/i', $jobNo, $match)) {
            $parentJobNo = $match[1];
            $partialSeq = $match[2] === '' ? 1 : ((int) $match[2]) + 1;
        }

        return [
            'sheet_row'            => $sheetRow,
            'job_no'               => $jobNo,
            'parent_job_no'        => $parentJobNo,
            'partial_seq'          => $partialSeq,
            'job_date'             => $jobDate,
            'service_id'           => $this->serviceIdFor($jobNo, $jobTypeName),
            'job_type_id'          => $jobTypeId,
            'port_id'              => $portId,
            '_port_name'           => $portName,
            '_job_type_name'       => $jobTypeName,
            'customer_id'          => $customer['id'] ?? null,
            'party_name'           => $customer['name'] ?? ($customerName !== '' ? $customerName : null),
            'goods_name'           => $this->cellText($row, $columns['goods_name']) ?: null,
            'be_no'                => $this->cellText($row, $columns['be_no']) ?: null,
            'be_date'              => $beDate,
            'bl_no'                => $this->cellText($row, $columns['bl_no']) ?: null,
            'lc_no'                => $this->cellText($row, $columns['lc_no']) ?: null,
            'invoice_no'           => $this->cellText($row, $columns['invoice_no']) ?: null,
            'mbl_mawb_no'          => $this->cellText($row, $columns['mbl_mawb_no']) ?: null,
            'gross_weight'         => $this->parseNumber($this->cellValue($row, $columns['gross_weight'])),
            'pack_quantity'        => $this->parseNumber($this->cellValue($row, $columns['pack_quantity'])),
            'pack_unit'            => $this->cellText($row, $columns['pack_unit']) ?: null,
            'assessable_value'     => $this->parseNumber($this->cellValue($row, $columns['assessable_value'])),
            'currency_type'        => $this->cellText($row, $columns['currency_type']) ?: null,
            'currency_rate'        => $this->parseNumber($this->cellValue($row, $columns['currency_rate'])),
            'assessable_value_bdt' => $this->parseNumber($this->cellValue($row, $columns['assessable_value_bdt'])),
            'total_payable_1'      => $this->parseNumber($this->cellValue($row, $columns['total_payable_1'])),
            'remarks'              => $this->cellText($row, $columns['remarks']) ?: null,
            'status'               => self::STATUS_MAP[$statusRaw] ?? 'Active',
            'warnings'             => $warnings,
        ];
    }

    private function serviceIdFor(string $jobNo, string $jobTypeName): int
    {
        return match (true) {
            (bool) preg_match('/_IM[A-Z]+-/i', $jobNo) => self::SERVICE_IMPORT,
            (bool) preg_match('/_EX[A-Z]+-/i', $jobNo) => self::SERVICE_EXPORT,
            strtolower($jobTypeName) === 'export'      => self::SERVICE_EXPORT,
            default                                    => self::SERVICE_IMPORT,
        };
    }

    /**
     * Excel serial numbers and "17 Nov 2025" / "27/8/2026" / "2026-08-27" text are accepted. Anything else,
     * or a year outside 2000-2100, returns null instead of guessing.
     */
    private function parseDate(mixed $value): ?string
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;

            if ($serial < 20000 || $serial > 80000) {
                return null;
            }

            return $this->withinYearRange(ExcelDate::excelToDateTimeObject($serial))?->format('Y-m-d');
        }

        $text = trim((string) $value);

        foreach (['j M Y', 'j F Y', 'j/n/Y', 'j-n-Y', 'Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $text);
            $errors = \DateTimeImmutable::getLastErrors();

            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $this->withinYearRange($date)?->format('Y-m-d');
            }
        }

        return null;
    }

    private function withinYearRange(\DateTimeInterface $date): ?\DateTimeInterface
    {
        $year = (int) $date->format('Y');

        return $year >= self::MIN_DATE_YEAR && $year <= self::MAX_DATE_YEAR ? $date : null;
    }

    private function parseNumber(mixed $value): ?float
    {
        if (is_string($value)) {
            $value = str_replace(',', '', trim($value));
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<string, array{count: int, rows: array<int, int>}>  $skipped
     */
    private function recordSkip(array &$skipped, string $reason, int $sheetRow): void
    {
        $skipped[$reason] ??= ['count' => 0, 'rows' => []];
        $skipped[$reason]['count']++;

        if (count($skipped[$reason]['rows']) < self::SKIP_SAMPLE_LIMIT) {
            $skipped[$reason]['rows'][] = $sheetRow;
        }
    }

    private function normalizeHeader(mixed $value): string
    {
        return is_scalar($value) ? preg_replace('/[^a-z0-9]/', '', strtolower((string) $value)) : '';
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function cellValue(array $row, ?int $column): mixed
    {
        return $column === null ? null : ($row[$column] ?? null);
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function cellText(array $row, ?int $column): string
    {
        $value = $this->cellValue($row, $column);

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function hasValue(mixed $value): bool
    {
        return is_scalar($value) && trim((string) $value) !== '';
    }

    /**
     * True when the row holds anything besides an (empty) job number.
     *
     * @param  array<int, mixed>  $row
     * @param  array<string, int|null>  $columns
     */
    private function hasData(array $row, array $columns): bool
    {
        foreach ($columns as $key => $column) {
            if ($key !== 'job_no' && $this->cellText($row, $column) !== '') {
                return true;
            }
        }

        return false;
    }

    // ─── Lookups ──────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function buildLookups(): array
    {
        $ports = [];
        ChevronPort::get(['id', 'name', 'code'])->each(function ($port) use (&$ports) {
            $ports[strtolower(trim($port->name))] = $port->id;
            if (trim((string) $port->code) !== '') {
                $ports[strtolower(trim($port->code))] = $port->id;
            }
        });

        $jobTypes = ChevronJobType::where('is_active', true)
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($type) => [strtolower(trim($type->name)) => $type->id])
            ->all();

        $customers = ChevronCustomer::get(['id', 'name'])
            ->mapWithKeys(fn ($customer) => [strtolower(trim($customer->name)) => ['id' => $customer->id, 'name' => $customer->name]])
            ->all();

        $jobs = [];
        $partialSeqs = [];
        foreach (ChevronJob::query()->toBase()->get(['id', 'job_no', 'parent_job_id', 'partial_seq']) as $job) {
            $jobs[strtolower(trim($job->job_no))] = ['id' => $job->id, 'is_partial' => $job->parent_job_id !== null];

            if ($job->parent_job_id !== null) {
                $partialSeqs[$job->parent_job_id.':'.$job->partial_seq] = true;
            }
        }

        return compact('ports', 'jobTypes', 'customers', 'jobs', 'partialSeqs');
    }

    /**
     * Lower-cased job numbers in the file that can act as a partial job's parent.
     *
     * @param  array<int, array<string, mixed>>  $parsed
     * @return array<string, true>
     */
    private function fileParentKeys(array $parsed): array
    {
        $keys = [];
        foreach ($parsed as $row) {
            if ($row['parent_job_no'] === null) {
                $keys[strtolower($row['job_no'])] = true;
            }
        }

        return $keys;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $lookups
     * @param  array<string, true>  $fileParents
     */
    private function parentKnown(array $row, array $lookups, array $fileParents): bool
    {
        $parentKey = strtolower($row['parent_job_no']);
        $existing = $lookups['jobs'][$parentKey] ?? null;

        return ($existing !== null && ! $existing['is_partial']) || isset($fileParents[$parentKey]);
    }

    // ─── Persistence ──────────────────────────────────────────────────────────

    /**
     * Insert the rows chunk by chunk, parents before partial jobs.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $lookups
     * @return array{inserted: int, skipped_existing: int, linked_partials: int, unlinked_partials: int, new_ports: int, new_job_types: int}
     */
    private function persistRows(array $rows, array &$lookups): array
    {
        $stats = [
            'inserted'          => 0,
            'skipped_existing'  => 0,
            'linked_partials'   => 0,
            'unlinked_partials' => 0,
            'new_ports'         => 0,
            'new_job_types'     => 0,
        ];

        $parents = array_filter($rows, fn (array $row) => $row['parent_job_no'] === null);
        $partials = array_filter($rows, fn (array $row) => $row['parent_job_no'] !== null);

        foreach (array_chunk(array_merge($parents, $partials), self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, &$lookups, &$stats) {
                foreach ($chunk as $row) {
                    $this->insertRow($row, $lookups, $stats);
                }
            });
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $lookups
     * @param  array<string, int>  $stats
     */
    private function insertRow(array $row, array &$lookups, array &$stats): void
    {
        $jobKey = strtolower($row['job_no']);

        if (isset($lookups['jobs'][$jobKey])) {
            $stats['skipped_existing']++;

            return;
        }

        $portId = $row['port_id'] ?? $this->resolvePort($row['_port_name'], $lookups, $stats);
        $jobTypeId = $row['job_type_id'] ?? $this->resolveJobType($row['_job_type_name'], $lookups, $stats);

        $parentId = null;
        $partialSeq = null;
        if ($row['parent_job_no'] !== null) {
            $parent = $lookups['jobs'][strtolower($row['parent_job_no'])] ?? null;

            if ($parent !== null && ! $parent['is_partial'] && ! isset($lookups['partialSeqs'][$parent['id'].':'.$row['partial_seq']])) {
                $parentId = $parent['id'];
                $partialSeq = $row['partial_seq'];
            }
        }

        $job = (new ChevronJob)->forceFill([
            'job_no'               => $row['job_no'],
            'parent_job_id'        => $parentId,
            'partial_seq'          => $partialSeq,
            'branch_id'            => self::CTG_BRANCH_ID,
            'service_id'           => $row['service_id'],
            'job_type_id'          => $jobTypeId,
            'port_id'              => $portId,
            'job_date'             => $row['job_date'],
            'customer_id'          => $row['customer_id'],
            'party_name'           => $row['party_name'],
            'goods_name'           => $row['goods_name'],
            'be_no'                => $row['be_no'],
            'be_date'              => $row['be_date'],
            'bl_no'                => $row['bl_no'],
            'lc_no'                => $row['lc_no'],
            'invoice_no'           => $row['invoice_no'],
            'mbl_mawb_no'          => $row['mbl_mawb_no'],
            'gross_weight'         => $row['gross_weight'],
            'pack_quantity'        => $row['pack_quantity'],
            'pack_unit'            => $row['pack_unit'],
            'assessable_value'     => $row['assessable_value'],
            'currency_type'        => $row['currency_type'],
            'currency_rate'        => $row['currency_rate'],
            'assessable_value_bdt' => $row['assessable_value_bdt'],
            'total_payable_1'      => $row['total_payable_1'],
            'remarks'              => $row['remarks'],
            'status'               => $row['status'],
        ]);

        try {
            $job->save();
        } catch (UniqueConstraintViolationException) {
            $stats['skipped_existing']++;

            return;
        }

        $lookups['jobs'][$jobKey] = ['id' => $job->id, 'is_partial' => $parentId !== null];
        $stats['inserted']++;

        if ($parentId !== null) {
            $lookups['partialSeqs'][$parentId.':'.$partialSeq] = true;
            $stats['linked_partials']++;
        } elseif ($row['parent_job_no'] !== null) {
            $stats['unlinked_partials']++;
        }
    }

    /**
     * @param  array<string, mixed>  $lookups
     * @param  array<string, int>  $stats
     */
    private function resolvePort(string $name, array &$lookups, array &$stats): ?int
    {
        if ($name === '') {
            return null;
        }

        $key = strtolower($name);
        if (isset($lookups['ports'][$key])) {
            return $lookups['ports'][$key];
        }

        $port = ChevronPort::firstOrCreate(
            ['name' => $name, 'code' => mb_substr(strtoupper($name), 0, 10)],
            ['branch_id' => self::CTG_BRANCH_ID, 'is_active' => true]
        );

        $lookups['ports'][$key] = $port->id;
        $stats['new_ports'] += $port->wasRecentlyCreated ? 1 : 0;

        return $port->id;
    }

    /**
     * @param  array<string, mixed>  $lookups
     * @param  array<string, int>  $stats
     */
    private function resolveJobType(string $name, array &$lookups, array &$stats): ?int
    {
        if ($name === '') {
            return null;
        }

        $key = strtolower($name);
        if (isset($lookups['jobTypes'][$key])) {
            return $lookups['jobTypes'][$key];
        }

        $jobType = ChevronJobType::firstOrCreate(['name' => $name], ['is_active' => true]);

        $lookups['jobTypes'][$key] = $jobType->id;
        $stats['new_job_types'] += $jobType->wasRecentlyCreated ? 1 : 0;

        return $jobType->id;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function validateFile(Request $request): void
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 10 MB.',
            ]
        );
    }

    private function zipError(): JsonResponse
    {
        return response()->json([
            'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Please enable it in cPanel → MultiPHP INI Editor, or upload a .csv file instead.',
        ], 500);
    }
}

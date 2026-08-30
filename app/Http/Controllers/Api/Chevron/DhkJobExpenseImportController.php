<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Head Office / Dhaka (branch "DK") job expense importer.
 *
 * Handles the "DHK JOB INFO_EXP" workbook produced by the Dhaka office.
 * Deliberately separate from JobExpenseImportController (Chittagong) so the
 * Chittagong pipeline is never affected by changes made here.
 */
class DhkJobExpenseImportController extends Controller
{
    private const CHUNK_SIZE = 200;

    private const HEAD_OFFICE_BRANCH_ID = 5;

    private const PREVIEW_LIMIT = 100;

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(120);

        try {
            $rows = $this->loadRows($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $groups = $this->parseAndGroupRows($rows);
        $lookups = $this->buildLookups();

        $summary = [
            'total_groups'   => count($groups),
            'total_items'    => array_sum(array_map(fn ($g) => count($g['items']), $groups)),
            'new_jobs'       => 0,
            'new_customers'  => 0,
            'new_categories' => 0,
            'new_heads'      => 0,
            'new_employees'  => 0,
        ];

        $seenJobs = [];
        $seenCustomers = [];
        $seenEmployees = [];
        $seenHeads = [];

        foreach ($groups as $group) {
            $jobKey = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);
            $empKey = $this->normalizeName($group['employee_name']);

            if (! isset($lookups['jobs'][$jobKey]) && ! isset($seenJobs[$jobKey])) {
                $seenJobs[$jobKey] = true;
                $summary['new_jobs']++;
            }
            if ($group['importer_name'] !== '' && ! isset($lookups['customers'][$importerKey]) && ! isset($seenCustomers[$importerKey])) {
                $seenCustomers[$importerKey] = true;
                $summary['new_customers']++;
            }
            if ($empKey !== '' && ! isset($lookups['employeesByName'][$empKey]) && ! isset($seenEmployees[$empKey])) {
                $seenEmployees[$empKey] = true;
                $summary['new_employees']++;
            }

            foreach ($group['items'] as $item) {
                $headKey = strtolower($item['expense_head']);

                if ($item['expense_head'] !== '' && ! isset($lookups['heads'][$headKey]) && ! isset($seenHeads[$headKey])) {
                    $seenHeads[$headKey] = true;
                    $summary['new_heads']++;
                }
            }
        }

        $previewGroups = [];
        foreach (array_slice($groups, 0, self::PREVIEW_LIMIT) as $group) {
            $group['new_job'] = ! isset($lookups['jobs'][strtolower($group['job_no'])]);
            $group['new_customer'] = $group['importer_name'] !== '' && ! isset($lookups['customers'][strtolower($group['importer_name'])]);
            $group['new_employee'] = ! isset($lookups['employeesByName'][$this->normalizeName($group['employee_name'])]);
            $group['total_amount'] = array_sum(array_column($group['items'], 'amount'));
            $previewGroups[] = $group;
        }

        return response()->json([
            'summary' => $summary,
            'preview' => $previewGroups,
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(0);

        try {
            $rows = $this->loadRows($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $groups = $this->parseAndGroupRows($rows);

        if (empty($groups)) {
            return response()->json([
                'message'  => 'No valid expense rows found in the file.',
                'inserted' => 0,
            ]);
        }

        $lookups = $this->buildLookups();
        $entityStats = $this->resolveEntities($lookups, $groups);
        $persistStats = $this->persistExpenses($lookups, $groups);

        return response()->json([
            'message'           => "{$persistStats['inserted']} expense(s) imported successfully.",
            'inserted_expenses' => $persistStats['inserted'],
            'inserted_items'    => $persistStats['inserted_items'],
            'skipped'           => $persistStats['skipped'],
            'new_jobs'          => $entityStats['new_jobs'],
            'new_customers'     => $entityStats['new_customers'],
            'new_categories'    => $entityStats['new_categories'],
            'new_heads'         => $entityStats['new_heads'],
            'new_employees'     => $entityStats['new_employees'],
        ]);
    }

    // ─── Spreadsheet loading ─────────────────────────────────────────────────

    private function loadRows(string $pathname): array
    {
        $spreadsheet = IOFactory::load($pathname);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        if ($highestRow <= 1) {
            return [];
        }

        // DHK workbook columns: A=SL NO, B=Job NO, C=Importer, D=Exp Date,
        // E=Exp. By, F=Expense Head, G=Receiptable, H=Amount, I=Complition,
        // J=Complition Date, K=Remarks
        return $sheet->rangeToArray('A1:K'.$highestRow, null, true, true, false);
    }

    // ─── Parsing ─────────────────────────────────────────────────────────────

    private function parseAndGroupRows(array $rows): array
    {
        $groups = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $jobNo = trim($row[1] ?? '');
            if ($jobNo === '') {
                continue;
            }

            $expDate = $this->parseDate($row[3] ?? '');
            if ($expDate === null) {
                continue;
            }

            $employee = $this->parseEmployee($row[4] ?? '');
            $empKey = $employee['name'] === '' ? '' : $this->normalizeName($employee['name']);

            $groupKey = strtolower($jobNo).'|'.$expDate.'|'.$empKey;

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'job_no'        => $jobNo,
                    'importer_name' => trim($row[2] ?? ''),
                    'date'          => $expDate,
                    'employee_name' => $employee['name'],
                    'employee_code' => $employee['code'],
                    'items'         => [],
                ];
            }

            $groups[$groupKey]['items'][] = [
                'category_name' => '',
                'expense_head'  => trim($row[5] ?? ''),
                'receiptable'   => $this->mapReceiptable($row[6] ?? ''),
                'amount'        => $this->parseAmount($row[7] ?? null),
                'note'          => trim($row[10] ?? '') ?: null,
            ];
        }

        return array_values($groups);
    }

    /**
     * Split the "EXP. BY" column into an employee code and a display name.
     *
     * The Dhaka workbook stores employees as one string such as
     * "CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)".
     * Some rows omit the code entirely and just hold the name.
     *
     * @return array{code: string, name: string}
     */
    private function parseEmployee(mixed $value): array
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return ['code' => '', 'name' => ''];
        }

        if (str_contains($raw, '_')) {
            $parts = explode('_', $raw, 3);

            return [
                'code' => trim($parts[0]),
                'name' => trim($parts[1] ?? $raw),
            ];
        }

        return ['code' => '', 'name' => $raw];
    }

    private function normalizeName(string $name): string
    {
        $norm = strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
        $norm = str_replace('.', '', $norm);
        $norm = trim($norm);

        foreach (['mr ', 'md ', 'mrs ', 'ms ', 'sir '] as $prefix) {
            if (str_starts_with($norm, $prefix)) {
                $norm = trim(substr($norm, strlen($prefix)));
            }
        }

        return $norm;
    }

    // ─── Lookups ──────────────────────────────────────────────────────────────

    private function buildLookups(): array
    {
        $employees = Employee::get(['id', 'code', 'name'])
            ->mapWithKeys(fn ($e) => [strtolower(trim($e->code ?? '')) => $e->id])
            ->all();

        $customers = ChevronCustomer::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $heads = ChevronExpenseHead::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        // Name-only rows must reuse an existing employee wherever it lives.
        $employeesByName = Employee::get(['id', 'name'])
            ->mapWithKeys(fn ($e) => [$this->normalizeName($e->name) => $e->id])
            ->all();

        $existingExpenses = DB::table('chevron_job_expenses')
            ->where('branch_id', self::HEAD_OFFICE_BRANCH_ID)
            ->select('job_no', 'date', 'employee_id')
            ->get()
            ->mapWithKeys(fn ($e) => [
                strtolower($e->job_no ?? '').'|'.($e->date ?? '').'|'.($e->employee_id ?? '') => true,
            ])
            ->all();

        $jobs = ChevronJob::pluck('id', 'job_no')
            ->mapWithKeys(fn ($id, $no) => [strtolower(trim($no)) => $id])
            ->all();

        $headEmpPairs = DB::table('chevron_expense_head_employees')
            ->get(['expense_head_id', 'employee_id'])
            ->mapWithKeys(fn ($r) => ["{$r->expense_head_id}:{$r->employee_id}" => true])
            ->all();

        return compact('jobs', 'customers', 'heads', 'employees', 'employeesByName', 'existingExpenses', 'headEmpPairs');
    }

    // ─── Entity resolution ────────────────────────────────────────────────────

    private function resolveEntities(array &$lookups, array $groups): array
    {
        $stats = [
            'new_jobs'       => 0,
            'new_customers'  => 0,
            'new_categories' => 0,
            'new_heads'      => 0,
            'new_employees'  => 0,
        ];

        $uniqueImporters = [];
        $uniqueJobs = [];
        $uniqueHeads = [];
        $uniqueEmployeesByCode = [];
        $uniqueEmployeesByName = [];

        foreach ($groups as $group) {
            $jobKey = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);
            $empNameKey = $this->normalizeName($group['employee_name']);
            $empCodeKey = strtolower($group['employee_code']);

            if (! isset($lookups['jobs'][$jobKey])) {
                $uniqueJobs[$jobKey] = ['job_no' => $group['job_no'], 'importer' => $group['importer_name']];
            }
            if ($group['importer_name'] !== '' && ! isset($lookups['customers'][$importerKey])) {
                $uniqueImporters[$importerKey] = $group['importer_name'];
            }

            if ($empCodeKey !== '' && ! isset($lookups['employees'][$empCodeKey]) && ! isset($uniqueEmployeesByCode[$empCodeKey])) {
                $uniqueEmployeesByCode[$empCodeKey] = $group;
            } elseif ($empCodeKey === '' && $empNameKey !== '' && ! isset($lookups['employeesByName'][$empNameKey]) && ! isset($uniqueEmployeesByName[$empNameKey])) {
                $uniqueEmployeesByName[$empNameKey] = $group;
            }

            foreach ($group['items'] as $item) {
                $headKey = strtolower($item['expense_head']);

                if ($item['expense_head'] !== '' && ! isset($lookups['heads'][$headKey])) {
                    $uniqueHeads[$headKey] = $item['expense_head'];
                }
            }
        }

        DB::transaction(function () use (&$lookups, &$stats, $uniqueImporters, $uniqueJobs, $uniqueHeads, $uniqueEmployeesByCode, $uniqueEmployeesByName, $groups) {

            // 1. Customers (no deps)
            foreach ($uniqueImporters as $key => $name) {
                $customer = ChevronCustomer::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $customer) {
                    $customer = ChevronCustomer::create([
                        'id_prefix'   => 'CUS-',
                        'customer_id' => ChevronCustomer::generateCustomerId('CUS-'),
                        'name'        => $name,
                        'status'      => 'Active',
                    ]);
                    $stats['new_customers']++;
                }
                $lookups['customers'][$key] = $customer->id;
            }

            // 2. Jobs (needs customer_id)
            foreach ($uniqueJobs as $key => $data) {
                $importerKey = strtolower($data['importer']);
                $customerId = $lookups['customers'][$importerKey] ?? null;

                $job = ChevronJob::whereRaw('LOWER(TRIM(job_no)) = ?', [$key])->first();
                if (! $job) {
                    $job = ChevronJob::create([
                        'job_no'      => $data['job_no'],
                        'branch_id'   => self::HEAD_OFFICE_BRANCH_ID,
                        'service_id'  => 1,
                        'status'      => 'Active',
                        'party_name'  => $data['importer'],
                        'customer_id' => $customerId,
                    ]);
                    $stats['new_jobs']++;
                }
                $lookups['jobs'][$key] = $job->id;
            }

            // 3. Employees (code takes priority, then normalized name)
            foreach ($uniqueEmployeesByCode as $code => $group) {
                $employee = Employee::whereRaw('LOWER(TRIM(code)) = ?', [$code])->first();
                if (! $employee) {
                    $employee = Employee::create([
                        'code'           => $group['employee_code'],
                        'name'           => $group['employee_name'],
                        'type'           => 'prepare',
                        'current_status' => 'Active',
                        'is_active'      => true,
                    ]);
                    $stats['new_employees']++;
                }
                $lookups['employees'][$code] = $employee->id;
                $lookups['employeesByName'][$this->normalizeName($employee->name)] = $employee->id;
            }

            foreach ($uniqueEmployeesByName as $nameKey => $group) {
                if (isset($lookups['employeesByName'][$nameKey])) {
                    continue;
                }

                $employee = Employee::whereRaw('LOWER(TRIM(name)) = ?', [$nameKey])->first();
                if (! $employee) {
                    $employee = Employee::create([
                        'name'           => $group['employee_name'],
                        'type'           => 'prepare',
                        'current_status' => 'Active',
                        'is_active'      => true,
                    ]);
                    $stats['new_employees']++;
                }
                $lookups['employeesByName'][$nameKey] = $employee->id;
            }

            // 4. Expense heads (under a shared "General" category when new)
            $fallbackCatId = null;
            foreach ($uniqueHeads as $key => $name) {
                $head = ChevronExpenseHead::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $head) {
                    if ($fallbackCatId === null) {
                        $fallback = ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', ['miscellaneous'])->first()
                            ?? ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', ['general'])->first()
                            ?? ChevronExpenseCategory::create([
                                'name'      => 'General',
                                'is_bill'   => false,
                                'is_job'    => true,
                                'is_active' => true,
                            ]);
                        $fallbackCatId = $fallback->id;

                        if ($fallback->wasRecentlyCreated) {
                            $stats['new_categories']++;
                        }
                    }

                    $head = ChevronExpenseHead::create([
                        'name'                => $name,
                        'expense_category_id' => $fallbackCatId,
                        'type'                => 'External',
                        'is_active'           => true,
                    ]);
                    $stats['new_heads']++;
                }
                $lookups['heads'][$key] = $head->id;
            }

            // 5. Head ↔ employee pivot
            $pivotRows = [];
            foreach ($groups as $group) {
                $empId = $this->resolveEmployeeId($lookups, $group);
                if ($empId === null) {
                    continue;
                }

                foreach ($group['items'] as $item) {
                    $headKey = strtolower($item['expense_head']);
                    $headId = $lookups['heads'][$headKey] ?? null;
                    if ($headId === null) {
                        continue;
                    }

                    $pairKey = "{$headId}:{$empId}";
                    if (! isset($lookups['headEmpPairs'][$pairKey])) {
                        $pivotRows[] = ['expense_head_id' => $headId, 'employee_id' => $empId];
                        $lookups['headEmpPairs'][$pairKey] = true;
                    }
                }
            }

            if (! empty($pivotRows)) {
                DB::table('chevron_expense_head_employees')->insertOrIgnore(array_unique($pivotRows, SORT_REGULAR));
            }
        });

        return $stats;
    }

    private function resolveEmployeeId(array &$lookups, array $group): ?int
    {
        $code = strtolower($group['employee_code']);
        if ($code !== '' && isset($lookups['employees'][$code])) {
            return $lookups['employees'][$code];
        }

        $nameKey = $this->normalizeName($group['employee_name']);
        if ($nameKey !== '' && isset($lookups['employeesByName'][$nameKey])) {
            return $lookups['employeesByName'][$nameKey];
        }

        return null;
    }

    // ─── Persistence ──────────────────────────────────────────────────────────

    private function persistExpenses(array &$lookups, array $groups): array
    {
        $stats = ['inserted' => 0, 'inserted_items' => 0, 'skipped' => 0];

        // Pre-generate the starting expense number once with a single lock query
        // instead of calling generateExpenseNo() (lockForUpdate) per row.
        $expenseCounter = DB::transaction(fn () => (DB::table('chevron_job_expenses')
            ->lockForUpdate()
            ->max(DB::raw('CAST(SUBSTRING(expense_no, 4) AS UNSIGNED)')) ?? 0) + 1
        );

        $now = now();

        foreach (array_chunk($groups, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, &$lookups, &$stats, &$expenseCounter, $now) {
                $itemRows = [];

                foreach ($chunk as $group) {
                    $jobKey = strtolower($group['job_no']);
                    $jobId = $lookups['jobs'][$jobKey] ?? null;
                    $empId = $this->resolveEmployeeId($lookups, $group);

                    $dedupKey = $jobKey.'|'.$group['date'].'|'.($empId ?? '');
                    if (isset($lookups['existingExpenses'][$dedupKey])) {
                        $stats['skipped']++;

                        continue;
                    }

                    $totalAmount = array_sum(array_column($group['items'], 'amount'));
                    $expenseNo = 'EXP'.str_pad($expenseCounter++, 6, '0', STR_PAD_LEFT);

                    $expenseId = DB::table('chevron_job_expenses')->insertGetId([
                        'expense_no'            => $expenseNo,
                        'branch_id'             => self::HEAD_OFFICE_BRANCH_ID,
                        'job_id'                => $jobId,
                        'job_no'                => $group['job_no'],
                        'employee_id'           => $empId,
                        'date'                  => $group['date'],
                        'total_expense_amount'  => $totalAmount,
                        'total_approved_amount' => $totalAmount,
                        'status'                => 'Approved',
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ]);

                    $lookups['existingExpenses'][$dedupKey] = true;
                    $stats['inserted']++;

                    foreach ($group['items'] as $item) {
                        $headKey = strtolower($item['expense_head']);
                        $itemRows[] = [
                            'job_expense_id'  => $expenseId,
                            'expense_head_id' => $lookups['heads'][$headKey] ?? null,
                            'receiptable'     => $item['receiptable'],
                            'expense_amount'  => $item['amount'],
                            'approved_amount' => $item['amount'],
                            'expense_date'    => $group['date'],
                            'note'            => $item['note'],
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                        $stats['inserted_items']++;
                    }
                }

                // Bulk insert all items for this chunk in one query
                if (! empty($itemRows)) {
                    DB::table('chevron_job_expense_items')->insert($itemRows);
                }
            });
        }

        return $stats;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // Dhaka workbook exposes dates as m/d/Y strings (e.g. "1/4/2026").
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function mapReceiptable(mixed $value): string
    {
        return strtolower(trim((string) $value)) === 'yes' ? 'Yes' : 'No';
    }

    private function parseAmount(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        // The Dhaka workbook pads amounts with spaces and uses comma
        // thousand separators ("  5,000.00 "), which fail is_numeric().
        $clean = preg_replace('/[\s,]/', '', (string) $value) ?? '';

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    private function validateFile(Request $request): void
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:20480'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 20 MB.',
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

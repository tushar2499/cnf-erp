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
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Chittagong branch (ID 6) job expense importer for the "Job Report CTG JOB EXP" workbook.
 *
 * Reads only the "Job_Exp_Statement" sheet. The header row is located by name, so the
 * leading "Source_Name" column and the rows above the header do not matter.
 *
 * Rules:
 *   - A job missing from chevron_jobs is created with basic data (job no, branch, service, importer),
 *     then all of its expenses are added.
 *   - Expenses are grouped by job + date + employee. Employees are always the central "employees"
 *     table; chevron_employees is never read or written.
 *   - A job + date that already has an expense in this branch is skipped as a whole, whatever the
 *     employee, so uploading the same file twice never duplicates. The check ignores the employee
 *     on purpose: older rows hold ids that do not reliably point at "employees". All groups of one
 *     job + date are saved in the same transaction, so a failed run can be re-uploaded safely.
 *   - Rows that cannot be imported are skipped and reported by reason.
 */
class CtgJobExpenseStatementImportController extends Controller
{
    private const SHEET_NAME = 'Job_Exp_Statement';

    private const BRANCH_ID = 6;

    private const CHUNK_SIZE = 200;

    private const PREVIEW_LIMIT = 100;

    private const SKIP_SAMPLE_LIMIT = 10;

    private const FALLBACK_CATEGORY = 'General';

    private const SERVICE_IMPORT = 1;

    private const SERVICE_EXPORT = 2;

    /** Normalized header text of every column we read. */
    private const HEADERS = [
        'job_no'      => 'jobno',
        'importer'    => 'importername',
        'date'        => 'expdate',
        'employee'    => 'expby',
        'category'    => 'category',
        'head'        => 'expensehead',
        'receiptable' => 'receiptable',
        'amount'      => 'amount',
        'note'        => 'remarks',
    ];

    private const REQUIRED_COLUMNS = ['job_no', 'date', 'head', 'amount'];

    // ─── Endpoints ────────────────────────────────────────────────────────────

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (!extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(300);
        ini_set('memory_limit', '768M');

        try {
            [$groups, $skipped] = $this->parseWorkbook($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: ' . $e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();

        $summary = [
            'total_groups'       => count($groups),
            'total_items'        => array_sum(array_map(fn($g) => count($g['items']), $groups)),
            'existing_expenses'  => 0,
            'expenses_to_insert' => 0,
            'new_jobs'           => 0,
            'new_customers'      => 0,
            'new_heads'          => 0,
            'new_employees'      => 0,
        ];

        $seenJobs      = [];
        $seenCustomers = [];
        $seenEmployees = [];
        $seenHeads     = [];
        $previewGroups = [];

        foreach ($groups as $group) {
            $exists = isset($lookups['existingExpenses'][$this->jobDateKey($group)]);
            $summary[$exists ? 'existing_expenses' : 'expenses_to_insert']++;

            $jobKey      = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);
            $newJob      = !isset($lookups['jobs'][$jobKey]);
            $newCustomer = $group['importer_name'] !== '' && !isset($lookups['customers'][$importerKey]);
            $newEmployee = $group['employee_key'] !== '' && !isset($lookups['employeesByName'][$group['employee_key']]);

            if (!$exists) {

                if ($newJob && !isset($seenJobs[$jobKey])) {
                    $seenJobs[$jobKey] = true;
                    $summary['new_jobs']++;
                }

                if ($newCustomer && !isset($seenCustomers[$importerKey])) {
                    $seenCustomers[$importerKey] = true;
                    $summary['new_customers']++;
                }

                if ($newEmployee && !isset($seenEmployees[$group['employee_key']])) {
                    $seenEmployees[$group['employee_key']] = true;
                    $summary['new_employees']++;
                }

                foreach ($group['items'] as $item) {
                    $headKey = strtolower($item['expense_head']);

                    if (!isset($lookups['heads'][$headKey]) && !isset($seenHeads[$headKey])) {
                        $seenHeads[$headKey] = true;
                        $summary['new_heads']++;
                    }

                }

            }

            if (count($previewGroups) < self::PREVIEW_LIMIT) {
                $previewGroups[] = $group + [
                    'exists'       => $exists,
                    'new_job'      => $newJob,
                    'new_customer' => $newCustomer,
                    'new_employee' => $newEmployee,
                    'total_amount' => round(array_sum(array_column($group['items'], 'amount')), 2),
                ];
            }

        }

        return response()->json([
            'summary'      => $summary,
            'skipped_rows' => $skipped,
            'preview'      => $previewGroups,
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (!extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(0);
        ini_set('memory_limit', '768M');

        try {
            [$groups, $skipped] = $this->parseWorkbook($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: ' . $e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();

        $pending = array_values(array_filter(
            $groups,
            fn($group) => !isset($lookups['existingExpenses'][$this->jobDateKey($group)])
        ));
        $skippedExisting = count($groups) - count($pending);

        $entityStats  = $this->resolveEntities($lookups, $pending);
        $persistStats = $this->persistExpenses($lookups, $pending);

        return response()->json([
            'message' => "{$persistStats['inserted']} expense(s) imported successfully.",
            'inserted_expenses' => $persistStats['inserted'],
            'inserted_items'    => $persistStats['inserted_items'],
            'skipped_existing'  => $skippedExisting,
            'skipped_rows'      => $skipped,
            'new_jobs'          => $entityStats['new_jobs'],
            'new_customers'     => $entityStats['new_customers'],
            'new_categories'    => $entityStats['new_categories'],
            'new_heads'         => $entityStats['new_heads'],
            'new_employees'     => $entityStats['new_employees'],
        ]);
    }

// ─── Spreadsheet loading ─────────────────────────────────────────────────

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, array{count: int, rows: array<int, int>}>}
     */
    private function parseWorkbook(string $path): array
    {
        $rows = $this->loadRows($path);

        return $this->parseAndGroupRows($rows);
    }

    /**
     * Load the statement sheet only (columns A–N, values only) to keep memory low on large workbooks.
     * Formula cells are replaced by their cached value, because the lookup sheets are not loaded.
     *
     * @return array<int, array<int, mixed>>
     */
    private function loadRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);

        if (!in_array(self::SHEET_NAME, $reader->listWorksheetNames($path), true)) {
            throw new \RuntimeException('Sheet "' . self::SHEET_NAME . '" was not found in the workbook.');
        }

        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([self::SHEET_NAME]);
        $reader->setReadFilter(new class implements IReadFilter
        {
            /** @var array<int, string> */
            private array $columns;

            public function __construct()
            {
                $this->columns = range('A', 'N');
            }

            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
            {
                return in_array($columnAddress, $this->columns, true);
            }

        });

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getSheet(0);
        $rows        = $sheet->rangeToArray('A1:N' . $sheet->getHighestDataRow(), null, false, false, false);

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

// ─── Parsing ─────────────────────────────────────────────────────────────

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, array{count: int, rows: array<int, int>}>}
     */
    private function parseAndGroupRows(array $rows): array
    {
        [$headerIndex, $columns] = $this->locateColumns($rows);

        $groups       = [];
        $jobImporters = [];
        $skipped      = [];

        foreach ($rows as $index => $row) {
            if ($index <= $headerIndex) {
                continue;
            }

            $sheetRow  = $index + 1;
            $jobNo     = $this->cellText($row, $columns['job_no']);
            $head      = $this->cellText($row, $columns['head']);
            $rawAmount = $this->cellText($row, $columns['amount']);

            if ($jobNo === '' && $head === '' && $rawAmount === '') {
                continue;
            }

            if ($jobNo === '') {
                $this->recordSkip($skipped, 'missing_job_no', $sheetRow);

                continue;
            }

            $date = $columns['date'] === null ? null : $this->parseDate($row[$columns['date']] ?? null);
            if ($date === null) {
                $this->recordSkip($skipped, 'invalid_date', $sheetRow);

                continue;
            }

            if ($head === '') {
                $this->recordSkip($skipped, 'missing_expense_head', $sheetRow);

                continue;
            }

            $amount = $this->parseAmount($rawAmount);
            if ($amount === null) {
                $this->recordSkip($skipped, 'invalid_amount', $sheetRow);

                continue;
            }

            $jobKey   = strtolower($jobNo);
            $importer = $this->cellText($row, $columns['importer']);
            if ($importer !== '' && !str_starts_with($importer, '#') && !isset($jobImporters[$jobKey])) {
                $jobImporters[$jobKey] = $importer;
            }

            $employeeName = $this->cleanEmployeeName($this->cellText($row, $columns['employee']));
            $employeeKey  = $employeeName === '' ? '' : $this->normalizeName($employeeName);
            $groupKey     = $jobKey . '|' . $date . '|' . $employeeKey;

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'job_no'        => $jobNo,
                    'importer_name' => '',
                    'date'          => $date,
                    'employee_name' => $employeeName,
                    'employee_key'  => $employeeKey,
                    'items'         => [],
                ];
            }

            $groups[$groupKey]['items'][] = [
                'category_name' => $this->cellText($row, $columns['category']),
                'expense_head'  => $head,
                'receiptable'   => strtolower($this->cellText($row, $columns['receiptable'])) === 'yes' ? 'Yes' : 'No',
                'amount'        => $amount,
                'note'          => $this->cellText($row, $columns['note']) ?: null,
            ];
        }

        foreach ($groups as $key => $group) {
            $groups[$key]['importer_name'] = $jobImporters[strtolower($group['job_no'])] ?? '';
        }

        return [array_values($groups), $skipped];
    }

    /**
     * Find the header row (first 10 rows) and map each field to its column index.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: int, 1: array<string, int|null>}
     */
    private function locateColumns(array $rows): array
    {
        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            $normalized = array_map(
                fn($value) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $value)),
                $row
            );

            if (!in_array(self::HEADERS['job_no'], $normalized, true) || !in_array(self::HEADERS['amount'], $normalized, true)) {
                continue;
            }

            $columns = [];
            foreach (self::HEADERS as $field => $header) {
                $position        = array_search($header, $normalized, true);
                $columns[$field] = $position === false ? null : $position;
            }

            $missing = array_filter(self::REQUIRED_COLUMNS, fn($field) => $columns[$field] === null);
            if ($missing !== []) {
                throw new \RuntimeException('Missing column(s) in the header row: ' . implode(', ', array_map(fn($field) => self::HEADERS[$field], $missing)) . '.');
            }

            return [$index, $columns];
        }

        throw new \RuntimeException('Header row not found. Expected columns "Job NO:" and "AMOUNT" in the first 10 rows of sheet "' . self::SHEET_NAME . '".');
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

    /**
     * @param  array<int, mixed>  $row
     */
    private function cellText(array $row, ?int $column): string
    {
        return $column === null ? '' : trim((string) ($row[$column] ?? ''));
    }

// ─── Lookups ──────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function buildLookups(): array
    {
        $jobs = ChevronJob::pluck('id', 'job_no')
            ->mapWithKeys(fn($id, $no) => [strtolower(trim($no)) => $id])
            ->all();

        $customers = ChevronCustomer::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $heads = ChevronExpenseHead::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $employeesByName = [];
        foreach (Employee::get(['id', 'name']) as $employee) {
            $nameKey = $this->normalizeName($employee->name);
            if ($nameKey !== '') {
                $employeesByName[$nameKey] ??= $employee->id;
            }

        }

        $existingExpenses = [];
        foreach (DB::table('chevron_job_expenses')->where('branch_id', self::BRANCH_ID)->select('job_no', 'date')->cursor() as $expense) {
            $existingExpenses[strtolower(trim((string) $expense->job_no)) . '|' . $expense->date] = true;
        }

        $headEmpPairs = DB::table('chevron_expense_head_employees')
            ->get(['expense_head_id', 'employee_id'])
            ->mapWithKeys(fn($row) => ["{$row->expense_head_id}:{$row->employee_id}" => true])
            ->all();

        return compact('jobs', 'customers', 'heads', 'employeesByName', 'existingExpenses', 'headEmpPairs');
    }

    /**
     * @param  array<string, mixed>  $group
     */
    private function jobDateKey(array $group): string
    {
        return strtolower($group['job_no']) . '|' . $group['date'];
    }

// ─── Entity resolution ────────────────────────────────────────────────────

    /**
     * Create the customers, jobs, employees, heads and categories that the pending groups need.
     *
     * @param  array<string, mixed>  $lookups
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<string, int>
     */
    private function resolveEntities(array &$lookups, array $groups): array
    {
        $stats = ['new_jobs' => 0, 'new_customers' => 0, 'new_categories' => 0, 'new_heads' => 0, 'new_employees' => 0];

        $uniqueImporters = [];
        $uniqueJobs      = [];
        $uniqueHeads     = [];
        $uniqueEmployees = [];

        foreach ($groups as $group) {
            $jobKey      = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);

            if (!isset($lookups['jobs'][$jobKey])) {
                $uniqueJobs[$jobKey] = ['job_no' => $group['job_no'], 'importer' => $group['importer_name']];
            }

            if ($group['importer_name'] !== '' && !isset($lookups['customers'][$importerKey])) {
                $uniqueImporters[$importerKey] = $group['importer_name'];
            }

            if ($group['employee_key'] !== '' && !isset($uniqueEmployees[$group['employee_key']])) {
                $uniqueEmployees[$group['employee_key']] = $group['employee_name'];
            }

            foreach ($group['items'] as $item) {
                $headKey = strtolower($item['expense_head']);
                if (!isset($lookups['heads'][$headKey]) && !isset($uniqueHeads[$headKey])) {
                    $uniqueHeads[$headKey] = ['name' => $item['expense_head'], 'category' => $item['category_name']];
                }

            }

        }

        DB::transaction(function () use (&$lookups, &$stats, $uniqueImporters, $uniqueJobs, $uniqueHeads, $uniqueEmployees, $groups) {

// 1. Customers
            foreach ($uniqueImporters as $key => $name) {
                $customer = ChevronCustomer::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (!$customer) {
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

// 2. Jobs missing from chevron_jobs — created with basic data only
            foreach ($uniqueJobs as $key => $data) {
                $job = ChevronJob::whereRaw('LOWER(TRIM(job_no)) = ?', [$key])->first();
                if (!$job) {
                    $job = ChevronJob::create([
                        'job_no'      => $data['job_no'],
                        'branch_id'   => self::BRANCH_ID,
                        'service_id'  => $this->serviceIdFor($data['job_no']),
                        'status'      => 'Active',
                        'party_name'  => $data['importer'] !== '' ? $data['importer'] : null,
                        'customer_id' => $lookups['customers'][strtolower($data['importer'])] ?? null,
                    ]);
                    $stats['new_jobs']++;
                }

                $lookups['jobs'][$key] = $job->id;
            }

// 3. Employees (central "employees" table) — reuse by name, otherwise create
            foreach ($uniqueEmployees as $nameKey => $displayName) {
                if (isset($lookups['employeesByName'][$nameKey])) {
                    continue;
                }

                $employee = Employee::create([
                    'name'           => $displayName,
                    'type'           => 'prepare',
                    'current_status' => 'Active',
                    'is_active'      => true,
                ]);
                $stats['new_employees']++;
                $lookups['employeesByName'][$nameKey] = $employee->id;
            }

            // 4. Expense heads (category from the sheet, "General" when blank)
            $categoryIds = [];

            foreach ($uniqueHeads as $key => $data) {
                $head = ChevronExpenseHead::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();

                if (!$head) {
                    $categoryName = $data['category'] !== '' ? $data['category'] : self::FALLBACK_CATEGORY;
                    $categoryKey  = strtolower($categoryName);

                    if (!isset($categoryIds[$categoryKey])) {
                        $category = ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', [$categoryKey])->first() ?? ChevronExpenseCategory::create([
                            'name'      => $categoryName,
                            'is_bill'   => false,
                            'is_job'    => true,
                            'is_active' => true,
                        ]);

                        if ($category->wasRecentlyCreated) {
                            $stats['new_categories']++;
                        }

                        $categoryIds[$categoryKey] = $category->id;
                    }

                    $head = ChevronExpenseHead::create([
                        'name'                => $data['name'],
                        'expense_category_id' => $categoryIds[$categoryKey],
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
                $employeeId = $lookups['employeesByName'][$group['employee_key']] ?? null;

                if ($employeeId === null) {
                    continue;
                }

                foreach ($group['items'] as $item) {
                    $headId  = $lookups['heads'][strtolower($item['expense_head'])] ?? null;
                    $pairKey = "{$headId}:{$employeeId}";

                    if ($headId !== null && !isset($lookups['headEmpPairs'][$pairKey])) {
                        $pivotRows[$pairKey]               = ['expense_head_id' => $headId, 'employee_id' => $employeeId];
                        $lookups['headEmpPairs'][$pairKey] = true;
                    }

                }

            }

            if ($pivotRows !== []) {
                DB::table('chevron_expense_head_employees')->insertOrIgnore(array_values($pivotRows));
            }

        });

        return $stats;
    }

    private function serviceIdFor(string $jobNo): ?int
    {
        return match (true) {
            (bool) preg_match('/_IM[A-Z]+-/i', $jobNo) => self::SERVICE_IMPORT,
            (bool) preg_match('/_EX[A-Z]+-/i', $jobNo) => self::SERVICE_EXPORT,
            default                                    => null,
        };
    }

// ─── Persistence ──────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $lookups
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<string, int>
     */
    private function persistExpenses(array &$lookups, array $groups): array
    {
        $stats = ['inserted' => 0, 'inserted_items' => 0];

        if ($groups === []) {
            return $stats;
        }

        $expenseCounter = DB::transaction(fn() => (DB::table('chevron_job_expenses')
                ->lockForUpdate()
                ->max(DB::raw('CAST(SUBSTRING(expense_no, 4) AS UNSIGNED)')) ?? 0) + 1
        );

        $now = now();

        // Keep every group of one job + date in the same transaction (see class docblock).
        $buckets = [];

        foreach ($groups as $group) {
            $buckets[$this->jobDateKey($group)][] = $group;
        }

        foreach (array_chunk($buckets, self::CHUNK_SIZE) as $bucketChunk) {
            $chunk = array_merge(...$bucketChunk);

            DB::transaction(function () use ($chunk, &$lookups, &$stats, &$expenseCounter, $now) {
                $itemRows = [];

                foreach ($chunk as $group) {
                    $totalAmount = round(array_sum(array_column($group['items'], 'amount')), 2);

                    $expenseId = DB::table('chevron_job_expenses')->insertGetId([
                        'expense_no'            => 'EXP' . str_pad((string) $expenseCounter++, 6, '0', STR_PAD_LEFT),
                        'branch_id'             => self::BRANCH_ID,
                        'job_id'                => $lookups['jobs'][strtolower($group['job_no'])] ?? null,
                        'job_no'                => $group['job_no'],
                        'employee_id'           => $lookups['employeesByName'][$group['employee_key']] ?? null,
                        'date'                  => $group['date'],
                        'total_expense_amount'  => $totalAmount,
                        'total_approved_amount' => $totalAmount,
                        'status'                => 'Approved',
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ]);
                    $stats['inserted']++;

                    foreach ($group['items'] as $item) {
                        $itemRows[] = [
                            'job_expense_id'  => $expenseId,
                            'expense_head_id' => $lookups['heads'][strtolower($item['expense_head'])] ?? null,
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

                if ($itemRows !== []) {
                    DB::table('chevron_job_expense_items')->insert($itemRows);
                }

            });
        }

        return $stats;
    }

// ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Lower-case, dot-free, whitespace-collapsed name without honorific or "_dk" suffix, used to match employees.
     */
    private function normalizeName(string $name): string
    {
        $normalized = strtolower(trim(preg_replace('/[.\s]+/', ' ', $name) ?? $name));

        foreach (['mr ', 'md ', 'mrs ', 'ms ', 'sir '] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = trim(substr($normalized, strlen($prefix)));
            }

        }

        return trim(preg_replace('/_dk$/', '', $normalized) ?? $normalized);
    }

    private function cleanEmployeeName(string $name): string
    {
        return trim(preg_replace('/_dk$/i', '', $name) ?? $name);
    }

    private function parseAmount(string $value): ?float
    {
        $clean = preg_replace('/[\s,]/', '', $value) ?? '';

        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = null;

        if (is_numeric($value) && (float) $value > 1) {
            try {
                $date = ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception) {
                $date = null;
            }

        } else {
            foreach (['Y-m-d', 'd/M/Y', 'd-M-Y', 'd/m/Y'] as $format) {
                try {
                    $date = Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');

                    break;
                } catch (\Exception) {
                    continue;
                }

            }

        }

        $year = $date === null ? 0 : (int) substr($date, 0, 4);

        return $year >= 2000 && $year <= 2100 ? $date : null;
    }

    private function validateFile(Request $request): void
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls|max:51200'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) are allowed.',
                'file.max'      => 'The file size must not exceed 50 MB.',
            ]
        );
    }

    private function zipError(): JsonResponse
    {
        return response()->json([
            'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files.',
        ], 500);
    }

}

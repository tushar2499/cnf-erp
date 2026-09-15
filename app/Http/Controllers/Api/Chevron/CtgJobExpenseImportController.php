<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Chittagong branch (ID 6) job expense importer.
 *
 * Import order (matches business rule):
 *   1. Parse sheet 1 "EXP" → resolve/create all entities (heads, employees, pivot) → persist expenses
 *   2. Parse sheet 2 "EXP HEAD" → import any remaining heads not yet in DB (skip duplicates)
 *
 * Duplicate detection:
 *   - Expense heads : case-insensitive name match
 *   - Expenses      : job_no + date + employee_id composite key
 */
class CtgJobExpenseImportController extends Controller
{
    private const CHUNK_SIZE = 200;

    private const BRANCH_ID = 6;

    private const PREVIEW_LIMIT = 100;

    private const FALLBACK_CATEGORY = 'General';

    // ─── Endpoints ────────────────────────────────────────────────────────────

    public function preview(Request $request): JsonResponse
    {
        $this->validateFile($request);

        if (! extension_loaded('zip')) {
            return $this->zipError();
        }

        set_time_limit(120);

        try {
            [$headNames, $expenseRows] = $this->loadSheets($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();
        $groups = $this->parseAndGroupRows($expenseRows);

        $summary = [
            'total_groups'        => count($groups),
            'total_items'         => array_sum(array_map(fn ($g) => count($g['items']), $groups)),
            'new_jobs'            => 0,
            'new_customers'       => 0,
            'new_categories'      => 0,
            'new_heads_from_exp'  => 0,
            'new_employees'       => 0,
            'total_sheet2_heads'  => count($headNames),
            'new_sheet2_heads'    => 0,
        ];

        $seenJobs = [];
        $seenCustomers = [];
        $seenEmployees = [];
        $seenCategories = [];
        $seenHeads = [];

        foreach ($groups as $group) {
            $jobKey = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);
            $empKey = strtolower($group['employee_name']);

            if (! isset($lookups['jobs'][$jobKey]) && ! isset($seenJobs[$jobKey])) {
                $seenJobs[$jobKey] = true;
                $summary['new_jobs']++;
            }
            if ($group['importer_name'] !== '' && ! isset($lookups['customers'][$importerKey]) && ! isset($seenCustomers[$importerKey])) {
                $seenCustomers[$importerKey] = true;
                $summary['new_customers']++;
            }
            if ($group['employee_name'] !== '' && ! isset($lookups['employees'][$empKey]) && ! isset($seenEmployees[$empKey])) {
                $seenEmployees[$empKey] = true;
                $summary['new_employees']++;
            }
            foreach ($group['items'] as $item) {
                $catKey = strtolower($item['category_name']);
                $headKey = strtolower($item['expense_head']);

                if ($item['category_name'] !== '' && ! isset($lookups['categories'][$catKey]) && ! isset($seenCategories[$catKey])) {
                    $seenCategories[$catKey] = true;
                    $summary['new_categories']++;
                }
                if ($item['expense_head'] !== '' && ! isset($lookups['heads'][$headKey]) && ! isset($seenHeads[$headKey])) {
                    $seenHeads[$headKey] = true;
                    $summary['new_heads_from_exp']++;
                }
            }
        }

        // Sheet 2 heads — only those not already in DB and not already counted above
        $seenSheet2 = [];
        foreach ($headNames as $name) {
            $key = strtolower(trim($name));
            if ($key === '' || isset($lookups['heads'][$key]) || isset($seenHeads[$key]) || isset($seenSheet2[$key])) {
                continue;
            }
            $seenSheet2[$key] = true;
            $summary['new_sheet2_heads']++;
        }

        $previewGroups = [];
        foreach (array_slice($groups, 0, self::PREVIEW_LIMIT) as $group) {
            $group['new_job'] = ! isset($lookups['jobs'][strtolower($group['job_no'])]);
            $group['new_customer'] = $group['importer_name'] !== '' && ! isset($lookups['customers'][strtolower($group['importer_name'])]);
            $group['new_employee'] = $group['employee_name'] !== '' && ! isset($lookups['employees'][strtolower($group['employee_name'])]);
            $group['total_amount'] = array_sum(array_column($group['items'], 'amount'));
            $previewGroups[] = $group;
        }

        return response()->json([
            'summary'       => $summary,
            'preview'       => $previewGroups,
            'preview_heads' => array_slice($headNames, 0, self::PREVIEW_LIMIT),
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
            [$headNames, $expenseRows] = $this->loadSheets($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: '.$e->getMessage()], 422);
        }

        $lookups = $this->buildLookups();

        // Step 1 – expenses (creates heads + employee pivot as needed)
        $groups = $this->parseAndGroupRows($expenseRows);
        $entityStats = $this->resolveEntities($lookups, $groups);
        $persistStats = $this->persistExpenses($lookups, $groups);

        // Step 2 – sheet 2 heads (after expenses so duplicates created in step 1 are detected)
        $headStats = $this->importExpenseHeads($lookups, $headNames);

        return response()->json([
            'message'            => "{$persistStats['inserted']} expense(s) imported successfully.",
            'inserted_expenses'  => $persistStats['inserted'],
            'inserted_items'     => $persistStats['inserted_items'],
            'skipped_expenses'   => $persistStats['skipped'],
            'new_jobs'           => $entityStats['new_jobs'],
            'new_customers'      => $entityStats['new_customers'],
            'new_categories'     => $entityStats['new_categories'],
            'new_heads_from_exp' => $entityStats['new_heads'],
            'new_employees'      => $entityStats['new_employees'],
            'inserted_heads'     => $headStats['inserted'],
            'skipped_heads'      => $headStats['skipped'],
        ]);
    }

    // ─── Spreadsheet loading ─────────────────────────────────────────────────

    /** @return array{0: string[], 1: array<int, array>} */
    private function loadSheets(string $path): array
    {
        $spreadsheet = IOFactory::load($path);

        // Sheet 1 "EXP" – cols A–I  ($formatData = false → raw values, no locale-formatted date strings)
        $expSheet = $spreadsheet->getSheet(0);
        $expenseRows = $expSheet->rangeToArray('A1:I'.$expSheet->getHighestDataRow(), null, true, false, false);

        // Sheet 2 "EXP HEAD" – single col A, row 1 = header
        $headSheet = $spreadsheet->getSheet(1);
        $headNames = [];
        for ($r = 2; $r <= $headSheet->getHighestDataRow(); $r++) {
            $name = trim((string) $headSheet->getCell('A'.$r)->getValue());
            if ($name !== '') {
                $headNames[] = $name;
            }
        }

        return [$headNames, $expenseRows];
    }

    // ─── Parsing ─────────────────────────────────────────────────────────────

    private function parseAndGroupRows(array $rows): array
    {
        $groups = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue; // header
            }

            $jobNo = trim($row[1] ?? '');
            if ($jobNo === '') {
                continue;
            }

            $expDate = $this->parseDate($row[3] ?? '');
            if ($expDate === null) {
                continue;
            }

            $empName = trim($row[4] ?? '');
            $groupKey = strtolower($jobNo).'|'.$expDate.'|'.strtolower($empName);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'job_no'        => $jobNo,
                    'importer_name' => trim($row[2] ?? ''),
                    'date'          => $expDate,
                    'employee_name' => $empName,
                    'items'         => [],
                ];
            }

            $groups[$groupKey]['items'][] = [
                'category_name' => trim($row[5] ?? ''),
                'expense_head'  => trim($row[6] ?? ''),
                'receiptable'   => $this->mapReceiptable($row[7] ?? ''),
                'amount'        => is_numeric($row[8] ?? null) ? (float) $row[8] : 0.0,
            ];
        }

        return array_values($groups);
    }

    // ─── Lookups ──────────────────────────────────────────────────────────────

    private function buildLookups(): array
    {
        $jobs = ChevronJob::pluck('id', 'job_no')
            ->mapWithKeys(fn ($id, $no) => [strtolower(trim($no)) => $id])
            ->all();

        $customers = ChevronCustomer::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $categories = ChevronExpenseCategory::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $heads = ChevronExpenseHead::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $employees = DB::table('chevron_employees')
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id])
            ->all();

        $headEmpPairs = DB::table('chevron_expense_head_employees')
            ->get(['expense_head_id', 'employee_id'])
            ->mapWithKeys(fn ($r) => ["{$r->expense_head_id}:{$r->employee_id}" => true])
            ->all();

        $existingExpenses = DB::table('chevron_job_expenses')
            ->select('job_no', 'date', 'employee_id')
            ->get()
            ->mapWithKeys(fn ($e) => [
                strtolower($e->job_no ?? '').'|'.($e->date ?? '').'|'.($e->employee_id ?? '_null_') => true,
            ])
            ->all();

        return compact('jobs', 'customers', 'categories', 'heads', 'employees', 'headEmpPairs', 'existingExpenses');
    }

    // ─── Entity resolution (expenses) ─────────────────────────────────────────

    private function resolveEntities(array &$lookups, array $groups): array
    {
        $stats = [
            'new_jobs'       => 0,
            'new_customers'  => 0,
            'new_categories' => 0,
            'new_heads'      => 0,
            'new_employees'  => 0,
        ];

        $uniqueCategories = [];
        $uniqueHeadCategoryPairs = [];
        $uniqueImporters = [];
        $uniqueJobs = [];
        $uniqueEmployees = [];

        foreach ($groups as $group) {
            $jobKey = strtolower($group['job_no']);
            $importerKey = strtolower($group['importer_name']);
            $empKey = strtolower($group['employee_name']);

            if (! isset($lookups['jobs'][$jobKey])) {
                $uniqueJobs[$jobKey] = ['job_no' => $group['job_no'], 'importer' => $group['importer_name']];
            }
            if ($group['importer_name'] !== '' && ! isset($lookups['customers'][$importerKey])) {
                $uniqueImporters[$importerKey] = $group['importer_name'];
            }
            if ($group['employee_name'] !== '' && ! isset($lookups['employees'][$empKey])) {
                $uniqueEmployees[$empKey] = $group['employee_name'];
            }

            foreach ($group['items'] as $item) {
                $catKey = strtolower($item['category_name']);
                $headKey = strtolower($item['expense_head']);

                if ($item['category_name'] !== '' && ! isset($lookups['categories'][$catKey])) {
                    $uniqueCategories[$catKey] = $item['category_name'];
                }
                // First-seen category wins for a head name
                if ($item['expense_head'] !== '' && ! isset($lookups['heads'][$headKey]) && ! isset($uniqueHeadCategoryPairs[$headKey])) {
                    $uniqueHeadCategoryPairs[$headKey] = [
                        'name'          => $item['expense_head'],
                        'category_name' => $item['category_name'],
                    ];
                }
            }
        }

        DB::transaction(function () use (&$lookups, &$stats, $uniqueCategories, $uniqueImporters, $uniqueJobs, $uniqueHeadCategoryPairs, $uniqueEmployees, $groups) {

            // 1. Expense categories
            foreach ($uniqueCategories as $key => $name) {
                $cat = ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $cat) {
                    $cat = ChevronExpenseCategory::create([
                        'name'      => $name,
                        'is_bill'   => false,
                        'is_job'    => true,
                        'is_active' => true,
                    ]);
                    $stats['new_categories']++;
                }
                $lookups['categories'][$key] = $cat->id;
            }

            // 2. Customers
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

            // 3. Jobs (needs customer_id)
            foreach ($uniqueJobs as $key => $data) {
                $importerKey = strtolower($data['importer']);
                $customerId = $lookups['customers'][$importerKey] ?? null;

                $job = ChevronJob::whereRaw('LOWER(TRIM(job_no)) = ?', [$key])->first();
                if (! $job) {
                    $job = ChevronJob::create([
                        'job_no'      => $data['job_no'],
                        'branch_id'   => self::BRANCH_ID,
                        'service_id'  => 1,
                        'status'      => 'Active',
                        'party_name'  => $data['importer'],
                        'customer_id' => $customerId,
                    ]);
                    $stats['new_jobs']++;
                }
                $lookups['jobs'][$key] = $job->id;
            }

            // 4. Expense heads from sheet 1 (category from col F, fallback General)
            $fallbackCatId = null;
            foreach ($uniqueHeadCategoryPairs as $key => $data) {
                $catKey = strtolower($data['category_name']);
                $catId = $lookups['categories'][$catKey] ?? null;

                if ($catId === null) {
                    if ($fallbackCatId === null) {
                        $fallbackCatId = $this->getOrCreateFallbackCategory($lookups);
                    }
                    $catId = $fallbackCatId;
                }

                $head = ChevronExpenseHead::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $head) {
                    $head = ChevronExpenseHead::create([
                        'name'                => $data['name'],
                        'expense_category_id' => $catId,
                        'type'                => 'External',
                        'is_active'           => true,
                    ]);
                    $stats['new_heads']++;
                }
                $lookups['heads'][$key] = $head->id;
            }

            // 5. Employees
            $empCounter = null;
            foreach ($uniqueEmployees as $key => $name) {
                $employee = DB::table('chevron_employees')->whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $employee) {
                    if ($empCounter === null) {
                        $empCounter = (int) (DB::table('chevron_employees')
                            ->lockForUpdate()
                            ->where('employee_prefix', 'EMP-')
                            ->max(DB::raw('CAST(SUBSTRING(employee_id, 5) AS UNSIGNED)')) ?? 0);
                    }
                    $empCounter++;
                    $employeeId = DB::table('chevron_employees')->insertGetId([
                        'employee_prefix' => 'EMP-',
                        'employee_id'     => 'EMP-'.str_pad($empCounter, 4, '0', STR_PAD_LEFT),
                        'name'            => $name,
                        'branch_id'       => self::BRANCH_ID,
                        'is_active'       => true,
                        'type'            => 'prepare',
                        'current_status'  => 'Active',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    $lookups['employees'][$key] = $employeeId;
                    $stats['new_employees']++;
                } else {
                    $lookups['employees'][$key] = $employee->id;
                }
            }

            // 6. Head ↔ employee pivot
            $pivotRows = [];
            foreach ($groups as $group) {
                $empKey = strtolower($group['employee_name']);
                $empId = $lookups['employees'][$empKey] ?? null;
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

    // ─── Expense Head Import (sheet 2 – runs after expenses) ─────────────────

    private function importExpenseHeads(array &$lookups, array $headNames): array
    {
        $stats = ['inserted' => 0, 'skipped' => 0];
        $toInsert = [];

        foreach ($headNames as $name) {
            $key = strtolower(trim($name));
            if ($key === '' || isset($lookups['heads'][$key]) || isset($toInsert[$key])) {
                $stats['skipped']++;

                continue;
            }
            $toInsert[$key] = $name;
        }

        if (empty($toInsert)) {
            return $stats;
        }

        DB::transaction(function () use (&$lookups, &$stats, $toInsert) {
            $fallbackCatId = $this->getOrCreateFallbackCategory($lookups);

            foreach ($toInsert as $key => $name) {
                // Guard against concurrent imports
                $existing = ChevronExpenseHead::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if ($existing) {
                    $lookups['heads'][$key] = $existing->id;
                    $stats['skipped']++;

                    continue;
                }

                $head = ChevronExpenseHead::create([
                    'name'                => $name,
                    'expense_category_id' => $fallbackCatId,
                    'type'                => 'External',
                    'is_active'           => true,
                ]);
                $lookups['heads'][$key] = $head->id;
                $stats['inserted']++;
            }
        });

        return $stats;
    }

    private function getOrCreateFallbackCategory(array &$lookups): int
    {
        $key = strtolower(self::FALLBACK_CATEGORY);
        if (isset($lookups['categories'][$key])) {
            return $lookups['categories'][$key];
        }

        $cat = ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first()
            ?? ChevronExpenseCategory::create([
                'name'      => self::FALLBACK_CATEGORY,
                'is_bill'   => false,
                'is_job'    => true,
                'is_active' => true,
            ]);

        $lookups['categories'][$key] = $cat->id;

        return $cat->id;
    }

    // ─── Persistence ──────────────────────────────────────────────────────────

    private function persistExpenses(array &$lookups, array $groups): array
    {
        $stats = ['inserted' => 0, 'inserted_items' => 0, 'skipped' => 0];

        if (empty($groups)) {
            return $stats;
        }

        $expenseCounter = DB::transaction(fn () => (DB::table('chevron_job_expenses')
            ->lockForUpdate()
            ->max(DB::raw('CAST(SUBSTRING(expense_no, 4) AS UNSIGNED)')) ?? 0) + 1
        );

        $now = now();

        foreach (array_chunk($groups, self::CHUNK_SIZE) as $chunk) {
            DB::transaction(function () use ($chunk, &$lookups, &$stats, &$expenseCounter, $now) {
                $itemRows = [];

                foreach ($chunk as $group) {
                    $empKey = strtolower($group['employee_name']);
                    $jobKey = strtolower($group['job_no']);
                    $empId = $lookups['employees'][$empKey] ?? null;
                    $jobId = $lookups['jobs'][$jobKey] ?? null;
                    $dedupKey = $jobKey.'|'.$group['date'].'|'.($empId ?? '_null_');

                    if (isset($lookups['existingExpenses'][$dedupKey])) {
                        $stats['skipped']++;

                        continue;
                    }

                    $totalAmount = array_sum(array_column($group['items'], 'amount'));
                    $expenseNo = 'EXP'.str_pad($expenseCounter++, 6, '0', STR_PAD_LEFT);

                    $expenseId = DB::table('chevron_job_expenses')->insertGetId([
                        'expense_no'            => $expenseNo,
                        'branch_id'             => self::BRANCH_ID,
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
                        $headId = $lookups['heads'][$headKey] ?? null;

                        if ($headId === null) {
                            continue;
                        }

                        $itemRows[] = [
                            'job_expense_id'  => $expenseId,
                            'expense_head_id' => $headId,
                            'receiptable'     => $item['receiptable'],
                            'expense_amount'  => $item['amount'],
                            'approved_amount' => $item['amount'],
                            'expense_date'    => $group['date'],
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ];
                        $stats['inserted_items']++;
                    }
                }

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

        // Primary path: Excel stores dates as numeric serials (e.g. 46130).
        // With $formatData = false in rangeToArray, this is always the form we receive.
        if (is_numeric($value) && (float) $value > 1) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);

                return $dt->format('Y-m-d');
            } catch (\Exception) {
                // fall through to string parsing
            }
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // ISO: YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return $str;
        }

        // DD/Mon/YYYY (e.g. 19/Apr/2024) – format used by older CTG files
        try {
            return Carbon::createFromFormat('d/M/Y', $str)->format('Y-m-d');
        } catch (\Exception) {
            // fall through
        }

        // DD-Mon-YYYY (e.g. 19-Apr-2024)
        try {
            return Carbon::createFromFormat('d-M-Y', $str)->format('Y-m-d');
        } catch (\Exception) {
            // fall through
        }

        // DD/MM/YYYY  (e.g. 19/04/2024)
        try {
            return Carbon::createFromFormat('d/m/Y', $str)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function mapReceiptable(mixed $value): string
    {
        return strtolower(trim((string) $value)) === 'yes' ? 'Yes' : 'No';
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

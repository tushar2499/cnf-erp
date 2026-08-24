<?php

namespace App\Http\Controllers\Api\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JobExpenseImportController extends Controller
{
    private const CHUNK_SIZE = 200;

    private const BRANCH_ID = 6;

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
                    $summary['new_heads']++;
                }
            }
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

        // Read only columns A–L and only rows that contain data (not full 1M sheet)
        return $sheet->rangeToArray('A1:L'.$highestRow, null, true, true, false);
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
                'note'          => trim($row[11] ?? '') ?: null,
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

        $employees = ChevronEmployee::pluck('id', 'name')
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
                strtolower($e->job_no ?? '').'|'.($e->date ?? '').'|'.($e->employee_id ?? '') => true,
            ])
            ->all();

        return compact('jobs', 'customers', 'categories', 'heads', 'employees', 'headEmpPairs', 'existingExpenses');
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
                if ($item['expense_head'] !== '' && ! isset($lookups['heads'][$headKey])) {
                    $uniqueHeadCategoryPairs[$headKey] = [
                        'name'          => $item['expense_head'],
                        'category_name' => $item['category_name'],
                    ];
                }
            }
        }

        DB::transaction(function () use (&$lookups, &$stats, $uniqueCategories, $uniqueImporters, $uniqueJobs, $uniqueHeadCategoryPairs, $uniqueEmployees, $groups) {

            // 1. Expense categories (no deps)
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

            // 2. Customers (no deps)
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

            // 4. Expense heads (needs category_id)
            $fallbackCatId = null;
            foreach ($uniqueHeadCategoryPairs as $key => $data) {
                $catKey = strtolower($data['category_name']);
                $catId = $lookups['categories'][$catKey] ?? null;

                if ($catId === null) {
                    if ($fallbackCatId === null) {
                        $fallback = ChevronExpenseCategory::whereRaw('LOWER(TRIM(name)) = ?', ['general'])->first()
                            ?? ChevronExpenseCategory::create([
                                'name'      => 'General',
                                'is_bill'   => false,
                                'is_job'    => true,
                                'is_active' => true,
                            ]);
                        $fallbackCatId = $fallback->id;
                        $lookups['categories']['general'] = $fallbackCatId;
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

            // 5. Employees (no deps)
            foreach ($uniqueEmployees as $key => $name) {
                $employee = ChevronEmployee::whereRaw('LOWER(TRIM(name)) = ?', [$key])->first();
                if (! $employee) {
                    $employee = ChevronEmployee::create([
                        'employee_prefix' => 'EMP-',
                        'employee_id'     => ChevronEmployee::generateEmployeeId('EMP-'),
                        'name'            => $name,
                        'branch_id'       => self::BRANCH_ID,
                        'is_active'       => true,
                        'type'            => 'prepare',
                        'current_status'  => 'Active',
                    ]);
                    $stats['new_employees']++;
                }
                $lookups['employees'][$key] = $employee->id;
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

    // ─── Persistence ──────────────────────────────────────────────────────────

    private function persistExpenses(array &$lookups, array $groups): array
    {
        $stats = ['inserted' => 0, 'inserted_items' => 0, 'skipped' => 0];

        // Pre-generate the starting expense number once with a single lock query
        // instead of calling generateExpenseNo() (lockForUpdate) per row
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

                    $dedupKey = $jobKey.'|'.$group['date'].'|'.($empId ?? '');
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

        // Handle DD/Mon/YYYY format produced by this Excel file (e.g. 19/Apr/2024)
        try {
            return Carbon::createFromFormat('d/M/Y', $value)->format('Y-m-d');
        } catch (\Exception) {
            // fall through to generic parser
        }

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

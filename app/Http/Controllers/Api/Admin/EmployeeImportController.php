<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBranch;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBranchAccess;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasTrading\NasTradingBranch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EmployeeImportController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $spreadsheet = $this->loadSpreadsheet($request);

        if ($spreadsheet instanceof JsonResponse) {
            return $spreadsheet;
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $parsed = [];

        foreach ($rows as $i => $row) {

            if ($i === 0) {
                continue;
            }

            $name = trim($row[2] ?? '');

            if ($name === '') {
                continue;
            }

            $parsed[] = [
                'company'      => trim($row[0] ?? ''),
                'code'         => trim($row[1] ?? ''),
                'name'         => $name,
                'designation'  => trim($row[3] ?? ''),
                'joining_date' => $this->parseDate($row[4] ?? null),
            ];
        }

        $companyMap = $this->companyBranchMap();

        foreach ($parsed as &$row) {
            $row['exists'] = $this->employeeExists($row['name'], $row['code']);
            $row['designation_exists'] = $this->designationExists($row['designation']);

            $company = $this->resolveCompany($row['company'], $companyMap);
            $row['company_id'] = $company !== null ? $company['id'] : null;
            $row['branch_ids'] = $company !== null ? array_keys($company['branches']) : [];
            $row['branch_names'] = $company !== null ? array_values($company['branches']) : [];
        }

        unset($row);

        return response()->json([
            'current_designations' => Designation::pluck('name')->values(),
            'companies'            => array_map(fn ($c) => [
                'id'       => $c['id'],
                'name'     => $c['name'],
                'type'     => $c['type'],
                'branches' => $c['branches'],
            ], array_values($companyMap)),
            'preview'              => $parsed,
            'total'                => count($parsed),
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $spreadsheet = $this->loadSpreadsheet($request);

        if ($spreadsheet instanceof JsonResponse) {
            return $spreadsheet;
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $parsed = [];

        foreach ($rows as $i => $row) {

            if ($i === 0) {
                continue;
            }

            $name = trim($row[2] ?? '');

            if ($name === '') {
                continue;
            }

            $parsed[] = [
                'company'      => trim($row[0] ?? ''),
                'code'         => trim($row[1] ?? ''),
                'name'         => $name,
                'designation'  => trim($row[3] ?? ''),
                'joining_date' => $this->parseDate($row[4] ?? null),
            ];
        }

        if (empty($parsed)) {
            return response()->json([
                'message'  => 'No employee rows found in the file.',
                'inserted' => 0,
            ], 422);
        }

        $this->wipeExisting();

        $companyMap = $this->companyBranchMap();

        $result = DB::transaction(function () use ($parsed, $companyMap) {
            $designationIds = [];

            foreach ($parsed as $row) {
                $designationIds[$row['designation']] = $this->resolveDesignation($row['designation']);
            }

            $inserted = 0;
            $accessCreated = 0;

            foreach ($parsed as $row) {
                $employee = Employee::create([
                    'name'           => $row['name'],
                    'code'           => $row['code'] ?: null,
                    'designation_id' => $designationIds[$row['designation']] ?? null,
                    'joining_date'   => $row['joining_date'],
                    'current_status' => 'Active',
                    'type'           => 'prepare',
                    'is_active'      => true,
                ]);
                $inserted++;

                $company = $this->resolveCompany($row['company'], $companyMap);

                if ($company === null) {
                    continue;
                }

                foreach (array_keys($company['branches']) as $branchId) {
                    EmployeeBranchAccess::create([
                        'employee_id' => $employee->id,
                        'company_id'  => $company['id'],
                        'branch_id'   => $branchId,
                    ]);
                    $accessCreated++;
                }

            }

            return [
                'employees'     => $inserted,
                'designations'  => count($designationIds),
                'branch_access' => $accessCreated,
            ];
        });

        return response()->json([
            'message'  => "{$result['employees']} employee(s), {$result['designations']} designation(s) and {$result['branch_access']} branch access record(s) imported successfully.",
            'inserted' => $result,
        ]);
    }

    private function loadSpreadsheet(Request $request): JsonResponse|Spreadsheet
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:5120'],
            [
                'file.required' => 'Please select a file to upload.',
                'file.file'     => 'The uploaded value is not a valid file.',
                'file.mimes'    => 'Only Excel files (.xlsx, .xls) and CSV files are allowed.',
                'file.max'      => 'The file size must not exceed 5 MB.',
            ]
        );

        if (! extension_loaded('zip')) {
            return response()->json([
                'message' => 'The server is missing the PHP "zip" extension required to read .xlsx files. Please enable it in cPanel → MultiPHP INI Editor, or upload a .csv file instead.',
            ], 500);
        }

        try {
            return IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not read the file: '.$e->getMessage(),
            ], 422);
        }

    }

    private function parseDate(mixed $value): ?string
    {

        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        $formats = ['d/M/Y', 'd/m/Y', 'Y-m-d', 'm/d/Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $trimmed);

            if ($date !== false && $date->format($format) === $trimmed) {
                return $date->format('Y-m-d');
            }

        }

        $ts = strtotime($trimmed);

        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function employeeExists(string $name, string $code): bool
    {

        if ($code !== '') {
            return Employee::where('code', $code)->exists();
        }

        return Employee::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
    }

    private function designationExists(string $designation): bool
    {

        if ($designation === '') {
            return false;
        }

        return Designation::whereRaw('LOWER(name) = ?', [strtolower($designation)])->exists();
    }

    private function resolveDesignation(string $designation): ?int
    {

        if ($designation === '') {
            return null;
        }

        return Designation::firstOrCreate(
            ['name' => $designation],
            ['is_active' => true]
        )->id;
    }

    private function wipeExisting(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {

            DB::table('users')
                ->whereNotNull('employee_id')
                ->update(['employee_id' => null, 'updated_at' => now()]);

            foreach (['employee_branch_access', 'employees', 'designations'] as $table) {
                DB::table($table)->truncate();
            }

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

    }

    private function companyBranchMap(): array
    {
        $loaders = [
            'cnf'     => fn () => ChevronBranch::where('is_active', true)->pluck('name', 'id')->all(),
            'freight' => fn () => NasFreightsBranch::where('is_active', true)->pluck('name', 'id')->all(),
            'trading' => fn () => NasTradingBranch::where('is_active', true)->pluck('name', 'id')->all(),
        ];

        $map = [];

        foreach (Company::where('is_active', true)->get(['id', 'name', 'type']) as $company) {
            $branches = isset($loaders[$company->type]) ? ($loaders[$company->type])() : [];
            $map[strtolower(trim($company->name))] = [
                'id'       => $company->id,
                'name'     => $company->name,
                'type'     => $company->type,
                'branches' => $branches,
            ];
        }

        return $map;
    }

    private function resolveCompany(string $companyName, array $map): ?array
    {
        $key = strtolower(trim($companyName));

        if ($key === '') {
            return null;
        }

        if (isset($map[$key])) {
            return $map[$key];
        }

        foreach ($map as $name => $company) {

            if (str_contains($name, $key) || str_contains($key, $name)) {
                return $company;
            }

        }

        return null;
    }
}

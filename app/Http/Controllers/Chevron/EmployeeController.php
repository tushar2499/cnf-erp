<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronDesignation;
use App\Models\Chevron\ChevronEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronEmployee::with('designation', 'branch'))
                ->addIndexColumn()
                ->addColumn('designation_name', fn($r) => $r->designation?->name ?? '-')
                ->addColumn('branch_name',      fn($r) => $r->branch?->name ?? '-')
                ->addColumn('status_badge', fn($r) => match($r->current_status) {
                    'Active'     => '<span class="badge bg-success">Active</span>',
                    'Inactive'   => '<span class="badge bg-secondary">Inactive</span>',
                    'Resigned'   => '<span class="badge bg-warning text-dark">Resigned</span>',
                    'Terminated' => '<span class="badge bg-danger">Terminated</span>',
                    default      => '<span class="badge bg-secondary">' . $r->current_status . '</span>',
                })
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'              . $r->id . '"
                        data-employee_prefix="' . e($r->employee_prefix) . '"
                        data-employee_id="'     . e($r->employee_id) . '"
                        data-name="'            . e($r->name) . '"
                        data-designation_id="'  . $r->designation_id . '"
                        data-joining_date="'    . $r->joining_date?->format('Y-m-d') . '"
                        data-short_name="'      . e($r->short_name) . '"
                        data-father_name="'     . e($r->father_name) . '"
                        data-mother_name="'     . e($r->mother_name) . '"
                        data-current_status="'  . $r->current_status . '"
                        data-branch_id="'       . $r->branch_id . '"
                        data-is_active="'       . (int)$r->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('chevron.stakeholders.employees.destroy', $r->id) . '"
                        data-name="' . e($r->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->editColumn('joining_date', fn($r) => $r->joining_date?->format('d M, Y'))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $designations = ChevronDesignation::where('is_active', true)->orderBy('name')->get();
        $branches     = ChevronBranch::where('is_active', true)->orderBy('name')->get();

        return view('chevron.stakeholders.employees.index', compact('designations', 'branches'));
    }

    public function nextId(Request $request)
    {
        $prefix = $request->input('prefix', 'EMP-');
        return response()->json(['employee_id' => ChevronEmployee::generateEmployeeId($prefix)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_prefix' => ['required', 'string', 'max:20'],
            'name'            => ['required', 'string', 'max:255'],
            'designation_id'  => ['required', 'exists:chevron_designations,id'],
            'joining_date'    => ['required', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            $employeeId = ChevronEmployee::generateEmployeeId($request->employee_prefix);
            ChevronEmployee::create([
                'employee_prefix' => $request->employee_prefix,
                'employee_id'     => $employeeId,
                'name'            => $request->name,
                'designation_id'  => $request->designation_id,
                'joining_date'    => $request->joining_date,
                'short_name'      => $request->short_name,
                'father_name'     => $request->father_name,
                'mother_name'     => $request->mother_name,
                'current_status'  => $request->current_status ?? 'Active',
                'branch_id'       => $request->branch_id ?: null,
                'is_active'       => $request->boolean('is_active', true),
            ]);
        });

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function update(Request $request, ChevronEmployee $employee)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'designation_id' => ['required', 'exists:chevron_designations,id'],
            'joining_date'   => ['required', 'date'],
        ]);

        $employee->update([
            'name'           => $request->name,
            'designation_id' => $request->designation_id,
            'joining_date'   => $request->joining_date,
            'short_name'     => $request->short_name,
            'father_name'    => $request->father_name,
            'mother_name'    => $request->mother_name,
            'current_status' => $request->current_status ?? 'Active',
            'branch_id'      => $request->branch_id ?: null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroy(ChevronEmployee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted.']);
    }

    public function sampleDownload()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Employees');

        $headers = ['Employee Prefix','Employee ID','Name','Designation','Branch','Joining Date','Short Name','Father Name','Mother Name','Status'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D2626');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $desig  = ChevronDesignation::first()?->name ?? 'Manager';
        $branch = ChevronBranch::first()?->name ?? 'Head Office';

        $sheet->fromArray([
            ['EMP-',     '',              'Mr. John Doe',   $desig, $branch, '2024-01-15', 'John',  '', '', 'Active'],
            ['CLCNFCTG', 'CLCNFCTG07',   'Ms. Jane Smith', $desig, $branch, '2023-06-01', 'Jane',  '', '', 'Active'],
            ['MGT',      '',              'Mr. ABC Khan',   $desig, $branch, '2022-03-10', 'ABC',   '', '', 'Active'],
        ], null, 'A2');

        $widths = ['A'=>16,'B'=>14,'C'=>28,'D'=>24,'E'=>20,'F'=>14,'G'=>16,'H'=>22,'I'=>22,'J'=>12];
        foreach ($widths as $col => $w) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth($w);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'employees-sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Lookup maps
        $existingIds   = ChevronEmployee::pluck('employee_id')
            ->map(fn($v) => strtolower(trim($v)))->flip()->all();
        $existingNames = ChevronEmployee::pluck('name')
            ->map(fn($v) => strtolower(trim($v)))->flip()->all();

        $desigMap  = ChevronDesignation::pluck('id','name')
            ->mapWithKeys(fn($id,$n) => [strtolower(trim($n)) => $id])->all();
        $branchMap = ChevronBranch::pluck('id','name')
            ->mapWithKeys(fn($id,$n) => [strtolower(trim($n)) => $id])->all();

        $defaultBranchId = ChevronBranch::value('id');

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) continue;
            $name = trim($row[2] ?? '');
            if ($name === '') continue;

            $prefix     = trim($row[0] ?? 'EMP-') ?: 'EMP-';
            $empId      = trim($row[1] ?? '');
            $desigName  = trim($row[3] ?? '');
            $branchName = trim($row[4] ?? '');
            $joiningDate = trim($row[5] ?? '');
            $shortName  = trim($row[6] ?? '');
            $fatherName = trim($row[7] ?? '');
            $motherName = trim($row[8] ?? '');
            $status     = trim($row[9] ?? 'Active') ?: 'Active';

            $desigId   = $desigMap[strtolower($desigName)] ?? null;
            $branchId  = $branchMap[strtolower($branchName)] ?? $defaultBranchId;

            // Date validation
            $dateValid = false;
            $parsedDate = null;
            if ($joiningDate) {
                try {
                    $parsedDate = \Carbon\Carbon::parse($joiningDate)->format('Y-m-d');
                    $dateValid = true;
                } catch (\Exception $e) {}
            }

            // Exists check: by employee_id if provided, else by name
            $exists = $empId !== ''
                ? isset($existingIds[strtolower($empId)])
                : isset($existingNames[strtolower($name)]);

            $warns = [];
            if (!$desigId)    $warns[] = 'Designation not found';
            if (!$branchName) $warns[] = 'No branch — default used';
            if (!$dateValid && $joiningDate) $warns[] = 'Invalid date';

            $preview[] = [
                'employee_prefix'  => $prefix,
                'employee_id'      => $empId,
                'name'             => $name,
                'designation_name' => $desigName,
                'designation_id'   => $desigId,
                'designation_found'=> $desigId !== null,
                'branch_name'      => $branchName,
                'branch_id'        => $branchId,
                'joining_date'     => $parsedDate ?? ($dateValid ? $joiningDate : null),
                'short_name'       => $shortName,
                'father_name'      => $fatherName,
                'mother_name'      => $motherName,
                'status'           => $status,
                'exists'           => $exists,
                'warnings'         => $warns,
            ];
        }

        return response()->json(['rows' => $preview]);
    }

    public function import(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $inserted = 0;
        DB::transaction(function () use ($request, &$inserted) {
            foreach ($request->rows as $row) {
                $name   = trim($row['name'] ?? '');
                $empId  = trim($row['employee_id'] ?? '');
                $prefix = trim($row['employee_prefix'] ?? 'EMP-') ?: 'EMP-';

                if ($name === '') continue;

                // Re-check exists
                if ($empId !== '' && ChevronEmployee::where('employee_id', $empId)->exists()) continue;
                if ($empId === '' && ChevronEmployee::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) continue;

                // Auto-generate ID if not provided
                $finalId = $empId !== '' ? $empId : ChevronEmployee::generateEmployeeId($prefix);

                ChevronEmployee::create([
                    'employee_prefix' => $prefix,
                    'employee_id'     => $finalId,
                    'name'            => $name,
                    'designation_id'  => $row['designation_id'] ?? null,
                    'joining_date'    => $row['joining_date'] ?? null,
                    'short_name'      => $row['short_name'] ?? null,
                    'father_name'     => $row['father_name'] ?? null,
                    'mother_name'     => $row['mother_name'] ?? null,
                    'current_status'  => $row['status'] ?? 'Active',
                    'status'          => $row['status'] ?? 'Active',
                    'branch_id'       => $row['branch_id'] ?? null,
                    'is_active'       => strtolower($row['status'] ?? 'active') === 'active',
                ]);
                $inserted++;
            }
        });

        return response()->json([
            'message'  => "{$inserted} employee(s) imported successfully.",
            'inserted' => $inserted,
        ]);
    }
}

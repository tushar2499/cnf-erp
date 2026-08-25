<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Employee\DestroyEmployeeRequest;
use App\Http\Requests\Admin\Employee\IndexEmployeeRequest;
use App\Http\Requests\Admin\Employee\StoreEmployeeRequest;
use App\Http\Requests\Admin\Employee\UpdateEmployeeRequest;
use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBranchAccess;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasTrading\NasTradingBranch;
use App\Models\NasTrading\NasTradingEmployee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    // ── Unified Employees (access management) ────────────────────────────────

    public function indexUnified(IndexEmployeeRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Employee::with('users')->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('users_count', fn (Employee $r) => $r->users->count())
                ->addColumn('status_badge', fn (Employee $r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', function (Employee $r) use ($request) {
                    $html = '<button class="btn btn-sm btn-outline-primary btn-manage-access me-1"
                        data-id="'.$r->id.'"
                        data-name="'.e($r->name).'"
                        title="Manage Branch Access">
                        <i class="fa fa-code-branch"></i>
                    </button>';
                    if ($request->user()->hasPermission('admin.employees.delete') && $r->users->isEmpty()) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete-unified"
                            data-url="'.route('admin.employees.unified.destroy', $r->id).'"
                            data-name="'.e($r->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return redirect()->route('admin.employees.index');
    }

    public function branchAccess(IndexEmployeeRequest $request, Employee $employee)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);

        $branchLoaders = [
            'cnf'     => fn () => ChevronBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'freight' => fn () => NasFreightsBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'trading' => fn () => NasTradingBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];

        $granted = EmployeeBranchAccess::where('employee_id', $employee->id)
            ->get()
            ->groupBy('company_id')
            ->map(fn ($rows) => $rows->pluck('branch_id')->toArray());

        $data = $companies->map(fn ($co) => [
            'id'       => $co->id,
            'name'     => $co->name,
            'type'     => $co->type,
            'branches' => isset($branchLoaders[$co->type]) ? ($branchLoaders[$co->type])() : collect(),
            'granted'  => $granted[$co->id] ?? [],
        ]);

        return response()->json(['employee' => $employee, 'companies' => $data]);
    }

    public function saveBranchAccess(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.edit'), 403);

        $request->validate([
            'access'                => ['nullable', 'array'],
            'access.*.company_id'   => ['required', 'exists:companies,id'],
            'access.*.branch_ids'   => ['nullable', 'array'],
            'access.*.branch_ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($request, $employee) {
            EmployeeBranchAccess::where('employee_id', $employee->id)->delete();

            $rows = [];
            foreach ($request->input('access', []) as $entry) {
                foreach ($entry['branch_ids'] ?? [] as $branchId) {
                    $rows[] = [
                        'employee_id' => $employee->id,
                        'company_id'  => $entry['company_id'],
                        'branch_id'   => $branchId,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            if ($rows) {
                EmployeeBranchAccess::insert($rows);
            }
        });

        return response()->json(['message' => 'Branch access saved.']);
    }

    public function destroyUnified(DestroyEmployeeRequest $request, Employee $employee)
    {
        if ($employee->users()->exists()) {
            return response()->json(['message' => 'Cannot delete: employee has linked users.'], 422);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }

    // ── All Employees (unified) ──────────────────────────────────────────────

    public function index(IndexEmployeeRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Employee::with('users', 'designation', 'teamLeader')->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('designation_name', fn (Employee $r) => $r->designation?->name ?? '—')
                ->addColumn('type_badge', fn (Employee $r) => match ($r->type) {
                    'team_leader' => '<span class="badge bg-primary"><i class="fa fa-user-tie me-1"></i>Team Leader</span>',
                    'prepare'     => '<span class="badge bg-warning text-dark"><i class="fa fa-user-check me-1"></i>Prepare</span>',
                    default       => '<span class="badge bg-secondary">—</span>',
                })
                ->addColumn('team_leader_name', fn (Employee $r) => $r->teamLeader?->name ?? '—')
                ->editColumn('joining_date', fn (Employee $r) => $r->joining_date?->format('d M, Y'))
                ->addColumn('status_badge', fn (Employee $r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->addColumn('action', function (Employee $r) use ($request) {
                    $html = '';

                    $html .= '<button class="btn btn-sm btn-outline-info btn-view-employee me-1"
                        data-id="'.$r->id.'"
                        data-name="'.e($r->name).'"
                        data-type="'.e($r->type ?? '').'"
                        title="View">
                        <i class="fa fa-eye"></i>
                    </button>';

                    if ($request->user()->hasPermission('admin.employees.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-secondary btn-edit me-1"
                            data-id="'.$r->id.'"
                            data-name="'.e($r->name).'"
                            data-code="'.e((string) $r->code).'"
                            data-designation_id="'.($r->designation_id ?? '').'"
                            data-joining_date="'.($r->joining_date?->format('Y-m-d') ?? '').'"
                            data-short_name="'.e((string) $r->short_name).'"
                            data-father_name="'.e((string) $r->father_name).'"
                            data-mother_name="'.e((string) $r->mother_name).'"
                            data-phone="'.e((string) $r->phone).'"
                            data-email="'.e((string) $r->email).'"
                            data-address="'.e((string) $r->address).'"
                            data-current_status="'.e($r->current_status ?? 'Active').'"
                            data-type="'.e($r->type ?? 'team_leader').'"
                            data-team_leader_id="'.($r->team_leader_id ?? '').'"
                            data-is_active="'.(int) $r->is_active.'"
                            title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>';

                        $html .= '<button class="btn btn-sm btn-outline-primary btn-manage-access me-1"
                            data-id="'.$r->id.'"
                            data-name="'.e($r->name).'"
                            title="Branch Access">
                            <i class="fa fa-code-branch"></i>
                        </button>';
                    }

                    if ($request->user()->hasPermission('admin.employees.delete') && $r->users->isEmpty()) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('admin.employees.unified.destroy', $r->id).'"
                            data-name="'.e($r->name).'"
                            title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->make(true);
        }

        $designations = Designation::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.employees.index', compact('designations'));
    }

    public function updateUnified(Request $request, Employee $employee)
    {
        abort_unless($request->user()->hasPermission('admin.employees.edit'), 403);

        $type = $request->input('type', 'team_leader');

        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:100'],
            'type'           => ['required', 'in:team_leader,prepare'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'joining_date'   => ['nullable', 'date'],
            'short_name'     => ['nullable', 'string', 'max:255'],
            'father_name'    => ['nullable', 'string', 'max:255'],
            'mother_name'    => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'address'        => ['nullable', 'string'],
            'current_status' => ['nullable', 'in:Active,Inactive,Resigned,Terminated'],
            'team_leader_id' => [$type === 'prepare' ? 'required' : 'nullable', 'exists:employees,id'],
            'is_active'      => ['boolean'],
        ]);

        $employee->update([
            'name'           => $request->name,
            'code'           => $request->code ?: null,
            'type'           => $type,
            'designation_id' => $request->designation_id ?: null,
            'joining_date'   => $request->joining_date ?: null,
            'short_name'     => $request->short_name,
            'father_name'    => $request->father_name,
            'mother_name'    => $request->mother_name,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'address'        => $request->address,
            'current_status' => $request->current_status ?? $employee->current_status,
            'team_leader_id' => $type === 'prepare' ? $request->team_leader_id : null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasPermission('admin.employees.create'), 403);

        $type = $request->input('type', 'team_leader');

        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:100'],
            'type'           => ['required', 'in:team_leader,prepare'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'joining_date'   => ['nullable', 'date'],
            'short_name'     => ['nullable', 'string', 'max:255'],
            'father_name'    => ['nullable', 'string', 'max:255'],
            'mother_name'    => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'address'        => ['nullable', 'string'],
            'current_status' => ['nullable', 'in:Active,Inactive,Resigned,Terminated'],
            'team_leader_id' => [$type === 'prepare' ? 'required' : 'nullable', 'exists:employees,id'],
            'is_active'      => ['boolean'],
        ]);

        Employee::create([
            'name'           => $request->name,
            'code'           => $request->code ?: null,
            'type'           => $type,
            'designation_id' => $request->designation_id ?: null,
            'joining_date'   => $request->joining_date ?: null,
            'short_name'     => $request->short_name,
            'father_name'    => $request->father_name,
            'mother_name'    => $request->mother_name,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'address'        => $request->address,
            'current_status' => $request->current_status ?? 'Active',
            'team_leader_id' => $type === 'prepare' ? $request->team_leader_id : null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function showEmployee(IndexEmployeeRequest $request, Employee $employee)
    {
        $employee->load(['designation', 'teamLeader']);

        $members = $employee->type === 'team_leader'
            ? $employee->teamMembers()->with('designation')->orderBy('name')
                ->get(['id', 'name', 'code', 'designation_id', 'joining_date', 'current_status', 'is_active'])
                ->map(fn (Employee $m) => [
                    'id'             => $m->id,
                    'name'           => $m->name,
                    'code'           => $m->code ?: null,
                    'designation'    => $m->designation?->name,
                    'joining_date'   => $m->joining_date?->format('d M, Y'),
                    'current_status' => $m->current_status ?? 'Active',
                    'is_active'      => $m->is_active,
                ])
            : collect();

        return response()->json([
            'employee' => [
                'id'             => $employee->id,
                'name'           => $employee->name,
                'code'           => $employee->code ?: null,
                'short_name'     => $employee->short_name ?: null,
                'type'           => $employee->type,
                'designation'    => $employee->designation?->name,
                'joining_date'   => $employee->joining_date?->format('d M, Y'),
                'current_status' => $employee->current_status ?? 'Active',
                'is_active'      => $employee->is_active,
                'father_name'    => $employee->father_name ?: null,
                'mother_name'    => $employee->mother_name ?: null,
                'phone'          => $employee->phone ?: null,
                'email'          => $employee->email ?: null,
                'address'        => $employee->address ?: null,
                'team_leader'    => $employee->teamLeader ? ['id' => $employee->teamLeader->id, 'name' => $employee->teamLeader->name] : null,
            ],
            'members' => $members,
        ]);
    }

    public function search(IndexEmployeeRequest $request)
    {
        $exclude = $request->integer('exclude', 0);

        return response()->json(
            Employee::where('is_active', true)
                ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))
                ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
                ->orderBy('name')
                ->limit(200)
                ->get(['id', 'name', 'code'])
        );
    }

    public function nextId(IndexEmployeeRequest $request)
    {
        $prefix = $request->input('prefix', 'EMP-');

        return response()->json(['employee_id' => ChevronEmployee::generateEmployeeId($prefix)]);
    }

    public function storeChevron(StoreEmployeeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $employeeId = ChevronEmployee::generateEmployeeId($request->employee_prefix);
            $employee = ChevronEmployee::create([
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
                'type'            => $request->type,
                'team_leader_id'  => $request->type === 'prepare' ? $request->team_leader_id : null,
            ]);

            if ($request->type === 'team_leader') {
                $employee->customers()->sync($request->input('customer_ids', []));
            }
        });

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function update(UpdateEmployeeRequest $request, ChevronEmployee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->update([
                'name'           => $request->name,
                'designation_id' => $request->designation_id,
                'joining_date'   => $request->joining_date,
                'short_name'     => $request->short_name,
                'father_name'    => $request->father_name,
                'mother_name'    => $request->mother_name,
                'current_status' => $request->current_status ?? $employee->current_status,
                'branch_id'      => $request->filled('branch_id') ? $request->branch_id : $employee->branch_id,
                'is_active'      => $request->boolean('is_active', true),
                'type'           => $request->type,
                'team_leader_id' => $request->type === 'prepare' ? $request->team_leader_id : null,
            ]);

            if ($request->type === 'team_leader') {
                $employee->customers()->sync($request->input('customer_ids', []));
            } else {
                $employee->customers()->detach();
            }
        });

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroy(DestroyEmployeeRequest $request, ChevronEmployee $employee)
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }

    public function sampleDownload(IndexEmployeeRequest $request)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Employees');

        $headers = ['Employee Prefix', 'Employee ID', 'Name', 'Designation', 'Branch', 'Joining Date', 'Short Name', 'Father Name', 'Mother Name', 'Status'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D2626');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $desig = Designation::first()?->name ?? 'Manager';
        $branch = ChevronBranch::first()?->name ?? 'Head Office';

        $sheet->fromArray([
            ['EMP-',     '',              'Mr. John Doe',   $desig, $branch, '2024-01-15', 'John',  '', '', 'Active'],
            ['CLCNFCTG', 'CLCNFCTG07',   'Ms. Jane Smith', $desig, $branch, '2023-06-01', 'Jane',  '', '', 'Active'],
            ['MGT',      '',              'Mr. ABC Khan',   $desig, $branch, '2022-03-10', 'ABC',   '', '', 'Active'],
        ], null, 'A2');

        $widths = ['A' => 16, 'B' => 14, 'C' => 28, 'D' => 24, 'E' => 20, 'F' => 14, 'G' => 16, 'H' => 22, 'I' => 22, 'J' => 12];
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

    public function importPreview(StoreEmployeeRequest $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $existingIds = ChevronEmployee::pluck('employee_id')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();
        $existingNames = ChevronEmployee::pluck('name')
            ->map(fn ($v) => strtolower(trim($v)))->flip()->all();

        $desigMap = Designation::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $n) => [strtolower(trim($n)) => $id])->all();
        $branchMap = ChevronBranch::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $n) => [strtolower(trim($n)) => $id])->all();

        $defaultBranchId = ChevronBranch::value('id');

        $preview = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $name = trim($row[2] ?? '');
            if ($name === '') {
                continue;
            }

            $prefix = trim($row[0] ?? 'EMP-') ?: 'EMP-';
            $empId = trim($row[1] ?? '');
            $desigName = trim($row[3] ?? '');
            $branchName = trim($row[4] ?? '');
            $joiningDate = trim($row[5] ?? '');
            $shortName = trim($row[6] ?? '');
            $fatherName = trim($row[7] ?? '');
            $motherName = trim($row[8] ?? '');
            $status = trim($row[9] ?? 'Active') ?: 'Active';

            $desigId = $desigMap[strtolower($desigName)] ?? null;
            $branchId = $branchMap[strtolower($branchName)] ?? $defaultBranchId;

            $dateValid = false;
            $parsedDate = null;
            if ($joiningDate) {
                try {
                    $parsedDate = Carbon::parse($joiningDate)->format('Y-m-d');
                    $dateValid = true;
                } catch (\Exception) {
                }
            }

            $exists = $empId !== ''
                ? isset($existingIds[strtolower($empId)])
                : isset($existingNames[strtolower($name)]);

            $warns = [];
            if (! $desigId) {
                $warns[] = 'Designation not found';
            }
            if (! $branchName) {
                $warns[] = 'No branch — default used';
            }
            if (! $dateValid && $joiningDate) {
                $warns[] = 'Invalid date';
            }

            $preview[] = [
                'employee_prefix'   => $prefix,
                'employee_id'       => $empId,
                'name'              => $name,
                'designation_name'  => $desigName,
                'designation_id'    => $desigId,
                'designation_found' => $desigId !== null,
                'branch_name'       => $branchName,
                'branch_id'         => $branchId,
                'joining_date'      => $parsedDate ?? ($dateValid ? $joiningDate : null),
                'short_name'        => $shortName,
                'father_name'       => $fatherName,
                'mother_name'       => $motherName,
                'status'            => $status,
                'exists'            => $exists,
                'warnings'          => $warns,
            ];
        }

        return response()->json(['rows' => $preview]);
    }

    public function import(StoreEmployeeRequest $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $inserted = 0;
        DB::transaction(function () use ($request, &$inserted) {
            foreach ($request->rows as $row) {
                $name = trim($row['name'] ?? '');
                $empId = trim($row['employee_id'] ?? '');
                $prefix = trim($row['employee_prefix'] ?? 'EMP-') ?: 'EMP-';

                if ($name === '') {
                    continue;
                }

                if ($empId !== '' && ChevronEmployee::where('employee_id', $empId)->exists()) {
                    continue;
                }
                if ($empId === '' && ChevronEmployee::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
                    continue;
                }

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

    // ── NAS Freights ─────────────────────────────────────────────────────────

    public function indexNasFreights(IndexEmployeeRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsEmployee::orderBy('name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';
                    if ($request->user()->hasPermission('admin.employees.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit-nf" data-id="'.$r->id.'"><i class="fa fa-edit"></i></button> ';
                    }
                    if ($request->user()->hasPermission('admin.employees.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete-nf"
                            data-url="'.route('admin.employees.nas-freights.destroy', $r->id).'"
                            data-name="'.e($r->name).'"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return redirect()->route('admin.employees.index');
    }

    public function showNasFreights(IndexEmployeeRequest $request, NasFreightsEmployee $employee)
    {
        return response()->json($employee);
    }

    public function storeNasFreights(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.create'), 403);

        $request->validate(['name' => 'required|string|max:255']);

        NasFreightsEmployee::create([
            'code'        => NasFreightsEmployee::generateCode(),
            'name'        => $request->name,
            'designation' => $request->designation,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'status'      => $request->status ?? 'Active',
        ]);

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function updateNasFreights(Request $request, NasFreightsEmployee $employee)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.edit'), 403);

        $request->validate(['name' => 'required|string|max:255']);

        $employee->update([
            'name'        => $request->name,
            'designation' => $request->designation,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'status'      => $request->status ?? 'Active',
        ]);

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroyNasFreights(Request $request, NasFreightsEmployee $employee)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.delete'), 403);

        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }

    // ── NAS Trading ──────────────────────────────────────────────────────────

    public function indexNasTrading(IndexEmployeeRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingEmployee::orderBy('name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';
                    if ($request->user()->hasPermission('admin.employees.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit-nt" data-id="'.$r->id.'"><i class="fa fa-edit"></i></button> ';
                    }
                    if ($request->user()->hasPermission('admin.employees.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete-nt"
                            data-url="'.route('admin.employees.nas-trading.destroy', $r->id).'"
                            data-name="'.e($r->name).'"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return redirect()->route('admin.employees.index');
    }

    public function showNasTrading(IndexEmployeeRequest $request, NasTradingEmployee $employee)
    {
        return response()->json($employee);
    }

    public function storeNasTrading(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.create'), 403);

        $request->validate(['name' => 'required|string|max:255']);

        NasTradingEmployee::create([
            'code'         => NasTradingEmployee::generateCode(),
            'name'         => $request->name,
            'designation'  => $request->designation,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'address'      => $request->address,
            'joining_date' => $request->joining_date ?: null,
            'status'       => $request->status ?? 'Active',
        ]);

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function updateNasTrading(Request $request, NasTradingEmployee $employee)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.edit'), 403);

        $request->validate(['name' => 'required|string|max:255']);

        $employee->update([
            'name'         => $request->name,
            'designation'  => $request->designation,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'address'      => $request->address,
            'joining_date' => $request->joining_date ?: null,
            'status'       => $request->status ?? 'Active',
        ]);

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroyNasTrading(Request $request, NasTradingEmployee $employee)
    {
        abort_unless(auth()->user()->hasPermission('admin.employees.delete'), 403);

        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }
}

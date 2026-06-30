<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronDesignation;
use App\Models\Chevron\ChevronEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}

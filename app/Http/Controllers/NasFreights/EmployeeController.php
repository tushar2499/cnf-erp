<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsEmployee::where('branch_id', session('nas_freights_branch_id')))
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('nas-freights.employees.destroy', $r->id) . '"
                        data-name="' . e($r->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.employees.index');
    }

    public function show(NasFreightsEmployee $employee)
    {
        return response()->json($employee);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            NasFreightsEmployee::create([
                'branch_id'   => session('nas_freights_branch_id'),
                'code'        => NasFreightsEmployee::generateCode(),
                'name'        => $request->name,
                'designation' => $request->designation,
                'phone'       => $request->phone,
                'email'       => $request->email,
                'status'      => $request->status ?? 'Active',
            ]);
        });

        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function update(Request $request, NasFreightsEmployee $employee)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $employee->update([
            'name'        => $request->name,
            'designation' => $request->designation,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'status'      => $request->status ?? 'Active',
        ]);

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroy(NasFreightsEmployee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted.']);
    }
}

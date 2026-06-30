<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingEmployee::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.employees.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.employees.index');
    }

    public function show(NasTradingEmployee $employee)
    {
        return response()->json($employee);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        DB::transaction(function () use ($request) {
            NasTradingEmployee::create([
                'code'        => NasTradingEmployee::generateCode(),
                'name'        => $request->name,
                'designation' => $request->designation,
                'phone'       => $request->phone,
                'email'       => $request->email,
                'address'     => $request->address,
                'join_date'   => $request->join_date ?: null,
                'status'      => $request->status ?? 'Active',
            ]);
        });
        return response()->json(['message' => 'Employee created successfully.']);
    }

    public function update(Request $request, NasTradingEmployee $employee)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $employee->update([
            'name'        => $request->name,
            'designation' => $request->designation,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'address'     => $request->address,
            'join_date'   => $request->join_date ?: null,
            'status'      => $request->status ?? 'Active',
        ]);
        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroy(NasTradingEmployee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted.']);
    }
}

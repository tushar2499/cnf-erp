<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBranch;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsBranch::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="' . $r->id . '"
                        data-name="' . e($r->name) . '"
                        data-code="' . e($r->code) . '"
                        data-address="' . e($r->address) . '"
                        data-phone="' . e($r->phone) . '"
                        data-is_active="' . (int)$r->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('nas-freights.settings.branches.destroy', $r->id) . '"
                        data-name="' . e($r->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-freights.settings.branches.index');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasFreightsBranch::create([
            'name'      => $request->name,
            'code'      => $request->code ? strtoupper($request->code) : null,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Branch created successfully.']);
    }

    public function update(Request $request, NasFreightsBranch $branch)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $branch->update([
            'name'      => $request->name,
            'code'      => $request->code ? strtoupper($request->code) : null,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Branch updated successfully.']);
    }

    public function destroy(NasFreightsBranch $branch)
    {
        $branch->delete();
        return response()->json(['message' => 'Branch deleted.']);
    }
}

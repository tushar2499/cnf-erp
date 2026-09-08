<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\Branch\DestroyBranchRequest;
use App\Http\Requests\NasFreights\Branch\IndexBranchRequest;
use App\Http\Requests\NasFreights\Branch\StoreBranchRequest;
use App\Http\Requests\NasFreights\Branch\UpdateBranchRequest;
use App\Models\NasFreights\NasFreightsBranch;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(IndexBranchRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsBranch::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn ($r) => '<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'.$r->id.'"
                        data-name="'.e($r->name).'"
                        data-code="'.e($r->code).'"
                        data-address="'.e($r->address).'"
                        data-phone="'.e($r->phone).'"
                        data-is_active="'.(int) $r->is_active.'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('nas-freights.settings.branches.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.branches.index');
    }

    public function store(StoreBranchRequest $request)
    {
        NasFreightsBranch::create([
            'name'      => $request->name,
            'code'      => $request->code ? strtoupper($request->code) : null,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Branch created successfully.']);
    }

    public function update(UpdateBranchRequest $request, NasFreightsBranch $branch)
    {
        $branch->update([
            'name'      => $request->name,
            'code'      => $request->code ? strtoupper($request->code) : null,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Branch updated successfully.']);
    }

    public function destroy(DestroyBranchRequest $request, NasFreightsBranch $branch)
    {
        $branch->delete();

        return response()->json(['message' => 'Branch deleted.']);
    }
}

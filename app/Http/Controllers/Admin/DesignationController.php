<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Designation\DestroyDesignationRequest;
use App\Http\Requests\Admin\Designation\IndexDesignationRequest;
use App\Http\Requests\Admin\Designation\StoreDesignationRequest;
use App\Http\Requests\Admin\Designation\UpdateDesignationRequest;
use App\Models\Designation;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function index(IndexDesignationRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Designation::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn ($row) => '
                    <button class="btn btn-sm btn-outline-secondary btn-edit me-1"
                        data-id="'.$row->id.'"
                        data-name="'.e($row->name).'"
                        data-is_active="'.(int) $row->is_active.'"
                        title="Edit" aria-label="Edit '.e($row->name).'">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route('admin.designations.destroy', $row->id).'"
                        data-name="'.e($row->name).'"
                        title="Delete" aria-label="Delete '.e($row->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.designations.index');
    }

    public function store(StoreDesignationRequest $request)
    {
        $validated = $request->validated();

        Designation::create([
            'name'      => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Designation created successfully.']);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $validated = $request->validated();

        $designation->update([
            'name'      => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Designation updated successfully.']);
    }

    public function destroy(DestroyDesignationRequest $request, Designation $designation)
    {
        $designation->delete();

        return response()->json(['message' => 'Designation deleted.']);
    }
}

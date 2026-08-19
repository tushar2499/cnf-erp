<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('admin.designations.list'), 403);

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

    public function store(Request $request)
    {
        abort_unless($request->user()->hasPermission('admin.designations.create'), 403);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        Designation::create([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Designation created successfully.']);
    }

    public function update(Request $request, Designation $designation)
    {
        abort_unless($request->user()->hasPermission('admin.designations.edit'), 403);

        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $designation->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Designation updated successfully.']);
    }

    public function destroy(Request $request, Designation $designation)
    {
        abort_unless($request->user()->hasPermission('admin.designations.delete'), 403);

        $designation->delete();

        return response()->json(['message' => 'Designation deleted.']);
    }
}

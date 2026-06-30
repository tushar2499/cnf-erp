<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronDesignation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronDesignation::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($row) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-is_active="' . (int)$row->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('chevron.stakeholders.designations.destroy', $row->id) . '"
                        data-name="' . e($row->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.stakeholders.designations.index');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        ChevronDesignation::create([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Designation created successfully.']);
    }

    public function update(Request $request, ChevronDesignation $designation)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $designation->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Designation updated successfully.']);
    }

    public function destroy(ChevronDesignation $designation)
    {
        $designation->delete();
        return response()->json(['message' => 'Designation deleted.']);
    }
}

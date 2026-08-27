<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Branch\DestroyBranchRequest;
use App\Http\Requests\Chevron\Branch\IndexBranchRequest;
use App\Http\Requests\Chevron\Branch\StoreBranchRequest;
use App\Http\Requests\Chevron\Branch\UpdateBranchRequest;
use App\Models\Chevron\ChevronBranch;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(IndexBranchRequest $request)
    {

        if ($request->ajax()) {
            return DataTables::of(ChevronBranch::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($row) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('cnf.branch.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$row->id.'"
                            data-name="'.e($row->name).'"
                            data-code="'.e($row->code).'"
                            data-address="'.e($row->address).'"
                            data-phone="'.e($row->phone).'"
                            data-is_active="'.(int) $row->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('cnf.branch.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('chevron.settings.branches.destroy', $row->id).'"
                            data-name="'.e($row->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.branches.index');
    }

    public function store(StoreBranchRequest $request)
    {
        ChevronBranch::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Branch created successfully.']);
    }

    public function update(UpdateBranchRequest $request, ChevronBranch $branch)
    {
        $branch->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Branch updated successfully.']);
    }

    public function destroy(DestroyBranchRequest $request, ChevronBranch $branch)
    {
        $branch->delete();

        return response()->json(['message' => 'Branch deleted.']);
    }
}

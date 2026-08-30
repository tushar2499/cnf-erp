<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\JobType\DestroyJobTypeRequest;
use App\Http\Requests\Chevron\JobType\IndexJobTypeRequest;
use App\Http\Requests\Chevron\JobType\StoreJobTypeRequest;
use App\Http\Requests\Chevron\JobType\UpdateJobTypeRequest;
use App\Models\Chevron\ChevronJobType;
use Yajra\DataTables\Facades\DataTables;

class JobTypeController extends Controller
{
    public function index(IndexJobTypeRequest $request)
    {

        if ($request->ajax()) {
            return DataTables::of(ChevronJobType::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($row) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('cnf.job-type.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$row->id.'"
                            data-name="'.e($row->name).'"
                            data-code="'.e($row->code).'"
                            data-is_active="'.(int) $row->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('cnf.job-type.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('chevron.settings.job-types.destroy', $row->id).'"
                            data-name="'.e($row->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.job-types.index');
    }

    public function store(StoreJobTypeRequest $request)
    {
        ChevronJobType::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Job type created successfully.']);
    }

    public function update(UpdateJobTypeRequest $request, ChevronJobType $jobType)
    {
        $jobType->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Job type updated successfully.']);
    }

    public function destroy(DestroyJobTypeRequest $request, ChevronJobType $jobType)
    {
        $jobType->delete();

        return response()->json(['message' => 'Job type deleted.']);
    }
}

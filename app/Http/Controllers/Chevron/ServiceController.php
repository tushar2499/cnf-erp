<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Service\DestroyServiceRequest;
use App\Http\Requests\Chevron\Service\IndexServiceRequest;
use App\Http\Requests\Chevron\Service\StoreServiceRequest;
use App\Http\Requests\Chevron\Service\UpdateServiceRequest;
use App\Models\Chevron\ChevronService;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index(IndexServiceRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronService::query())
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('cnf.service.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$row->id.'"
                            data-name="'.e($row->name).'"
                            data-is_active="'.(int) $row->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('cnf.service.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('chevron.settings.services.destroy', $row->id).'"
                            data-name="'.e($row->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.services.index');
    }

    public function store(StoreServiceRequest $request)
    {
        ChevronService::create([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Service created successfully.']);
    }

    public function update(UpdateServiceRequest $request, ChevronService $service)
    {
        $service->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Service updated successfully.']);
    }

    public function destroy(DestroyServiceRequest $request, ChevronService $service)
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted.']);
    }
}

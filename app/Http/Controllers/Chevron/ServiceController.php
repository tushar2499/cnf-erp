<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronService::query())
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="' . $row->id . '"
                            data-name="' . e($row->name) . '"
                            data-is_active="' . (int)$row->is_active . '">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="' . route('chevron.settings.services.destroy', $row->id) . '"
                            data-name="' . e($row->name) . '">
                            <i class="fa fa-trash"></i>
                        </button>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.services.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        ChevronService::create($data);

        return response()->json(['message' => 'Service created successfully.']);
    }

    public function update(Request $request, ChevronService $service)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $service->update($data);

        return response()->json(['message' => 'Service updated successfully.']);
    }

    public function destroy(ChevronService $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted.']);
    }
}

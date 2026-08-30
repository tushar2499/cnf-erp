<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Port\DestroyPortRequest;
use App\Http\Requests\Chevron\Port\IndexPortRequest;
use App\Http\Requests\Chevron\Port\StorePortRequest;
use App\Http\Requests\Chevron\Port\UpdatePortRequest;
use App\Models\Chevron\ChevronPort;
use Yajra\DataTables\Facades\DataTables;

class PortController extends Controller
{
    public function index(IndexPortRequest $request)
    {

        $branchId = session('active_branch_id');

        if ($request->ajax()) {
            return DataTables::of(ChevronPort::where('branch_id', $branchId))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($row) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('cnf.port.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$row->id.'"
                            data-name="'.e($row->name).'"
                            data-code="'.e($row->code).'"
                            data-prefix="'.e($row->prefix).'"
                            data-is_active="'.(int) $row->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('cnf.port.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('chevron.settings.ports.destroy', $row->id).'"
                            data-name="'.e($row->name).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.ports.index');
    }

    public function store(StorePortRequest $request)
    {
        ChevronPort::create([
            'branch_id' => session('active_branch_id'),
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'prefix'    => $request->prefix,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Port created successfully.']);
    }

    public function update(UpdatePortRequest $request, ChevronPort $port)
    {
        $port->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'prefix'    => $request->prefix,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Port updated successfully.']);
    }

    public function destroy(DestroyPortRequest $request, ChevronPort $port)
    {
        $port->delete();

        return response()->json(['message' => 'Port deleted.']);
    }
}

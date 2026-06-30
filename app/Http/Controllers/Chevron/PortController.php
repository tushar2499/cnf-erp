<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronPort;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PortController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronPort::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($row) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="' . $row->id . '"
                        data-name="' . e($row->name) . '"
                        data-code="' . e($row->code) . '"
                        data-prefix="' . e($row->prefix) . '"
                        data-is_active="' . (int)$row->is_active . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route('chevron.settings.ports.destroy', $row->id) . '"
                        data-name="' . e($row->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.ports.index');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        ChevronPort::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'prefix'   => $request->prefix,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Port created successfully.']);
    }

    public function update(Request $request, ChevronPort $port)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $port->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'prefix'   => $request->prefix,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return response()->json(['message' => 'Port updated successfully.']);
    }

    public function destroy(ChevronPort $port)
    {
        $port->delete();
        return response()->json(['message' => 'Port deleted.']);
    }
}

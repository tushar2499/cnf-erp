<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingPort;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PortController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingPort::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('type_badge', fn($r) => match($r->type) {
                    'Sea'  => '<span class="badge bg-info">Sea</span>',
                    'Air'  => '<span class="badge bg-primary">Air</span>',
                    'Land' => '<span class="badge bg-warning text-dark">Land</span>',
                    default => $r->type,
                })
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.ports.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'type_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.ports.index');
    }

    public function show(NasTradingPort $port)
    {
        return response()->json($port);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'type' => 'required']);
        NasTradingPort::create($request->only('name', 'country', 'type', 'status'));
        return response()->json(['message' => 'Port created successfully.']);
    }

    public function update(Request $request, NasTradingPort $port)
    {
        $request->validate(['name' => 'required|string|max:255', 'type' => 'required']);
        $port->update($request->only('name', 'country', 'type', 'status'));
        return response()->json(['message' => 'Port updated successfully.']);
    }

    public function destroy(NasTradingPort $port)
    {
        $port->delete();
        return response()->json(['message' => 'Port deleted.']);
    }
}

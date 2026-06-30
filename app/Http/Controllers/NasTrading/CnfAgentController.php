<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingCnfAgent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CnfAgentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingCnfAgent::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.cnf-agents.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.cnf-agents.index');
    }

    public function show(NasTradingCnfAgent $cnfAgent)
    {
        return response()->json($cnfAgent);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasTradingCnfAgent::create($request->only('name', 'phone', 'address', 'status'));
        return response()->json(['message' => 'CNF Agent created successfully.']);
    }

    public function update(Request $request, NasTradingCnfAgent $cnfAgent)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $cnfAgent->update($request->only('name', 'phone', 'address', 'status'));
        return response()->json(['message' => 'CNF Agent updated successfully.']);
    }

    public function destroy(NasTradingCnfAgent $cnfAgent)
    {
        $cnfAgent->delete();
        return response()->json(['message' => 'CNF Agent deleted.']);
    }
}

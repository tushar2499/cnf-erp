<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingTransportCompany;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransportCompanyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingTransportCompany::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.transport-companies.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.transport-companies.index');
    }

    public function show(NasTradingTransportCompany $transportCompany)
    {
        return response()->json($transportCompany);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasTradingTransportCompany::create($request->only('name', 'phone', 'address', 'status'));
        return response()->json(['message' => 'Transport Company created successfully.']);
    }

    public function update(Request $request, NasTradingTransportCompany $transportCompany)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $transportCompany->update($request->only('name', 'phone', 'address', 'status'));
        return response()->json(['message' => 'Transport Company updated successfully.']);
    }

    public function destroy(NasTradingTransportCompany $transportCompany)
    {
        $transportCompany->delete();
        return response()->json(['message' => 'Transport Company deleted.']);
    }
}

<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingBank;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingBank::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.banks.destroy', $r->id) . '" data-name="' . e($r->name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.banks.index');
    }

    public function show(NasTradingBank $bank)
    {
        return response()->json($bank);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        NasTradingBank::create($request->only('name', 'branch', 'swift_code', 'account_no', 'status'));
        return response()->json(['message' => 'Bank created successfully.']);
    }

    public function update(Request $request, NasTradingBank $bank)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $bank->update($request->only('name', 'branch', 'swift_code', 'account_no', 'status'));
        return response()->json(['message' => 'Bank updated successfully.']);
    }

    public function destroy(NasTradingBank $bank)
    {
        $bank->delete();
        return response()->json(['message' => 'Bank deleted.']);
    }
}

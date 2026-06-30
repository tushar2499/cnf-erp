<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingSupplier::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.suppliers.destroy', $r->id) . '" data-name="' . e($r->company_name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.suppliers.index');
    }

    public function show(NasTradingSupplier $supplier)
    {
        return response()->json($supplier);
    }

    public function store(Request $request)
    {
        $request->validate(['company_name' => 'required|string|max:255']);
        DB::transaction(function () use ($request) {
            NasTradingSupplier::create([
                'code'           => NasTradingSupplier::generateCode(),
                'company_name'   => $request->company_name,
                'country'        => $request->country,
                'contact_person' => $request->contact_person,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'currency'       => $request->currency ?? 'USD',
                'status'         => $request->status ?? 'Active',
            ]);
        });
        return response()->json(['message' => 'Supplier created successfully.']);
    }

    public function update(Request $request, NasTradingSupplier $supplier)
    {
        $request->validate(['company_name' => 'required|string|max:255']);
        $supplier->update([
            'company_name'   => $request->company_name,
            'country'        => $request->country,
            'contact_person' => $request->contact_person,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'currency'       => $request->currency ?? 'USD',
            'status'         => $request->status ?? 'Active',
        ]);
        return response()->json(['message' => 'Supplier updated successfully.']);
    }

    public function destroy(NasTradingSupplier $supplier)
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingSupplier::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'country', 'currency'])
                ->map(fn($s) => ['id' => $s->id, 'text' => $s->code . ' | ' . $s->company_name, 'country' => $s->country, 'currency' => $s->currency])
        );
    }
}

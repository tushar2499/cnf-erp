<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingCustomer::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) =>
                    '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '"><i class="fa fa-edit"></i></button> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.customers.destroy', $r->id) . '" data-name="' . e($r->company_name) . '"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.customers.index');
    }

    public function show(NasTradingCustomer $customer)
    {
        return response()->json($customer);
    }

    public function store(Request $request)
    {
        $request->validate(['company_name' => 'required|string|max:255']);
        DB::transaction(function () use ($request) {
            NasTradingCustomer::create([
                'code'             => NasTradingCustomer::generateCode(),
                'company_name'     => $request->company_name,
                'contact_person'   => $request->contact_person,
                'phone'            => $request->phone,
                'email'            => $request->email,
                'address'          => $request->address,
                'delivery_address' => $request->delivery_address,
                'credit_limit'     => $request->credit_limit ?? 0,
                'status'           => $request->status ?? 'Active',
            ]);
        });
        return response()->json(['message' => 'Customer created successfully.']);
    }

    public function update(Request $request, NasTradingCustomer $customer)
    {
        $request->validate(['company_name' => 'required|string|max:255']);
        $customer->update([
            'company_name'     => $request->company_name,
            'contact_person'   => $request->contact_person,
            'phone'            => $request->phone,
            'email'            => $request->email,
            'address'          => $request->address,
            'delivery_address' => $request->delivery_address,
            'credit_limit'     => $request->credit_limit ?? 0,
            'status'           => $request->status ?? 'Active',
        ]);
        return response()->json(['message' => 'Customer updated successfully.']);
    }

    public function destroy(NasTradingCustomer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'address', 'delivery_address'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->code . ' | ' . $c->company_name, 'address' => $c->address, 'delivery_address' => $c->delivery_address])
        );
    }
}

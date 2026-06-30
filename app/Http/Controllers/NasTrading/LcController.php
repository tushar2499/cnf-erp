<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingCustomer;
use App\Models\NasTrading\NasTradingSupplier;
use App\Models\NasTrading\NasTradingBank;
use App\Models\NasTrading\NasTradingImporter;
use App\Models\NasTrading\NasTradingPsiCompany;
use App\Models\NasTrading\NasTradingPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LcController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasTradingLc::latest();
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('lc_open_date', fn($r) => $r->lc_open_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn($r) => match($r->lc_status) {
                    'Open'      => '<span class="badge bg-success">Open</span>',
                    'Closed'    => '<span class="badge bg-secondary">Closed</span>',
                    'Cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    'Amended'   => '<span class="badge bg-warning text-dark">Amended</span>',
                    default     => $r->lc_status,
                })
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.lcs.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-trading.lcs.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-edit"></i></a> ' .
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.lcs.destroy', $r->id) . '" data-name="' . e($r->lc_no_system) . '" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.lcs.index');
    }

    public function create()
    {
        $banks       = NasTradingBank::where('status', 'Active')->get();
        $importers   = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies= NasTradingPsiCompany::where('status', 'Active')->get();
        $ports       = NasTradingPort::where('status', 'Active')->get();
        return view('nas-trading.lcs.create', compact('banks', 'importers', 'psiCompanies', 'ports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'pfi_no'      => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $lc = NasTradingLc::create(array_merge(
                ['lc_no_system' => NasTradingLc::generateLcNo(), 'created_by' => auth()->id()],
                $request->except(['_token', 'items'])
            ));

            if ($request->items) {
                foreach ($request->items as $item) {
                    if (!empty($item['product_name'])) {
                        $lc->items()->create($item);
                    }
                }
            }
        });

        return response()->json(['message' => 'LC created successfully.', 'redirect' => route('nas-trading.lcs.index')]);
    }

    public function show(NasTradingLc $lc)
    {
        $lc->load('items', 'expenses.expenseHead');
        $banks       = NasTradingBank::where('status', 'Active')->get();
        $importers   = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies= NasTradingPsiCompany::where('status', 'Active')->get();
        $ports       = NasTradingPort::where('status', 'Active')->get();
        $expenseHeads= \App\Models\NasTrading\NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.lcs.show', compact('lc', 'banks', 'importers', 'psiCompanies', 'ports', 'expenseHeads'));
    }

    public function edit(NasTradingLc $lc)
    {
        $lc->load('items');
        $banks       = NasTradingBank::where('status', 'Active')->get();
        $importers   = NasTradingImporter::where('status', 'Active')->get();
        $psiCompanies= NasTradingPsiCompany::where('status', 'Active')->get();
        $ports       = NasTradingPort::where('status', 'Active')->get();
        return view('nas-trading.lcs.edit', compact('lc', 'banks', 'importers', 'psiCompanies', 'ports'));
    }

    public function update(Request $request, NasTradingLc $lc)
    {
        $request->validate([
            'customer_id' => 'required',
            'pfi_no'      => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($request, $lc) {
            $lc->update($request->except(['_token', '_method', 'items']));
            $lc->items()->delete();
            if ($request->items) {
                foreach ($request->items as $item) {
                    if (!empty($item['product_name'])) {
                        $lc->items()->create($item);
                    }
                }
            }
        });

        return response()->json(['message' => 'LC updated successfully.', 'redirect' => route('nas-trading.lcs.show', $lc->id)]);
    }

    public function destroy(NasTradingLc $lc)
    {
        $lc->items()->delete();
        $lc->expenses()->delete();
        $lc->delete();
        return response()->json(['message' => 'LC deleted.']);
    }

    public function generateBill(NasTradingLc $lc)
    {
        $lc->load('items', 'expenses.expenseHead');
        $expenseHeads = \App\Models\NasTrading\NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.customer-bills.generate', compact('lc', 'expenseHeads'));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'address', 'delivery_address'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->code . ' | ' . $c->company_name, 'address' => $c->address, 'delivery_address' => $c->delivery_address])
        );
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingSupplier::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name', 'country', 'currency'])
                ->map(fn($s) => ['id' => $s->id, 'text' => $s->code . ' | ' . $s->company_name, 'country' => $s->country, 'currency' => $s->currency])
        );
    }

    public function search(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingLc::where(fn($q) => $q->where('lc_no_system', 'like', "%{$term}%")->orWhere('lc_no', 'like', "%{$term}%")->orWhere('pfi_no', 'like', "%{$term}%")->orWhere('customer_name', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'lc_no_system', 'lc_no', 'pfi_no', 'customer_id', 'customer_name'])
                ->map(fn($l) => ['id' => $l->id, 'text' => $l->lc_no_system . ' | ' . $l->pfi_no . ' | ' . $l->customer_name, 'lc_no' => $l->lc_no, 'pfi_no' => $l->pfi_no, 'customer_id' => $l->customer_id, 'customer_name' => $l->customer_name])
        );
    }
}

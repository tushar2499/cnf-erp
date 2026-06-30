<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsMoneyReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MoneyReceiptController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsMoneyReceipt::where('branch_id', session('nas_freights_branch_id'))->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('receipt_date', fn($r) => $r->receipt_date?->format('d-M-Y'))
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-freights.money-receipts.show', $r->id) . '" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-freights.money-receipts.print', $r->id) . '" target="_blank" class="btn btn-sm btn-outline-dark" title="Print"><i class="fa fa-print"></i></a>')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('nas-freights.money-receipts.index');
    }

    public function create()
    {
        return view('nas-freights.money-receipts.create', [
            'paymentModes' => NasFreightsMoneyReceipt::paymentModes(),
        ]);
    }

    public function getBills(Request $request)
    {
        $query = NasFreightsCustomerBill::where('status', 'Approved');
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        return response()->json(
            $query->orderBy('bill_date')->get(['id', 'bill_no', 'customer_name', 'total_amount'])
                ->map(fn($b) => [
                    'id'           => $b->id,
                    'text'         => $b->bill_no . ' — ' . $b->customer_name,
                    'bill_no'      => $b->bill_no,
                    'total_amount' => $b->total_amount,
                    'customer_name'=> $b->customer_name,
                ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_date'    => ['required', 'date'],
            'bill_id'         => ['required', 'exists:nas_freights_customer_bills,id'],
            'amount_received' => ['required', 'numeric', 'min:0.01'],
            'payment_mode'    => ['required'],
        ]);

        DB::transaction(function () use ($request) {
            $bill = NasFreightsCustomerBill::findOrFail($request->bill_id);

            NasFreightsMoneyReceipt::create([
                'branch_id'       => session('nas_freights_branch_id'),
                'receipt_no'      => NasFreightsMoneyReceipt::generateReceiptNo(),
                'receipt_date'    => $request->receipt_date,
                'customer_id'     => $bill->customer_id,
                'customer_name'   => $bill->customer_name,
                'bill_id'         => $bill->id,
                'bill_no'         => $bill->bill_no,
                'bill_amount'     => $bill->total_amount,
                'amount_received' => $request->amount_received,
                'payment_mode'    => $request->payment_mode,
                'reference_no'    => $request->reference_no,
                'note'            => $request->note,
                'entry_by'        => Auth::user()?->name ?? 'System',
            ]);

            $bill->update(['status' => 'Paid']);
        });

        return response()->json(['message' => 'Money receipt created successfully.', 'redirect' => route('nas-freights.money-receipts.index')]);
    }

    public function show(NasFreightsMoneyReceipt $moneyReceipt)
    {
        return view('nas-freights.money-receipts.show', compact('moneyReceipt'));
    }

    public function printView(NasFreightsMoneyReceipt $moneyReceipt)
    {
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        return view('nas-freights.money-receipts.print', compact('moneyReceipt', 'company'));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        $ids  = NasFreightsCustomerBill::where('status', 'Approved')->pluck('customer_id')->unique();

        return response()->json(
            NasFreightsCustomer::whereIn('id', $ids)
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('customer_id', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'customer_id', 'name'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->customer_id . ' | ' . $c->name])
        );
    }
}

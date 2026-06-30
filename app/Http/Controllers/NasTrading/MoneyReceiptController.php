<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingMoneyReceipt;
use App\Models\NasTrading\NasTradingCustomerBill;
use App\Models\NasTrading\NasTradingCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MoneyReceiptController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingMoneyReceipt::latest())
                ->addIndexColumn()
                ->editColumn('receipt_date', fn($r) => $r->receipt_date?->format('d-M-Y'))
                ->editColumn('amount_received', fn($r) => number_format($r->amount_received, 2))
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.money-receipts.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a>')
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('nas-trading.money-receipts.index');
    }

    public function create(Request $request)
    {
        $billId = $request->query('bill_id');
        $bill = $billId ? NasTradingCustomerBill::find($billId) : null;
        return view('nas-trading.money-receipts.create', compact('bill'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_date'    => 'required|date',
            'customer_id'     => 'required',
            'bill_id'         => 'required|exists:nas_trading_customer_bills,id',
            'amount_received' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $bill = NasTradingCustomerBill::findOrFail($request->bill_id);

            NasTradingMoneyReceipt::create([
                'receipt_no'      => NasTradingMoneyReceipt::generateReceiptNo(),
                'receipt_date'    => $request->receipt_date,
                'customer_id'     => $request->customer_id,
                'customer_name'   => $bill->customer_name,
                'bill_id'         => $bill->id,
                'bill_no'         => $bill->bill_no,
                'bill_amount'     => $bill->total_amount,
                'amount_received' => $request->amount_received,
                'payment_mode'    => $request->payment_mode ?? 'Bank Transfer',
                'reference_no'    => $request->reference_no,
                'note'            => $request->note,
                'created_by'      => auth()->id(),
            ]);

            $bill->update(['status' => 'Paid']);
        });

        return response()->json(['message' => 'Money receipt created. Bill marked as Paid.', 'redirect' => route('nas-trading.money-receipts.index')]);
    }

    public function show(NasTradingMoneyReceipt $moneyReceipt)
    {
        return view('nas-trading.money-receipts.show', compact('moneyReceipt'));
    }

    public function getBills(Request $request)
    {
        $customerId = $request->customer_id;
        $bills = NasTradingCustomerBill::where('status', 'Confirmed')
            ->where('customer_id', $customerId)
            ->get(['id', 'bill_no', 'bill_date', 'total_amount']);
        return response()->json($bills->map(fn($b) => [
            'id'           => $b->id,
            'text'         => $b->bill_no . ' | BDT ' . number_format($b->total_amount, 2),
            'total_amount' => $b->total_amount,
        ]));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        $customerIds = NasTradingCustomerBill::where('status', 'Confirmed')->pluck('customer_id');
        return response()->json(
            NasTradingCustomer::whereIn('id', $customerIds)
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->code . ' | ' . $c->company_name])
        );
    }
}

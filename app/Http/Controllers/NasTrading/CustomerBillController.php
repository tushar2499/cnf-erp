<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingCustomerBill;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerBillController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasTradingCustomerBill::latest())
                ->addIndexColumn()
                ->editColumn('bill_date', fn($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('total_amount', fn($r) => number_format($r->total_amount, 2))
                ->addColumn('status_badge', fn($r) => match($r->status) {
                    'Draft'     => '<span class="badge bg-secondary">Draft</span>',
                    'Confirmed' => '<span class="badge bg-success">Confirmed</span>',
                    'Paid'      => '<span class="badge bg-primary">Paid</span>',
                    default     => $r->status,
                })
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.customer-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-eye"></i></a> ' .
                    ($r->status === 'Draft' ? '<a href="' . route('nas-trading.customer-bills.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-edit"></i></a> ' : '') .
                    ($r->status === 'Draft' ? '<button class="btn btn-sm btn-outline-success btn-confirm" data-url="' . route('nas-trading.customer-bills.confirm', $r->id) . '" style="padding:2px 6px;font-size:.7rem" title="Confirm"><i class="fa fa-check"></i></button> ' : '') .
                    ($r->status !== 'Paid' ? '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-trading.customer-bills.destroy', $r->id) . '" data-name="' . e($r->bill_no) . '" style="padding:2px 6px;font-size:.7rem"><i class="fa fa-trash"></i></button>' : ''))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('nas-trading.customer-bills.index');
    }

    public function create()
    {
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.customer-bills.create', compact('expenseHeads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'bill_date'   => 'required|date',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $bill = NasTradingCustomerBill::create([
                'bill_no'          => NasTradingCustomerBill::generateBillNo(),
                'lc_id'            => $request->lc_id,
                'lc_no'            => $request->lc_no,
                'pfi_no'           => $request->pfi_no,
                'customer_id'      => $request->customer_id,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'currency'         => $request->currency ?? 'BDT',
                'exchange_rate'    => $request->exchange_rate,
                'sub_total'        => $request->sub_total ?? 0,
                'vat_pct'          => $request->vat_pct ?? 0,
                'vat_amount'       => $request->vat_amount ?? 0,
                'total_amount'     => $request->total_amount ?? 0,
                'status'           => 'Draft',
                'note'             => $request->note,
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->items as $idx => $item) {
                if (!empty($item['description'])) {
                    $bill->items()->create([
                        'description'    => $item['description'],
                        'expense_head_id'=> $item['expense_head_id'] ?? null,
                        'qty'            => $item['qty'] ?? 1,
                        'unit_price'     => $item['unit_price'] ?? 0,
                        'amount'         => $item['amount'] ?? 0,
                        'note'           => $item['note'] ?? null,
                        'sort_order'     => $idx,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Bill created successfully.', 'redirect' => route('nas-trading.customer-bills.index')]);
    }

    public function show(NasTradingCustomerBill $customerBill)
    {
        $customerBill->load('items');
        return view('nas-trading.customer-bills.show', compact('customerBill'));
    }

    public function edit(NasTradingCustomerBill $customerBill)
    {
        if ($customerBill->status !== 'Draft') {
            return redirect()->route('nas-trading.customer-bills.show', $customerBill->id)
                ->with('error', 'Only Draft bills can be edited.');
        }
        $customerBill->load('items');
        $expenseHeads = NasTradingExpenseHead::where('status', 'Active')->get();
        return view('nas-trading.customer-bills.edit', compact('customerBill', 'expenseHeads'));
    }

    public function update(Request $request, NasTradingCustomerBill $customerBill)
    {
        $request->validate(['customer_id' => 'required', 'bill_date' => 'required|date', 'items' => 'required|array|min:1']);

        DB::transaction(function () use ($request, $customerBill) {
            $customerBill->update([
                'customer_id'      => $request->customer_id,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'currency'         => $request->currency ?? 'BDT',
                'exchange_rate'    => $request->exchange_rate,
                'sub_total'        => $request->sub_total ?? 0,
                'vat_pct'          => $request->vat_pct ?? 0,
                'vat_amount'       => $request->vat_amount ?? 0,
                'total_amount'     => $request->total_amount ?? 0,
                'note'             => $request->note,
            ]);

            $customerBill->items()->delete();
            foreach ($request->items as $idx => $item) {
                if (!empty($item['description'])) {
                    $customerBill->items()->create([
                        'description'    => $item['description'],
                        'expense_head_id'=> $item['expense_head_id'] ?? null,
                        'qty'            => $item['qty'] ?? 1,
                        'unit_price'     => $item['unit_price'] ?? 0,
                        'amount'         => $item['amount'] ?? 0,
                        'note'           => $item['note'] ?? null,
                        'sort_order'     => $idx,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Bill updated successfully.', 'redirect' => route('nas-trading.customer-bills.show', $customerBill->id)]);
    }

    public function confirm(NasTradingCustomerBill $customerBill)
    {
        $customerBill->update(['status' => 'Confirmed']);
        return response()->json(['message' => 'Bill confirmed successfully.']);
    }

    public function destroy(NasTradingCustomerBill $customerBill)
    {
        if ($customerBill->status === 'Paid') {
            return response()->json(['message' => 'Cannot delete a Paid bill.'], 422);
        }
        $customerBill->items()->delete();
        $customerBill->delete();
        return response()->json(['message' => 'Bill deleted.']);
    }
}

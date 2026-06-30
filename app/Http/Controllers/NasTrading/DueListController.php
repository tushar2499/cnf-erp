<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingCustomerBill;
use App\Models\NasTrading\NasTradingCustomer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DueListController extends Controller
{
    public function customerDue(Request $request)
    {
        if ($request->ajax()) {
            $query = NasTradingCustomerBill::where('status', 'Confirmed')
                ->when($request->from_date,   fn($q) => $q->whereDate('bill_date', '>=', $request->from_date))
                ->when($request->to_date,     fn($q) => $q->whereDate('bill_date', '<=', $request->to_date))
                ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
                ->latest('bill_date');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date', fn($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('total_amount', fn($r) => number_format($r->total_amount, 2))
                ->addColumn('overdue_days', fn($r) => (int) now()->startOfDay()->diffInDays($r->bill_date->startOfDay(), false) * -1)
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-trading.customer-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem" title="View"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-trading.money-receipts.create') . '?bill_id=' . $r->id . '" class="btn btn-sm btn-outline-success" style="padding:2px 6px;font-size:.7rem" title="Receive Payment"><i class="fa fa-money-bill-wave"></i></a>')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('nas-trading.due-lists.customer');
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasTradingCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->code . ' | ' . $c->company_name])
        );
    }
}

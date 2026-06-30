<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsSupplierBill;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsSupplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DueListController extends Controller
{
    /* ── Customer Due: confirmed bills not yet paid ── */
    public function customerDue(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsCustomerBill::where('status', 'Approved')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->from_date,   fn($q) => $q->whereDate('bill_date', '>=', $request->from_date))
                ->when($request->to_date,     fn($q) => $q->whereDate('bill_date', '<=', $request->to_date))
                ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
                ->latest('bill_date');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date',  fn($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('from_date',  fn($r) => $r->from_date?->format('d-M-Y'))
                ->editColumn('to_date',    fn($r) => $r->to_date?->format('d-M-Y'))
                ->addColumn('overdue_days', fn($r) => (int) now()->startOfDay()->diffInDays($r->bill_date->startOfDay(), false) * -1)
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-freights.customer-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem" title="View"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-freights.money-receipts.create') . '?bill_id=' . $r->id . '" class="btn btn-sm btn-outline-success" style="padding:2px 6px;font-size:.7rem" title="Receive Payment"><i class="fa fa-money-bill-wave"></i></a>'
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('nas-freights.due-lists.customer');
    }

    /* ── Supplier Due: confirmed payment orders not yet paid ── */
    public function supplierDue(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsSupplierBill::where('status', 'Approved')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->from_date,   fn($q) => $q->whereDate('bill_date', '>=', $request->from_date))
                ->when($request->to_date,     fn($q) => $q->whereDate('bill_date', '<=', $request->to_date))
                ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
                ->latest('bill_date');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bill_date',  fn($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('from_date',  fn($r) => $r->from_date?->format('d-M-Y'))
                ->editColumn('to_date',    fn($r) => $r->to_date?->format('d-M-Y'))
                ->addColumn('overdue_days', fn($r) => (int) now()->startOfDay()->diffInDays($r->bill_date->startOfDay(), false) * -1)
                ->addColumn('action', fn($r) =>
                    '<a href="' . route('nas-freights.supplier-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" style="padding:2px 6px;font-size:.7rem" title="View"><i class="fa fa-eye"></i></a> ' .
                    '<a href="' . route('nas-freights.supplier-payments.create') . '?bill_id=' . $r->id . '" class="btn btn-sm btn-outline-success" style="padding:2px 6px;font-size:.7rem" title="Make Payment"><i class="fa fa-money-bill-wave"></i></a>'
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('nas-freights.due-lists.supplier');
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('customer_id', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'customer_id', 'name'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->customer_id . ' | ' . $c->name])
        );
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsSupplier::where('is_active', true)
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn($s) => ['id' => $s->id, 'text' => $s->code . ' | ' . $s->company_name])
        );
    }
}

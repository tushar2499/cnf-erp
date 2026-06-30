<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsSupplierBill;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsSupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsSupplierPayment::where('branch_id', session('nas_freights_branch_id'))->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('payment_date', fn($r) => $r->payment_date?->format('d-M-Y'))
                ->addColumn('action', fn($r) => '
                    <a href="' . route('nas-freights.supplier-payments.show', $r->id) . '" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a>')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('nas-freights.supplier-payments.index');
    }

    public function create()
    {
        return view('nas-freights.supplier-payments.create', [
            'paymentModes' => NasFreightsSupplierPayment::paymentModes(),
        ]);
    }

    public function getBills(Request $request)
    {
        $query = NasFreightsSupplierBill::where('status', 'Approved');
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        return response()->json(
            $query->orderBy('bill_date')->get(['id', 'pay_order_no', 'supplier_name', 'total_amount'])
                ->map(fn($b) => [
                    'id'            => $b->id,
                    'text'          => $b->pay_order_no . ' — ' . $b->supplier_name,
                    'pay_order_no'  => $b->pay_order_no,
                    'total_amount'  => $b->total_amount,
                    'supplier_name' => $b->supplier_name,
                ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_date' => ['required', 'date'],
            'bill_id'      => ['required', 'exists:nas_freights_supplier_bills,id'],
            'amount_paid'  => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required'],
        ]);

        DB::transaction(function () use ($request) {
            $bill = NasFreightsSupplierBill::findOrFail($request->bill_id);

            NasFreightsSupplierPayment::create([
                'branch_id'     => session('nas_freights_branch_id'),
                'payment_no'    => NasFreightsSupplierPayment::generatePaymentNo(),
                'payment_date'  => $request->payment_date,
                'supplier_id'   => $bill->supplier_id,
                'supplier_name' => $bill->supplier_name,
                'bill_id'       => $bill->id,
                'bill_no'       => $bill->pay_order_no,
                'bill_amount'   => $bill->total_amount,
                'amount_paid'   => $request->amount_paid,
                'payment_mode'  => $request->payment_mode,
                'reference_no'  => $request->reference_no,
                'note'          => $request->note,
                'entry_by'      => Auth::user()?->name ?? 'System',
            ]);

            $bill->update(['status' => 'Paid']);
        });

        return response()->json(['message' => 'Supplier payment created successfully.', 'redirect' => route('nas-freights.supplier-payments.index')]);
    }

    public function show(NasFreightsSupplierPayment $supplierPayment)
    {
        return view('nas-freights.supplier-payments.show', compact('supplierPayment'));
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');
        $ids  = NasFreightsSupplierBill::where('status', 'Approved')->pluck('supplier_id')->unique();

        return response()->json(
            NasFreightsSupplier::whereIn('id', $ids)
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn($s) => ['id' => $s->id, 'text' => $s->code . ' | ' . $s->company_name])
        );
    }
}

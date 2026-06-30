<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsSupplierBill;
use App\Models\NasFreights\NasFreightsSupplierBillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupplierBillController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsSupplierBill::where('branch_id', session('nas_freights_branch_id'))->latest())
                ->addIndexColumn()
                ->editColumn('bill_date',  fn($r) => $r->bill_date?->format('d-M-Y'))
                ->editColumn('from_date',  fn($r) => $r->from_date?->format('d-M-Y'))
                ->editColumn('to_date',    fn($r) => $r->to_date?->format('d-M-Y'))
                ->addColumn('status_badge', fn($r) => match($r->status) {
                    'Approved'  => '<span class="badge bg-success">CONFIRMED</span>',
                    'Paid'      => '<span class="badge bg-primary">PAID</span>',
                    'Submitted' => '<span class="badge bg-warning text-dark">SUBMITTED</span>',
                    default     => '<span class="badge bg-secondary">DRAFT</span>',
                })
                ->addColumn('action', function ($r) {
                    $edit = ($r->status === 'Draft' || $r->status === 'Submitted')
                        ? '<a href="' . route('nas-freights.supplier-bills.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a> '
                        : '';
                    $print = '<a href="' . route('nas-freights.supplier-bills.print', $r->id) . '" target="_blank" class="btn btn-sm btn-outline-dark" title="Print"><i class="fa fa-print"></i></a> ';
                    $view = '<a href="' . route('nas-freights.supplier-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a> ' . $print . $edit;
                    $confirm = ($r->status === 'Draft' || $r->status === 'Submitted')
                        ? '<button class="btn btn-sm btn-outline-success btn-confirm" data-url="' . route('nas-freights.supplier-bills.confirm', $r->id) . '" data-name="' . e($r->pay_order_no) . '" title="Confirm"><i class="fa fa-check"></i></button> '
                        : '';
                    $del = ($r->status !== 'Paid')
                        ? '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-freights.supplier-bills.destroy', $r->id) . '" data-name="' . e($r->pay_order_no) . '"><i class="fa fa-trash"></i></button>'
                        : '';
                    return $view . $confirm . $del;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.supplier-bills.index');
    }

    public function create()
    {
        return view('nas-freights.supplier-bills.create');
    }

    public function loadItems(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date'],
        ]);

        $items = NasFreightsBookingItem::with('booking')
            ->whereHas('booking', function ($q) use ($request) {
                $q->whereBetween('job_date', [$request->from_date, $request->to_date]);
                if ($request->supplier_id) {
                    $q->whereHas('items', fn($qi) => $qi->where('supplier_id', $request->supplier_id));
                }
            })
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->get()
            ->map(function ($item) {
                $b  = $item->booking;
                $loc = trim(
                    ($b->customer_name ? $b->customer_name . ' ' : '') .
                    ($item->location_from ?? '') .
                    ($item->location_to ? ' - ' . $item->location_to : '')
                );
                return [
                    'booking_id'      => $b->id,
                    'booking_item_id' => $item->id,
                    'booking_date'    => $b->job_date?->format('d-M-Y'),
                    'entry_date'      => $b->created_at?->format('d-M-Y'),
                    'item_code'       => $item->cover_van_no,
                    'item_name'       => $item->cover_van_no . ($item->location_from ? ' || ' . $item->location_from : ''),
                    'location'        => $loc,
                    'b_qty'           => (float) $item->qty,
                    'd_qty'           => 0,
                    'due_qty'         => (float) $item->qty,
                    'price'           => (float) $item->supplier_rate,
                    'demurrage_day'   => (float) ($item->demurrage_days ?? 0),
                    'demurrage_amount'=> (float) ($item->sup_demurrage_charge ?? 0),
                    'line_amount'     => round((float)$item->qty * (float)$item->supplier_rate + (float)($item->sup_demurrage_charge ?? 0), 2),
                    'notes'           => '',
                ];
            });

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date'],
            'bill_date' => ['required', 'date'],
            'items'     => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () use ($request) {
            $total = collect($request->items)->sum('line_amount');

            $bill = NasFreightsSupplierBill::create([
                'branch_id'     => session('nas_freights_branch_id'),
                'pay_order_no'  => NasFreightsSupplierBill::generatePayOrderNo(),
                'from_date'     => $request->from_date,
                'to_date'       => $request->to_date,
                'supplier_id'   => $request->supplier_id ?: null,
                'supplier_name' => $request->supplier_name,
                'bill_date'     => $request->bill_date,
                'bill_by'       => $request->bill_by,
                'note'          => $request->note,
                'total_amount'  => $total,
                'status'        => 'Draft',
                'entry_by'      => Auth::user()?->name ?? 'System',
            ]);

            foreach ($request->items as $item) {
                NasFreightsSupplierBillItem::create([
                    'bill_id'          => $bill->id,
                    'booking_id'       => $item['booking_id'] ?: null,
                    'booking_item_id'  => $item['booking_item_id'] ?: null,
                    'booking_date'     => $item['booking_date'] ? \Carbon\Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'entry_date'       => $item['entry_date']   ? \Carbon\Carbon::parse($item['entry_date'])->format('Y-m-d')   : null,
                    'item_code'        => $item['item_code']        ?? null,
                    'item_name'        => $item['item_name']        ?? null,
                    'location'         => $item['location']         ?? null,
                    'b_qty'            => $item['b_qty']            ?? 0,
                    'd_qty'            => $item['d_qty']            ?? 0,
                    'due_qty'          => $item['due_qty']          ?? 0,
                    'price'            => $item['price']            ?? 0,
                    'demurrage_day'    => $item['demurrage_day']    ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount']      ?? 0,
                    'notes'            => $item['notes']            ?? null,
                ]);
            }
        });

        return response()->json(['message' => 'Payment order created successfully.', 'redirect' => route('nas-freights.supplier-bills.index')]);
    }

    public function show(NasFreightsSupplierBill $supplierBill)
    {
        $supplierBill->load('items');
        return view('nas-freights.supplier-bills.show', compact('supplierBill'));
    }

    public function printView(NasFreightsSupplierBill $supplierBill)
    {
        $supplierBill->load(['items.booking']);
        $supplier = $supplierBill->supplier_id
            ? NasFreightsSupplier::find($supplierBill->supplier_id)
            : null;
        $company = \App\Models\Company::where('slug', 'nas-freights')->first();
        return view('nas-freights.supplier-bills.print', compact('supplierBill', 'supplier', 'company'));
    }

    public function edit(NasFreightsSupplierBill $supplierBill)
    {
        $supplierBill->load('items');
        return view('nas-freights.supplier-bills.edit', compact('supplierBill'));
    }

    public function update(Request $request, NasFreightsSupplierBill $supplierBill)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date'],
            'bill_date' => ['required', 'date'],
            'items'     => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $supplierBill) {
            $total = collect($request->items)->sum('line_amount');

            $supplierBill->update([
                'from_date'     => $request->from_date,
                'to_date'       => $request->to_date,
                'supplier_id'   => $request->supplier_id ?: null,
                'supplier_name' => $request->supplier_name,
                'bill_date'     => $request->bill_date,
                'bill_by'       => $request->bill_by,
                'note'          => $request->note,
                'total_amount'  => $total,
            ]);

            $supplierBill->items()->delete();
            foreach ($request->items as $item) {
                NasFreightsSupplierBillItem::create([
                    'bill_id'          => $supplierBill->id,
                    'booking_id'       => $item['booking_id'] ?: null,
                    'booking_item_id'  => $item['booking_item_id'] ?: null,
                    'booking_date'     => $item['booking_date'] ? \Carbon\Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'entry_date'       => $item['entry_date']   ? \Carbon\Carbon::parse($item['entry_date'])->format('Y-m-d')   : null,
                    'item_code'        => $item['item_code']        ?? null,
                    'item_name'        => $item['item_name']        ?? null,
                    'location'         => $item['location']         ?? null,
                    'b_qty'            => $item['b_qty']            ?? 0,
                    'd_qty'            => $item['d_qty']            ?? 0,
                    'due_qty'          => $item['due_qty']          ?? 0,
                    'price'            => $item['price']            ?? 0,
                    'demurrage_day'    => $item['demurrage_day']    ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount']      ?? 0,
                    'notes'            => $item['notes']            ?? null,
                ]);
            }
        });

        return response()->json(['message' => 'Payment order updated successfully.', 'redirect' => route('nas-freights.supplier-bills.index')]);
    }

    public function confirm(NasFreightsSupplierBill $supplierBill)
    {
        $supplierBill->update(['status' => 'Approved']);
        return response()->json(['message' => 'Payment order confirmed successfully.']);
    }

    public function destroy(NasFreightsSupplierBill $supplierBill)
    {
        $supplierBill->items()->delete();
        $supplierBill->delete();
        return response()->json(['message' => 'Payment order deleted.']);
    }

    public function searchSuppliers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsSupplier::where('is_active', true)
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where(fn($q) => $q->where('company_name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'company_name'])
                ->map(fn($s) => ['id' => $s->id, 'text' => $s->code . ' | ' . $s->company_name, 'name' => $s->company_name])
        );
    }
}

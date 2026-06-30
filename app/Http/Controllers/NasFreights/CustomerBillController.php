<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\NasFreights\NasFreightsCustomerBillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerBillController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsCustomerBill::where('branch_id', session('nas_freights_branch_id'))->latest();

            return DataTables::of($query)
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
                        ? '<a href="' . route('nas-freights.customer-bills.edit', $r->id) . '" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a> '
                        : '';
                    $view = '<a href="' . route('nas-freights.customer-bills.show', $r->id) . '" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a> '
                          . '<a href="' . route('nas-freights.customer-bills.print', $r->id) . '" target="_blank" class="btn btn-sm btn-outline-dark" title="Print"><i class="fa fa-print"></i></a> '
                          . $edit;
                    $confirm = ($r->status === 'Draft' || $r->status === 'Submitted')
                        ? '<button class="btn btn-sm btn-outline-success btn-confirm" data-url="' . route('nas-freights.customer-bills.confirm', $r->id) . '" data-name="' . e($r->bill_no) . '" title="Confirm"><i class="fa fa-check"></i></button> '
                        : '';
                    $del = ($r->status !== 'Paid')
                        ? '<button class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('nas-freights.customer-bills.destroy', $r->id) . '" data-name="' . e($r->bill_no) . '"><i class="fa fa-trash"></i></button>'
                        : '';
                    return $view . $confirm . $del;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.customer-bills.index');
    }

    public function create()
    {
        return view('nas-freights.customer-bills.create', [
            'deliveryTypes' => NasFreightsCustomerBill::deliveryTypes(),
            'billTypes'     => NasFreightsCustomerBill::billTypes(),
        ]);
    }

    public function loadItems(Request $request)
    {
        $request->validate([
            'from_date'   => ['required', 'date'],
            'to_date'     => ['required', 'date'],
            'customer_id' => ['required'],
        ]);

        // (booking_id, cover_van_no) pairs already present in any customer bill item
        $billedPairs = NasFreightsCustomerBillItem::select('booking_id', 'item_code')
            ->whereNotNull('booking_id')
            ->get()
            ->map(fn($r) => $r->booking_id . '_' . $r->item_code)
            ->flip()
            ->toArray();

        $bookingItems = NasFreightsBookingItem::with('booking')
            ->whereHas('booking', function ($q) use ($request) {
                $q->whereBetween('job_date', [$request->from_date, $request->to_date])
                  ->where('customer_id', $request->customer_id);
            })
            ->get()
            ->filter(fn($item) => !isset($billedPairs[$item->booking_id . '_' . $item->cover_van_no]))
            ->values();

        $firstBooking = $bookingItems->first()?->booking;

        $items = $bookingItems->map(function ($item) {
                $b   = $item->booking;
                $loc = trim(($item->location_from ?? '') . ($item->location_to ? ' - ' . $item->location_to : ''));
                return [
                    'booking_id'      => $b->id,
                    'booking_item_id' => $item->id,
                    'booking_date'    => $b->job_date?->format('d-M-Y'),
                    'delivery_date'   => $b->delivery_date?->format('d-M-Y'),
                    'item_code'       => $item->cover_van_no,
                    'item_name'       => $item->cover_van_no . ($item->location_from ? ' || ' . $item->location_from : ''),
                    'location'        => $loc,
                    'b_qty'           => (float) $item->qty,
                    'd_qty'           => 0,
                    'due_qty'         => (float) $item->qty,
                    'price'           => (float) $item->customer_rate,
                    'disc_percent'    => 0,
                    'discount'        => 0,
                    'ait_percent'     => (float) ($b->ait_percent ?? 0),
                    'demurrage_day'   => (float) ($item->demurrage_days ?: 0),
                    'demurrage_amount'=> (float) ($item->cus_demurrage_charge ?: 0),
                    'line_amount'     => round((float)$item->qty * (float)$item->customer_rate, 2),
                ];
            });

        $customerAddress = '';
        if ($request->customer_id) {
            $customer = NasFreightsCustomer::find($request->customer_id);
            $customerAddress = $customer?->address ?? '';
        }

        return response()->json([
            'items'            => $items,
            'customer_address' => $customerAddress,
            'delivery_type'    => $firstBooking?->sales_type ?? '',
            'tds_percent'      => (float) ($firstBooking?->tds_percent ?? 0),
            'vat_percent'      => (float) ($firstBooking?->vat_percent ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
            'items'         => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () use ($request) {
            $billNo   = NasFreightsCustomerBill::generateBillNo();
            $subTotal = collect($request->items)->sum('line_amount');
            $totalDem = collect($request->items)->sum('demurrage_amount');
            $tdsAmt   = round($subTotal * ($request->tds_percent ?? 0) / 100, 2);
            $vatAmt   = round($subTotal * ($request->vat_percent ?? 0) / 100, 2);

            $bill = NasFreightsCustomerBill::create([
                'bill_no'          => $billNo,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'customer_id'      => $request->customer_id ?: null,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'delivery_no'      => $billNo,
                'delivery_type'    => $request->delivery_type,
                'tds_percent'      => $request->tds_percent ?? 0,
                'tds_amount'       => $tdsAmt,
                'vat_percent'      => $request->vat_percent ?? 0,
                'vat_amount'       => $vatAmt,
                'bill_type'        => $request->bill_type,
                'bill_by'          => $request->bill_by,
                'note'             => $request->note,
                'sub_total'        => $subTotal,
                'total_amount'     => $subTotal + $totalDem + $tdsAmt + $vatAmt,
                'branch_id'        => session('nas_freights_branch_id'),
                'status'           => 'Draft',
                'entry_by'         => \Illuminate\Support\Facades\Auth::user()?->name ?? 'System',
            ]);

            foreach ($request->items as $item) {
                NasFreightsCustomerBillItem::create([
                    'bill_id'         => $bill->id,
                    'booking_id'      => $item['booking_id'] ?: null,
                    'booking_item_id' => $item['booking_item_id'] ?: null,
                    'booking_date'    => $item['booking_date'] ? \Carbon\Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'delivery_date'   => $item['delivery_date'] ? \Carbon\Carbon::parse($item['delivery_date'])->format('Y-m-d') : null,
                    'item_code'       => $item['item_code'] ?? null,
                    'item_name'       => $item['item_name'] ?? null,
                    'location'        => $item['location'] ?? null,
                    'b_qty'           => $item['b_qty'] ?? 0,
                    'd_qty'           => $item['d_qty'] ?? 0,
                    'due_qty'         => $item['due_qty'] ?? 0,
                    'price'           => $item['price'] ?? 0,
                    'disc_percent'    => $item['disc_percent'] ?? 0,
                    'discount'        => $item['discount'] ?? 0,
                    'ait_percent'      => $item['ait_percent'] ?? 0,
                    'demurrage_day'    => $item['demurrage_day'] ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Customer bill created successfully.', 'redirect' => route('nas-freights.customer-bills.index')]);
    }

    public function show(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load('items');
        return view('nas-freights.customer-bills.show', compact('customerBill'));
    }

    public function edit(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load('items');
        return view('nas-freights.customer-bills.edit', [
            'customerBill'  => $customerBill,
            'deliveryTypes' => NasFreightsCustomerBill::deliveryTypes(),
            'billTypes'     => NasFreightsCustomerBill::billTypes(),
        ]);
    }

    public function update(Request $request, NasFreightsCustomerBill $customerBill)
    {
        $request->validate([
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
            'items'         => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $customerBill) {
            $subTotal = collect($request->items)->sum('line_amount');
            $totalDem = collect($request->items)->sum('demurrage_amount');
            $tdsAmt   = round($subTotal * ($request->tds_percent ?? 0) / 100, 2);
            $vatAmt   = round($subTotal * ($request->vat_percent ?? 0) / 100, 2);

            $customerBill->update([
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'customer_id'      => $request->customer_id ?: null,
                'customer_name'    => $request->customer_name,
                'customer_address' => $request->customer_address,
                'bill_date'        => $request->bill_date,
                'delivery_type'    => $request->delivery_type,
                'tds_percent'      => $request->tds_percent ?? 0,
                'tds_amount'       => $tdsAmt,
                'vat_percent'      => $request->vat_percent ?? 0,
                'vat_amount'       => $vatAmt,
                'bill_type'        => $request->bill_type,
                'bill_by'          => $request->bill_by,
                'note'             => $request->note,
                'sub_total'        => $subTotal,
                'total_amount'     => $subTotal + $totalDem + $tdsAmt + $vatAmt,
            ]);

            $customerBill->items()->delete();
            foreach ($request->items as $item) {
                NasFreightsCustomerBillItem::create([
                    'bill_id'         => $customerBill->id,
                    'booking_id'      => $item['booking_id'] ?: null,
                    'booking_item_id' => $item['booking_item_id'] ?: null,
                    'booking_date'    => $item['booking_date'] ? \Carbon\Carbon::parse($item['booking_date'])->format('Y-m-d') : null,
                    'delivery_date'   => $item['delivery_date'] ? \Carbon\Carbon::parse($item['delivery_date'])->format('Y-m-d') : null,
                    'item_code'       => $item['item_code'] ?? null,
                    'item_name'       => $item['item_name'] ?? null,
                    'location'        => $item['location'] ?? null,
                    'b_qty'           => $item['b_qty'] ?? 0,
                    'd_qty'           => $item['d_qty'] ?? 0,
                    'due_qty'         => $item['due_qty'] ?? 0,
                    'price'           => $item['price'] ?? 0,
                    'disc_percent'    => $item['disc_percent'] ?? 0,
                    'discount'        => $item['discount'] ?? 0,
                    'ait_percent'      => $item['ait_percent'] ?? 0,
                    'demurrage_day'    => $item['demurrage_day'] ?? 0,
                    'demurrage_amount' => $item['demurrage_amount'] ?? 0,
                    'line_amount'      => $item['line_amount'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Customer bill updated successfully.', 'redirect' => route('nas-freights.customer-bills.index')]);
    }

    public function printView(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->load(['items.booking.products', 'items.bookingItem']);
        return view('nas-freights.customer-bills.print', compact('customerBill'));
    }

    public function confirm(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->update(['status' => 'Approved']);
        return response()->json(['message' => 'Bill confirmed successfully.']);
    }

    public function destroy(NasFreightsCustomerBill $customerBill)
    {
        $customerBill->items()->delete();
        $customerBill->delete();
        return response()->json(['message' => 'Bill deleted.']);
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsCustomer::where('status', 'Active')
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('customer_id', 'like', "%{$term}%")->orWhere('mobile', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'customer_id', 'name', 'mobile', 'address'])
                ->map(fn($c) => [
                    'id'      => $c->id,
                    'text'    => $c->customer_id . '|' . $c->name . '|' . $c->mobile,
                    'name'    => $c->name,
                    'address' => $c->address,
                ])
        );
    }
}

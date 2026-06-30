<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsBookingProduct;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsSupplier;
use App\Models\NasFreights\NasFreightsVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsBooking::with(['items', 'products'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('item_details', fn($r) => e($r->products->pluck('goods_name')->join(', ') ?: $r->goods_name))
                ->addColumn('t_qty', fn($r) => number_format($r->items->sum('qty'), 2))
                ->addColumn('item_amount', fn($r) => number_format($r->items->sum('amount'), 2))
                ->addColumn('status_badge', fn($r) => match($r->status) {
                    'Approved'  => '<span class="badge bg-success">APPROVED</span>',
                    'Submitted' => '<span class="badge bg-warning text-dark">SUBMITTED</span>',
                    'Rejected'  => '<span class="badge bg-danger">REJECTED</span>',
                    default     => '<span class="badge bg-secondary">DRAFT</span>',
                })
                ->addColumn('billed_badge', function ($r) {
                    $billed = \App\Models\NasFreights\NasFreightsCustomerBillItem::where('booking_id', $r->id)->exists();
                    return $billed
                        ? '<span class="badge bg-success">BILLED</span>'
                        : '<span class="badge bg-secondary">NOT BILLED</span>';
                })
                ->addColumn('action', function ($r) {
                    $canAct = !in_array($r->status, ['Approved', 'Rejected']);
                    $html   = '<div class="d-flex flex-nowrap gap-1">';
                    $html  .= '<a href="' . route('nas-freights.bookings.edit', $r->id) . '" class="btn btn-xs btn-outline-dark" title="Print"><i class="fa fa-print"></i></a>';
                    if ($canAct) {
                        $html .= '<a href="' . route('nas-freights.bookings.edit', $r->id) . '" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>';
                        $html .= '<button class="btn btn-xs btn-success btn-confirm"
                            data-url="' . route('nas-freights.bookings.confirm', $r->id) . '"
                            data-no="' . e($r->job_no) . '">Confirm</button>';
                        $html .= '<button class="btn btn-xs btn-danger btn-reject"
                            data-url="' . route('nas-freights.bookings.reject', $r->id) . '"
                            data-no="' . e($r->job_no) . '">Reject</button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['item_details', 'status_badge', 'billed_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.bookings.index');
    }

    public function confirm(NasFreightsBooking $booking)
    {
        $booking->update(['status' => 'Approved']);
        return response()->json(['message' => 'Booking ' . $booking->job_no . ' confirmed.']);
    }

    public function reject(NasFreightsBooking $booking)
    {
        $booking->update(['status' => 'Rejected']);
        return response()->json(['message' => 'Booking ' . $booking->job_no . ' rejected.']);
    }

    public function create()
    {
        return view('nas-freights.bookings.create', $this->formData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_prefix'         => ['required'],
            'sales_type'             => ['required'],
            'job_date'               => ['required', 'date'],
            'customer_id'            => ['required'],
            'delivery_date'          => ['required', 'date'],
            'cover_van_no'           => ['required', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'products'               => ['nullable', 'array'],
            'products.*.goods_name'  => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request) {
            $firstProduct = $request->products[0] ?? [];

            $booking = NasFreightsBooking::create([
                'job_no'            => NasFreightsBooking::generateJobNo(),
                'booking_prefix'    => $request->booking_prefix,
                'sales_type'        => $request->sales_type,
                'sales_person_id'   => $request->sales_person_id ?: null,
                'sales_person_name' => $request->sales_person_name,
                'job_date'          => $request->job_date,
                'goods_name'        => $firstProduct['goods_name'] ?? '',
                'customer_id'       => $request->customer_id,
                'customer_name'     => $request->customer_name,
                'delivery_address'  => $request->delivery_address,
                'lc_no'             => $request->lc_no,
                'invoice_no'        => $request->invoice_no,
                'delivery_date'     => $request->delivery_date,
                'po_number'         => $request->po_number,
                'cover_van_no'      => $request->cover_van_no,
                'note'              => $request->note,
                'tds_section'       => $request->tds_section,
                'tds_percent'       => $request->tds_percent ?? 0,
                'tds_amount'        => $request->tds_amount ?? 0,
                'vat_percent'       => $request->vat_percent ?? 0,
                'vat_amount'        => $request->vat_amount ?? 0,
                'ait_percent'       => $request->ait_percent ?? 0,
                'ait_amount'        => $request->ait_amount ?? 0,
                'total_amount'      => $request->total_amount ?? 0,
                'discount'          => $request->discount ?? 0,
                'forfeited_amount'  => $request->forfeited_amount ?? 0,
                'status'            => 'Draft',
                'delivery_status'   => 'Pending',
                'entry_by'          => auth()->user()?->name ?? 'System',
                'branch_id'         => session('nas_freights_branch_id'),
            ]);

            foreach ($request->products ?? [] as $product) {
                NasFreightsBookingProduct::create([
                    'booking_id'  => $booking->id,
                    'goods_name'  => $product['goods_name'] ?? '',
                    'qty'         => $product['qty'] ?? 0,
                    'qty_unit'    => $product['qty_unit'] ?? null,
                    'net_weight'  => $product['net_weight'] ?? 0,
                    'weight_unit' => $product['weight_unit'] ?? null,
                ]);
            }

            foreach ($request->items as $item) {
                NasFreightsBookingItem::create([
                    'booking_id'          => $booking->id,
                    'cover_van_no'        => $item['cover_van_no'] ?? null,
                    'capacity'            => $item['capacity'] ?? null,
                    'supplier_id'         => $item['supplier_id'] ?: null,
                    'supplier_name'       => $item['supplier_name'] ?? null,
                    'qty'                 => $item['qty'] ?? 1,
                    'supplier_rate'       => $item['supplier_rate'] ?? 0,
                    'customer_rate'       => $item['customer_rate'] ?? 0,
                    'demurrage_days'      => $item['demurrage_days'] ?? 0,
                    'cus_demurrage_charge'=> $item['cus_demurrage_charge'] ?? 0,
                    'sup_demurrage_charge'=> $item['sup_demurrage_charge'] ?? 0,
                    'amount'              => $item['amount'] ?? 0,
                    'location_from'       => $item['location_from'] ?? null,
                    'location_to'         => $item['location_to'] ?? null,
                ]);
            }
        });

        return response()->json(['message' => 'Booking created successfully.', 'redirect' => route('nas-freights.bookings.index')]);
    }

    public function edit(NasFreightsBooking $booking)
    {
        $booking->load(['items', 'products']);
        return view('nas-freights.bookings.edit', array_merge($this->formData(), compact('booking')));
    }

    public function update(Request $request, NasFreightsBooking $booking)
    {
        $request->validate([
            'booking_prefix'         => ['required'],
            'sales_type'             => ['required'],
            'job_date'               => ['required', 'date'],
            'customer_id'            => ['required'],
            'delivery_date'          => ['required', 'date'],
            'cover_van_no'           => ['required', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'products'               => ['nullable', 'array'],
            'products.*.goods_name'  => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $booking) {
            $firstProduct = $request->products[0] ?? [];

            $booking->update([
                'booking_prefix'    => $request->booking_prefix,
                'sales_type'        => $request->sales_type,
                'sales_person_id'   => $request->sales_person_id ?: null,
                'sales_person_name' => $request->sales_person_name,
                'job_date'          => $request->job_date,
                'goods_name'        => $firstProduct['goods_name'] ?? $booking->goods_name,
                'customer_id'       => $request->customer_id,
                'customer_name'     => $request->customer_name,
                'delivery_address'  => $request->delivery_address,
                'lc_no'             => $request->lc_no,
                'invoice_no'        => $request->invoice_no,
                'delivery_date'     => $request->delivery_date,
                'po_number'         => $request->po_number,
                'cover_van_no'      => $request->cover_van_no,
                'note'              => $request->note,
                'tds_section'       => $request->tds_section,
                'tds_percent'       => $request->tds_percent ?? 0,
                'tds_amount'        => $request->tds_amount ?? 0,
                'vat_percent'       => $request->vat_percent ?? 0,
                'vat_amount'        => $request->vat_amount ?? 0,
                'ait_percent'       => $request->ait_percent ?? 0,
                'ait_amount'        => $request->ait_amount ?? 0,
                'total_amount'      => $request->total_amount ?? 0,
            ]);

            $booking->products()->delete();
            foreach ($request->products ?? [] as $product) {
                NasFreightsBookingProduct::create([
                    'booking_id'  => $booking->id,
                    'goods_name'  => $product['goods_name'] ?? '',
                    'qty'         => $product['qty'] ?? 0,
                    'qty_unit'    => $product['qty_unit'] ?? null,
                    'net_weight'  => $product['net_weight'] ?? 0,
                    'weight_unit' => $product['weight_unit'] ?? null,
                ]);
            }

            $booking->items()->delete();
            foreach ($request->items as $item) {
                NasFreightsBookingItem::create([
                    'booking_id'          => $booking->id,
                    'cover_van_no'        => $item['cover_van_no'] ?? null,
                    'capacity'            => $item['capacity'] ?? null,
                    'supplier_id'         => $item['supplier_id'] ?: null,
                    'supplier_name'       => $item['supplier_name'] ?? null,
                    'qty'                 => $item['qty'] ?? 1,
                    'supplier_rate'       => $item['supplier_rate'] ?? 0,
                    'customer_rate'       => $item['customer_rate'] ?? 0,
                    'demurrage_days'      => $item['demurrage_days'] ?? 0,
                    'cus_demurrage_charge'=> $item['cus_demurrage_charge'] ?? 0,
                    'sup_demurrage_charge'=> $item['sup_demurrage_charge'] ?? 0,
                    'amount'              => $item['amount'] ?? 0,
                    'location_from'       => $item['location_from'] ?? null,
                    'location_to'         => $item['location_to'] ?? null,
                ]);
            }
        });

        return response()->json(['message' => 'Booking updated successfully.', 'redirect' => route('nas-freights.bookings.index')]);
    }

    public function destroy(NasFreightsBooking $booking)
    {
        $booking->items()->delete();
        $booking->delete();
        return response()->json(['message' => 'Booking deleted.']);
    }

    public function searchEmployees(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsEmployee::where('status', 'Active')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'code', 'name'])
                ->map(fn($e) => ['id' => $e->id, 'text' => $e->code . ' — ' . $e->name, 'name' => $e->name])
        );
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsCustomer::where('status', 'Active')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where(fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('customer_id', 'like', "%{$term}%")->orWhere('mobile', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'customer_id', 'name', 'mobile', 'address'])
                ->map(fn($c) => ['id' => $c->id, 'text' => $c->customer_id . '|' . $c->name . '|' . $c->mobile, 'name' => $c->name, 'address' => $c->address])
        );
    }

    public function searchVehicles(Request $request)
    {
        $term = $request->input('q', '');
        return response()->json(
            NasFreightsVehicle::where('status', 'Active')
                ->where('branch_id', session('nas_freights_branch_id'))
                ->where(fn($q) => $q->where('vehicle_number', 'like', "%{$term}%")->orWhere('vehicle_name', 'like', "%{$term}%"))
                ->limit(15)->get(['id', 'vehicle_number', 'vehicle_name', 'vehicle_type'])
                ->map(fn($v) => ['id' => $v->vehicle_number, 'text' => $v->vehicle_number . ($v->vehicle_name ? ' — ' . $v->vehicle_name : ''), 'vehicle_type' => $v->vehicle_type])
        );
    }

    private function formData(): array
    {
        return [
            'bookingPrefixes' => NasFreightsBooking::bookingPrefixes(),
            'salesTypes'      => NasFreightsBooking::salesTypes(),
            'qtyUnits'        => NasFreightsBooking::qtyUnits(),
            'weightUnits'     => NasFreightsBooking::weightUnits(),
            'suppliers'       => NasFreightsSupplier::where('is_active', true)->orderBy('company_name')->get(['id', 'code', 'company_name']),
        ];
    }
}

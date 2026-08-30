<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsContainerType;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsFreightBooking;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsPackageType;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FreightBookingController extends Controller
{
    private function formData(): array
    {
        return [
            'serviceTypes'   => NasFreightsFreightBooking::serviceTypes(),
            'statuses'       => NasFreightsFreightBooking::statuses(),
            'incoterms'      => ['EXW', 'FCA', 'FAS', 'FOB', 'CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'],
            'currencies'     => ['BDT', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'INR', 'SGD', 'AUD', 'AED'],
            'weightUnits'    => ['KG', 'MT', 'LB'],
            'containerSizes' => NasFreightsContainerType::active()->pluck('name'),
            'packageTypes'   => NasFreightsPackageType::active()->pluck('name'),
            'today'          => now()->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $query = NasFreightsFreightBooking::with(['customer', 'shippingCarrier'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->status_filter, fn ($q, $s) => $q->where('status', $s))
                ->when($fromDate, fn ($q) => $q->whereDate('booking_date', '>=', $fromDate))
                ->when($toDate, fn ($q) => $q->whereDate('booking_date', '<=', $toDate))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('booking_date', fn ($r) => $r->booking_date?->format('d M Y') ?? '—')
                ->addColumn('customer_name', fn ($r) => $r->customer?->name ?? '—')
                ->addColumn('igm_no', fn ($r) => $r->igm_no ?? '—')
                ->addColumn('route', fn ($r) => ($r->pol ?? '—').' → '.($r->pod ?? '—'))
                ->addColumn('carrier', fn ($r) => $r->shippingCarrier?->name ?? '—')
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Confirmed'   => '<span class="badge bg-success">Confirmed</span>',
                    'In-Transit'  => '<span class="badge bg-info text-dark">In-Transit</span>',
                    'Delivered'   => '<span class="badge bg-primary">Delivered</span>',
                    'Cancelled'   => '<span class="badge bg-danger">Cancelled</span>',
                    default       => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', fn ($r) => '
                    <a href="'.route('nas-freights.freight-import-bookings.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View"><i class="fa fa-eye"></i></a>
                    <a href="'.route('nas-freights.freight-import-bookings.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                        data-url="'.route('nas-freights.freight-import-bookings.destroy', $r->id).'"
                        data-name="'.e($r->freight_booking_no).'"><i class="fa fa-trash"></i></button>')
                ->filterColumn('customer_name', fn ($q, $k) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->filterColumn('igm_no', fn ($q, $k) => $q->where('igm_no', 'like', "%{$k}%"))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.freight-import-bookings.index');
    }

    public function create()
    {
        return view('nas-freights.freight-import-bookings.create', array_merge($this->formData(), [
            'freightBooking' => null,
            'existingItems'  => [],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_date' => ['required', 'date'],
            'service_type' => ['required'],
        ]);

        DB::transaction(function () use ($request) {
            $freightBooking = NasFreightsFreightBooking::create(array_merge($this->prepareData($request), [
                'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            ]));
            $this->saveItems($freightBooking, $request->input('items', []));
        });

        return redirect()->route('nas-freights.freight-import-bookings.index')
            ->with('success', 'Freight Import Booking created successfully.');
    }

    public function show(NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->load(['customer', 'salesperson', 'overseasAgent', 'shippingCarrier', 'rfq', 'items']);

        return view('nas-freights.freight-import-bookings.show', compact('freightBooking'));
    }

    public function edit(NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->load(['items', 'overseasAgent', 'shippingCarrier']);
        $existingItems = $freightBooking->items->map(fn ($i) => [
            'item_type'          => $i->item_type,
            'container_size'     => $i->container_size,
            'container_no'       => $i->container_no,
            'seal_no'            => $i->seal_no,
            'package_type'       => $i->package_type,
            'hs_code'            => $i->hs_code,
            'commodity'          => $i->commodity,
            'quantity'           => $i->quantity,
            'gross_weight'       => $i->gross_weight,
            'weight_unit'        => $i->weight_unit,
            'volume_cbm'         => $i->volume_cbm,
            'country_of_origin'  => $i->country_of_origin,
            'is_dangerous_goods' => $i->is_dangerous_goods ? '1' : '0',
            'special_handling'   => $i->special_handling,
        ])->values();

        return view('nas-freights.freight-import-bookings.create', array_merge($this->formData(), [
            'freightBooking' => $freightBooking,
            'existingItems'  => $existingItems,
        ]));
    }

    public function update(Request $request, NasFreightsFreightBooking $freightBooking)
    {
        $request->validate([
            'booking_date' => ['required', 'date'],
            'service_type' => ['required'],
        ]);

        DB::transaction(function () use ($request, $freightBooking) {
            $freightBooking->update($this->prepareData($request));
            $freightBooking->items()->delete();
            $this->saveItems($freightBooking, $request->input('items', []));
        });

        return back()->with('success', 'Freight Import Booking '.$freightBooking->freight_booking_no.' updated.');
    }

    public function destroy(NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->delete();

        return response()->json(['message' => 'Freight Import Booking '.$freightBooking->freight_booking_no.' deleted.']);
    }

    public function searchCustomers(Request $request)
    {
        $q = $request->get('q', '');

        return response()->json(
            NasFreightsCustomer::where('name', 'like', '%'.$q.'%')
                ->orWhere('customer_id', 'like', '%'.$q.'%')
                ->limit(20)
                ->select(['id', 'name', 'customer_id', 'address'])
                ->get()
                ->map(fn ($c) => ['id' => $c->id, 'text' => $c->customer_id.' — '.$c->name, 'name' => $c->name, 'address' => $c->address])
        );
    }

    public function searchEmployees(Request $request)
    {
        $q = $request->get('q', '');

        return response()->json(
            NasFreightsEmployee::where('name', 'like', '%'.$q.'%')
                ->where('is_active', true)
                ->limit(20)
                ->select(['id', 'name', 'code'])
                ->get()
                ->map(fn ($e) => ['id' => $e->id, 'text' => $e->name])
        );
    }

    public function searchOverseasAgents(Request $request)
    {
        $q = $request->get('q', '');

        return response()->json(
            NasFreightsOverseasAgent::where('is_active', true)
                ->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('agent_code', 'like', '%'.$q.'%')
                    ->orWhere('country', 'like', '%'.$q.'%'))
                ->limit(20)
                ->get(['id', 'agent_code', 'name', 'country', 'city'])
                ->map(fn ($a) => ['id' => $a->id, 'text' => $a->agent_code.' — '.$a->name.' ('.$a->country.')'])
        );
    }

    public function searchShippingCarriers(Request $request)
    {
        $q = $request->get('q', '');

        return response()->json(
            NasFreightsShippingCarrier::where('is_active', true)
                ->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('carrier_code', 'like', '%'.$q.'%')
                    ->orWhere('scac_code', 'like', '%'.$q.'%'))
                ->limit(20)
                ->get(['id', 'carrier_code', 'name', 'scac_code'])
                ->map(fn ($c) => ['id' => $c->id, 'text' => $c->carrier_code.' — '.$c->name.($c->scac_code ? ' ('.$c->scac_code.')' : '')])
        );
    }

    private function prepareData(Request $request): array
    {
        return [
            'branch_id'             => session('nas_freights_branch_id'),
            'customer_id'           => $request->customer_id ?: null,
            'salesperson_id'        => $request->salesperson_id ?: null,
            'overseas_agent_id'     => $request->overseas_agent_id ?: null,
            'shipping_carrier_id'   => $request->shipping_carrier_id ?: null,
            'booking_date'          => $request->booking_date,
            'service_type'          => $request->service_type,
            'incoterms'             => $request->incoterms ?: null,
            'currency'              => $request->currency ?: 'BDT',
            'pol'                   => $request->pol ?: null,
            'pod'                   => $request->pod ?: null,
            'place_of_receipt'      => $request->place_of_receipt ?: null,
            'place_of_delivery'     => $request->place_of_delivery ?: null,
            'commodity_description' => $request->commodity_description ?: null,
            'vessel_name'           => $request->vessel_name ?: null,
            'voyage_no'             => $request->voyage_no ?: null,
            'bl_no'                 => $request->bl_no ?: null,
            'igm_no'                => $request->igm_no ?: null,
            'delivery_order_no'     => $request->delivery_order_no ?: null,
            'etd'                   => $request->etd ?: null,
            'eta'                   => $request->eta ?: null,
            'status'                => $request->status ?: 'Draft',
            'remarks'               => $request->remarks ?: null,
        ];
    }

    private function saveItems(NasFreightsFreightBooking $freightBooking, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['item_type'])) {
                continue;
            }

            $freightBooking->items()->create([
                'item_type'          => $item['item_type'],
                'container_size'     => $item['item_type'] === 'container' ? ($item['container_size'] ?? null) : null,
                'container_no'       => $item['item_type'] === 'container' ? ($item['container_no'] ?? null) : null,
                'seal_no'            => $item['item_type'] === 'container' ? ($item['seal_no'] ?? null) : null,
                'package_type'       => $item['item_type'] === 'package' ? ($item['package_type'] ?? null) : null,
                'hs_code'            => $item['hs_code'] ?? null,
                'commodity'          => $item['commodity'] ?? null,
                'quantity'           => max(1, (int) ($item['quantity'] ?? 1)),
                'gross_weight'       => is_numeric($item['gross_weight'] ?? '') ? $item['gross_weight'] : null,
                'weight_unit'        => $item['weight_unit'] ?? 'KG',
                'volume_cbm'         => is_numeric($item['volume_cbm'] ?? '') ? $item['volume_cbm'] : null,
                'country_of_origin'  => $item['country_of_origin'] ?? null,
                'is_dangerous_goods' => ! empty($item['is_dangerous_goods']),
                'special_handling'   => $item['special_handling'] ?? null,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightImportBooking\CreateFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\DestroyFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\EditFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\IndexFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\ShowFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\StoreFreightImportBookingRequest;
use App\Http\Requests\NasFreights\FreightImportBooking\UpdateFreightImportBookingRequest;
use App\Models\Employee;
use App\Models\NasFreights\NasFreightsContainerType;
use App\Models\NasFreights\NasFreightsCustomer;
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

    public function index(IndexFreightImportBookingRequest $request)
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

    public function create(CreateFreightImportBookingRequest $request)
    {
        return view('nas-freights.freight-import-bookings.create', array_merge($this->formData(), [
            'freightBooking' => null,
            'existingItems'  => [],
        ]));
    }

    public function store(StoreFreightImportBookingRequest $request)
    {

        DB::transaction(function () use ($request) {
            $freightBooking = NasFreightsFreightBooking::create(array_merge($this->prepareData($request), [
                'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            ]));
            $this->saveItems($freightBooking, $request->input('items', []));
        });

        return redirect()->route('nas-freights.freight-import-bookings.index')
            ->with('success', 'Freight Import Booking/Job created successfully.');
    }

    public function show(ShowFreightImportBookingRequest $request, NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->load(['customer', 'salesperson', 'overseasAgent', 'shippingCarrier', 'rfq', 'items']);

        return view('nas-freights.freight-import-bookings.show', compact('freightBooking'));
    }

    public function edit(EditFreightImportBookingRequest $request, NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->load(['items', 'overseasAgent', 'shippingCarrier']);
        $existingItems = $freightBooking->items->map(fn ($i) => [
            'item_type'          => $i->item_type,
            'container_size'     => $i->container_size,
            'container_no'       => $i->container_no,
            'seal_no'            => $i->seal_no,
            'package_type'       => $i->item_type === 'package' ? $i->package_type : null,
            'package_qty'        => $i->item_type === 'container' ? $i->package_qty : null,
            'package_unit'       => $i->item_type === 'container' ? $i->package_type : null,
            'quantity'           => $i->quantity,
            'net_weight'         => $i->net_weight,
            'gross_weight'       => $i->gross_weight,
            'chargeable_weight'  => $i->chargeable_weight,
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

    public function update(UpdateFreightImportBookingRequest $request, NasFreightsFreightBooking $freightBooking)
    {

        DB::transaction(function () use ($request, $freightBooking) {
            $freightBooking->update($this->prepareData($request));
            $freightBooking->items()->delete();
            $this->saveItems($freightBooking, $request->input('items', []));
        });

        return back()->with('success', 'Freight Import Booking/Job '.$freightBooking->freight_booking_no.' updated.');
    }

    public function destroy(DestroyFreightImportBookingRequest $request, NasFreightsFreightBooking $freightBooking)
    {
        $freightBooking->delete();

        return response()->json(['message' => 'Freight Import Booking/Job '.$freightBooking->freight_booking_no.' deleted.']);
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
            Employee::where('name', 'like', '%'.$q.'%')
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
            'customer_invoice_no'   => $request->customer_invoice_no ?: null,
            'customer_invoice_date' => $request->customer_invoice_date ?: null,
            'agent_invoice_no'      => $request->agent_invoice_no ?: null,
            'salesperson_id'        => $request->salesperson_id ?: null,
            'overseas_agent_id'     => $request->overseas_agent_id ?: null,
            'shipping_carrier_id'   => $request->shipping_carrier_id ?: null,
            'booking_date'          => $request->booking_date,
            'service_type'          => $request->service_type,
            'incoterms'             => $request->incoterms ?: null,
            'currency'              => $request->currency ?: 'BDT',
            'exchange_rate'         => $request->exchange_rate ?: null,
            'buy_amount'            => $request->buy_amount ?: null,
            'buy_bdt_amount'        => ($request->buy_amount && $request->exchange_rate)
                                        ? round((float) $request->buy_amount * (float) $request->exchange_rate, 2)
                                        : null,
            'sell_amount'           => $request->sell_amount ?: null,
            'sell_bdt_amount'       => ($request->sell_amount && $request->exchange_rate)
                                        ? round((float) $request->sell_amount * (float) $request->exchange_rate, 2)
                                        : null,
            'pol'                   => $request->pol ?: null,
            'pod'                   => $request->pod ?: null,
            'commodity_description' => $request->commodity_description ?: null,
            'hs_codes'              => $this->normalizeHsCodes($request->input('hs_codes', [])),
            'vessel_name'           => $request->vessel_name ?: null,
            'voyage_no'             => $request->voyage_no ?: null,
            'flight_no'             => $request->flight_no ?: null,
            'flight_date'           => $request->flight_date ?: null,
            'bl_no'                 => $request->bl_no ?: null,
            'mbl_mawb_no'           => $request->mbl_mawb_no ?: null,
            'mbl_mawb_date'         => $request->mbl_mawb_date ?: null,
            'hbl_hawb_no'           => $request->hbl_hawb_no ?: null,
            'hbl_hawb_date'         => $request->hbl_hawb_date ?: null,
            'lc_no'                 => $request->lc_no ?: null,
            'cad_no'                => $request->cad_no ?: null,
            'tt_no'                 => $request->tt_no ?: null,
            'rfq_tender_no'         => $request->rfq_tender_no ?: null,
            'etd'                   => $request->etd ?: null,
            'eta'                   => $request->eta ?: null,
            'status'                => $request->status ?: 'Draft',
            'remarks'               => $request->remarks ?: null,
        ];
    }

    /**
     * @param  array<int, mixed>  $codes
     * @return array<int, string>|null
     */
    private function normalizeHsCodes(array $codes): ?array
    {
        $clean = collect($codes)
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $clean ?: null;
    }

    /**
     * Air shipments carry gross + chargeable weight; every other mode carries net + gross weight.
     */
    private function saveItems(NasFreightsFreightBooking $freightBooking, array $items): void
    {
        $isAir = $freightBooking->isAir();

        foreach ($items as $item) {
            if (empty($item['item_type'])) {
                continue;
            }

            $isContainer = $item['item_type'] === 'container';

            $freightBooking->items()->create([
                'item_type'          => $item['item_type'],
                'container_size'     => $isContainer ? ($item['container_size'] ?? null) : null,
                'container_no'       => $isContainer ? ($item['container_no'] ?? null) : null,
                'seal_no'            => $isContainer ? ($item['seal_no'] ?? null) : null,
                'package_type'       => match ($item['item_type']) {
                    'container' => ($item['package_unit'] ?? null) ?: null,
                    'package'   => ($item['package_type'] ?? null) ?: null,
                    default     => null,
                },
                'package_qty'        => $isContainer && is_numeric($item['package_qty'] ?? '') ? max(1, (int) $item['package_qty']) : null,
                'quantity'           => max(1, (int) ($item['quantity'] ?? 1)),
                'net_weight'         => ! $isAir && is_numeric($item['net_weight'] ?? '') ? $item['net_weight'] : null,
                'gross_weight'       => is_numeric($item['gross_weight'] ?? '') ? $item['gross_weight'] : null,
                'chargeable_weight'  => $isAir && is_numeric($item['chargeable_weight'] ?? '') ? $item['chargeable_weight'] : null,
                'weight_unit'        => $item['weight_unit'] ?? 'KG',
                'volume_cbm'         => is_numeric($item['volume_cbm'] ?? '') ? $item['volume_cbm'] : null,
                'country_of_origin'  => $item['country_of_origin'] ?? null,
                'is_dangerous_goods' => ! empty($item['is_dangerous_goods']),
                'special_handling'   => $item['special_handling'] ?? null,
            ]);
        }
    }
}

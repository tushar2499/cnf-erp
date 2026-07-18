<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingProduct;
use App\Models\NasFreights\NasFreightsContainerType;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsPackageType;
use App\Models\NasFreights\NasFreightsRfq;
use App\Models\NasFreights\NasFreightsRfqItem;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RfqController extends Controller
{
    private function formData(): array
    {
        return [
            'types'             => NasFreightsRfq::types(),
            'serviceTypes'      => NasFreightsRfq::serviceTypes(),
            'statuses'          => NasFreightsRfq::statuses(),
            'incoterms'         => NasFreightsRfq::incoterms(),
            'currencies'        => NasFreightsRfq::currencies(),
            'lostReasons'       => NasFreightsRfq::lostReasons(),
            'containerSizes'    => NasFreightsContainerType::active()->pluck('name'),
            'packageTypes'      => NasFreightsPackageType::active()->pluck('name'),
            'weightUnits'       => NasFreightsRfqItem::weightUnits(),
            'today'             => now()->format('Y-m-d'),
            'defaultValidUntil' => now()->addDays(30)->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NasFreightsRfq::with(['customer'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->status_filter, fn ($q, $s) => $q->where('status', $s));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('rfq_date', fn ($r) => $r->rfq_date?->format('d M Y') ?? '—')
                ->editColumn('valid_until', fn ($r) => $r->valid_until?->format('d M Y') ?? '—')
                ->addColumn('customer_name', fn ($r) => $r->customer?->name ?? '—')
                ->addColumn('type_badge', fn ($r) => $r->type === 'import'
                    ? '<span class="badge bg-info text-dark">Import</span>'
                    : '<span class="badge bg-warning text-dark">Export</span>')
                ->addColumn('route', fn ($r) => ($r->pol ?? '—').' → '.($r->pod ?? '—'))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'Win'     => '<span class="badge bg-success">Win</span>',
                    'Lose'    => '<span class="badge bg-danger">Lose</span>',
                    default   => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', fn ($r) => '
                    <a href="'.route('nas-freights.rfqs.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View"><i class="fa fa-eye"></i></a>
                    <a href="'.route('nas-freights.rfqs.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                        data-url="'.route('nas-freights.rfqs.destroy', $r->id).'"
                        data-name="'.e($r->rfq_no).'"><i class="fa fa-trash"></i></button>')
                ->filterColumn('customer_name', fn ($q, $k) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.rfqs.index');
    }

    public function create()
    {
        return view('nas-freights.rfqs.create', array_merge($this->formData(), [
            'rfq'           => null,
            'existingItems' => [],
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfq_date' => ['required', 'date'],
            'type'     => ['required', 'in:import,export'],
        ]);

        DB::transaction(function () use ($request) {
            $rfq = NasFreightsRfq::create(array_merge($this->prepareData($request), [
                'rfq_no' => NasFreightsRfq::generateRfqNo(),
            ]));
            $this->saveItems($rfq, $request->input('items', []));
        });

        return redirect()->route('nas-freights.rfqs.index')
            ->with('success', 'RFQ created successfully.');
    }

    public function show(NasFreightsRfq $rfq)
    {
        $rfq->load(['customer', 'salesperson', 'convertedBooking', 'items', 'overseasAgent', 'shippingCarrier']);

        return view('nas-freights.rfqs.show', [
            'rfq'         => $rfq,
            'lostReasons' => NasFreightsRfq::lostReasons(),
        ]);
    }

    public function edit(NasFreightsRfq $rfq)
    {
        $rfq->load(['items', 'overseasAgent', 'shippingCarrier']);
        $existingItems = $rfq->items->map(fn ($i) => [
            'item_type'          => $i->item_type,
            'container_size'     => $i->container_size,
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

        return view('nas-freights.rfqs.create', array_merge($this->formData(), [
            'rfq'           => $rfq,
            'existingItems' => $existingItems,
        ]));
    }

    public function update(Request $request, NasFreightsRfq $rfq)
    {
        $request->validate([
            'rfq_date' => ['required', 'date'],
            'type'     => ['required', 'in:import,export'],
        ]);

        DB::transaction(function () use ($request, $rfq) {
            $rfq->update($this->prepareData($request));
            $rfq->items()->delete();
            $this->saveItems($rfq, $request->input('items', []));
        });

        return back()->with('success', 'RFQ '.$rfq->rfq_no.' updated successfully.');
    }

    public function updateStatus(Request $request, NasFreightsRfq $rfq)
    {
        $request->validate([
            'status'      => ['required', 'in:Draft,Pending,Win,Lose'],
            'lost_reason' => ['required_if:status,Lose'],
        ]);

        $rfq->update([
            'status'      => $request->status,
            'lost_reason' => $request->status === 'Lose' ? $request->lost_reason : null,
        ]);

        return back()->with('success', 'Status updated to '.$request->status.'.');
    }

    public function convertToBooking(NasFreightsRfq $rfq)
    {
        if ($rfq->status !== 'Win') {
            return back()->with('error', 'Only Win RFQs can be converted to a booking.');
        }

        if ($rfq->converted_booking_id) {
            return redirect()->route('nas-freights.bookings.edit', $rfq->converted_booking_id)
                ->with('info', 'This RFQ was already converted to booking '.$rfq->convertedBooking?->job_no.'.');
        }

        $rfq->load(['customer', 'salesperson', 'items']);

        $booking = DB::transaction(function () use ($rfq) {
            $booking = NasFreightsBooking::create([
                'job_no'            => NasFreightsBooking::generateJobNo(),
                'branch_id'         => $rfq->branch_id,
                'booking_prefix'    => 'CORPORATE_SALES',
                'sales_type'        => strtoupper($rfq->type),
                'sales_person_id'   => $rfq->salesperson_id,
                'sales_person_name' => $rfq->salesperson?->name,
                'job_date'          => now()->toDateString(),
                'customer_id'       => $rfq->customer_id,
                'customer_name'     => $rfq->customer?->name,
                'delivery_address'  => $rfq->customer?->address,
                'goods_name'        => $rfq->commodity_description ?? $rfq->items->first()?->commodity ?? '',
                'note'              => implode(' | ', array_filter([
                    $rfq->remarks,
                    $rfq->pol && $rfq->pod ? 'POL: '.$rfq->pol.' / POD: '.$rfq->pod : null,
                    'RFQ: '.$rfq->rfq_no,
                ])),
                'cover_van_no'      => 'TBD',
                'delivery_date'     => now()->addDays(7)->toDateString(),
                'tds_percent'       => 0,
                'tds_amount'        => 0,
                'vat_percent'       => 0,
                'vat_amount'        => 0,
                'ait_percent'       => 0,
                'ait_amount'        => 0,
                'total_amount'      => 0,
                'discount'          => 0,
                'forfeited_amount'  => 0,
                'status'            => 'Draft',
                'delivery_status'   => 'Pending',
                'entry_by'          => auth()->user()?->name ?? 'System',
            ]);

            foreach ($rfq->items as $item) {
                NasFreightsBookingProduct::create([
                    'booking_id'  => $booking->id,
                    'goods_name'  => $item->commodity ?? $item->container_size ?? $item->package_type ?? 'Cargo',
                    'qty'         => $item->quantity,
                    'qty_unit'    => 'PCS',
                    'net_weight'  => $item->gross_weight ?? 0,
                    'weight_unit' => $item->weight_unit ?? 'KG',
                ]);
            }

            $rfq->update(['converted_booking_id' => $booking->id]);

            return $booking;
        });

        return redirect()->route('nas-freights.bookings.edit', $booking->id)
            ->with('success', 'Booking '.$booking->job_no.' created from RFQ '.$rfq->rfq_no.'. Please review and add transport details.');
    }

    public function destroy(NasFreightsRfq $rfq)
    {
        $rfq->delete();

        return response()->json(['message' => 'RFQ '.$rfq->rfq_no.' deleted.']);
    }

    public function searchOverseasAgents(Request $request)
    {
        $q = $request->get('q', '');
        $results = NasFreightsOverseasAgent::where('is_active', true)
            ->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')
                ->orWhere('agent_code', 'like', '%'.$q.'%')
                ->orWhere('country', 'like', '%'.$q.'%'))
            ->limit(20)
            ->get(['id', 'agent_code', 'name', 'country', 'city'])
            ->map(fn ($a) => [
                'id'   => $a->id,
                'text' => $a->agent_code.' — '.$a->name.' ('.$a->country.')',
            ]);

        return response()->json($results);
    }

    public function searchShippingCarriers(Request $request)
    {
        $q = $request->get('q', '');
        $results = NasFreightsShippingCarrier::where('is_active', true)
            ->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')
                ->orWhere('carrier_code', 'like', '%'.$q.'%')
                ->orWhere('scac_code', 'like', '%'.$q.'%'))
            ->limit(20)
            ->get(['id', 'carrier_code', 'name', 'scac_code'])
            ->map(fn ($c) => [
                'id'   => $c->id,
                'text' => $c->carrier_code.' — '.$c->name.($c->scac_code ? ' ('.$c->scac_code.')' : ''),
            ]);

        return response()->json($results);
    }

    public function searchCustomers(Request $request)
    {
        $q = $request->get('q', '');
        $results = NasFreightsCustomer::where('name', 'like', '%'.$q.'%')
            ->orWhere('customer_id', 'like', '%'.$q.'%')
            ->limit(20)
            ->select(['id', 'name', 'customer_id', 'address'])
            ->get()
            ->map(fn ($c) => [
                'id'      => $c->id,
                'text'    => $c->customer_id.' — '.$c->name,
                'name'    => $c->name,
                'address' => $c->address,
            ]);

        return response()->json($results);
    }

    public function searchEmployees(Request $request)
    {
        $q = $request->get('q', '');
        $results = NasFreightsEmployee::where('name', 'like', '%'.$q.'%')
            ->where('is_active', true)
            ->limit(20)
            ->select(['id', 'name', 'code'])
            ->get()
            ->map(fn ($e) => ['id' => $e->id, 'text' => $e->name]);

        return response()->json($results);
    }

    private function prepareData(Request $request): array
    {
        $status = $request->status ?: 'Draft';

        return [
            'branch_id'             => session('nas_freights_branch_id'),
            'customer_id'           => $request->customer_id ?: null,
            'rfq_date'              => $request->rfq_date,
            'valid_until'           => $request->valid_until ?: null,
            'type'                  => $request->type,
            'service_type'          => $request->service_type ?: 'FCL',
            'incoterms'             => $request->incoterms ?: null,
            'currency'              => $request->currency ?: 'BDT',
            'pol'                   => $request->pol ?: null,
            'pod'                   => $request->pod ?: null,
            'place_of_receipt'      => $request->place_of_receipt ?: null,
            'place_of_delivery'     => $request->place_of_delivery ?: null,
            'commodity_description' => $request->commodity_description ?: null,
            'remarks'               => $request->remarks ?: null,
            'salesperson_id'        => $request->salesperson_id ?: null,
            'overseas_agent_id'     => $request->overseas_agent_id ?: null,
            'shipping_carrier_id'   => $request->shipping_carrier_id ?: null,
            'status'                => $status,
            'lost_reason'           => $status === 'Lose' ? ($request->lost_reason ?: null) : null,
        ];
    }

    private function saveItems(NasFreightsRfq $rfq, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['item_type'])) {
                continue;
            }

            $rfq->items()->create([
                'item_type'          => $item['item_type'],
                'container_size'     => $item['item_type'] === 'container' ? ($item['container_size'] ?? null) : null,
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

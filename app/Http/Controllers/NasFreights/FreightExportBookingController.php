<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\FreightExportBooking\CreateFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\DestroyFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\EditFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\IndexFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\ShowFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\StoreFreightExportBookingRequest;
use App\Http\Requests\NasFreights\FreightExportBooking\UpdateFreightExportBookingRequest;
use App\Models\Employee;
use App\Models\NasFreights\NasFreightsContainerType;
use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingExpense;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsPackageType;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FreightExportBookingController extends Controller
{
    private function formData(): array
    {
        return [
            'serviceTypes'   => NasFreightsFreightExportBooking::serviceTypes(),
            'statuses'       => NasFreightsFreightExportBooking::statuses(),
            'incoterms'      => ['EXW', 'FCA', 'FAS', 'FOB', 'CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'],
            'currencies'     => ['BDT', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'INR', 'SGD', 'AUD', 'AED'],
            'weightUnits'    => ['KG', 'MT', 'LB'],
            'containerSizes' => NasFreightsContainerType::active()->pluck('name'),
            'packageTypes'   => NasFreightsPackageType::active()->pluck('name'),
            'today'          => now()->format('Y-m-d'),
        ];
    }

    public function index(IndexFreightExportBookingRequest $request)
    {

        if ($request->ajax()) {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $canManageExpense = $request->user()->hasPermission('freight.export-booking.expense-manage');
            $canManageTransport = $request->user()->hasPermission('freight.export-booking.transport-manage');

            $query = NasFreightsFreightExportBooking::with(['customer', 'shippingCarrier'])
                ->where('branch_id', session('nas_freights_branch_id'))
                ->when($request->status_filter, fn ($q, $s) => $q->where('status', $s))
                ->when($fromDate, fn ($q) => $q->whereDate('booking_date', '>=', $fromDate))
                ->when($toDate, fn ($q) => $q->whereDate('booking_date', '<=', $toDate))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('booking_date', fn ($r) => $r->booking_date?->format('d M Y') ?? '—')
                ->addColumn('customer_name', fn ($r) => $r->customer?->name ?? '—')
                ->addColumn('party_bill_ref_no', fn ($r) => $r->party_bill_ref_no ?? '—')
                ->addColumn('party_invoice_no', fn ($r) => $r->party_invoice_no ?? '—')
                ->addColumn('export_bl_no', fn ($r) => $r->export_bl_no ?? '—')
                ->addColumn('route', fn ($r) => ($r->pol ?? '—').' → '.($r->pod ?? '—'))
                ->addColumn('carrier', fn ($r) => $r->shippingCarrier?->name ?? '—')
                ->addColumn('transport_amount', fn ($r) => $r->transport_amount > 0 ? number_format($r->transport_amount, 2) : '—')
                ->addColumn('expense_amount', function ($r) {
                    $total = NasFreightsFreightExportBookingExpense::where('booking_id', $r->id)
                        ->value('total_expense_amount');

                    return $total > 0 ? number_format($total, 2) : '—';
                })
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Confirmed'  => '<span class="badge bg-success">Confirmed</span>',
                    'In-Transit' => '<span class="badge bg-info text-dark">In-Transit</span>',
                    'Delivered'  => '<span class="badge bg-primary">Delivered</span>',
                    'Cancelled'  => '<span class="badge bg-danger">Cancelled</span>',
                    default      => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', function ($r) use ($canManageExpense, $canManageTransport) {
                    $html = '<div class="d-flex flex-nowrap gap-1">';

                    if ($canManageExpense) {
                        $expenseUrl = route('nas-freights.freight-export-bookings.expense.edit', $r->id);
                        $html .= '<a href="'.$expenseUrl.'" class="btn btn-sm btn-outline-success py-0 px-1" title="Expense"><i class="fa fa-receipt"></i></a>';
                    }

                    if ($canManageTransport) {
                        $transportUrl = route('nas-freights.freight-export-bookings.transport.edit', $r->id);
                        $html .= '<a href="'.$transportUrl.'" class="btn btn-sm btn-outline-warning py-0 px-1" title="Transport"><i class="fa fa-truck"></i></a>';
                    }

                    $html .= '<a href="'.route('nas-freights.freight-export-bookings.show', $r->id).'" class="btn btn-sm btn-outline-info py-0 px-1" title="View"><i class="fa fa-eye"></i></a>'
                    .'<a href="'.route('nas-freights.freight-export-bookings.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit"><i class="fa fa-edit"></i></a>'
                    .'<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"'
                    .' data-url="'.route('nas-freights.freight-export-bookings.destroy', $r->id).'"'
                    .' data-name="'.e($r->export_booking_no).'"><i class="fa fa-trash"></i></button>';

                    $html .= '</div>';

                    return $html;
                })
                ->filterColumn('customer_name', fn ($q, $k) => $q->whereHas('customer', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->filterColumn('party_bill_ref_no', fn ($q, $k) => $q->where('party_bill_ref_no', 'like', "%{$k}%"))
                ->filterColumn('party_invoice_no', fn ($q, $k) => $q->where('party_invoice_no', 'like', "%{$k}%"))
                ->filterColumn('export_bl_no', fn ($q, $k) => $q->where('export_bl_no', 'like', "%{$k}%"))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.freight-export-bookings.index');
    }

    public function create(CreateFreightExportBookingRequest $request)
    {
        return view('nas-freights.freight-export-bookings.create', array_merge($this->formData(), [
            'exportBooking' => null,
            'existingItems' => [],
        ]));
    }

    public function store(StoreFreightExportBookingRequest $request)
    {

        DB::transaction(function () use ($request) {
            $exportBooking = NasFreightsFreightExportBooking::create(array_merge($this->prepareData($request), [
                'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            ]));
            $this->saveItems($exportBooking, $request->input('items', []));
        });

        return redirect()->route('nas-freights.freight-export-bookings.index')
            ->with('success', 'Freight Export Booking/Job created successfully.');
    }

    public function show(ShowFreightExportBookingRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        $exportBooking->load(['customer', 'salesperson', 'overseasAgent', 'shippingCarrier', 'items', 'transportItems', 'bills', 'expense.items.expenseHead']);

        return view('nas-freights.freight-export-bookings.show', compact('exportBooking'));
    }

    public function edit(EditFreightExportBookingRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        $exportBooking->load(['items', 'overseasAgent', 'shippingCarrier', 'transportItems', 'bills', 'expense.items.expenseHead']);
        $existingItems = $exportBooking->items->map(fn ($i) => [
            'item_type'          => $i->item_type,
            'container_size'     => $i->container_size,
            'container_no'       => $i->container_no,
            'seal_no'            => $i->seal_no,
            'package_type'       => $i->item_type === 'package' ? $i->package_type : null,
            'package_qty'        => $i->item_type === 'container' ? $i->package_qty : null,
            'package_unit'       => $i->item_type === 'container' ? $i->package_type : null,
            'quantity'           => $i->quantity,
            'gross_weight'       => $i->gross_weight,
            'weight_unit'        => $i->weight_unit,
            'volume_cbm'         => $i->volume_cbm,
            'country_of_origin'  => $i->country_of_origin,
            'is_dangerous_goods' => $i->is_dangerous_goods ? '1' : '0',
            'special_handling'   => $i->special_handling,
        ])->values();

        return view('nas-freights.freight-export-bookings.create', array_merge($this->formData(), [
            'exportBooking' => $exportBooking,
            'existingItems' => $existingItems,
        ]));
    }

    public function update(UpdateFreightExportBookingRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {

        DB::transaction(function () use ($request, $exportBooking) {
            $exportBooking->update($this->prepareData($request));
            $exportBooking->items()->delete();
            $this->saveItems($exportBooking, $request->input('items', []));
        });

        return redirect()->route('nas-freights.freight-export-bookings.index')->with('success', 'Freight Export Booking/Job '.$exportBooking->export_booking_no.' updated.');
    }

    public function destroy(DestroyFreightExportBookingRequest $request, NasFreightsFreightExportBooking $exportBooking)
    {
        $exportBooking->delete();

        return response()->json(['message' => 'Freight Export Booking/Job '.$exportBooking->export_booking_no.' deleted.']);
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

    public function quickStoreCustomer(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $customer = DB::transaction(function () use ($request) {
            $prefix = 'CUS-';

            return NasFreightsCustomer::create([
                'branch_id'   => session('nas_freights_branch_id'),
                'id_prefix'   => $prefix,
                'customer_id' => NasFreightsCustomer::generateCustomerId($prefix),
                'name'        => $request->name,
                'phone'       => $request->phone ?? '',
                'status'      => 'Active',
            ]);
        });

        return response()->json([
            'id'      => $customer->id,
            'text'    => $customer->customer_id.' — '.$customer->name,
            'name'    => $customer->name,
            'address' => $customer->address ?? '',
            'message' => 'Customer "'.$customer->name.'" created successfully.',
        ]);
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
            'party_bill_ref_no'     => $request->party_bill_ref_no ?: null,
            'party_bill_date'       => $request->party_bill_date ?: null,
            'party_invoice_no'      => $request->party_invoice_no ?: null,
            'party_invoice_date'    => $request->party_invoice_date ?: null,
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
            'commodity_description' => $request->commodity_description ?: null,
            'hs_codes'              => $this->normalizeHsCodes($request->input('hs_codes', [])),
            'vessel_name'           => $request->vessel_name ?: null,
            'voyage_no'             => $request->voyage_no ?: null,
            'export_bl_no'          => $request->export_bl_no ?: null,
            'booking_note_no'       => $request->booking_note_no ?: null,
            'bl_date'               => $request->bl_date ?: null,
            'exp_no'                => $request->exp_no ?: null,
            'exp_date'              => $request->exp_date ?: null,
            'invoice_no'            => $request->invoice_no ?: null,
            'invoice_date'          => $request->invoice_date ?: null,
            'lc_no'                 => $request->lc_no ?: null,
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

    private function saveItems(NasFreightsFreightExportBooking $exportBooking, array $items): void
    {

        foreach ($items as $item) {

            if (empty($item['item_type'])) {
                continue;
            }

            $isContainer = $item['item_type'] === 'container';

            $exportBooking->items()->create([
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

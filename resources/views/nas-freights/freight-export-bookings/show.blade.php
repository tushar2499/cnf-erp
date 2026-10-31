@extends('nas-freights.layouts.app')

@section('title', 'Freight Export Booking/Job — ' . $exportBooking->export_booking_no)

@push('styles')
<style>
.info-label  { font-size:.68rem; font-weight:700; color:#5b6785; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.1rem; }
.info-value  { font-size:.82rem; color:#1e293b; overflow-wrap:anywhere; }
.info-sub    { font-size:.7rem; color:#5b6785; }
.section-card { border:1px solid #dee2e6; border-radius:.35rem; margin-bottom:.9rem; }
.form-header  { background:linear-gradient(135deg,#0a4f3c,#14b8a6); color:#fff; padding:.45rem .9rem; border-radius:.35rem .35rem 0 0; font-weight:600; font-size:.78rem; }
.section-body { padding:.65rem .85rem; }
.cargo-table th { background:#f1f3f5; font-size:.68rem; font-weight:700; padding:.25rem .5rem; white-space:nowrap; }
.cargo-table td { font-size:.75rem; padding:.3rem .5rem; vertical-align:middle; }
.status-pill  { font-size:.78rem; padding:.2rem .75rem; border-radius:2rem; font-weight:700; display:inline-block; }
.status-Draft      { background:#e2e8f0; color:#475569; }
.status-Confirmed  { background:#dcfce7; color:#166534; }
.status-In-Transit { background:#dbeafe; color:#1e40af; }
.status-Delivered  { background:#ede9fe; color:#5b21b6; }
.status-Cancelled  { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div></div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        Freight Export Booking/Job &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $exportBooking->export_booking_no }}</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('nas-freights.freight-export-bookings.edit', $exportBooking->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-2">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ═══ BOOKING/JOB INFORMATION — same groups and order as the create/edit form ═══ --}}
<div class="section-card">
    <div class="form-header"><i class="fa fa-ship me-1"></i> Booking/Job Information</div>
    <div class="section-body">

        {{-- Group 1: Booking/Job details --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Booking/Job No</div>
                <div class="info-value fw-bold">{{ $exportBooking->export_booking_no }}</div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Booking/Job Date</div>
                <div class="info-value">{{ $exportBooking->booking_date?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Service Type/Mode</div>
                <div class="info-value">{{ $exportBooking->service_type ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Currency</div>
                <div class="info-value">{{ $exportBooking->currency ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Incoterms</div>
                <div class="info-value">{{ $exportBooking->incoterms ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="info-label">Status</div>
                <div class="info-value"><span class="status-pill status-{{ str_replace(' ', '-', $exportBooking->status) }}">{{ $exportBooking->status }}</span></div>
            </div>
        </div>

        {{-- Group 2: Parties --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="info-label">Customer (Exporter)</div>
                <div class="info-value fw-semibold">{{ $exportBooking->customer?->name ?? '—' }}</div>
                @if($exportBooking->customer?->customer_id)
                <div class="info-sub">{{ $exportBooking->customer->customer_id }}</div>
                @endif
            </div>
            <div class="col-12 col-md-4">
                <div class="info-label">Overseas Agent / Consignee</div>
                @if($exportBooking->overseasAgent)
                    <div class="info-value fw-semibold">{{ $exportBooking->overseasAgent->name }}</div>
                    <div class="info-sub">{{ $exportBooking->overseasAgent->agent_code }}@if($exportBooking->overseasAgent->country) &nbsp;·&nbsp;{{ $exportBooking->overseasAgent->country }}@endif</div>
                @else
                    <div class="info-value">—</div>
                @endif
            </div>
            <div class="col-12 col-md-4">
                <div class="info-label">Salesperson</div>
                <div class="info-value">{{ $exportBooking->salesperson?->name ?? '—' }}</div>
            </div>
        </div>

        {{-- Group 3: Party bill & invoice --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="info-label">Party Bill Ref No</div>
                <div class="info-value">{{ $exportBooking->party_bill_ref_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Party Bill Ref Date</div>
                <div class="info-value">{{ $exportBooking->party_bill_date?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Party Invoice No</div>
                <div class="info-value">{{ $exportBooking->party_invoice_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Party Invoice Date</div>
                <div class="info-value">{{ $exportBooking->party_invoice_date?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>

        {{-- Group 4: Shipment & route --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="info-label">Shipping Carrier</div>
                @if($exportBooking->shippingCarrier)
                    <div class="info-value fw-semibold">{{ $exportBooking->shippingCarrier->name }}</div>
                    <div class="info-sub">{{ $exportBooking->shippingCarrier->carrier_code }}@if($exportBooking->shippingCarrier->scac_code) &nbsp;·&nbsp;SCAC: {{ $exportBooking->shippingCarrier->scac_code }}@endif</div>
                @else
                    <div class="info-value">—</div>
                @endif
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Port of Loading (POL)</div>
                <div class="info-value">{{ $exportBooking->pol ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Port of Discharge (POD)</div>
                <div class="info-value">{{ $exportBooking->pod ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Place of Receipt</div>
                <div class="info-value">{{ $exportBooking->place_of_receipt ?? '—' }}</div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="info-label">Vessel Name</div>
                <div class="info-value">{{ $exportBooking->vessel_name ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Voyage No</div>
                <div class="info-value">{{ $exportBooking->voyage_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">ETD / Flight Date</div>
                <div class="info-value">{{ $exportBooking->etd?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">ETA</div>
                <div class="info-value">{{ $exportBooking->eta?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>

        {{-- Group 5: Cargo description --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="info-label">Commodity Description</div>
                <div class="info-value">{{ $exportBooking->commodity_description ?? '—' }}</div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="info-label">HS Code</div>
                @if($exportBooking->hs_codes)
                <div class="info-value d-flex flex-wrap gap-1">
                    @foreach($exportBooking->hs_codes as $hsCode)
                    <span class="badge bg-light text-dark border">{{ $hsCode }}</span>
                    @endforeach
                </div>
                @else
                <div class="info-value">—</div>
                @endif
            </div>
        </div>

        {{-- Group 6: Shipping & trade documents --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="info-label">Export B/L No</div>
                <div class="info-value fw-semibold">{{ $exportBooking->export_bl_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">B/L Date</div>
                <div class="info-value">{{ $exportBooking->bl_date?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Booking Note No</div>
                <div class="info-value fw-semibold">{{ $exportBooking->booking_note_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">LC No</div>
                <div class="info-value">{{ $exportBooking->lc_no ?? '—' }}</div>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="info-label">EXP No</div>
                <div class="info-value">{{ $exportBooking->exp_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">EXP Date</div>
                <div class="info-value">{{ $exportBooking->exp_date?->format('d M Y') ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Invoice No</div>
                <div class="info-value">{{ $exportBooking->invoice_no ?? '—' }}</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="info-label">Invoice Date</div>
                <div class="info-value">{{ $exportBooking->invoice_date?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12">
                <div class="info-label">Remarks</div>
                <div class="info-value">{{ $exportBooking->remarks ?? '—' }}</div>
            </div>
        </div>

    </div>
</div>

{{-- ═══ CARGO ITEMS — same column order as the create/edit form ═══ --}}
<div class="section-card">
    <div class="form-header"><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</div>
    <div class="section-body p-0">
        @if($exportBooking->items->isEmpty())
        <div class="text-center text-muted py-3" style="font-size:.8rem;"><i class="fa fa-inbox me-1"></i> No cargo items.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="table table-bordered table-hover mb-0 cargo-table" style="min-width:1300px;">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Item Type</th>
                        <th class="text-center">Qty</th>
                        <th>Container Size / Pkg</th>
                        <th class="text-end">Pkg Qty</th>
                        <th>Pkg Unit</th>
                        <th>Container No</th>
                        <th>Seal No</th>
                        <th class="text-end">Weight</th>
                        <th>Unit</th>
                        <th class="text-end">CBM</th>
                        <th>Country of Origin</th>
                        <th class="text-center">DG</th>
                        <th>Special Handling</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exportBooking->items as $i => $item)
                    @php $isContainer = $item->item_type === 'container'; @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $i + 1 }}</td>
                        <td>
                            @if($isContainer)
                                <span class="badge bg-primary bg-opacity-75">Container</span>
                            @else
                                <span class="badge bg-secondary">Package</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td>{{ ($isContainer ? $item->container_size : $item->package_type) ?? '—' }}</td>
                        <td class="text-end text-nowrap">{{ $isContainer && $item->package_qty ? number_format($item->package_qty) : '—' }}</td>
                        <td>{{ ($isContainer ? $item->package_type : null) ?? '—' }}</td>
                        <td>{{ $item->container_no ?? '—' }}</td>
                        <td>{{ $item->seal_no ?? '—' }}</td>
                        <td class="text-end text-nowrap">{{ $item->gross_weight ? number_format($item->gross_weight, 2) : '—' }}</td>
                        <td>{{ $item->gross_weight ? $item->weight_unit : '—' }}</td>
                        <td class="text-end">{{ $item->volume_cbm ? number_format($item->volume_cbm, 3) : '—' }}</td>
                        <td>{{ $item->country_of_origin ?? '—' }}</td>
                        <td class="text-center">
                            @if($item->is_dangerous_goods) <span class="badge bg-danger">DG</span>
                            @else <span class="text-muted">No DG</span> @endif
                        </td>
                        <td>{{ $item->special_handling ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ═══ BILLS, EXPENSE & TRANSPORT — same partials and order as the edit page ═══ --}}
@include('nas-freights.freight-export-bookings._bills', ['exportBooking' => $exportBooking])

@include('nas-freights.freight-export-bookings._expense', ['exportBooking' => $exportBooking])

@include('nas-freights.freight-export-bookings._transport', ['exportBooking' => $exportBooking])
@endsection

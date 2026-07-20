@extends('nas-freights.layouts.app')

@section('title', 'Freight Booking — ' . $freightBooking->freight_booking_no)

@push('styles')
<style>
.info-label  { font-size:.68rem; font-weight:700; color:#6b7a99; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.1rem; }
.info-value  { font-size:.82rem; color:#1e293b; }
.section-card { border:1px solid #dee2e6; border-radius:.35rem; margin-bottom:.9rem; }
.form-header  { background:linear-gradient(135deg,#0a4f3c,#14b8a6); color:#fff; padding:.45rem .9rem; border-radius:.35rem .35rem 0 0; font-weight:600; font-size:.78rem; }
.section-body { padding:.65rem .85rem; }
.cargo-table th { background:#f1f3f5; font-size:.68rem; font-weight:700; padding:.25rem .5rem; white-space:nowrap; }
.cargo-table td { font-size:.75rem; padding:.3rem .5rem; vertical-align:middle; }
.status-pill  { font-size:.85rem; padding:.35rem .85rem; border-radius:2rem; font-weight:700; display:inline-block; }
.status-Draft      { background:#e2e8f0; color:#475569; }
.status-Confirmed  { background:#dcfce7; color:#166534; }
.status-In-Transit { background:#dbeafe; color:#1e40af; }
.status-Delivered  { background:#ede9fe; color:#5b21b6; }
.status-Cancelled  { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('nas-freights.freight-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('nas-freights.freight-bookings.edit', $freightBooking->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
    </div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        Freight Booking &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $freightBooking->freight_booking_no }}</span>
        &nbsp;<span class="status-pill status-{{ str_replace(' ', '-', $freightBooking->status) }}">{{ $freightBooking->status }}</span>
    </div>
    <div></div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-2">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">

        <div class="section-card">
            <div class="form-header"><i class="fa fa-ship me-1"></i> Booking Information</div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking No</div>
                        <div class="info-value fw-bold">{{ $freightBooking->freight_booking_no }}</div>
                    </div>
                    @if($freightBooking->rfq_no)
                    <div class="col-6 col-md-3">
                        <div class="info-label">From RFQ</div>
                        <div class="info-value">
                            <a href="{{ route('nas-freights.rfqs.show', $freightBooking->rfq_id) }}">{{ $freightBooking->rfq_no }}</a>
                        </div>
                    </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking Date</div>
                        <div class="info-value">{{ $freightBooking->booking_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $freightBooking->salesperson?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Customer</div>
                        <div class="info-value fw-semibold">{{ $freightBooking->customer?->name ?? '—' }}</div>
                        @if($freightBooking->customer?->customer_id)
                        <div style="font-size:.7rem; color:#6b7280;">{{ $freightBooking->customer->customer_id }}</div>
                        @endif
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Overseas Agent</div>
                        <div class="info-value">
                            @if($freightBooking->overseasAgent)
                                <span class="fw-semibold">{{ $freightBooking->overseasAgent->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $freightBooking->overseasAgent->agent_code }}@if($freightBooking->overseasAgent->country) &nbsp;·&nbsp;{{ $freightBooking->overseasAgent->country }}@endif</div>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Shipping Carrier</div>
                        <div class="info-value">
                            @if($freightBooking->shippingCarrier)
                                <span class="fw-semibold">{{ $freightBooking->shippingCarrier->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $freightBooking->shippingCarrier->carrier_code }}@if($freightBooking->shippingCarrier->scac_code) &nbsp;·&nbsp;SCAC: {{ $freightBooking->shippingCarrier->scac_code }}@endif</div>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Type</div>
                        <div class="info-value">
                            @if($freightBooking->type === 'import')
                                <span class="badge bg-info text-dark">Import</span>
                            @else
                                <span class="badge bg-warning text-dark">Export</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Service</div>
                        <div class="info-value">{{ $freightBooking->service_type ?? '—' }}</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Incoterms</div>
                        <div class="info-value">{{ $freightBooking->incoterms ?? '—' }}</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $freightBooking->currency ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">POL</div>
                        <div class="info-value">{{ $freightBooking->pol ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">POD</div>
                        <div class="info-value">{{ $freightBooking->pod ?? '—' }}</div>
                    </div>
                    @if($freightBooking->place_of_receipt || $freightBooking->place_of_delivery)
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Receipt</div>
                        <div class="info-value">{{ $freightBooking->place_of_receipt ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Delivery</div>
                        <div class="info-value">{{ $freightBooking->place_of_delivery ?? '—' }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($freightBooking->vessel_name || $freightBooking->bl_no || $freightBooking->etd || $freightBooking->eta)
        <div class="section-card">
            <div class="form-header"><i class="fa fa-anchor me-1"></i> Shipment Details</div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">Vessel</div>
                        <div class="info-value">{{ $freightBooking->vessel_name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Voyage No</div>
                        <div class="info-value">{{ $freightBooking->voyage_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">B/L No</div>
                        <div class="info-value fw-semibold">{{ $freightBooking->bl_no ?? '—' }}</div>
                    </div>
                    <div class="col-3 col-md-1_5">
                        <div class="info-label">ETD</div>
                        <div class="info-value">{{ $freightBooking->etd?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-3 col-md-1_5">
                        <div class="info-label">ETA</div>
                        <div class="info-value">{{ $freightBooking->eta?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="section-card">
            <div class="form-header"><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</div>
            <div class="section-body p-0">
                @if($freightBooking->items->isEmpty())
                <div class="text-center text-muted py-3" style="font-size:.8rem;"><i class="fa fa-inbox me-1"></i> No cargo items.</div>
                @else
                <div style="overflow-x:auto;">
                    <table class="table table-bordered table-hover mb-0 cargo-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Type</th><th>Size / Package</th><th>HS Code</th><th>Commodity</th>
                                <th>Qty</th><th>Weight</th><th>CBM</th><th>Origin</th><th>DG</th><th>Special</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($freightBooking->items as $i => $item)
                            <tr>
                                <td class="text-center fw-bold">{{ $i + 1 }}</td>
                                <td>
                                    @if($item->item_type === 'container')
                                        <span class="badge bg-primary bg-opacity-75">Container</span>
                                    @else
                                        <span class="badge bg-secondary">Package</span>
                                    @endif
                                </td>
                                <td>{{ $item->container_size ?? $item->package_type ?? '—' }}</td>
                                <td>{{ $item->hs_code ?? '—' }}</td>
                                <td>{{ $item->commodity ?? '—' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end text-nowrap">{{ $item->gross_weight ? number_format($item->gross_weight, 2).' '.$item->weight_unit : '—' }}</td>
                                <td class="text-end">{{ $item->volume_cbm ? number_format($item->volume_cbm, 3) : '—' }}</td>
                                <td>{{ $item->country_of_origin ?? '—' }}</td>
                                <td class="text-center">
                                    @if($item->is_dangerous_goods) <span class="badge bg-danger">DG</span>
                                    @else <span class="text-muted">—</span> @endif
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

    </div>

    <div class="col-lg-4">

        <div class="section-card">
            <div class="form-header"><i class="fa fa-tasks me-1"></i> Status</div>
            <div class="section-body text-center py-3">
                <div class="status-pill status-{{ str_replace(' ', '-', $freightBooking->status) }}">{{ $freightBooking->status }}</div>
            </div>
        </div>

        @if($freightBooking->commodity_description || $freightBooking->remarks)
        <div class="section-card">
            <div class="form-header"><i class="fa fa-sticky-note me-1"></i> Notes</div>
            <div class="section-body">
                @if($freightBooking->commodity_description)
                <div class="mb-2">
                    <div class="info-label">Commodity</div>
                    <div class="info-value">{{ $freightBooking->commodity_description }}</div>
                </div>
                @endif
                @if($freightBooking->remarks)
                <div>
                    <div class="info-label">Remarks</div>
                    <div class="info-value">{{ $freightBooking->remarks }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="section-card">
            <div class="form-header"><i class="fa fa-info-circle me-1"></i> Record Info</div>
            <div class="section-body">
                <div class="mb-1">
                    <span class="info-label">Created</span>
                    <div class="info-value">{{ $freightBooking->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div>
                    <span class="info-label">Last Updated</span>
                    <div class="info-value">{{ $freightBooking->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

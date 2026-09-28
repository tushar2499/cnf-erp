@extends('nas-freights.layouts.app')

@section('title', 'Freight Export Booking — ' . $exportBooking->export_booking_no)

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
    <div></div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        Freight Export Booking &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $exportBooking->export_booking_no }}</span>
        &nbsp;<span class="status-pill status-{{ str_replace(' ', '-', $exportBooking->status) }}">{{ $exportBooking->status }}</span>
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

<div class="row g-3">
    <div class="col-lg-8">

        <div class="section-card">
            <div class="form-header"><i class="fa fa-ship me-1"></i> Booking Information</div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking No</div>
                        <div class="info-value fw-bold">{{ $exportBooking->export_booking_no }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking Date</div>
                        <div class="info-value">{{ $exportBooking->booking_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $exportBooking->salesperson?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Customer (Exporter)</div>
                        <div class="info-value fw-semibold">{{ $exportBooking->customer?->name ?? '—' }}</div>
                        @if($exportBooking->customer?->customer_id)
                        <div style="font-size:.7rem; color:#6b7280;">{{ $exportBooking->customer->customer_id }}</div>
                        @endif
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Overseas Agent</div>
                        <div class="info-value">
                            @if($exportBooking->overseasAgent)
                                <span class="fw-semibold">{{ $exportBooking->overseasAgent->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $exportBooking->overseasAgent->agent_code }}@if($exportBooking->overseasAgent->country) &nbsp;·&nbsp;{{ $exportBooking->overseasAgent->country }}@endif</div>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Shipping Carrier</div>
                        <div class="info-value">
                            @if($exportBooking->shippingCarrier)
                                <span class="fw-semibold">{{ $exportBooking->shippingCarrier->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $exportBooking->shippingCarrier->carrier_code }}@if($exportBooking->shippingCarrier->scac_code) &nbsp;·&nbsp;SCAC: {{ $exportBooking->shippingCarrier->scac_code }}@endif</div>
                            @else —
                            @endif
                        </div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Service</div>
                        <div class="info-value">{{ $exportBooking->service_type ?? '—' }}</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Incoterms</div>
                        <div class="info-value">{{ $exportBooking->incoterms ?? '—' }}</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $exportBooking->currency ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">POL</div>
                        <div class="info-value">{{ $exportBooking->pol ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">POD</div>
                        <div class="info-value">{{ $exportBooking->pod ?? '—' }}</div>
                    </div>
                    @if($exportBooking->place_of_receipt || $exportBooking->place_of_delivery)
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Receipt</div>
                        <div class="info-value">{{ $exportBooking->place_of_receipt ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Delivery</div>
                        <div class="info-value">{{ $exportBooking->place_of_delivery ?? '—' }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($exportBooking->vessel_name || $exportBooking->export_bl_no || $exportBooking->booking_note_no || $exportBooking->etd || $exportBooking->eta)
        <div class="section-card">
            <div class="form-header"><i class="fa fa-anchor me-1"></i> Shipment Details</div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">Vessel</div>
                        <div class="info-value">{{ $exportBooking->vessel_name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Voyage No</div>
                        <div class="info-value">{{ $exportBooking->voyage_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Export B/L No</div>
                        <div class="info-value fw-semibold">{{ $exportBooking->export_bl_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking Note No</div>
                        <div class="info-value fw-semibold">{{ $exportBooking->booking_note_no ?? '—' }}</div>
                    </div>
                    <div class="col-3 col-md-1_5">
                        <div class="info-label">ETD</div>
                        <div class="info-value">{{ $exportBooking->etd?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-3 col-md-1_5">
                        <div class="info-label">ETA</div>
                        <div class="info-value">{{ $exportBooking->eta?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="section-card">
            <div class="form-header"><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</div>
            <div class="section-body p-0">
                @if($exportBooking->items->isEmpty())
                <div class="text-center text-muted py-3" style="font-size:.8rem;"><i class="fa fa-inbox me-1"></i> No cargo items.</div>
                @else
                <div style="overflow-x:auto;">
                    <table class="table table-bordered table-hover mb-0 cargo-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Type</th><th>Size / Package</th><th>Container No</th><th>Seal No</th><th>HS Code</th><th>Commodity</th>
                                <th>Qty</th><th>Weight</th><th>CBM</th><th>Origin</th><th>DG</th><th>Special</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exportBooking->items as $i => $item)
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
                                <td>{{ $item->container_no ?? '—' }}</td>
                                <td>{{ $item->seal_no ?? '—' }}</td>
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
                <div class="status-pill status-{{ str_replace(' ', '-', $exportBooking->status) }}">{{ $exportBooking->status }}</div>
            </div>
        </div>

        @if($exportBooking->commodity_description || $exportBooking->remarks)
        <div class="section-card">
            <div class="form-header"><i class="fa fa-sticky-note me-1"></i> Notes</div>
            <div class="section-body">
                @if($exportBooking->commodity_description)
                <div class="mb-2">
                    <div class="info-label">Commodity</div>
                    <div class="info-value">{{ $exportBooking->commodity_description }}</div>
                </div>
                @endif
                @if($exportBooking->remarks)
                <div>
                    <div class="info-label">Remarks</div>
                    <div class="info-value">{{ $exportBooking->remarks }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif


    </div>
</div>

{{-- Transport Section — full width below two-column layout --}}
@if(auth()->user()->hasPermission('freight.export-booking.transport-manage'))
<div class="section-card mt-1">
    <div class="form-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#0c2340,#1a6b60)">
        <span><i class="fa fa-truck me-1"></i> Cover Van / Transport Details
            @if($exportBooking->transport_amount > 0)
                <span class="ms-2 badge bg-light text-dark fw-bold" style="font-size:.78rem;">
                    Total: {{ number_format($exportBooking->transport_amount, 2) }}
                </span>
            @endif
        </span>
        <a href="{{ route('nas-freights.freight-export-bookings.transport.edit', $exportBooking->id) }}"
           class="btn btn-sm btn-light py-0 px-2" style="font-size:.72rem;">
            <i class="fa fa-edit me-1"></i>{{ $exportBooking->transportItems->isEmpty() ? 'Add Transport' : 'Edit Transport' }}
        </a>
    </div>
    @if($exportBooking->transportItems->isEmpty())
        <div class="section-body text-center text-muted py-3" style="font-size:.8rem;">
            <i class="fa fa-truck me-1"></i> No transport details added yet.
        </div>
    @else
        <div class="p-0" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <table class="table table-bordered table-hover mb-0" style="font-size:.73rem; white-space:nowrap; min-width:900px;">
                <thead>
                    <tr style="background:#1a6b60; color:#fff;">
                        <th style="padding:.3rem .5rem; width:35px;">#</th>
                        <th style="padding:.3rem .5rem;">Cover Van No</th>
                        <th style="padding:.3rem .5rem;">Challan No</th>
                        <th style="padding:.3rem .5rem;">Capacity</th>
                        <th style="padding:.3rem .5rem;">Supplier</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Qty</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Sup. Rate</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Cus. Rate</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Demrr. Days</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Cus. Demurrage</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Sup. Demurrage</th>
                        <th style="padding:.3rem .5rem; text-align:right;">Amount</th>
                        <th style="padding:.3rem .5rem;">From</th>
                        <th style="padding:.3rem .5rem;">To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exportBooking->transportItems as $i => $ti)
                    <tr>
                        <td class="text-center fw-bold" style="padding:.3rem .5rem;">{{ $i + 1 }}</td>
                        <td class="fw-semibold" style="padding:.3rem .5rem;">{{ $ti->cover_van_no ?? '—' }}</td>
                        <td style="padding:.3rem .5rem;">{{ $ti->challan_no ?? '—' }}</td>
                        <td style="padding:.3rem .5rem;">{{ $ti->capacity ?? '—' }}</td>
                        <td style="padding:.3rem .5rem;">{{ $ti->supplier_name ?? '—' }}</td>
                        <td class="text-end" style="padding:.3rem .5rem;">{{ number_format($ti->qty, 2) }}</td>
                        <td class="text-end" style="padding:.3rem .5rem;">{{ number_format($ti->supplier_rate, 2) }}</td>
                        <td class="text-end" style="padding:.3rem .5rem;">{{ number_format($ti->customer_rate, 2) }}</td>
                        <td class="text-center" style="padding:.3rem .5rem;">{{ $ti->demurrage_days }}</td>
                        <td class="text-end" style="padding:.3rem .5rem;">{{ number_format($ti->cus_demurrage_charge, 2) }}</td>
                        <td class="text-end" style="padding:.3rem .5rem;">{{ number_format($ti->sup_demurrage_charge, 2) }}</td>
                        <td class="text-end fw-bold" style="padding:.3rem .5rem; color:#0a4f3c;">{{ number_format($ti->amount, 2) }}</td>
                        <td style="padding:.3rem .5rem;">{{ $ti->location_from ?? '—' }}</td>
                        <td style="padding:.3rem .5rem;">{{ $ti->location_to ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f0f8ff; font-weight:700; border-top:2px solid #0c2340;">
                        <td colspan="11" class="text-end" style="padding:.35rem .5rem;">Total Transport Amount</td>
                        <td class="text-end" style="padding:.35rem .5rem; color:#0a4f3c;">{{ number_format($exportBooking->transport_amount, 2) }}</td>
                        <td colspan="2" style="padding:.35rem .5rem;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endif
@endsection
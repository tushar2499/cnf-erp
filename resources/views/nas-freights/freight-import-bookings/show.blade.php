@extends('nas-freights.layouts.app')

@section('title', 'Freight Import Booking/Job — ' . $freightBooking->freight_booking_no)

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

.info-sub { font-size:.7rem; color:#6b7280; margin-top:.05rem; }
.fb-label { display:block; font-size:.7rem; font-weight:600; color:#495057; margin-bottom:.1rem; }

/* ── Financial Summary card ── */
.fin-summary-card { width:100%; max-width:460px; border:1px solid #dee2e6; border-radius:.35rem; }
.fin-summary-body { padding:.45rem .65rem .55rem; }

.fin-meta-bar {
    display:flex; align-items:center; gap:.45rem;
    background:#f4faf8; border:1px solid #c8e6de; border-radius:.25rem;
    padding:.3rem .6rem; margin-bottom:.45rem;
}
.fin-cur-chip {
    font-size:.72rem; font-weight:700; color:#fff;
    background:#0a4f3c; border-radius:.2rem;
    padding:.1rem .45rem; letter-spacing:.03em; flex-shrink:0;
}
.fin-meta-divider { color:#adb5bd; font-size:.75rem; }
.fin-meta-txt { font-size:.73rem; color:#374151; line-height:1.3; }
.fin-meta-txt strong { color:#0a4f3c; }

.fin-table { border-collapse:collapse; width:100%; }
.fin-table thead th {
    font-size:.65rem; font-weight:700; color:#6c757d;
    padding:0 .35rem .22rem; border-bottom:1px solid #dee2e6; white-space:nowrap;
}
.fin-table tbody td { padding:.28rem .35rem; vertical-align:middle; }
.fin-table tbody tr:not(:last-child) td { border-bottom:1px solid #f0f0f0; }
.fin-cur-badge {
    font-size:.6rem; font-weight:700; color:#0a4f3c;
    background:#e8f5f1; border-radius:.2rem; padding:.05rem .25rem; margin-left:.2rem;
}
.fin-amount-val { font-size:.78rem; font-weight:600; color:#1e293b; }
.fin-arrow { font-size:.65rem; color:#adb5bd; }
.fin-bdt-val { font-size:.78rem; font-weight:700; color:#0a4f3c; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div></div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        Freight Import Booking/Job &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $freightBooking->freight_booking_no }}</span>
        &nbsp;<span class="status-pill status-{{ str_replace(' ', '-', $freightBooking->status) }}">{{ $freightBooking->status }}</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('nas-freights.freight-import-bookings.edit', $freightBooking->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('nas-freights.freight-import-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <div class="form-header"><i class="fa fa-ship me-1"></i> Booking/Job Information</div>
            <div class="section-body">
                <div class="row g-2">

                    {{-- Row 1: core identifiers --}}
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking/Job No</div>
                        <div class="info-value fw-bold">{{ $freightBooking->freight_booking_no }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking/Job Date</div>
                        <div class="info-value">{{ $freightBooking->booking_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Service Type</div>
                        <div class="info-value">{{ $freightBooking->service_type ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $freightBooking->salesperson?->name ?? '—' }}</div>
                    </div>

                    {{-- Row 2: parties --}}
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Customer (Importer)</div>
                        <div class="info-value fw-semibold">{{ $freightBooking->customer?->name ?? '—' }}</div>
                        @if($freightBooking->customer?->customer_id)
                        <div class="info-sub">{{ $freightBooking->customer->customer_id }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Overseas Agent</div>
                        @if($freightBooking->overseasAgent)
                        <div class="info-value fw-semibold">{{ $freightBooking->overseasAgent->name }}</div>
                        <div class="info-sub">{{ $freightBooking->overseasAgent->agent_code }}@if($freightBooking->overseasAgent->country) &nbsp;·&nbsp; {{ $freightBooking->overseasAgent->country }}@endif</div>
                        @else
                        <div class="info-value">—</div>
                        @endif
                    </div>

                    {{-- Row 3: shipping + commercial --}}
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Shipping Carrier / Airline</div>
                        @if($freightBooking->shippingCarrier)
                        <div class="info-value fw-semibold">{{ $freightBooking->shippingCarrier->name }}</div>
                        <div class="info-sub">{{ $freightBooking->shippingCarrier->carrier_code }}@if($freightBooking->shippingCarrier->scac_code) &nbsp;·&nbsp; SCAC: {{ $freightBooking->shippingCarrier->scac_code }}@endif</div>
                        @else
                        <div class="info-value">—</div>
                        @endif
                    </div>
                    @if($freightBooking->rfq_no)
                    <div class="col-6 col-sm-3">
                        <div class="info-label">From RFQ</div>
                        <div class="info-value"><a href="{{ route('nas-freights.rfqs.show', $freightBooking->rfq_id) }}">{{ $freightBooking->rfq_no }}</a></div>
                    </div>
                    @endif
                    <div class="col-6 col-sm-3">
                        <div class="info-label">Incoterms</div>
                        <div class="info-value">{{ $freightBooking->incoterms ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $freightBooking->currency ?? '—' }}</div>
                    </div>

                    {{-- Row 4: invoice refs --}}
                    <div class="col-6 col-md-3">
                        <div class="info-label">Customer Invoice No</div>
                        <div class="info-value">{{ $freightBooking->customer_invoice_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Customer Invoice Date</div>
                        <div class="info-value">{{ $freightBooking->customer_invoice_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Agent Invoice No</div>
                        <div class="info-value">{{ $freightBooking->agent_invoice_no ?? '—' }}</div>
                    </div>

                    {{-- Row 5: routing --}}
                    <div class="col-6 col-md-3">
                        <div class="info-label">Port of Loading (POL)</div>
                        <div class="info-value">{{ $freightBooking->pol ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Port of Discharge (POD)</div>
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

        @if($freightBooking->vessel_name || $freightBooking->bl_no || $freightBooking->igm_no || $freightBooking->delivery_order_no || $freightBooking->etd || $freightBooking->eta)
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
                    <div class="col-6 col-md-3">
                        <div class="info-label">IGM No</div>
                        <div class="info-value fw-semibold">{{ $freightBooking->igm_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Delivery Order No</div>
                        <div class="info-value">{{ $freightBooking->delivery_order_no ?? '—' }}</div>
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

        @php
            $documentRefs = [
                'Flight No'        => $freightBooking->flight_no,
                'Flight Date'      => $freightBooking->flight_date?->format('d M Y'),
                'MB/L / MAWB No'   => $freightBooking->mbl_mawb_no,
                'MB/L / MAWB Date' => $freightBooking->mbl_mawb_date?->format('d M Y'),
                'HBL/HAWB No'      => $freightBooking->hbl_hawb_no,
                'HBL/HAWB Date'    => $freightBooking->hbl_hawb_date?->format('d M Y'),
                'LC No'            => $freightBooking->lc_no,
                'CAD No'           => $freightBooking->cad_no,
                'TT No'            => $freightBooking->tt_no,
                'RFQ/Tender No'    => $freightBooking->rfq_tender_no,
            ];
        @endphp
        @if(collect($documentRefs)->filter()->isNotEmpty())
        <div class="section-card">
            <div class="form-header"><i class="fa fa-file-alt me-1"></i> Flight &amp; Document References</div>
            <div class="section-body">
                <div class="row g-3">
                    @foreach($documentRefs as $label => $value)
                    <div class="col-6 col-md-3">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{{ $value ?? '—' }}</div>
                    </div>
                    @endforeach
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
                                <th>#</th><th>Type</th><th>Qty</th><th>Size / Package</th><th>Pkg Qty</th><th>Pkg Unit</th><th>Container No</th><th>Seal No</th>@if($freightBooking->isAir())<th>Gross Weight</th><th>Chargeable Weight</th>@else<th>Net Weight</th><th>Gross Weight</th>@endif<th>CBM</th><th>Origin</th><th>DG</th><th>Special</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($freightBooking->items as $i => $item)
                            @php $isContainer = $item->item_type === 'container'; @endphp
                            <tr>
                                <td class="text-center fw-bold">{{ $i + 1 }}</td>
                                <td>
                                    @if($item->item_type === 'container')
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
                                @if($freightBooking->isAir())
                                <td class="text-end text-nowrap">{{ $item->gross_weight ? number_format($item->gross_weight, 2).' '.$item->weight_unit : '—' }}</td>
                                <td class="text-end text-nowrap">{{ $item->chargeable_weight ? number_format($item->chargeable_weight, 2).' '.$item->weight_unit : '—' }}</td>
                                @else
                                <td class="text-end text-nowrap">{{ $item->net_weight ? number_format($item->net_weight, 2).' '.$item->weight_unit : '—' }}</td>
                                <td class="text-end text-nowrap">{{ $item->gross_weight ? number_format($item->gross_weight, 2).' '.$item->weight_unit : '—' }}</td>
                                @endif
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

        @if($freightBooking->commodity_description || $freightBooking->hs_codes || $freightBooking->remarks)
        <div class="section-card">
            <div class="form-header"><i class="fa fa-sticky-note me-1"></i> Notes</div>
            <div class="section-body">
                @if($freightBooking->commodity_description)
                <div class="mb-2">
                    <div class="info-label">Commodity</div>
                    <div class="info-value">{{ $freightBooking->commodity_description }}</div>
                </div>
                @endif
                @if($freightBooking->hs_codes)
                <div class="mb-2">
                    <div class="info-label">HS Code</div>
                    <div class="info-value d-flex flex-wrap gap-1">
                        @foreach($freightBooking->hs_codes as $hsCode)
                        <span class="badge bg-light text-dark border">{{ $hsCode }}</span>
                        @endforeach
                    </div>
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

@if($freightBooking->exchange_rate || $freightBooking->buy_amount || $freightBooking->sell_amount)
<div class="d-flex justify-content-end mt-1 mb-3">
    <div class="fin-summary-card">
        <div class="form-header py-1"><i class="fa fa-exchange-alt me-1"></i> Currency &amp; Financial Summary</div>
        <div class="fin-summary-body">

            {{-- Inline currency + rate meta bar --}}
            <div class="fin-meta-bar">
                <span class="fin-cur-chip">{{ $freightBooking->currency ?? 'BDT' }}</span>
                <span class="fin-meta-divider">·</span>
                @php
                    $rate = $freightBooking->exchange_rate
                        ? rtrim(rtrim(number_format((float)$freightBooking->exchange_rate, 6), '0'), '.')
                        : null;
                @endphp
                <span class="fin-meta-txt">
                    Exchange Rate:&nbsp;
                    <strong>1 {{ $freightBooking->currency ?? 'BDT' }} = {{ $rate ?? '—' }} BDT</strong>
                </span>
            </div>

            {{-- Amount table --}}
            <table class="fin-table w-100">
                <colgroup>
                    <col style="width:130px">
                    <col style="width:120px">
                    <col style="width:36px">
                    <col style="width:120px">
                </colgroup>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount <span class="fin-cur-badge">{{ $freightBooking->currency ?? 'BDT' }}</span></th>
                        <th></th>
                        <th class="text-end">BDT Equivalent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fb-label align-middle">Buy Amount</td>
                        <td class="text-end fin-amount-val">
                            {{ $freightBooking->buy_amount ? number_format($freightBooking->buy_amount, 2) : '—' }}
                        </td>
                        <td class="text-center fin-arrow">→</td>
                        <td class="text-end fin-bdt-val">
                            {{ $freightBooking->buy_bdt_amount ? number_format($freightBooking->buy_bdt_amount, 2) : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fb-label align-middle">Sell Amount</td>
                        <td class="text-end fin-amount-val">
                            {{ $freightBooking->sell_amount ? number_format($freightBooking->sell_amount, 2) : '—' }}
                        </td>
                        <td class="text-center fin-arrow">→</td>
                        <td class="text-end fin-bdt-val">
                            {{ $freightBooking->sell_bdt_amount ? number_format($freightBooking->sell_bdt_amount, 2) : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
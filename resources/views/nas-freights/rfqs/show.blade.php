@extends('nas-freights.layouts.app')

@section('title', 'RFQ — ' . $rfq->rfq_no)

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
.status-Draft   { background:#e2e8f0; color:#475569; }
.status-Pending { background:#fef9c3; color:#92400e; }
.status-Win     { background:#dcfce7; color:#166534; }
.status-Lose    { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div></div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        RFQ Detail &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $rfq->rfq_no }}</span>
        &nbsp;<span class="status-pill status-{{ $rfq->status }}">{{ $rfq->status }}</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('nas-freights.rfqs.edit', $rfq->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('nas-freights.rfqs.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-2">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show py-2 mb-2">
    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">
    {{-- Left: RFQ Details --}}
    <div class="col-lg-8">

        <div class="section-card">
            <div class="form-header"><i class="fa fa-file-signature me-1"></i> RFQ Information</div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">RFQ No</div>
                        <div class="info-value fw-bold">{{ $rfq->rfq_no }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">RFQ Date</div>
                        <div class="info-value">{{ $rfq->rfq_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Valid Until</div>
                        <div class="info-value">{{ $rfq->valid_until?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $rfq->salesperson?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Customer</div>
                        <div class="info-value fw-semibold">{{ $rfq->customer?->name ?? '—' }}</div>
                        @if($rfq->customer?->customer_id)
                        <div style="font-size:.7rem; color:#6b7280;">{{ $rfq->customer->customer_id }}</div>
                        @endif
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Overseas Agent</div>
                        <div class="info-value">
                            @if($rfq->overseasAgent)
                                <span class="fw-semibold">{{ $rfq->overseasAgent->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $rfq->overseasAgent->agent_code }}@if($rfq->overseasAgent->country) &nbsp;·&nbsp;{{ $rfq->overseasAgent->country }}@endif</div>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Shipping Carrier</div>
                        <div class="info-value">
                            @if($rfq->shippingCarrier)
                                <span class="fw-semibold">{{ $rfq->shippingCarrier->name }}</span>
                                <div style="font-size:.7rem; color:#6b7280;">{{ $rfq->shippingCarrier->carrier_code }}@if($rfq->shippingCarrier->scac_code) &nbsp;·&nbsp;SCAC: {{ $rfq->shippingCarrier->scac_code }}@endif</div>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="info-label">Type</div>
                        <div class="info-value">
                            @if($rfq->type === 'import')
                                <span class="badge bg-info text-dark">Import</span>
                            @else
                                <span class="badge bg-warning text-dark">Export</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">MOD/Service Type</div>
                        <div class="info-value">{{ $rfq->service_type ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Incoterms</div>
                        <div class="info-value">{{ $rfq->incoterms ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $rfq->currency ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Port of Loading (POL)</div>
                        <div class="info-value">{{ $rfq->pol ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Port of Discharge (POD)</div>
                        <div class="info-value">{{ $rfq->pod ?? '—' }}</div>
                    </div>
                    @if($rfq->place_of_receipt || $rfq->place_of_delivery)
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Receipt</div>
                        <div class="info-value">{{ $rfq->place_of_receipt ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Place of Delivery</div>
                        <div class="info-value">{{ $rfq->place_of_delivery ?? '—' }}</div>
                    </div>
                    @endif
                    @if($rfq->commodity_description)
                    <div class="col-md-6">
                        <div class="info-label">Commodity Description</div>
                        <div class="info-value">{{ $rfq->commodity_description }}</div>
                    </div>
                    @endif
                    @if($rfq->remarks)
                    <div class="col-md-6">
                        <div class="info-label">Remarks</div>
                        <div class="info-value">{{ $rfq->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="form-header"><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</div>
            <div class="section-body p-0">
                @if($rfq->items->isEmpty())
                <div class="text-center text-muted py-3" style="font-size:.8rem;"><i class="fa fa-inbox me-1"></i> No cargo items.</div>
                @else
                <div style="overflow-x:auto;">
                    <table class="table table-bordered table-hover mb-0 cargo-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Size / Package</th>
                                <th>HS Code</th>
                                <th>Commodity</th>
                                <th>Qty</th>
                                <th>Weight</th>
                                <th>CBM</th>
                                <th>Origin</th>
                                <th>DG</th>
                                <th>Special</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfq->items as $i => $item)
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
                                <td class="text-end text-nowrap">
                                    {{ $item->gross_weight ? number_format($item->gross_weight, 2).' '.$item->weight_unit : '—' }}
                                </td>
                                <td class="text-end">{{ $item->volume_cbm ? number_format($item->volume_cbm, 3) : '—' }}</td>
                                <td>{{ $item->country_of_origin ?? '—' }}</td>
                                <td class="text-center">
                                    @if($item->is_dangerous_goods)
                                        <span class="badge bg-danger">DG</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
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

    {{-- Right: Status Panel --}}
    <div class="col-lg-4">

        <div class="section-card">
            <div class="form-header"><i class="fa fa-tasks me-1"></i> Status</div>
            <div class="section-body text-center py-3">
                <div class="status-pill status-{{ $rfq->status }} mb-2">{{ $rfq->status }}</div>
                @if($rfq->status === 'Lose' && $rfq->lost_reason)
                <div style="font-size:.75rem; color:#6b7280; margin-top:.3rem;">Reason: {{ $rfq->lost_reason }}</div>
                @endif
                @if($rfq->status === 'Win' && $rfq->converted_freight_booking_id)
                <div class="mt-2" style="font-size:.75rem;">
                    <i class="fa fa-check-circle text-success me-1"></i>
                    Freight Import Booking: <a href="{{ route('nas-freights.freight-import-bookings.show', $rfq->converted_freight_booking_id) }}" class="fw-semibold">{{ $rfq->convertedFreightBooking?->freight_booking_no }}</a>
                </div>
                @endif
            </div>
        </div>

        <div class="section-card">
            <div class="form-header"><i class="fa fa-exchange-alt me-1"></i> Update Status</div>
            <div class="section-body">
                <form method="POST" action="{{ route('nas-freights.rfqs.update-status', $rfq->id) }}">
                    @csrf @method('PATCH')

                    <div class="mb-2">
                        <label class="info-label d-block mb-1">Set Status</label>
                        <div class="d-grid gap-1">
                            @foreach(['Draft' => ['secondary', 'Draft'], 'Pending' => ['warning', 'Pending — Sent to Client'], 'Win' => ['success', 'Win'], 'Lose' => ['danger', 'Lose']] as $val => [$color, $label])
                            <div class="form-check ps-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="status" id="status_{{ $val }}" value="{{ $val }}"
                                    {{ $rfq->status === $val ? 'checked' : '' }}
                                    onchange="document.getElementById('lostReasonWrap').style.display = (this.value==='Lose') ? '' : 'none'">
                                <label class="form-check-label" for="status_{{ $val }}">
                                    <span class="badge bg-{{ $color }} {{ $color === 'warning' ? 'text-dark' : '' }}">{{ $val }}</span>
                                    <span style="font-size:.73rem; color:#374151;">{{ $label }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="lostReasonWrap" style="{{ $rfq->status === 'Lose' ? '' : 'display:none' }};" class="mb-2">
                        <label class="info-label d-block mb-1">Lost Reason <span class="text-danger">*</span></label>
                        <select name="lost_reason" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            @foreach($lostReasons as $reason)
                            <option value="{{ $reason }}" {{ $rfq->lost_reason === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fa fa-check me-1"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        @if($rfq->status === 'Win' && $rfq->type === 'import')
        <div class="section-card">
            <div class="form-header"><i class="fa fa-ship me-1"></i> Convert to Freight Import Booking</div>
            <div class="section-body">
                @if($rfq->converted_freight_booking_id)
                <div class="text-success text-center" style="font-size:.8rem;">
                    <i class="fa fa-check-circle me-1"></i> Already converted to
                    <a href="{{ route('nas-freights.freight-import-bookings.show', $rfq->converted_freight_booking_id) }}" class="fw-bold">{{ $rfq->convertedFreightBooking?->freight_booking_no }}</a>
                </div>
                @else
                <p style="font-size:.75rem; color:#6b7280; margin-bottom:.6rem;">
                    Create a Freight Import Booking from this RFQ. All freight details and cargo items will be pre-filled.
                </p>
                <form method="POST" action="{{ route('nas-freights.rfqs.convert-freight-booking', $rfq->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success w-100"
                        onclick="return confirm('Convert RFQ {{ $rfq->rfq_no }} to a Freight Import Booking?')">
                        <i class="fa fa-ship me-1"></i> Convert to Freight Import Booking
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        <div class="section-card">
            <div class="form-header"><i class="fa fa-info-circle me-1"></i> Record Info</div>
            <div class="section-body">
                <div class="mb-1">
                    <span class="info-label">Created</span>
                    <div class="info-value">{{ $rfq->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div>
                    <span class="info-label">Last Updated</span>
                    <div class="info-value">{{ $rfq->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checked = document.querySelector('input[name="status"]:checked');
    if (checked && checked.value === 'Lose') {
        document.getElementById('lostReasonWrap').style.display = '';
    }
});
</script>
@endpush

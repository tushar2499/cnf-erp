@extends('nas-freights.layouts.app')

@section('title', $rfq ? 'Edit RFQ' : 'New RFQ')

@push('styles')
<style>
.form-header { background: linear-gradient(135deg,#0a4f3c,#14b8a6); color:#fff; padding:.5rem 1rem; border-radius:.35rem .35rem 0 0; font-weight:600; font-size:.78rem; }
.section-card { border:1px solid #dee2e6; border-radius:.35rem; margin-bottom:.75rem; }
.section-card .section-body { padding:.6rem .75rem; }
.rfq-label { font-size:.7rem; font-weight:600; color:#495057; margin-bottom:.1rem; }
.rfq-input { font-size:.75rem; height:28px; padding:.18rem .4rem; }
.input-group > .rfq-input { height:28px; }
.rfq-textarea { font-size:.75rem; padding:.18rem .4rem; resize:vertical; }
#itemsTable th { background:#f1f3f5; font-size:.68rem; font-weight:700; padding:.25rem .4rem; white-space:nowrap; }
#itemsTable td { padding:.2rem .3rem; vertical-align:middle; }
.item-type-toggle .btn { font-size:.7rem; padding:.1rem .5rem; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-2">
    <div>
        <a href="{{ route('nas-freights.rfqs.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
    <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
        RFQ Entry @if($rfq)<span class="ms-2 badge bg-light text-dark border">{{ $rfq->rfq_no }}</span>@endif
    </div>
    <div></div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2 mb-2">
    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST"
      action="{{ $rfq ? route('nas-freights.rfqs.update', $rfq->id) : route('nas-freights.rfqs.store') }}">
@csrf
@if($rfq) @method('PUT') @endif

{{-- ═══ HEADER SECTION ═══ --}}
<div class="section-card">
    <div class="form-header"><i class="fa fa-file-signature me-1"></i> RFQ Information</div>
    <div class="section-body">
        <div class="row g-2 mb-2">
            <div class="col-md-2">
                <div class="rfq-label">RFQ No</div>
                <input type="text" class="form-control rfq-input bg-light" value="{{ $rfq?->rfq_no ?? 'Auto Generated' }}" readonly>
            </div>
            <div class="col-md-2">
                <div class="rfq-label">RFQ Date <span class="text-danger">*</span></div>
                <input type="date" name="rfq_date" class="form-control rfq-input" value="{{ old('rfq_date', $rfq?->rfq_date?->format('Y-m-d') ?? $today) }}" required>
            </div>
            <div class="col-md-2">
                <div class="rfq-label">Valid Until</div>
                <input type="date" name="valid_until" class="form-control rfq-input" value="{{ old('valid_until', $rfq?->valid_until?->format('Y-m-d') ?? $defaultValidUntil) }}">
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Customer</div>
                <select name="customer_id" id="customerSelect" class="form-select rfq-input" style="width:100%">
                    @if($rfq?->customer_id)
                    <option value="{{ $rfq->customer_id }}" selected>{{ $rfq->customer?->customer_id }} — {{ $rfq->customer?->name }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Salesperson</div>
                <select name="salesperson_id" id="salespersonSelect" class="form-select rfq-input" style="width:100%">
                    @if($rfq?->salesperson_id)
                    <option value="{{ $rfq->salesperson_id }}" selected>{{ $rfq->salesperson?->name }}</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <div class="rfq-label">Overseas Agent</div>
                <select name="overseas_agent_id" id="overseasAgentSelect" class="form-select rfq-input" style="width:100%">
                    @if($rfq?->overseas_agent_id)
                    <option value="{{ $rfq->overseas_agent_id }}" selected>
                        {{ $rfq->overseasAgent?->agent_code }} — {{ $rfq->overseasAgent?->name }} ({{ $rfq->overseasAgent?->country }})
                    </option>
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                <div class="rfq-label">Shipping Carrier</div>
                <select name="shipping_carrier_id" id="shippingCarrierSelect" class="form-select rfq-input" style="width:100%">
                    @if($rfq?->shipping_carrier_id)
                    <option value="{{ $rfq->shipping_carrier_id }}" selected>
                        {{ $rfq->shippingCarrier?->carrier_code }} — {{ $rfq->shippingCarrier?->name }}
                    </option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="rfq-label">Type <span class="text-danger">*</span></div>
                <select name="type" class="form-select rfq-input" required>
                    @foreach($types as $val => $label)
                    <option value="{{ $val }}" {{ old('type', $rfq?->type ?? 'import') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="rfq-label">MOD / Service Type</div>
                <select name="service_type" class="form-select rfq-input">
                    @foreach($serviceTypes as $st)
                    <option value="{{ $st }}" {{ old('service_type', $rfq?->service_type ?? 'FCL') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Incoterms</div>
                <select name="incoterms" class="form-select rfq-input">
                    <option value="">-- Select --</option>
                    @foreach($incoterms as $inc)
                    <option value="{{ $inc }}" {{ old('incoterms', $rfq?->incoterms) === $inc ? 'selected' : '' }}>{{ $inc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Currency</div>
                <select name="currency" class="form-select rfq-input">
                    @foreach($currencies as $cur)
                    <option value="{{ $cur }}" {{ old('currency', $rfq?->currency ?? 'BDT') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="rfq-label">Port of Loading (POL)</div>
                <input type="text" name="pol" class="form-control rfq-input" value="{{ old('pol', $rfq?->pol) }}" placeholder="e.g. Chittagong">
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Port of Discharge (POD)</div>
                <input type="text" name="pod" class="form-control rfq-input" value="{{ old('pod', $rfq?->pod) }}" placeholder="e.g. Singapore">
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Place of Receipt</div>
                <input type="text" name="place_of_receipt" class="form-control rfq-input" value="{{ old('place_of_receipt', $rfq?->place_of_receipt) }}" placeholder="Pre-carriage origin">
            </div>
            <div class="col-md-3">
                <div class="rfq-label">Place of Delivery</div>
                <input type="text" name="place_of_delivery" class="form-control rfq-input" value="{{ old('place_of_delivery', $rfq?->place_of_delivery) }}" placeholder="Final destination">
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <div class="rfq-label">Commodity Description</div>
                <input type="text" name="commodity_description" class="form-control rfq-input" value="{{ old('commodity_description', $rfq?->commodity_description) }}" placeholder="General cargo description">
            </div>
            <div class="col-md-6">
                <div class="rfq-label">Remarks</div>
                <input type="text" name="remarks" class="form-control rfq-input" value="{{ old('remarks', $rfq?->remarks) }}" placeholder="Special instructions or notes">
            </div>
        </div>
    </div>
</div>

{{-- ═══ CARGO ITEMS ═══ --}}
<div class="section-card">
    <div class="form-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</span>
        <button type="button" class="btn btn-sm btn-light py-0 px-2" id="addItemRow">
            <i class="fa fa-plus me-1"></i> Add Row
        </button>
    </div>
    <div class="section-body p-0">
        <div style="overflow-x:auto;">
            <table class="table table-bordered mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:35px">#</th>
                        <th style="width:30px"></th>
                        <th style="width:110px">Item Type</th>
                        <th style="width:70px">Qty</th>
                        <th style="width:120px">Container Size / Package</th>
                        <th style="width:110px">HS Code</th>
                        <th>Commodity</th>
                        <th style="width:100px">Weight</th>
                        <th style="width:70px">Unit</th>
                        <th style="width:80px">CBM</th>
                        <th style="width:130px">Country of Origin</th>
                        <th style="width:90px">DG</th>
                        <th>Special Handling</th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ STATUS (edit only — inline with main form) ═══ --}}
@if($rfq)
<div class="section-card">
    <div class="form-header"><i class="fa fa-tasks me-1"></i> Status</div>
    <div class="section-body">
        <div class="row g-3 align-items-start">
            <div class="col-auto">
                <label class="rfq-label d-block mb-1">Set Status</label>
                <div class="d-grid gap-1">
                    @foreach(['Draft' => ['secondary', 'Draft'], 'Pending' => ['warning', 'Pending — Sent to Client'], 'Win' => ['success', 'Win'], 'Lose' => ['danger', 'Lose']] as $val => [$color, $label])
                    <div class="form-check ps-0">
                        <input class="form-check-input ms-0 me-2" type="radio" name="status"
                               id="edit_status_{{ $val }}" value="{{ $val }}"
                               {{ old('status', $rfq->status) === $val ? 'checked' : '' }}
                               onchange="document.getElementById('editLostReasonWrap').style.display=(this.value==='Lose')?'':'none'">
                        <label class="form-check-label" for="edit_status_{{ $val }}">
                            <span class="badge bg-{{ $color }} {{ $color === 'warning' ? 'text-dark' : '' }}">{{ $val }}</span>
                            <span style="font-size:.73rem; color:#374151;">{{ $label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-3" id="editLostReasonWrap" style="{{ old('status', $rfq->status) === 'Lose' ? '' : 'display:none' }};">
                <label class="rfq-label d-block mb-1">Lost Reason <span class="text-danger">*</span></label>
                <select name="lost_reason" class="form-select form-select-sm">
                    <option value="">-- Select --</option>
                    @foreach($lostReasons as $reason)
                    <option value="{{ $reason }}" {{ old('lost_reason', $rfq->lost_reason) === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                    @endforeach
                </select>
            </div>
            @if($rfq->status === 'Win')
            <div class="col-auto">
                @if($rfq->converted_freight_booking_id)
                <span class="d-inline-flex align-items-center gap-2" style="font-size:.8rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:.4rem; padding:.4rem .75rem; color:#166534;">
                    <i class="fa fa-check-circle"></i> Converted to Freight Booking:
                    <a href="{{ route('nas-freights.freight-bookings.show', $rfq->converted_freight_booking_id) }}" class="fw-bold text-success">{{ $rfq->convertedFreightBooking?->freight_booking_no }}</a>
                </span>
                @else
                <label class="rfq-label d-block mb-1">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-success" id="btnConvertBooking">
                    <i class="fa fa-ship me-1"></i> Convert to Freight Booking
                </button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Submit --}}
<div class="d-flex justify-content-end gap-2 mt-3 mb-4">
    <a href="{{ route('nas-freights.rfqs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-times me-1"></i> Cancel
    </a>
    <button type="submit" class="btn btn-sm btn-success px-4">
        <i class="fa fa-save me-1"></i> {{ $rfq ? 'Update RFQ' : 'Save RFQ' }}
    </button>
</div>

</form>

{{-- Convert-to-booking form lives OUTSIDE the main form to avoid nested-form HTML violation --}}
@if($rfq && $rfq->status === 'Win' && !$rfq->converted_freight_booking_id)
<form id="convertBookingForm" method="POST" action="{{ route('nas-freights.rfqs.convert-freight-booking', $rfq->id) }}">
    @csrf
</form>
@endif

{{-- Item row template --}}
<template id="itemTemplate">
<tr>
    <td class="sl-no text-center fw-bold" style="font-size:.72rem;"></td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-item-row"><i class="fa fa-times"></i></button>
    </td>
    <td>
        <select name="items[0][item_type]" class="form-select form-select-sm item-type-sel" style="font-size:.72rem;">
            <option value="container">Container</option>
            <option value="package">Package</option>
        </select>
    </td>
    <td>
        <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-center" style="font-size:.72rem;" value="1" min="1">
    </td>
    <td>
        {{-- Container size (shown when type=container) --}}
        <select name="items[0][container_size]" class="form-select form-select-sm container-size-sel" style="font-size:.72rem;">
            <option value="">-- Size --</option>
            @foreach($containerSizes as $cs)
            <option value="{{ $cs }}">{{ $cs }}</option>
            @endforeach
        </select>
        {{-- Package type (shown when type=package) --}}
        <select name="items[0][package_type]" class="form-select form-select-sm package-type-sel d-none" style="font-size:.72rem;">
            <option value="">-- Type --</option>
            @foreach($packageTypes as $pt)
            <option value="{{ $pt }}">{{ $pt }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="text" name="items[0][hs_code]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="HS Code">
    </td>
    <td>
        <input type="text" name="items[0][commodity]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="Commodity">
    </td>
    <td>
        <input type="number" name="items[0][gross_weight]" class="form-control form-control-sm text-end" style="font-size:.72rem;" step="0.01" placeholder="0.00">
    </td>
    <td>
        <select name="items[0][weight_unit]" class="form-select form-select-sm" style="font-size:.72rem;">
            @foreach($weightUnits as $wu)
            <option value="{{ $wu }}">{{ $wu }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" name="items[0][volume_cbm]" class="form-control form-control-sm text-end" style="font-size:.72rem;" step="0.001" placeholder="0.000">
    </td>
    <td>
        <input type="text" name="items[0][country_of_origin]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="Country">
    </td>
    <td>
        <select name="items[0][is_dangerous_goods]" class="form-select form-select-sm" style="font-size:.72rem;">
            <option value="0">No DG</option>
            <option value="1">DG</option>
        </select>
    </td>
    <td>
        <input type="text" name="items[0][special_handling]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="e.g. Fragile">
    </td>
</tr>
</template>
@endsection

@push('scripts')
<script>
// Auto-collapse sidebar for the wide RFQ form
$(function () {
    if (!$('body').hasClass('sidebar-collapsed')) {
        $('body').addClass('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', '1');
    }
});

var existingItems = @json($existingItems);

$(function () {
    $('#customerSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search customer...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-freights.rfqs.search-customers') }}',
            dataType: 'json',
            delay: 300,
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d }),
        },
    });

    $('#salespersonSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search salesperson...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-freights.rfqs.search-employees') }}',
            dataType: 'json',
            delay: 300,
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d }),
        },
    });

    $('#overseasAgentSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search overseas agent...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-freights.rfqs.search-overseas-agents') }}',
            dataType: 'json',
            delay: 300,
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d }),
        },
    });

    $('#shippingCarrierSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search shipping carrier...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-freights.rfqs.search-shipping-carriers') }}',
            dataType: 'json',
            delay: 300,
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d }),
        },
    });

    if (existingItems.length > 0) {
        existingItems.forEach(function (item) { addItemRow(item); });
    } else {
        addItemRow();
    }

    $('#addItemRow').on('click', function () { addItemRow(); });

    $(document).on('click', '.remove-item-row', function () {
        if ($('#itemsBody tr').length <= 1) return;
        $(this).closest('tr').remove();
        reindexItems();
    });

    $(document).on('change', '.item-type-sel', function () {
        toggleItemTypeFields($(this).closest('tr'));
    });

    $('#btnConvertBooking').on('click', function () {
        if (confirm('Convert RFQ to a Booking? Customer and cargo details will be pre-filled.')) {
            document.getElementById('convertBookingForm').submit();
        }
    });
});

function addItemRow(data) {
    var tmpl = document.getElementById('itemTemplate').content.cloneNode(true);
    var $tr  = $(tmpl.querySelector('tr'));
    $('#itemsBody').append($tr);
    var $row = $('#itemsBody tr:last');

    if (data) {
        $row.find('.item-type-sel').val(data.item_type || 'container');
        toggleItemTypeFields($row);
        $row.find('.container-size-sel').val(data.container_size || '');
        $row.find('.package-type-sel').val(data.package_type || '');
        $row.find('[name$="[hs_code]"]').val(data.hs_code || '');
        $row.find('[name$="[commodity]"]').val(data.commodity || '');
        $row.find('[name$="[quantity]"]').val(data.quantity || 1);
        $row.find('[name$="[gross_weight]"]').val(data.gross_weight || '');
        $row.find('[name$="[weight_unit]"]').val(data.weight_unit || 'KG');
        $row.find('[name$="[volume_cbm]"]').val(data.volume_cbm || '');
        $row.find('[name$="[country_of_origin]"]').val(data.country_of_origin || '');
        $row.find('[name$="[is_dangerous_goods]"]').val(data.is_dangerous_goods == '1' ? '1' : '0');
        $row.find('[name$="[special_handling]"]').val(data.special_handling || '');
    }
    reindexItems();
}

function reindexItems() {
    $('#itemsBody tr').each(function (i) {
        var $tr = $(this);
        $tr.find('.sl-no').text(i + 1);
        $tr.find('[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/items\[\d+\]/, 'items[' + i + ']'));
        });
    });
}

function toggleItemTypeFields($row) {
    var type = $row.find('.item-type-sel').val();
    if (type === 'container') {
        $row.find('.container-size-sel').removeClass('d-none');
        $row.find('.package-type-sel').addClass('d-none');
    } else {
        $row.find('.container-size-sel').addClass('d-none');
        $row.find('.package-type-sel').removeClass('d-none');
    }
}
</script>
@endpush

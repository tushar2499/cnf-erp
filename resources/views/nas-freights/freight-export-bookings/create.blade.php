@extends('nas-freights.layouts.app')

@section('title', $exportBooking ? 'Edit Freight Export Booking' : 'New Freight Export Booking')

@push('styles')
<style>
.form-header  { background: linear-gradient(135deg,#0a4f3c,#14b8a6); color:#fff; padding:.5rem 1rem; border-radius:.35rem .35rem 0 0; font-weight:600; font-size:.78rem; }
.section-card { border:1px solid #dee2e6; border-radius:.35rem; margin-bottom:.75rem; }
.section-card .section-body { padding:.6rem .75rem; }
.fb-label  { font-size:.7rem; font-weight:600; color:#495057; margin-bottom:.1rem; }
.fb-input  { font-size:.75rem; height:28px; padding:.18rem .4rem; }
#itemsTable th { background:#f1f3f5; font-size:.68rem; font-weight:700; padding:.25rem .4rem; white-space:nowrap; }
#itemsTable td { padding:.2rem .3rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-2">
    <div></div>
    <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
        Freight Export Booking Entry
        @if($exportBooking)<span class="ms-2 badge bg-light text-dark border">{{ $exportBooking->export_booking_no }}</span>@endif
    </div>
    <div>
        <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2 mb-2">
    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST"
      action="{{ $exportBooking ? route('nas-freights.freight-export-bookings.update', $exportBooking->id) : route('nas-freights.freight-export-bookings.store') }}">
@csrf
@if($exportBooking) @method('PUT') @endif

{{-- ═══ BOOKING INFORMATION ═══ --}}
<div class="section-card">
    <div class="form-header"><i class="fa fa-ship me-1"></i> Booking Information</div>
    <div class="section-body">
        <div class="row g-2 mb-2">
            <div class="col-md-2">
                <div class="fb-label">Booking No</div>
                <input type="text" class="form-control fb-input bg-light" value="{{ $exportBooking?->export_booking_no ?? 'Auto Generated' }}" readonly>
            </div>
            <div class="col-md-2">
                <div class="fb-label">Booking Date <span class="text-danger">*</span></div>
                <input type="date" name="booking_date" class="form-control fb-input" value="{{ old('booking_date', $exportBooking?->booking_date?->format('Y-m-d') ?? $today) }}" required>
            </div>
            <div class="col-md-3">
                <div class="fb-label">Customer (Exporter)</div>
                <select name="customer_id" id="customerSelect" class="form-select fb-input" style="width:100%">
                    @if($exportBooking?->customer_id)
                    <option value="{{ $exportBooking->customer_id }}" selected>{{ $exportBooking->customer?->customer_id }} — {{ $exportBooking->customer?->name }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <div class="fb-label">Salesperson</div>
                <select name="salesperson_id" id="salespersonSelect" class="form-select fb-input" style="width:100%">
                    @if($exportBooking?->salesperson_id)
                    <option value="{{ $exportBooking->salesperson_id }}" selected>{{ $exportBooking->salesperson?->name }}</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <div class="fb-label">Overseas Agent</div>
                <select name="overseas_agent_id" id="overseasAgentSelect" class="form-select fb-input" style="width:100%">
                    @if($exportBooking?->overseas_agent_id)
                    <option value="{{ $exportBooking->overseas_agent_id }}" selected>
                        {{ $exportBooking->overseasAgent?->agent_code }} — {{ $exportBooking->overseasAgent?->name }} ({{ $exportBooking->overseasAgent?->country }})
                    </option>
                    @endif
                </select>
            </div>
            <div class="col-md-6">
                <div class="fb-label">Shipping Carrier</div>
                <select name="shipping_carrier_id" id="shippingCarrierSelect" class="form-select fb-input" style="width:100%">
                    @if($exportBooking?->shipping_carrier_id)
                    <option value="{{ $exportBooking->shipping_carrier_id }}" selected>
                        {{ $exportBooking->shippingCarrier?->carrier_code }} — {{ $exportBooking->shippingCarrier?->name }}
                    </option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="fb-label">Service Type <span class="text-danger">*</span></div>
                <select name="service_type" class="form-select fb-input" required>
                    @foreach($serviceTypes as $st)
                    <option value="{{ $st }}" {{ old('service_type', $exportBooking?->service_type ?? 'FCL') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="fb-label">Incoterms</div>
                <select name="incoterms" class="form-select fb-input">
                    <option value="">-- Select --</option>
                    @foreach($incoterms as $inc)
                    <option value="{{ $inc }}" {{ old('incoterms', $exportBooking?->incoterms) === $inc ? 'selected' : '' }}>{{ $inc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="fb-label">Currency</div>
                <select name="currency" class="form-select fb-input">
                    @foreach($currencies as $cur)
                    <option value="{{ $cur }}" {{ old('currency', $exportBooking?->currency ?? 'BDT') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="fb-label">Port of Loading (POL)</div>
                <input type="text" name="pol" class="form-control fb-input" value="{{ old('pol', $exportBooking?->pol) }}" placeholder="e.g. Chittagong">
            </div>
            <div class="col-md-3">
                <div class="fb-label">Port of Discharge (POD)</div>
                <input type="text" name="pod" class="form-control fb-input" value="{{ old('pod', $exportBooking?->pod) }}" placeholder="e.g. Singapore">
            </div>
            <div class="col-md-3">
                <div class="fb-label">Place of Receipt</div>
                <input type="text" name="place_of_receipt" class="form-control fb-input" value="{{ old('place_of_receipt', $exportBooking?->place_of_receipt) }}">
            </div>
            <div class="col-md-3">
                <div class="fb-label">Place of Delivery</div>
                <input type="text" name="place_of_delivery" class="form-control fb-input" value="{{ old('place_of_delivery', $exportBooking?->place_of_delivery) }}">
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="fb-label">Vessel Name</div>
                <input type="text" name="vessel_name" class="form-control fb-input" value="{{ old('vessel_name', $exportBooking?->vessel_name) }}" placeholder="e.g. MSC ANNA">
            </div>
            <div class="col-md-2">
                <div class="fb-label">Voyage No</div>
                <input type="text" name="voyage_no" class="form-control fb-input" value="{{ old('voyage_no', $exportBooking?->voyage_no) }}" placeholder="e.g. 024W">
            </div>
            <div class="col-md-4">
                <div class="fb-label">Export B/L No</div>
                <input type="text" name="export_bl_no" class="form-control fb-input" value="{{ old('export_bl_no', $exportBooking?->export_bl_no) }}" placeholder="e.g. EXP024W-0001">
            </div>
            <div class="col-md-3">
                <div class="fb-label">Booking Note No</div>
                <input type="text" name="booking_note_no" class="form-control fb-input" value="{{ old('booking_note_no', $exportBooking?->booking_note_no) }}" placeholder="e.g. MSL024W-8899">
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-2">
                <div class="fb-label">ETD</div>
                <input type="date" name="etd" class="form-control fb-input" value="{{ old('etd', $exportBooking?->etd?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <div class="fb-label">ETA</div>
                <input type="date" name="eta" class="form-control fb-input" value="{{ old('eta', $exportBooking?->eta?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <div class="fb-label">Commodity Description</div>
                <input type="text" name="commodity_description" class="form-control fb-input" value="{{ old('commodity_description', $exportBooking?->commodity_description) }}">
            </div>
            <div class="col-md-2">
                <div class="fb-label">Remarks</div>
                <input type="text" name="remarks" class="form-control fb-input" value="{{ old('remarks', $exportBooking?->remarks) }}">
            </div>
            <div class="col-md-2">
                <div class="fb-label">Status</div>
                <select name="status" class="form-select fb-input">
                    @foreach($statuses as $st)
                    <option value="{{ $st }}" {{ old('status', $exportBooking?->status ?? 'Draft') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
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
            <table class="table table-bordered mb-0" id="itemsTable" style="min-width:1450px;">
                <thead>
                    <tr>
                        <th style="width:35px" class="text-center">#</th>
                        <th style="width:35px" class="text-center"></th>
                        <th style="width:115px">Item Type</th>
                        <th style="width:75px">Qty</th>
                        <th style="width:160px">Container Size / Pkg</th>
                        <th style="width:145px">Container No</th>
                        <th style="width:130px">Seal No</th>
                        <th style="width:120px">HS Code</th>
                        <th style="width:160px">Commodity</th>
                        <th style="width:110px">Weight</th>
                        <th style="width:80px">Unit</th>
                        <th style="width:90px">CBM</th>
                        <th style="width:140px">Country of Origin</th>
                        <th style="width:90px">DG</th>
                        <th style="min-width:140px">Special Handling</th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3 mb-4">
    <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-times me-1"></i> Cancel
    </a>
    <button type="submit" class="btn btn-sm btn-success px-4">
        <i class="fa fa-save me-1"></i> {{ $exportBooking ? 'Update Export Booking' : 'Save Export Booking' }}
    </button>
</div>

</form>

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
        <select name="items[0][container_size]" class="form-select form-select-sm container-size-sel" style="font-size:.72rem;">
            <option value="">-- Size --</option>
            @foreach($containerSizes as $cs)
            <option value="{{ $cs }}">{{ $cs }}</option>
            @endforeach
        </select>
        <select name="items[0][package_type]" class="form-select form-select-sm package-type-sel d-none" style="font-size:.72rem;">
            <option value="">-- Type --</option>
            @foreach($packageTypes as $pt)
            <option value="{{ $pt }}">{{ $pt }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="text" name="items[0][container_no]" class="form-control form-control-sm container-no-input" style="font-size:.72rem;" placeholder="e.g. MSCU1234567">
    </td>
    <td>
        <input type="text" name="items[0][seal_no]" class="form-control form-control-sm seal-no-input" style="font-size:.72rem;" placeholder="e.g. SL1234567">
    </td>
    <td><input type="text" name="items[0][hs_code]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="HS Code"></td>
    <td><input type="text" name="items[0][commodity]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="Commodity"></td>
    <td><input type="number" name="items[0][gross_weight]" class="form-control form-control-sm text-end" style="font-size:.72rem;" step="0.01" placeholder="0.00"></td>
    <td>
        <select name="items[0][weight_unit]" class="form-select form-select-sm" style="font-size:.72rem;">
            @foreach($weightUnits as $wu)
            <option value="{{ $wu }}">{{ $wu }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" name="items[0][volume_cbm]" class="form-control form-control-sm text-end" style="font-size:.72rem;" step="0.001" placeholder="0.000"></td>
    <td><input type="text" name="items[0][country_of_origin]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="Country"></td>
    <td>
        <select name="items[0][is_dangerous_goods]" class="form-select form-select-sm" style="font-size:.72rem;">
            <option value="0">No DG</option>
            <option value="1">DG</option>
        </select>
    </td>
    <td><input type="text" name="items[0][special_handling]" class="form-control form-control-sm" style="font-size:.72rem;" placeholder="e.g. Fragile"></td>
</tr>
</template>
@endsection

@push('scripts')
<script>


var existingItems = @json($existingItems);

$(function () {
    $('#customerSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search customer...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-freights.freight-export-bookings.search-customers') }}', dataType: 'json', delay: 300, data: d => ({ q: d.term }), processResults: d => ({ results: d }) },
    });

    $('#salespersonSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search salesperson...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-freights.freight-export-bookings.search-employees') }}', dataType: 'json', delay: 300, data: d => ({ q: d.term }), processResults: d => ({ results: d }) },
    });

    $('#overseasAgentSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search overseas agent...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-freights.freight-export-bookings.search-overseas-agents') }}', dataType: 'json', delay: 300, data: d => ({ q: d.term }), processResults: d => ({ results: d }) },
    });

    $('#shippingCarrierSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search shipping carrier...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-freights.freight-export-bookings.search-shipping-carriers') }}', dataType: 'json', delay: 300, data: d => ({ q: d.term }), processResults: d => ({ results: d }) },
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
        $row.find('[name$="[container_no]"]').val(data.container_no || '');
        $row.find('[name$="[seal_no]"]').val(data.seal_no || '');
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
    var isContainer = type === 'container';
    $row.find('.container-size-sel').toggleClass('d-none', !isContainer);
    $row.find('.package-type-sel').toggleClass('d-none', isContainer);
    $row.find('.container-no-input').prop('disabled', !isContainer);
    $row.find('.seal-no-input').prop('disabled', !isContainer);
}
</script>
@endpush

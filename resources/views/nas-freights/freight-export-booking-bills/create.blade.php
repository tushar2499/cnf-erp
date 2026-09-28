@extends('nas-freights.layouts.app')

@section('title', isset($bill) ? 'Edit Bill — '.$bill->bill_no : 'New Export Booking Bill')

@push('styles')
<style>
/* ── Panels ── */
.panel { border: 1px solid #dee2e6; border-radius: .4rem; overflow: hidden; margin-bottom: 1rem; }
.panel-header {
    display: flex; align-items: center; gap: .45rem;
    padding: .45rem .85rem; font-size: .8rem; font-weight: 700;
    color: #fff; background: #1e293b;
}
.panel-body { padding: .75rem .85rem; }

/* ── Booking info ── */
.info-label { font-size: .67rem; font-weight: 700; color: #6b7a99; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .1rem; }
.info-value { font-size: .8rem; color: #1e293b; }
.booking-search-panel .panel-header { background: #0a4f3c; }
.booking-info-panel .panel-header  { background: #155e75; }

/* ── Bill form ── */
.bill-type-btn {
    flex: 1; padding: .55rem; border: 2px solid #dee2e6; border-radius: .4rem;
    background: #fff; cursor: pointer; transition: all .15s; font-size: .82rem; font-weight: 600; text-align: center;
}
.bill-type-btn:hover { border-color: #14b8a6; }
.bill-type-btn.active-customer { border-color: #1e40af; background: #dbeafe; color: #1e40af; }
.bill-type-btn.active-agent    { border-color: #854d0e; background: #fef9c3; color: #854d0e; }
.bill-type-btn.disabled-type   { opacity: .4; cursor: not-allowed; pointer-events: none; }

.form-label { font-size: .79rem; font-weight: 600; color: #374151; margin-bottom: .2rem; }
.req { color: #dc2626; }
.ro-field { background: #f1f5f9 !important; color: #6b7280; }

/* ── Items table ── */
#itemsTable th {
    background: #1e293b; color: #e2e8f0;
    font-size: .76rem; font-weight: 600;
    padding: .4rem .5rem; white-space: nowrap;
}
#itemsTable td { padding: .22rem .4rem; vertical-align: middle; }
#itemsTable tbody tr:nth-child(even) { background: #f8fafc; }
#itemsTable input { font-size: .78rem; padding: .2rem .35rem; height: auto; }

.total-row td { font-weight: 700; background: #f0fdf4 !important; border-top: 2px solid #14b8a6 !important; }
.total-label { font-size: .8rem; }

/* ── Currency badge ── */
.currency-badge {
    display: inline-block; min-width: 52px; text-align: center;
    padding: .18rem .55rem; border-radius: 2rem; font-size: .74rem; font-weight: 700;
    background: #e0f2fe; color: #0369a1;
}
.currency-badge.bdt { background: #dcfce7; color: #166534; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-2">
    <div></div>
    <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
        @if(isset($bill))
            Edit Bill &nbsp;<span class="badge bg-light text-dark border">{{ $bill->bill_no }}</span>
        @else
            New Export Booking Bill
        @endif
    </div>
    <div>
        <a href="{{ route('nas-freights.freight-export-booking-bills.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form id="billForm" method="POST"
      action="{{ isset($bill) ? route('nas-freights.freight-export-booking-bills.update', $bill->id) : route('nas-freights.freight-export-booking-bills.store') }}">
    @csrf
    @if(isset($bill)) @method('PUT') @endif
    <input type="hidden" name="export_booking_id" id="hidBookingId" value="{{ $booking?->id ?? '' }}">

    <div class="row g-3">

        @php $initCurrency = old('currency', isset($bill) ? $bill->currency : 'BDT'); @endphp
        {{-- ══════════════════════════ LEFT — Booking Info ══════════════════════════ --}}
        <div class="col-lg-5">

            {{-- Booking Search (create + edit) --}}
            <div class="panel booking-search-panel">
                <div class="panel-header"><i class="fa fa-search"></i> {{ isset($bill) ? 'Change Booking' : 'Select Booking' }}</div>
                <div class="panel-body">
                    <label class="form-label">Export Booking No / Customer <span class="req">*</span></label>
                    <select id="bookingSelect" class="form-select form-select-sm w-100">
                        @if($booking)
                            <option value="{{ $booking->id }}" selected>
                                {{ $booking->export_booking_no }}{{ $booking->customer ? ' — '.$booking->customer->name : '' }}
                            </option>
                        @else
                            <option value="">Search booking (min 2 chars)…</option>
                        @endif
                    </select>
                </div>
            </div>

            {{-- Booking Info Panel (shown after selection) --}}
            <div class="panel booking-info-panel" id="bookingInfoPanel" @if(!$booking) style="display:none" @endif>
                <div class="panel-header"><i class="fa fa-ship"></i> Booking Details</div>
                <div class="panel-body" id="bookingInfoBody">
                    @if($booking)
                        @include('nas-freights.freight-export-booking-bills._booking-info', ['booking' => $booking])
                    @endif
                </div>
            </div>

        </div>

        {{-- ══════════════════════════ RIGHT — Bill Form ══════════════════════════ --}}
        <div class="col-lg-7">

            <div class="panel">
                <div class="panel-header"><i class="fa fa-file-invoice-dollar"></i> Bill Details</div>
                <div class="panel-body">

                    {{-- Bill Type --}}
                    <div class="mb-3">
                        <label class="form-label d-block">Bill Type <span class="req">*</span></label>
                        <div class="d-flex gap-2">
                            <button type="button" class="bill-type-btn {{ isset($bill) && $bill->bill_type === 'Customer' ? 'active-customer' : '' }} {{ in_array('Customer', $existingBillTypes) ? 'disabled-type' : '' }}"
                                id="btnTypeCustomer" data-type="Customer">
                                <i class="fa fa-user me-1"></i> Customer
                                @if(in_array('Customer', $existingBillTypes))
                                    <span class="d-block small fw-normal text-muted">(already exists)</span>
                                @endif
                            </button>
                            <button type="button" class="bill-type-btn {{ isset($bill) && $bill->bill_type === 'Overseas Agent' ? 'active-agent' : '' }} {{ in_array('Overseas Agent', $existingBillTypes) ? 'disabled-type' : '' }}"
                                id="btnTypeAgent" data-type="Overseas Agent">
                                <i class="fa fa-globe me-1"></i> Overseas Agent
                                @if(in_array('Overseas Agent', $existingBillTypes))
                                    <span class="d-block small fw-normal text-muted">(already exists)</span>
                                @endif
                            </button>
                        </div>
                        <input type="hidden" name="bill_type" id="hidBillType" value="{{ old('bill_type', $bill->bill_type ?? '') }}" required>
                        <div id="billTypeError" class="text-danger small mt-1" style="display:none">Please select a bill type.</div>
                    </div>

                    {{-- Bill Date --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bill Date <span class="req">*</span></label>
                            <input type="date" name="bill_date" class="form-control form-control-sm @error('bill_date') is-invalid @enderror"
                                value="{{ old('bill_date', isset($bill) ? $bill->bill_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('bill_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Currency Toggle --}}
                        <div class="col-md-6">
                            <label class="form-label d-block">Currency <span class="req">*</span></label>
                            <input type="hidden" name="currency" id="currencyHidden" value="{{ $initCurrency }}">
                            <input type="hidden" name="exchange_rate" id="exchangeRateHidden" value="{{ old('exchange_rate', isset($bill) ? $bill->exchange_rate : 1) }}">
                            <div class="d-flex gap-2">
                                <button type="button" id="btnCurrencyBdt"
                                    class="bill-type-btn {{ $initCurrency === 'BDT' ? 'active-customer' : '' }}">
                                    <i class="fa fa-taka-sign me-1"></i> BDT
                                </button>
                                <button type="button" id="btnCurrencyForeign"
                                    class="bill-type-btn {{ $initCurrency !== 'BDT' ? 'active-agent' : '' }}">
                                    <i class="fa fa-dollar-sign me-1"></i> Foreign Currency
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Foreign currency fields (hidden when BDT) --}}
                    <div id="foreignCurrencyWrap" class="row g-2 mb-3" @if($initCurrency === 'BDT') style="display:none" @endif>
                        <div class="col-md-6">
                            <label class="form-label">Currency Code <span class="req">*</span></label>
                            <select id="currencySelect" class="form-select form-select-sm">
                                @foreach(['USD','EUR','GBP','JPY','CNY','SGD','AED','SAR','INR','THB','MYR','HKD'] as $cur)
                                    <option value="{{ $cur }}" {{ $initCurrency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exchange Rate (1 unit → BDT) <span class="req">*</span></label>
                            <input type="number" id="exchangeRateInput"
                                class="form-control form-control-sm @error('exchange_rate') is-invalid @enderror"
                                value="{{ old('exchange_rate', isset($bill) && $bill->currency !== 'BDT' ? $bill->exchange_rate : '') }}"
                                step="0.0001" min="0.0001" placeholder="e.g. 123.33">
                            @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2"
                            placeholder="Optional remarks">{{ old('remarks', isset($bill) ? $bill->remarks : '') }}</textarea>
                    </div>

                </div>
            </div>

            {{-- Items --}}
            <div class="panel">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-list-ul me-1"></i> Bill Items</span>
                    <button type="button" id="btnAddRow" class="btn btn-sm btn-outline-light py-0 px-2">
                        <i class="fa fa-plus me-1"></i> Add Row
                    </button>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Description / Name</th>
                                <th style="width:140px">Amount <span id="currencyLabel" class="currency-badge {{ $initCurrency === 'BDT' ? 'bdt' : '' }}">{{ $initCurrency }}</span></th>
                                <th style="width:140px">Amount (BDT)</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Rows populated by JS --}}
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="text-end total-label">Total</td>
                                <td class="text-end"><span id="totalAmount">0.00</span></td>
                                <td class="text-end"><span id="totalBdt">0.00</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('nas-freights.freight-export-booking-bills.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success px-4">
                    <i class="fa fa-save me-1"></i> {{ isset($bill) ? 'Update Bill' : 'Save Bill' }}
                </button>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Prefill items from server (edit mode) ──
    var existingItems = @json(isset($bill) ? $bill->items->map(fn($i) => ['name' => $i->name, 'amount' => $i->amount]) : []);
    if (existingItems.length) {
        existingItems.forEach(function (item) { addRow(item.name, item.amount); });
    } else {
        addRow('', ''); // one empty row on create
    }

    // ── Bill type toggle ──
    function selectBillType(type) {
        $('#hidBillType').val(type);
        $('#billTypeError').hide();
        $('#btnTypeCustomer, #btnTypeAgent').removeClass('active-customer active-agent');
        if (type === 'Customer')        $('#btnTypeCustomer').addClass('active-customer');
        if (type === 'Overseas Agent')  $('#btnTypeAgent').addClass('active-agent');
    }

    // init from hidden field (edit mode)
    var initType = $('#hidBillType').val();
    if (initType) selectBillType(initType);

    $('#btnTypeCustomer').on('click', function () { if (!$(this).hasClass('disabled-type')) selectBillType('Customer'); });
    $('#btnTypeAgent').on('click',    function () { if (!$(this).hasClass('disabled-type')) selectBillType('Overseas Agent'); });

    // ── Currency toggle (BDT vs Foreign) ──
    function isBdt() { return $('#currencyHidden').val() === 'BDT'; }

    function getExchangeRate() {
        return isBdt() ? 1 : (parseFloat($('#exchangeRateInput').val()) || 1);
    }

    function selectCurrencyMode(mode) {
        if (mode === 'BDT') {
            $('#currencyHidden').val('BDT');
            $('#exchangeRateHidden').val(1);
            $('#foreignCurrencyWrap').hide();
            $('#btnCurrencyBdt').addClass('active-customer');
            $('#btnCurrencyForeign').removeClass('active-agent');
            $('#currencyLabel').text('BDT').addClass('bdt');
        } else {
            var cur = $('#currencySelect').val() || 'USD';
            $('#currencyHidden').val(cur);
            $('#exchangeRateHidden').val($('#exchangeRateInput').val() || '');
            $('#foreignCurrencyWrap').show();
            $('#btnCurrencyBdt').removeClass('active-customer');
            $('#btnCurrencyForeign').addClass('active-agent');
            $('#currencyLabel').text(cur).removeClass('bdt');
        }
        recalc();
    }

    // sync hidden fields when foreign currency inputs change
    $('#currencySelect').on('change', function () {
        $('#currencyHidden').val($(this).val());
        $('#currencyLabel').text($(this).val());
        recalc();
    });
    $('#exchangeRateInput').on('input', function () {
        $('#exchangeRateHidden').val($(this).val());
        recalc();
    });

    $('#btnCurrencyBdt').on('click', function () { selectCurrencyMode('BDT'); });
    $('#btnCurrencyForeign').on('click', function () { selectCurrencyMode('foreign'); });

    // init on load
    selectCurrencyMode(isBdt() ? 'BDT' : 'foreign');

    // ── Add row ──
    $('#btnAddRow').on('click', function () { addRow('', ''); });

    function addRow(name, amount) {
        var idx = $('#itemsBody tr').length;
        var $tr = $('<tr>');

        $tr.append(
            $('<td class="text-center text-muted" style="font-size:.75rem">').text(idx + 1),
            $('<td>').append(
                $('<input type="text" class="form-control form-control-sm item-name" placeholder="e.g. Air Freight, C&F Charges…">')
                    .attr('name', 'items[' + idx + '][name]').val(name).prop('required', true)
            ),
            $('<td>').append(
                $('<input type="number" class="form-control form-control-sm item-amount text-end" placeholder="0.00" min="0" step="0.01">')
                    .attr('name', 'items[' + idx + '][amount]').val(amount)
            ),
            $('<td>').append(
                $('<input type="text" class="form-control form-control-sm ro-field text-end item-bdt" readonly>').val('0.00')
            ),
            $('<td class="text-center">').append(
                $('<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row py-0 px-1"><i class="fa fa-times"></i></button>')
            )
        );

        $('#itemsBody').append($tr);
        recalc();
        reindex();
    }

    // ── Remove row ──
    $(document).on('click', '.btn-remove-row', function () {
        if ($('#itemsBody tr').length <= 1) return; // keep at least one
        $(this).closest('tr').remove();
        reindex();
        recalc();
    });

    // ── Recalc on amount change ──
    $(document).on('input', '.item-amount', function () { recalc(); });

    function recalc() {
        var rate       = getExchangeRate();
        var totalAmt   = 0;
        var totalBdt   = 0;

        $('#itemsBody tr').each(function () {
            var amt    = parseFloat($(this).find('.item-amount').val()) || 0;
            var amtBdt = Math.round(amt * rate * 100) / 100;
            $(this).find('.item-bdt').val(amtBdt.toFixed(2));
            totalAmt += amt;
            totalBdt += amtBdt;
        });

        $('#totalAmount').text(totalAmt.toFixed(2));
        $('#totalBdt').text(totalBdt.toFixed(2));
    }

    function reindex() {
        $('#itemsBody tr').each(function (i) {
            $(this).find('td:first').text(i + 1);
            $(this).find('.item-name').attr('name',   'items[' + i + '][name]');
            $(this).find('.item-amount').attr('name', 'items[' + i + '][amount]');
        });
    }

    // ── Booking search (Select2) ──
    var currentBillId = '{{ isset($bill) ? $bill->id : '' }}';

    $('#bookingSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search booking…',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route('nas-freights.freight-export-booking-bills.search-bookings') }}',
            dataType: 'json',
            delay: 300,
            data: function (p) { return { q: p.term }; },
            processResults: function (data) { return { results: data }; }
        }
    }).on('select2:select', function (e) {
        var id = e.params.data.id;
        $('#hidBookingId').val(id);
        loadBookingInfo(id);
    });

    function loadBookingInfo(bookingId) {
        $.get('{{ route('nas-freights.freight-export-booking-bills.create') }}', { booking_id: bookingId }, function (html) {
            var $doc = $(html);
            var infoHtml = $doc.find('#bookingInfoBody').html();
            var existingTypes = [];

            $doc.find('.bill-type-btn.disabled-type').each(function () {
                existingTypes.push($(this).data('type'));
            });

            $('#bookingInfoBody').html(infoHtml);
            $('#bookingInfoPanel').show();

            // in edit mode exclude the current bill's own type from "already exists"
            $('#btnTypeCustomer, #btnTypeAgent').each(function () {
                var t = $(this).data('type');
                var alreadyExists = existingTypes.includes(t);
                if (alreadyExists) {
                    $(this).addClass('disabled-type');
                    $(this).find('span.small').remove();
                    $(this).append('<span class="d-block small fw-normal text-muted">(already exists)</span>');
                } else {
                    $(this).removeClass('disabled-type');
                    $(this).find('span.small').remove();
                }
            });
        }, 'html');
    }

    // ── Form submit validation ──
    $('#billForm').on('submit', function (e) {
        if (!$('#hidBillType').val()) {
            e.preventDefault();
            $('#billTypeError').show();
            $('html,body').animate({ scrollTop: $('#billTypeError').offset().top - 80 }, 300);
            return;
        }
        if (!$('#hidBookingId').val()) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Please select a booking first.' });
            return;
        }
    });
});
</script>
@endpush

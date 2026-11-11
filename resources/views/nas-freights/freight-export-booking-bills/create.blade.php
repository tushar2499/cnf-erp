@extends('nas-freights.layouts.app')

@section('title', isset($bill) ? 'Edit Bill — '.$bill->bill_no : 'New Export Booking/Job Bill')

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
.booking-expense-panel .panel-header { background: #047857; }

/* ── Job expense (read-only reference beside the bill form) ── */
.expense-toggle {
    display: inline-flex; align-items: center; gap: .45rem; min-height: 32px;
    padding: 0; border: 0; background: none; color: inherit; font: inherit; cursor: pointer;
}
.expense-toggle:focus-visible { outline: 2px solid #fff; outline-offset: 2px; border-radius: .2rem; }
.expense-toggle .expense-chevron { font-size: .65rem; transition: transform .15s; }
.expense-toggle.collapsed .expense-chevron { transform: rotate(-90deg); }
.expense-table { font-size: .76rem; }
.expense-table th { background: #ecfdf5; color: #14532d; font-size: .7rem; font-weight: 700; white-space: nowrap; }
.expense-table td { vertical-align: top; overflow-wrap: anywhere; }
.expense-table td.text-end { white-space: nowrap; font-variant-numeric: tabular-nums; }
.expense-meta { font-size: .68rem; color: #6b7280; }
.expense-total-row td { font-weight: 700; background: #f0fdf4; border-top: 2px solid #14532d; }
@media (prefers-reduced-motion: reduce) { .expense-toggle .expense-chevron { transition: none; } }

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
#itemsTable .select2-container { min-width: 220px; }
#itemsTable .select2-container .select2-selection { font-size: .78rem; min-height: calc(1.5em + .5rem + 2px); }
#itemsTable .select2-container .select2-selection__rendered { padding-top: .1rem; padding-bottom: .1rem; }
#itemsTable .desc-field { min-width: 0; }
#itemsTable .btn-desc-mode { flex: 0 0 auto; line-height: 1; }
.select2-results__option .add-new-desc { color: #15803d; font-weight: 600; }

/* ── Bill number prefix (typed by the user) ── */
.prefix-note { font-size: .76rem; color: #6b7280; }

/* Duplicate description: red border + inline message (icon + text, not colour alone) */
#itemsTable tr.desc-duplicate .form-control,
#itemsTable tr.desc-duplicate .select2-container .select2-selection { border-color: #dc2626 !important; box-shadow: 0 0 0 .15rem rgba(220, 38, 38, .15); }
#itemsTable tr.desc-duplicate { background: #fef2f2 !important; }
#itemsTable .desc-error { font-size: .72rem; font-weight: 600; color: #b91c1c; margin-top: .2rem; }

.subtotal-row td { font-weight: 600; background: #f0fdf4 !important; border-top: 2px solid #14b8a6 !important; }
.vat-row td { background: #fefce8 !important; border-top: 1px solid #e2e8f0 !important; }
.vat-row .vat-label { font-size: .76rem; font-weight: 700; color: #92400e; white-space: nowrap; }
.grand-total-row td { font-weight: 700; background: #dcfce7 !important; border-top: 2px solid #16a34a !important; font-size: .83rem; }
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
            New Export Booking/Job Bill
        @endif
    </div>
    <div class="d-flex flex-wrap justify-content-end gap-2">
        @if(isset($bill))
            @if(auth()->user()->hasPermission('freight.export-booking-bill.view'))
                <a href="{{ route('nas-freights.freight-export-booking-bills.show', $bill->id) }}" class="btn btn-sm btn-outline-info">
                    <i class="fa fa-eye me-1"></i> View
                </a>
            @endif
            @if(auth()->user()->hasPermission('freight.export-booking-bill.print'))
                <a href="{{ route('nas-freights.freight-export-booking-bills.print', $bill->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                    <i class="fa fa-print me-1"></i> Print
                </a>
            @endif
        @endif
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
                <div class="panel-header"><i class="fa fa-search"></i> {{ isset($bill) ? 'Change Booking/Job' : 'Select Booking/Job' }}</div>
                <div class="panel-body">
                    <label class="form-label">Export Booking/Job No / Customer <span class="req">*</span></label>
                    <select id="bookingSelect" class="form-select form-select-sm w-100">
                        @if($booking)
                            <option value="{{ $booking->id }}" selected>
                                {{ $booking->export_booking_no }}{{ $booking->customer ? ' — '.$booking->customer->name : '' }}
                            </option>
                        @else
                            <option value="">Search booking/job (min 2 chars)…</option>
                        @endif
                    </select>
                </div>
            </div>

            {{-- Booking Info Panel (shown after selection) --}}
            <div class="panel booking-info-panel" id="bookingInfoPanel" @if(!$booking) style="display:none" @endif>
                <div class="panel-header"><i class="fa fa-ship"></i> Booking/Job Details</div>
                <div class="panel-body" id="bookingInfoBody">
                    @if($booking)
                        @include('nas-freights.freight-export-booking-bills._booking-info', ['booking' => $booking])
                    @endif
                </div>
            </div>

            {{-- Job Expense (read-only reference; shown after selection, same permission as the booking/job expense) --}}
            <div id="bookingExpenseWrap">
                @if($booking)
                    @include('nas-freights.freight-export-booking-bills._booking-expense', ['booking' => $booking])
                @endif
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

                    {{-- Bill Ref / Job No: shown when the booking/job has more than one Customer bill. Stored separately, never part of the bill number. --}}
                    <div class="mb-3" id="prefixPanel" style="display:none">
                        <label class="form-label d-block">Bill Ref / Job No @unless(isset($bill))<span class="req">*</span>@endunless</label>
                        <div class="prefix-note mb-2" id="prefixNote"></div>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label class="form-label" for="prefixBillRef"><i class="fa fa-receipt me-1"></i> Bill Ref</label>
                                <input type="text" id="prefixBillRef" name="bill_prefix_ref" maxlength="100" autocomplete="off"
                                    class="form-control form-control-sm prefix-input @error('bill_prefix_ref') is-invalid @enderror"
                                    placeholder="Type Bill Ref" value="{{ old('bill_prefix_ref', $bill->bill_prefix_ref ?? '') }}">
                                @error('bill_prefix_ref')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="prefixJobNo"><i class="fa fa-hashtag me-1"></i> Job No</label>
                                <input type="text" id="prefixJobNo" name="bill_prefix_job" maxlength="100" autocomplete="off"
                                    class="form-control form-control-sm prefix-input @error('bill_prefix_job') is-invalid @enderror"
                                    placeholder="Type Job No" value="{{ old('bill_prefix_job', $bill->bill_prefix_job ?? '') }}">
                                @error('bill_prefix_job')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div id="prefixError" class="text-danger small mt-1" style="display:none">
                            <i class="fa fa-circle-exclamation me-1"></i> Enter a Bill Ref and/or Job No.
                        </div>
                        @error('bill_prefix')
                            <div class="text-danger small mt-1"><i class="fa fa-circle-exclamation me-1"></i> {{ $message }}</div>
                        @enderror
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

                    {{-- Attention / Designation (printed under the addressee) --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="attention"><i class="fa fa-user-tie me-1"></i> Attention</label>
                            <input type="text" id="attention" name="attention" maxlength="150" autocomplete="off"
                                class="form-control form-control-sm @error('attention') is-invalid @enderror"
                                placeholder="e.g. Mr. Rahman" value="{{ old('attention', $bill->attention ?? '') }}">
                            @error('attention')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="designation"><i class="fa fa-id-badge me-1"></i> Designation</label>
                            <input type="text" id="designation" name="designation" maxlength="150" autocomplete="off"
                                class="form-control form-control-sm @error('designation') is-invalid @enderror"
                                placeholder="e.g. Manager, Logistics" value="{{ old('designation', $bill->designation ?? '') }}">
                            @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

            {{-- Items + VAT + Grand Total --}}
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
                            {{-- Items subtotal --}}
                            <tr class="subtotal-row">
                                <td colspan="2" class="text-end total-label">Items Subtotal</td>
                                <td class="text-end"><span id="totalAmount">0.00</span></td>
                                <td class="text-end"><span id="totalBdt">0.00</span></td>
                                <td></td>
                            </tr>
                            {{-- VAT row --}}
                            <tr class="vat-row">
                                <td class="text-center vat-label">VAT</td>
                                <td>
                                    <input type="text" name="vat_title" id="vatTitle"
                                        class="form-control form-control-sm"
                                        placeholder="e.g. VAT 15%, AIT…"
                                        value="{{ old('vat_title', isset($bill) ? $bill->vat_title : '') }}">
                                </td>
                                <td>
                                    <input type="number" name="vat_amount" id="vatAmount"
                                        class="form-control form-control-sm text-end"
                                        placeholder="0.00" min="0" step="0.01"
                                        value="{{ old('vat_amount', isset($bill) && $bill->vat_amount > 0 ? $bill->vat_amount : '') }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm ro-field text-end" id="vatBdt" readonly value="0.00">
                                </td>
                                <td></td>
                            </tr>
                            {{-- Grand total --}}
                            <tr class="grand-total-row">
                                <td colspan="2" class="text-end total-label">Grand Total</td>
                                <td class="text-end"><span id="grandTotalAmt">0.00</span></td>
                                <td class="text-end"><span id="grandTotalBdt">0.00</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body d-flex justify-content-end gap-2 py-2">
                    <a href="{{ route('nas-freights.freight-export-booking-bills.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fa fa-save me-1"></i> {{ isset($bill) ? 'Update Bill' : 'Save Bill' }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {

    // Active descriptions from Settings. Only the text is submitted (no ids).
    var descriptionOptions  = @json($descriptions);
    var canAddDescription   = @json($canAddDescription);
    var descriptionStoreUrl = '{{ route('nas-freights.settings.bill-item-descriptions.store') }}';
    var ADD_NEW             = '__add_new__';
    var isEditMode          = @json(isset($bill));

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
        if (type === 'Customer') {
            $('#btnTypeCustomer').addClass('active-customer');
            // unlock BDT button
            $('#btnCurrencyBdt').removeClass('disabled-type');
        }
        if (type === 'Overseas Agent') {
            $('#btnTypeAgent').addClass('active-agent');
            // force foreign currency
            selectCurrencyMode('foreign');
            $('#btnCurrencyBdt').addClass('disabled-type');
        }
        updatePrefixPanel();
    }

    // ── Bill Ref / Job No (shown when the booking/job has more than one Customer bill) ──
    var originalBookingId  = @json($bill->export_booking_id ?? null);
    var hasSavedPrefix     = @json(isset($bill) && ($bill->bill_prefix_ref || $bill->bill_prefix_job));

    function updatePrefixPanel() {
        var customers = customerBillsAfterSave();
        var show      = $('#hidBillType').val() === 'Customer' && (customers > 1 || (isEditMode && hasSavedPrefix));

        $('#prefixPanel').toggle(show);
        // hidden inputs are never submitted, so edit keeps the saved values untouched
        $('.prefix-input').prop('disabled', !show);

        if (!show) {
            $('#prefixError').hide();
            $('.prefix-input').removeClass('is-invalid');
            return;
        }

        $('#prefixNote').text('This job has ' + customers + ' Customer bills. Type a Bill Ref and/or Job No for this bill.');
    }

    // Customer bills on the selected booking/job once this form is saved.
    function customerBillsAfterSave() {
        var count = parseInt($('#bookingPrefixData').attr('data-customer-bills'), 10) || 0;

        if (!isEditMode) return count + 1; // the bill being created
        // edit: the page already counts this bill, unless it is moved to another booking/job
        var moved = String($('#hidBookingId').val()) !== String(originalBookingId);
        return moved ? count + 1 : count;
    }

    // Typed Bill Ref and Job No, trimmed; '' when both are blank.
    function typedPrefix() {
        return $.grep([$.trim($('#prefixBillRef').val()), $.trim($('#prefixJobNo').val())], function (part) {
            return part !== '';
        }).join('');
    }

    $(document).on('input', '.prefix-input', function () {
        $('#prefixError').hide();
        $('.prefix-input').removeClass('is-invalid');
    });

    // init from hidden field (edit mode)
    var initType = $('#hidBillType').val();
    if (initType) selectBillType(initType);
    updatePrefixPanel();

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

    $('#btnCurrencyBdt').on('click', function () {
        if (!$(this).hasClass('disabled-type')) selectCurrencyMode('BDT');
    });
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
                $('<div class="desc-cell d-flex align-items-center gap-1">').append(
                    $('<div class="desc-field flex-grow-1">'),
                    $('<button type="button" class="btn btn-sm btn-outline-secondary btn-desc-mode px-2 py-1">')
                ),
                $('<div class="desc-error d-none" role="alert"></div>')
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
        // No saved descriptions yet → start as a plain text input so the user can type straight away.
        setDescriptionMode($tr, descriptionOptions.length ? 'select' : 'text', name);
        recalc();
    }

    // ── Description field: dropdown (Select2) <-> plain text input ──
    function destroyDescriptionSelect($el) {
        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    }

    function setDescriptionMode($row, mode, value) {
        var $field = $row.find('.desc-field');
        var $btn   = $row.find('.btn-desc-mode');
        value = value || '';

        destroyDescriptionSelect($field.find('.item-name'));
        $field.empty();

        if (mode === 'select') {
            var $sel = buildDescriptionSelect(value);
            $field.append($sel);
            initDescriptionSelect($sel, value);
            $btn.html('<i class="fa fa-keyboard"></i>')
                .attr({ title: 'Type description manually', 'aria-label': 'Type description manually' });
        } else {
            $field.append(
                $('<input type="text" class="form-control form-control-sm item-name" maxlength="255" required placeholder="Type description…">').val(value)
            );
            $btn.html('<i class="fa fa-list"></i>')
                .attr({ title: 'Choose from saved descriptions', 'aria-label': 'Choose from saved descriptions' });
        }

        $row.data('descMode', mode);
        reindex();
        validateDuplicateDescriptions();
    }

    // ── Duplicate description check (case-insensitive, trimmed — mirrors server `distinct:ignore_case`) ──
    // Marks every repeated row with an inline message and returns the first offending row (or null).
    function validateDuplicateDescriptions() {
        var seen = {};
        var firstDuplicate = null;

        $('#itemsBody tr').each(function (i) {
            var $row   = $(this);
            var $field = $row.find('.item-name');
            var raw    = $.trim($field.val() || '');
            var key    = raw.toLowerCase();
            var isDup  = false;

            if (raw !== '' && raw !== ADD_NEW) {
                if (Object.prototype.hasOwnProperty.call(seen, key)) {
                    isDup = true;
                    $row.find('.desc-error').empty()
                        .append('<i class="fa fa-circle-exclamation me-1"></i>')
                        .append(document.createTextNode('Duplicate — already used in row ' + (seen[key] + 1) + '. Each description can be used once.'));
                } else {
                    seen[key] = i;
                }
            }

            $row.toggleClass('desc-duplicate', isDup);
            $row.find('.desc-error').toggleClass('d-none', !isDup);
            $field.attr('aria-invalid', isDup ? 'true' : 'false');
            if (isDup && firstDuplicate === null) firstDuplicate = $row;
        });

        return firstDuplicate;
    }

    $(document).on('change input', '.item-name', validateDuplicateDescriptions);

    // Build the <select>; keep a saved value that is no longer in the list (edit mode).
    function buildDescriptionSelect(name) {
        var $sel = $('<select class="form-select form-select-sm item-name" required></select>');
        var found = !name;
        $sel.append('<option value=""></option>');
        if (canAddDescription) $sel.append($('<option>').val(ADD_NEW).text('Add new description…'));
        descriptionOptions.forEach(function (d) {
            $sel.append($('<option>').val(d).text(d));
            if (d === name) found = true;
        });
        if (!found) $sel.append($('<option>').val(name).text(name));
        return $sel;
    }

    function initDescriptionSelect($sel, name) {
        $sel.val(name || '').data('prev', name || '').select2({
            theme: 'bootstrap-5',
            width: '100%',
            tags: true,
            allowClear: true,
            placeholder: 'Select or type description…',
            language: {
                noResults: function () { return 'No saved descriptions — type your own text'; }
            },
            // "Add new" row shows only when nothing is typed, so Enter on a search term
            // picks the matching description (or "Use …"), never the Add-new row.
            matcher: function (params, data) {
                var term = $.trim(params.term || '');
                if (data.id === ADD_NEW) return term === '' ? data : null;
                if (term === '') return data;
                if (typeof data.text === 'undefined') return null;
                return data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1 ? data : null;
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                return term === '' ? null : { id: term, text: term, isNew: true };
            },
            templateResult: function (item) {
                if (item.loading) return item.text;
                if (item.id === ADD_NEW) {
                    return $('<span class="add-new-desc"><i class="fa fa-plus me-1"></i>Add new description…</span>');
                }
                return item.isNew ? 'Use "' + item.text + '"' : item.text;
            }
        });
    }

    // Select2 4.1 does not auto-focus the search box; do it for the description selects only.
    $(document).on('select2:open', '.item-name', function () {
        var field = document.querySelector('.select2-container--open .select2-search__field');
        if (field) {
            field.setAttribute('placeholder', 'Search, or type your own text…');
            field.focus();
        }
    });

    // Remember the last real value so "Add new" can restore it if the user cancels.
    $(document).on('select2:select', '.item-name', function (e) {
        var $sel = $(this);
        if (e.params.data.id === ADD_NEW) {
            $sel.val($sel.data('prev') || '').trigger('change');
            promptAddDescription($sel);
            return;
        }
        $sel.data('prev', $sel.val());
    });
    $(document).on('select2:clear', '.item-name', function () { $(this).data('prev', ''); });

    // Toggle dropdown <-> text input for one row.
    $(document).on('click', '.btn-desc-mode', function () {
        var $row  = $(this).closest('tr');
        var value = $.trim($row.find('.item-name').val() || '');
        var mode  = $row.data('descMode') === 'select' ? 'text' : 'select';
        setDescriptionMode($row, mode, value);
        if (mode === 'text') $row.find('.item-name').trigger('focus');
    });

    // Save a new description to Settings, add it to every dropdown and use it in this row.
    function promptAddDescription($sel) {
        Swal.fire({
            title: 'Add new description',
            input: 'text',
            inputPlaceholder: 'e.g. Air Freight Charges',
            inputAttributes: { maxlength: 255, 'aria-label': 'Description' },
            showCancelButton: true,
            confirmButtonText: 'Save & use',
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); },
            preConfirm: function (value) {
                value = $.trim(value);
                if (!value) {
                    Swal.showValidationMessage('Description is required.');
                    return false;
                }
                return $.ajax({
                    url: descriptionStoreUrl,
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content'), name: value, sort_order: 0, is_active: 1 }
                }).then(
                    function () { return value; },
                    function (xhr) {
                        var errors = xhr.responseJSON && xhr.responseJSON.errors;
                        Swal.showValidationMessage(
                            (errors && errors.name && errors.name[0]) ||
                            (xhr.responseJSON && xhr.responseJSON.message) ||
                            'Could not save the description.'
                        );
                        return false;
                    }
                );
            }
        }).then(function (result) {
            if (!result.isConfirmed || !result.value) return;
            registerDescription(result.value);
            $sel.val(result.value).data('prev', result.value).trigger('change');
        });
    }

    function registerDescription(value) {
        if (descriptionOptions.indexOf(value) === -1) descriptionOptions.push(value);
        $('select.item-name').each(function () {
            var present = $(this).find('option').filter(function () { return this.value === value; }).length > 0;
            if (!present) $(this).append($('<option>').val(value).text(value));
        });
    }

    // ── Remove row ──
    $(document).on('click', '.btn-remove-row', function () {
        if ($('#itemsBody tr').length <= 1) return; // keep at least one
        var $row = $(this).closest('tr');
        destroyDescriptionSelect($row.find('.item-name'));
        $row.remove();
        reindex();
        recalc();
        validateDuplicateDescriptions();
    });

    // ── Recalc on amount change ──
    $(document).on('input', '.item-amount', function () { recalc(); });
    $('#vatAmount').on('input', recalc);

    function recalc() {
        var rate       = getExchangeRate();
        var subtotalAmt = 0;
        var subtotalBdt = 0;

        $('#itemsBody tr').each(function () {
            var amt    = parseFloat($(this).find('.item-amount').val()) || 0;
            var amtBdt = Math.round(amt * rate * 100) / 100;
            $(this).find('.item-bdt').val(amtBdt.toFixed(2));
            subtotalAmt += amt;
            subtotalBdt += amtBdt;
        });

        $('#totalAmount').text(subtotalAmt.toFixed(2));
        $('#totalBdt').text(subtotalBdt.toFixed(2));

        // VAT
        var vatAmt = parseFloat($('#vatAmount').val()) || 0;
        var vatBdt = Math.round(vatAmt * rate * 100) / 100;
        $('#vatBdt').val(vatBdt.toFixed(2));

        // Grand total
        $('#grandTotalAmt').text((subtotalAmt + vatAmt).toFixed(2));
        $('#grandTotalBdt').text((subtotalBdt + vatBdt).toFixed(2));
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
        placeholder: 'Search booking/job…',
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
            $('#bookingExpenseWrap').html($doc.find('#bookingExpenseWrap').html());

            // a different booking/job has its own bills: start Bill Ref / Job No fresh
            $('.prefix-input').val('');
            updatePrefixPanel();

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

            // re-apply overseas currency lock if current type is still Overseas Agent
            var currentType = $('#hidBillType').val();
            if (currentType === 'Overseas Agent') {
                $('#btnCurrencyBdt').addClass('disabled-type');
            }
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
            Swal.fire({ icon: 'warning', title: 'Please select a booking/job first.' });
            return;
        }
        if (!isEditMode && $('#prefixPanel').is(':visible') && typedPrefix() === '') {
            e.preventDefault();
            $('#prefixError').show();
            $('.prefix-input').addClass('is-invalid');
            $('html,body').animate({ scrollTop: $('#prefixPanel').offset().top - 80 }, 300);
            $('#prefixBillRef').trigger('focus');
            return;
        }

        var $duplicateRow = validateDuplicateDescriptions();
        if ($duplicateRow) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate bill item description',
                text: 'Each description can be used only once in a bill. Please fix the highlighted row(s).'
            }).then(function () {
                $duplicateRow.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
                $duplicateRow.find('.select2-selection, input.item-name').first().trigger('focus');
            });
        }
    });
});
</script>
@endpush

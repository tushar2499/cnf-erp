@extends('nas-freights.layouts.app')

@section('title', 'Edit Customer Bill — {{ $customerBill->bill_no }}')

@push('styles')
<style>
.bill-panel { background:#fff; border:1px solid #dee2e6; border-radius:.4rem; overflow:hidden; margin-bottom:1rem; }
.bill-panel-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.83rem; font-weight:600; }
.form-label { font-size:.81rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
#rowsTable th { background:#1a6b60; color:#fff; font-size:.75rem; padding:.35rem .4rem; white-space:nowrap; }
#rowsTable td { font-size:.75rem; padding:.25rem .3rem; vertical-align:middle; white-space:nowrap; }
#rowsTable input[type=number], #rowsTable input[type=text] { font-size:.75rem; padding:.2rem .3rem; height:auto; min-width:60px; }
.req { color:#dc3545; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-edit me-2 text-info"></i> Edit Customer Bill — <strong>{{ $customerBill->bill_no }}</strong></h4>
    <a href="{{ route('nas-freights.customer-bills.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="billForm">
@csrf
@method('PUT')

{{-- ── Filter / Load Section ── --}}
<div class="bill-panel">
    <div class="bill-panel-header"><i class="fa fa-filter me-2"></i> Filter</div>
    <div class="p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date <span class="req">*</span></label>
                <input type="date" id="fldFromDate" class="form-control form-control-sm" value="{{ $customerBill->from_date?->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date <span class="req">*</span></label>
                <input type="date" id="fldToDate" class="form-control form-control-sm" value="{{ $customerBill->to_date?->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Customer</label>
                <select id="fldCustomer" class="form-select form-select-sm" style="width:100%"></select>
                <input type="hidden" id="fldCustomerId" name="customer_id" value="{{ $customerBill->customer_id }}">
                <input type="hidden" id="fldCustomerName" name="customer_name" value="{{ $customerBill->customer_name }}">
            </div>
            <div class="col-md-3">
                <button type="button" id="btnLoadData" class="btn btn-warning btn-sm w-100">
                    <i class="fa fa-sync me-1"></i> Reload Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Bill Header ── --}}
<div id="billHeaderSection">
    <div class="bill-panel">
        <div class="bill-panel-header"><i class="fa fa-file-alt me-2"></i> Bill Details</div>
        <div class="p-3">
            <div class="row g-2">
                {{-- Left: input fields --}}
                <div class="col-md-8">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Bill Date <span class="req">*</span></label>
                            <input type="date" id="fldBillDate" name="bill_date" class="form-control form-control-sm" value="{{ $customerBill->bill_date?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Delivery Type <span class="req">*</span></label>
                            <select id="fldDeliveryType" name="delivery_type" class="form-select form-select-sm" required>
                                <option value="">--Delivery Type--</option>
                                @foreach($deliveryTypes as $dt)
                                    <option value="{{ $dt }}" {{ $customerBill->delivery_type === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill No</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $customerBill->bill_no }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill Type <span class="req">*</span></label>
                            <select id="fldBillType" name="bill_type" class="form-select form-select-sm" required>
                                <option value="">--Transport Type--</option>
                                @foreach($billTypes as $bt)
                                    <option value="{{ $bt }}" {{ $customerBill->bill_type === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill By</label>
                            <select id="fldBillBy" class="form-select form-select-sm" style="width:100%"></select>
                            <input type="hidden" id="fldBillByName" name="bill_by" value="{{ $customerBill->bill_by }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bill Address</label>
                            <textarea id="fldBillAddress" name="customer_address" class="form-control form-control-sm" rows="2">{{ $customerBill->customer_address }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea id="fldNote" name="note" class="form-control form-control-sm" rows="2">{{ $customerBill->note }}</textarea>
                        </div>
                    </div>
                </div>
                {{-- Right: calculation table --}}
                <div class="col-md-4">
                    <table style="width:100%;border-collapse:collapse">
                        <tr>
                            <td class="text-end pe-2 py-1"><label class="form-label mb-0">TDS (%)</label></td>
                            <td class="py-1">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="fldTdsPct" name="tds_percent" class="form-control form-control-sm" value="{{ $customerBill->tds_percent }}" min="0" step="0.01" oninput="recalcBill()">
                                    <input type="text" id="fldTdsAmt" name="tds_amount" class="form-control form-control-sm bg-light fw-bold" readonly placeholder="Amt">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end pe-2 py-1"><label class="form-label mb-0">VAT (%)</label></td>
                            <td class="py-1">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="fldVatPct" name="vat_percent" class="form-control form-control-sm" value="{{ $customerBill->vat_percent }}" min="0" step="0.01" oninput="recalcBill()">
                                    <input type="text" id="fldVatAmt" name="vat_amount" class="form-control form-control-sm bg-light fw-bold" readonly placeholder="Amt">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end pe-2 py-1"><label class="form-label mb-0">Sub Total</label></td>
                            <td class="py-1"><input type="text" id="fldSubTotal" name="sub_total" class="form-control form-control-sm bg-light fw-bold text-end" readonly></td>
                        </tr>
                        <tr>
                            <td class="text-end pe-2 py-1"><label class="form-label mb-0 fw-bold" style="color:#dc3545">Total Amount</label></td>
                            <td class="py-1"><input type="text" id="fldTotalAmt" name="total_amount" class="form-control form-control-sm bg-light fw-bold text-end" style="color:#dc3545" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Table actions --}}
    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <span class="text-muted" style="font-size:.8rem">
            Total Items: <strong id="rowCount">0</strong>
        </span>
        <div class="d-flex gap-2">
            <button type="button" id="btnClearData" class="btn btn-sm btn-danger">
                <i class="fa fa-trash me-1"></i> Clear Data
            </button>
            <button type="button" id="btnAddRow" class="btn btn-sm btn-success">
                <i class="fa fa-plus me-1"></i> Add Row
            </button>
            <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
                <i class="fa fa-save me-1"></i> Update Bill
            </button>
        </div>
    </div>
</div>

{{-- ── Items Table ── --}}
<div class="bill-panel">
    <div style="overflow-x:auto">
        <table class="table table-bordered table-hover mb-0" id="rowsTable">
            <thead>
                <tr>
                    <th style="width:40px">SL</th>
                    <th style="width:30px"></th>
                    <th>Booking Date</th>
                    <th>Delivery Date</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Location</th>
                    <th>B.Qty</th>
                    <th>D.Qty</th>
                    <th>Due Qty</th>
                    <th>Price</th>
                    <th>Dem. Days</th>
                    <th>Dem. Amount</th>
                    <th>Disc%</th>
                    <th>Discount</th>
                    <th>AIT (%)</th>
                    <th>Line Amount</th>
                </tr>
            </thead>
            <tbody id="rowsBody"></tbody>
        </table>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';

/* ── Pre-initialize Customer Select2 ── */
@if($customerBill->customer_id)
$('#fldCustomer').append(new Option(@json($customerBill->customer_name), @json($customerBill->customer_id), true, true)).trigger('change');
@endif
$('#fldCustomer').select2({
    theme: 'bootstrap-5',
    placeholder: 'Enter (Code or Name) minimum 3 character',
    minimumInputLength: 3,
    allowClear: true,
    ajax: {
        url: '{{ route('nas-freights.customer-bills.search-customers') }}',
        dataType: 'json', delay: 250,
        data: d => ({ q: d.term }),
        processResults: d => ({ results: d }),
    },
}).on('select2:select', function (e) {
    var d = e.params.data;
    $('#fldCustomerId').val(d.id);
    $('#fldCustomerName').val(d.name);
    if (d.address) $('#fldBillAddress').val(d.address);
}).on('select2:clear', function () {
    $('#fldCustomerId').val('');
    $('#fldCustomerName').val('');
});

/* ── Pre-initialize Bill By Select2 ── */
@if($customerBill->bill_by)
$('#fldBillBy').append(new Option(@json($customerBill->bill_by), @json($customerBill->bill_by), true, true)).trigger('change');
@endif
$('#fldBillBy').select2({
    theme: 'bootstrap-5',
    placeholder: 'Enter Employee Name Or Code (Min 3 chars)',
    minimumInputLength: 3,
    allowClear: true,
    ajax: {
        url: '{{ route('nas-freights.bookings.search-employees') }}',
        dataType: 'json', delay: 250,
        data: d => ({ q: d.term }),
        processResults: d => ({ results: d }),
    },
}).on('select2:select', function (e) {
    $('#fldBillByName').val(e.params.data.name);
}).on('select2:clear', function () {
    $('#fldBillByName').val('');
});

/* ── Row template ── */
var rowIdx = 0;
function addRow(item) {
    rowIdx++;
    var i = rowIdx;
    var bQty   = parseFloat(item.b_qty  || 0);
    var dQty   = parseFloat(item.d_qty  || 0);
    var dueQty = parseFloat(item.due_qty != null ? item.due_qty : bQty - dQty);
    var price  = parseFloat(item.price  || 0);
    var demDay = parseFloat(item.demurrage_day || 0);
    var demAmt = parseFloat(item.demurrage_amount || 0);
    var discPct= parseFloat(item.disc_percent || 0);
    var disc   = parseFloat(item.discount || 0);
    var aitPct = parseFloat(item.ait_percent || 0);
    var lineAmt= parseFloat(item.line_amount || (dueQty * price));

    var tr = `<tr data-idx="${i}">
        <td class="text-center row-sl">${i}</td>
        <td class="text-center"><span class="text-danger fw-bold btn-del-row" style="cursor:pointer">×</span></td>
        <td><input type="text" class="form-control form-control-sm row-bdate" value="${item.booking_date||''}" style="width:100px"></td>
        <td><input type="text" class="form-control form-control-sm row-ddate" value="${item.delivery_date||''}" style="width:100px"></td>
        <td><input type="text" class="form-control form-control-sm row-icode" value="${item.item_code||''}" style="width:110px"></td>
        <td><input type="text" class="form-control form-control-sm row-iname" value="${item.item_name||''}" style="width:180px"></td>
        <td><input type="text" class="form-control form-control-sm row-loc"   value="${item.location||''}" style="width:200px"></td>
        <td><input type="number" class="form-control form-control-sm row-bqty text-end" value="${bQty}" min="0" step="0.01" style="width:70px" onchange="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-dqty text-end" value="${dQty}" min="0" step="0.01" style="width:70px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-dueqty text-end bg-light" value="${dueQty.toFixed(2)}" readonly style="width:70px"></td>
        <td><input type="number" class="form-control form-control-sm row-price text-end" value="${price}" min="0" step="0.01" style="width:85px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-demday text-end bg-light" value="${demDay.toFixed(2)}" readonly style="width:75px"></td>
        <td><input type="number" class="form-control form-control-sm row-demamt text-end" value="${demAmt.toFixed(2)}" min="0" step="0.01" style="width:85px" oninput="recalcBill()"></td>
        <td><input type="number" class="form-control form-control-sm row-discpct text-end" value="${discPct}" min="0" step="0.01" style="width:65px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-disc text-end bg-light" value="${disc.toFixed(2)}" readonly style="width:80px"></td>
        <td><input type="number" class="form-control form-control-sm row-aitpct text-end" value="${aitPct}" min="0" step="0.01" style="width:65px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-lineamt text-end bg-light fw-bold" value="${lineAmt.toFixed(2)}" readonly style="width:90px"></td>
        <td style="display:none">
            <input class="row-booking-id"      type="hidden" value="${item.booking_id||''}">
            <input class="row-booking-item-id" type="hidden" value="${item.booking_item_id||''}">
        </td>
    </tr>`;
    $('#rowsBody').append(tr);
    reindex();
    recalcBill();
}

function rowRecalc(el) {
    var $tr   = $(el).closest('tr');
    var dQty  = parseFloat($tr.find('.row-dqty').val()) || 0;
    var bQty  = parseFloat($tr.find('.row-bqty').val()) || 0;
    var dueQty = Math.max(0, bQty - dQty);
    $tr.find('.row-dueqty').val(dueQty.toFixed(2));
    var price   = parseFloat($tr.find('.row-price').val()) || 0;
    var discPct = parseFloat($tr.find('.row-discpct').val()) || 0;
    var aitPct  = parseFloat($tr.find('.row-aitpct').val()) || 0;
    var base    = dueQty * price;
    var disc    = base * discPct / 100;
    var ait     = (base - disc) * aitPct / 100;
    var lineAmt = base - disc + ait;
    $tr.find('.row-disc').val(disc.toFixed(2));
    $tr.find('.row-lineamt').val(lineAmt.toFixed(2));
    recalcBill();
}

function recalcBill() {
    var subTotal = 0, totalDem = 0;
    $('#rowsBody tr').each(function () {
        subTotal += parseFloat($(this).find('.row-lineamt').val()) || 0;
        totalDem += parseFloat($(this).find('.row-demamt').val()) || 0;
    });
    var tdsPct   = parseFloat($('#fldTdsPct').val()) || 0;
    var vatPct   = parseFloat($('#fldVatPct').val()) || 0;
    var tdsAmt   = subTotal * tdsPct / 100;
    var vatAmt   = subTotal * vatPct / 100;
    var totalAmt = subTotal + totalDem + tdsAmt + vatAmt;
    $('#fldSubTotal').val(subTotal.toFixed(2));
    $('#fldTdsAmt').val(tdsAmt.toFixed(2));
    $('#fldVatAmt').val(vatAmt.toFixed(2));
    $('#fldTotalAmt').val(totalAmt.toFixed(2));
    $('#rowCount').text($('#rowsBody tr').length);
}

function reindex() {
    $('#rowsBody tr').each(function (i) { $(this).find('.row-sl').text(i + 1); });
    $('#rowCount').text($('#rowsBody tr').length);
}

$(document).on('click', '.btn-del-row', function () {
    $(this).closest('tr').remove();
    reindex();
    recalcBill();
});

/* ── Load existing items ── */
@php
$existingItems = $customerBill->items->map(fn($i) => [
    'booking_id'       => $i->booking_id,
    'booking_item_id'  => $i->booking_item_id,
    'booking_date'     => $i->booking_date ? \Carbon\Carbon::parse($i->booking_date)->format('d-M-Y') : '',
    'delivery_date'    => $i->delivery_date ? \Carbon\Carbon::parse($i->delivery_date)->format('d-M-Y') : '',
    'item_code'        => $i->item_code,
    'item_name'        => $i->item_name,
    'location'         => $i->location,
    'b_qty'            => $i->b_qty,
    'd_qty'            => $i->d_qty,
    'due_qty'          => $i->due_qty,
    'price'            => $i->price,
    'demurrage_day'    => $i->demurrage_day,
    'demurrage_amount' => $i->demurrage_amount,
    'disc_percent'     => $i->disc_percent,
    'discount'         => $i->discount,
    'ait_percent'      => $i->ait_percent,
    'line_amount'      => $i->line_amount,
]);
@endphp
var existingItems = @json($existingItems);
existingItems.forEach(item => addRow(item));

/* ── Reload Data ── */
$('#btnLoadData').on('click', function () {
    var fromDate = $('#fldFromDate').val();
    var toDate   = $('#fldToDate').val();
    if (!fromDate || !toDate) { Swal.fire({ icon: 'warning', title: 'From Date and To Date are required.' }); return; }
    var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
    $.ajax({
        url: '{{ route('nas-freights.customer-bills.load-items') }}',
        method: 'POST',
        data: { _token: CSRF, from_date: fromDate, to_date: toDate, customer_id: $('#fldCustomerId').val() },
    })
    .done(function (r) {
        $('#rowsBody').empty(); rowIdx = 0;
        if (r.items && r.items.length) r.items.forEach(item => addRow(item));
        if (r.delivery_type) $('#fldDeliveryType').val(r.delivery_type);
        if (r.tds_percent !== undefined) $('#fldTdsPct').val(r.tds_percent);
        if (r.vat_percent !== undefined) $('#fldVatPct').val(r.vat_percent);
        recalcBill();
        if (!r.items || !r.items.length) Swal.fire({ icon: 'info', title: 'No booking items found.' });
    })
    .fail(function (xhr) { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed to load.' }); })
    .always(function () { $btn.prop('disabled', false).html('<i class="fa fa-sync me-1"></i> Reload Data'); });
});

/* ── Clear Data ── */
$('#btnClearData').on('click', function () {
    Swal.fire({ title: 'Clear all rows?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, clear' })
        .then(res => { if (res.isConfirmed) { $('#rowsBody').empty(); rowIdx = 0; recalcBill(); } });
});

/* ── Add Empty Row ── */
$('#btnAddRow').on('click', function () {
    addRow({ booking_date:'', delivery_date:'', item_code:'', item_name:'', location:'', b_qty:1, d_qty:0, due_qty:1, price:0, demurrage_day:0, demurrage_amount:0, disc_percent:0, discount:0, ait_percent:0, line_amount:0 });
});

/* ── Submit ── */
$('#billForm').on('submit', function (e) {
    e.preventDefault();
    if (!$('#fldBillDate').val())       { Swal.fire({ icon: 'warning', title: 'Bill Date is required.' }); return; }
    if (!$('#fldDeliveryType').val())   { Swal.fire({ icon: 'warning', title: 'Delivery Type is required.' }); return; }
    if (!$('#fldBillType').val())       { Swal.fire({ icon: 'warning', title: 'Bill Type is required.' }); return; }
    if ($('#rowsBody tr').length === 0) { Swal.fire({ icon: 'warning', title: 'Add at least one item row.' }); return; }

    var items = [];
    $('#rowsBody tr').each(function () {
        var $tr = $(this);
        items.push({
            booking_id:      $tr.find('.row-booking-id').val(),
            booking_item_id: $tr.find('.row-booking-item-id').val(),
            booking_date:    $tr.find('.row-bdate').val(),
            delivery_date:   $tr.find('.row-ddate').val(),
            item_code:       $tr.find('.row-icode').val(),
            item_name:       $tr.find('.row-iname').val(),
            location:        $tr.find('.row-loc').val(),
            b_qty:           $tr.find('.row-bqty').val(),
            d_qty:           $tr.find('.row-dqty').val(),
            due_qty:         $tr.find('.row-dueqty').val(),
            price:            $tr.find('.row-price').val(),
            demurrage_day:    $tr.find('.row-demday').val(),
            demurrage_amount: $tr.find('.row-demamt').val(),
            disc_percent:     $tr.find('.row-discpct').val(),
            discount:         $tr.find('.row-disc').val(),
            ait_percent:      $tr.find('.row-aitpct').val(),
            line_amount:      $tr.find('.row-lineamt').val(),
        });
    });

    var $btn = $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    $.ajax({
        url: '{{ route('nas-freights.customer-bills.update', $customerBill->id) }}',
        method: 'POST',
        data: {
            _token:           CSRF,
            _method:          'PUT',
            from_date:        $('#fldFromDate').val(),
            to_date:          $('#fldToDate').val(),
            customer_id:      $('#fldCustomerId').val(),
            customer_name:    $('#fldCustomerName').val(),
            customer_address: $('#fldBillAddress').val(),
            bill_date:        $('#fldBillDate').val(),
            delivery_type:    $('#fldDeliveryType').val(),
            tds_percent:      $('#fldTdsPct').val(),
            tds_amount:       $('#fldTdsAmt').val(),
            vat_percent:      $('#fldVatPct').val(),
            vat_amount:       $('#fldVatAmt').val(),
            total_amount:     $('#fldTotalAmt').val(),
            bill_type:        $('#fldBillType').val(),
            bill_by:          $('#fldBillByName').val(),
            note:             $('#fldNote').val(),
            sub_total:        $('#fldSubTotal').val(),
            items:            items,
        },
    })
    .done(function (r) {
        Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false });
        setTimeout(() => window.location.href = r.redirect, 1800);
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Update failed.' });
        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Update Bill');
    });
});
</script>
@endpush

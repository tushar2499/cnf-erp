@extends('nas-freights.layouts.app')

@section('title', 'New Supplier Payment Order')

@push('styles')
<style>
.bill-panel { background:#fff; border:1px solid #dee2e6; border-radius:.4rem; overflow:hidden; margin-bottom:1rem; }
.bill-panel-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.83rem; font-weight:600; }
.form-label { font-size:.81rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
#rowsTable th { background:#1a6b60; color:#fff; font-size:.75rem; padding:.35rem .4rem; white-space:nowrap; }
#rowsTable td { font-size:.75rem; padding:.25rem .3rem; vertical-align:middle; white-space:nowrap; }
#rowsTable input { font-size:.75rem; padding:.2rem .3rem; height:auto; }
.total-bar { background:#e8f4f1; border:1px solid #a8d5cc; border-radius:.3rem; padding:.5rem 1rem; }
.req { color:#dc3545; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice me-2 text-info"></i> New Supplier Payment Order</h4>
    <a href="{{ route('nas-freights.supplier-bills.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="billForm">
@csrf

{{-- ── Filter Section ── --}}
<div class="bill-panel">
    <div class="bill-panel-header"><i class="fa fa-filter me-2"></i> Filter</div>
    <div class="p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date <span class="req">*</span></label>
                <input type="date" id="fldFromDate" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date <span class="req">*</span></label>
                <input type="date" id="fldToDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Supplier <span class="text-muted fw-normal">(Optional)</span></label>
                <select id="fldSupplier" class="form-select form-select-sm" style="width:100%">
                    <option value="">minimum 4 characters (Optional)</option>
                </select>
                <input type="hidden" id="fldSupplierId"   name="supplier_id">
                <input type="hidden" id="fldSupplierName" name="supplier_name">
            </div>
            <div class="col-md-3">
                <div id="filterButtons" class="d-flex gap-2">
                    <button type="button" id="btnLoadData" class="btn btn-success btn-sm flex-grow-1">
                        <i class="fa fa-sync me-1"></i> Load Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Bill Header (shown after load) ── --}}
<div id="billHeaderSection" style="display:none">
    <div class="bill-panel">
        <div class="bill-panel-header"><i class="fa fa-file-alt me-2"></i> Payment Order Details</div>
        <div class="p-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Bill Date <span class="req">*</span></label>
                    <input type="date" id="fldBillDate" name="bill_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pay Order No</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="Auto Entry" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill By</label>
                    <select id="fldBillBy" class="form-select form-select-sm" style="width:100%">
                        <option value="">Enter Employee Name Or Code (Min 3 chars)</option>
                    </select>
                    <input type="hidden" id="fldBillByName" name="bill_by">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Note</label>
                    <textarea id="fldNote" name="note" class="form-control form-control-sm" rows="1"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Table actions + Total ── --}}
    <div class="d-flex justify-content-between align-items-center mb-2 px-1 flex-wrap gap-2">
        <div class="total-bar d-flex gap-4 align-items-center">
            <span style="font-size:.82rem">Total Items: <strong id="rowCount">0</strong></span>
            <span style="font-size:.85rem">Total Amount: <strong id="fldTotalDisplay" class="text-success">0.00</strong></span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnClearData" class="btn btn-sm btn-danger">
                <i class="fa fa-trash me-1"></i> Clear Data
            </button>
            <button type="button" id="btnAddRow" class="btn btn-sm btn-success">
                <i class="fa fa-plus me-1"></i> Add Row
            </button>
            <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
                <i class="fa fa-save me-1"></i> Save
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
                    <th>Date</th>
                    <th>Entry Date</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Location</th>
                    <th>B.Qty</th>
                    <th>D.Qty</th>
                    <th>Due Qty</th>
                    <th>Price</th>
                    <th>Demurrage Day</th>
                    <th>Demurrage Amount</th>
                    <th>Line Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody id="rowsBody"></tbody>
            <tfoot>
                <tr style="background:#f1f5f9; font-weight:600;">
                    <td colspan="13" class="text-end" style="font-size:.8rem; padding:.4rem .5rem;">Total Amount:</td>
                    <td class="text-end text-success" id="fldTotalAmt" style="font-size:.85rem; padding:.4rem .5rem;">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<input type="hidden" id="fldTotalAmountInput" name="total_amount">
</form>
@endsection

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';

/* ── Supplier Select2 ── */
$('#fldSupplier').select2({
    theme: 'bootstrap-5',
    placeholder: 'minimum 4 characters (Optional)',
    minimumInputLength: 4,
    allowClear: true,
    ajax: {
        url: '{{ route('nas-freights.supplier-bills.search-suppliers') }}',
        dataType: 'json', delay: 250,
        data: d => ({ q: d.term }),
        processResults: d => ({ results: d }),
    },
}).on('select2:select', function (e) {
    var d = e.params.data;
    $('#fldSupplierId').val(d.id);
    $('#fldSupplierName').val(d.name);
}).on('select2:clear', function () {
    $('#fldSupplierId').val('');
    $('#fldSupplierName').val('');
});

/* ── Employee Select2 (Bill By) ── */
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

/* ── Add Row ── */
var rowIdx = 0;
function addRow(item) {
    rowIdx++;
    var bQty   = parseFloat(item.b_qty   || 0);
    var dQty   = parseFloat(item.d_qty   || 0);
    var dueQty = parseFloat(item.due_qty != null ? item.due_qty : bQty - dQty);
    var price  = parseFloat(item.price   || 0);
    var demDay = parseFloat(item.demurrage_day    || 0);
    var demAmt = parseFloat(item.demurrage_amount || 0);
    var lineAmt= parseFloat(item.line_amount || (bQty * price + demAmt));

    var tr = `<tr>
        <td class="text-center row-sl">${rowIdx}</td>
        <td class="text-center"><span class="text-danger fw-bold btn-del-row" style="cursor:pointer">×</span></td>
        <td><input type="text" class="form-control form-control-sm row-bdate" value="${item.booking_date||''}" style="width:95px"></td>
        <td><input type="text" class="form-control form-control-sm row-edate" value="${item.entry_date||''}" style="width:95px"></td>
        <td><input type="text" class="form-control form-control-sm row-icode" value="${item.item_code||''}" style="width:110px"></td>
        <td><input type="text" class="form-control form-control-sm row-iname" value="${item.item_name||''}" style="width:190px"></td>
        <td><input type="text" class="form-control form-control-sm row-loc"   value="${item.location||''}" style="width:200px"></td>
        <td><input type="number" class="form-control form-control-sm row-bqty text-end" value="${bQty}" min="0" step="0.01" style="width:65px" onchange="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-dqty text-end" value="${dQty}" min="0" step="0.01" style="width:65px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-dueqty text-end bg-light" value="${dueQty.toFixed(2)}" readonly style="width:65px"></td>
        <td><input type="number" class="form-control form-control-sm row-price text-end" value="${price}" min="0" step="0.01" style="width:85px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-demday text-end" value="${demDay}" min="0" step="1" style="width:75px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-demamt text-end" value="${demAmt.toFixed(2)}" min="0" step="0.01" style="width:90px" oninput="rowRecalc(this)"></td>
        <td><input type="number" class="form-control form-control-sm row-lineamt text-end bg-light fw-bold" value="${lineAmt.toFixed(2)}" readonly style="width:90px"></td>
        <td><input type="text"   class="form-control form-control-sm row-notes" value="${item.notes||''}" style="width:120px"></td>
        <td style="display:none">
            <input class="row-booking-id"      type="hidden" value="${item.booking_id||''}">
            <input class="row-booking-item-id" type="hidden" value="${item.booking_item_id||''}">
        </td>
    </tr>`;
    $('#rowsBody').append(tr);
    reindex();
    recalcTotal();
}

function rowRecalc(el) {
    var $tr    = $(el).closest('tr');
    var bQty   = parseFloat($tr.find('.row-bqty').val()) || 0;
    var dQty   = parseFloat($tr.find('.row-dqty').val()) || 0;
    var dueQty = Math.max(0, bQty - dQty);
    $tr.find('.row-dueqty').val(dueQty.toFixed(2));
    var price  = parseFloat($tr.find('.row-price').val())  || 0;
    var demAmt = parseFloat($tr.find('.row-demamt').val()) || 0;
    var lineAmt = bQty * price + demAmt;
    $tr.find('.row-lineamt').val(lineAmt.toFixed(2));
    recalcTotal();
}

function recalcTotal() {
    var total = 0;
    $('#rowsBody tr').each(function () {
        total += parseFloat($(this).find('.row-lineamt').val()) || 0;
    });
    var fmt = total.toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    $('#fldTotalAmt').text(fmt);
    $('#fldTotalDisplay').text(fmt);
    $('#fldTotalAmountInput').val(total.toFixed(2));
    $('#rowCount').text($('#rowsBody tr').length);
}

function reindex() {
    $('#rowsBody tr').each(function (i) { $(this).find('.row-sl').text(i + 1); });
    $('#rowCount').text($('#rowsBody tr').length);
}

$(document).on('click', '.btn-del-row', function () {
    $(this).closest('tr').remove();
    reindex();
    recalcTotal();
});

/* ── Load Data ── */
$('#btnLoadData').on('click', function () {
    if (!$('#fldFromDate').val() || !$('#fldToDate').val()) {
        Swal.fire({ icon: 'warning', title: 'From Date and To Date are required.' });
        return;
    }
    var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');

    $.ajax({
        url: '{{ route('nas-freights.supplier-bills.load-items') }}',
        method: 'POST',
        data: {
            _token:      CSRF,
            from_date:   $('#fldFromDate').val(),
            to_date:     $('#fldToDate').val(),
            supplier_id: $('#fldSupplierId').val(),
        },
    })
    .done(function (r) {
        $('#rowsBody').empty(); rowIdx = 0;
        if (r.items && r.items.length) {
            r.items.forEach(item => addRow(item));
            $('#billHeaderSection').show();
        } else {
            Swal.fire({ icon: 'info', title: 'No booking items found.' });
            $('#billHeaderSection').show();
        }
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed to load data.' });
    })
    .always(function () {
        $btn.prop('disabled', false).html('<i class="fa fa-sync me-1"></i> Load Data');
    });
});

/* ── Clear Data ── */
$('#btnClearData').on('click', function () {
    Swal.fire({ title: 'Clear all rows?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, clear' })
        .then(res => { if (res.isConfirmed) { $('#rowsBody').empty(); rowIdx = 0; recalcTotal(); } });
});

/* ── Add Empty Row ── */
$('#btnAddRow').on('click', function () {
    addRow({ booking_date:'', entry_date:'', item_code:'', item_name:'', location:'', b_qty:1, d_qty:0, due_qty:1, price:0, demurrage_day:0, demurrage_amount:0, line_amount:0, notes:'' });
});

/* ── Submit ── */
$('#billForm').on('submit', function (e) {
    e.preventDefault();
    if (!$('#fldBillDate').val()) { Swal.fire({ icon: 'warning', title: 'Bill Date is required.' }); return; }
    if ($('#rowsBody tr').length === 0) { Swal.fire({ icon: 'warning', title: 'Add at least one item row.' }); return; }

    var items = [];
    $('#rowsBody tr').each(function () {
        var $tr = $(this);
        items.push({
            booking_id:       $tr.find('.row-booking-id').val(),
            booking_item_id:  $tr.find('.row-booking-item-id').val(),
            booking_date:     $tr.find('.row-bdate').val(),
            entry_date:       $tr.find('.row-edate').val(),
            item_code:        $tr.find('.row-icode').val(),
            item_name:        $tr.find('.row-iname').val(),
            location:         $tr.find('.row-loc').val(),
            b_qty:            $tr.find('.row-bqty').val(),
            d_qty:            $tr.find('.row-dqty').val(),
            due_qty:          $tr.find('.row-dueqty').val(),
            price:            $tr.find('.row-price').val(),
            demurrage_day:    $tr.find('.row-demday').val(),
            demurrage_amount: $tr.find('.row-demamt').val(),
            line_amount:      $tr.find('.row-lineamt').val(),
            notes:            $tr.find('.row-notes').val(),
        });
    });

    var $btn = $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    $.ajax({
        url: '{{ route('nas-freights.supplier-bills.store') }}',
        method: 'POST',
        data: {
            _token:        CSRF,
            from_date:     $('#fldFromDate').val(),
            to_date:       $('#fldToDate').val(),
            supplier_id:   $('#fldSupplierId').val(),
            supplier_name: $('#fldSupplierName').val(),
            bill_date:     $('#fldBillDate').val(),
            bill_by:       $('#fldBillByName').val(),
            note:          $('#fldNote').val(),
            total_amount:  $('#fldTotalAmountInput').val(),
            items:         items,
        },
    })
    .done(function (r) {
        Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false });
        setTimeout(() => window.location.href = r.redirect, 1800);
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Save failed.' });
        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
    });
});
</script>
@endpush

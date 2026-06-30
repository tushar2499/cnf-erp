@extends('nas-freights.layouts.app')

@section('title', 'New Booking')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.req { color:#dc3545; font-weight:700; }
.section-bar { background:#0c2340; color:#fff; padding:.4rem .85rem; font-size:.78rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; border-radius:.3rem .3rem 0 0; }
.booking-card { border:1px solid #dee2e6; border-radius:.4rem; overflow:hidden; margin-bottom:1rem; }
.booking-card .card-body { padding:.75rem; }

/* row table (cover van) */
#rowsTable { width:100%; border-collapse:collapse; font-size:.8rem; }
#rowsTable th { background:#1a6b60; color:#fff; padding:.4rem .5rem; white-space:nowrap; }
#rowsTable td { padding:.3rem .4rem; vertical-align:middle; border-bottom:1px solid #e9ecef; }
#rowsTable input[type=number], #rowsTable input[type=text], #rowsTable select { font-size:.78rem; padding:.2rem .4rem; }
.row-del-btn { background:none; border:none; color:#dc3545; font-size:.9rem; cursor:pointer; }
.total-bar { background:#f0f8ff; border-top:2px solid #0c2340; padding:.4rem .75rem; font-size:.82rem; font-weight:600; }

/* product table */
#productsTable { width:100%; border-collapse:collapse; font-size:.8rem; }
#productsTable th { background:#4a3060; color:#fff; padding:.4rem .5rem; white-space:nowrap; }
#productsTable td { padding:.3rem .4rem; vertical-align:middle; border-bottom:1px solid #e9ecef; }
#productsTable input[type=number], #productsTable input[type=text], #productsTable select { font-size:.78rem; padding:.2rem .4rem; }

.select2-container .select2-selection--single { height:31px; border:1px solid #ced4da; border-radius:.375rem; }
.select2-container .select2-selection--single .select2-selection__rendered { line-height:29px; font-size:.875rem; }
.select2-container .select2-selection--single .select2-selection__arrow { height:29px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-plus me-2 text-info"></i> New TMS Booking</h4>
    <a href="{{ route('nas-freights.bookings.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<form id="bookingForm" method="POST" action="{{ route('nas-freights.bookings.store') }}">
@csrf

{{-- ── Header Fields ── --}}
<div class="booking-card">
    <div class="section-bar"><i class="fa fa-info-circle me-2"></i>Booking Information</div>
    <div class="card-body">
        <div class="row g-2">

            {{-- Left column --}}
            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> Booking Prefix</label>
                        <select name="booking_prefix" id="fldPrefix" class="form-select form-select-sm">
                            <option value="">--Select Prefix--</option>
                            @foreach($bookingPrefixes as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> Sales Type</label>
                        <select name="sales_type" id="fldSalesType" class="form-select form-select-sm">
                            <option value="">--Sales Type--</option>
                            @foreach($salesTypes as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Sales Person</label>
                        <select id="fldSalesPerson" class="form-select form-select-sm" style="width:100%">
                            <option value="">Enter Employee Name Or Code (Minimum 3 characters)</option>
                        </select>
                        <input type="hidden" name="sales_person_id" id="fldSalesPersonId">
                        <input type="hidden" name="sales_person_name" id="fldSalesPersonName">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> Job Date</label>
                        <input type="date" name="job_date" id="fldJobDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> Customer</label>
                        <div class="input-group input-group-sm">
                            <select id="fldCustomer" class="form-select form-select-sm" style="width:calc(100% - 60px)">
                                <option value="">Search customer (min 3 chars)</option>
                            </select>
                            <button type="button" class="btn btn-success btn-sm px-2" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" title="Add Customer">Add</button>
                        </div>
                        <input type="hidden" name="customer_id" id="fldCustomerId">
                        <input type="hidden" name="customer_name" id="fldCustomerName">
                        <div class="invalid-feedback" id="customerErr">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="delivery_address" id="fldDeliveryAddress" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label">Job No</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="Auto Entry" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">LC No</label>
                        <input type="text" name="lc_no" id="fldLcNo" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Invoice No</label>
                        <input type="text" name="invoice_no" id="fldInvoiceNo" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> Delivery Date</label>
                        <input type="date" name="delivery_date" id="fldDeliveryDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">P/O Number</label>
                        <input type="text" name="po_number" id="fldPoNumber" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><span class="req">*</span> <span style="color:#1a6b60">Cover Van No:</span></label>
                        <select id="fldCoverVan" class="form-select form-select-sm" style="width:100%">
                            <option value="">Enter minimum 3 characters vehicle no / name</option>
                        </select>
                        <input type="hidden" name="cover_van_no" id="fldCoverVanNo">
                        <div class="invalid-feedback" id="vanErr">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Note</label>
                        <textarea name="note" id="fldNote" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Product Details ── --}}
<div class="booking-card">
    <div class="section-bar d-flex justify-content-between align-items-center" style="background:#4a3060">
        <span><i class="fa fa-boxes me-2"></i>Product Details</span>
        <button type="button" class="btn btn-sm btn-light py-0" id="btnAddProduct">
            <i class="fa fa-plus me-1"></i> Add Row
        </button>
    </div>
    <div style="overflow-x:auto">
        <table id="productsTable">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th style="width:30px">SL</th>
                    <th style="min-width:220px">Goods Name</th>
                    <th style="min-width:100px">Qty</th>
                    <th style="min-width:100px">Qty Unit</th>
                    <th style="min-width:110px">Net Weight</th>
                    <th style="min-width:100px">Weight Unit</th>
                </tr>
            </thead>
            <tbody id="productsBody"></tbody>
        </table>
        <div class="total-bar" style="border-top-color:#4a3060">Total Products: <span id="totalProducts">0</span></div>
    </div>
</div>

{{-- ── Cover Van Rows ── --}}
<div class="booking-card">
    <div class="section-bar d-flex justify-content-between align-items-center">
        <span><i class="fa fa-truck me-2"></i>Cover Van Details</span>
    </div>
    <div style="overflow-x:auto">
        <table id="rowsTable">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th style="width:30px">SL</th>
                    <th style="min-width:160px">Cover Van No</th>
                    <th style="min-width:90px">Capacity</th>
                    <th style="min-width:160px">Supplier</th>
                    <th style="min-width:80px">Qty</th>
                    <th style="min-width:100px">Supplier Rate</th>
                    <th style="min-width:100px">Customer Rate</th>
                    <th style="min-width:90px">Demrr. Days</th>
                    <th style="min-width:120px">Cus.Demurrage Charge</th>
                    <th style="min-width:120px">Sup.Demurrage Charge</th>
                    <th style="min-width:90px">Amount</th>
                    <th style="min-width:120px">Location From</th>
                    <th style="min-width:120px">Location To</th>
                </tr>
            </thead>
            <tbody id="rowsBody"></tbody>
        </table>
        <div class="total-bar">Total Items: <span id="totalItems">0</span></div>
    </div>
</div>

{{-- ── Financial Summary ── --}}
<div class="booking-card">
    <div class="section-bar"><i class="fa fa-calculator me-2"></i>Financial Summary</div>
    <div class="card-body">
        <div class="d-flex justify-content-end">
            <table style="width:520px;border-collapse:collapse">
                <tr>
                    <td class="text-end pe-2 py-1"><label class="form-label mb-0">TDS Section</label></td>
                    <td class="py-1"><input type="text" name="tds_section" id="fldTdsSection" class="form-control form-control-sm"></td>
                </tr>
                <tr>
                    <td class="text-end pe-2 py-1"><label class="form-label mb-0">TDS (%)</label></td>
                    <td class="py-1">
                        <div class="input-group input-group-sm">
                            <input type="number" name="tds_percent" id="fldTdsPct" class="form-control form-control-sm" value="0" min="0" step="0.01" oninput="recalc()" onchange="recalc()">
                            <input type="number" name="tds_amount" id="fldTdsAmt" class="form-control form-control-sm bg-light" readonly value="0" placeholder="TDS Amt">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-end pe-2 py-1"><label class="form-label mb-0">Vat (%)</label></td>
                    <td class="py-1">
                        <div class="input-group input-group-sm">
                            <input type="number" name="vat_percent" id="fldVatPct" class="form-control form-control-sm" value="0" min="0" step="0.01" oninput="recalc()" onchange="recalc()">
                            <input type="number" name="vat_amount" id="fldVatAmt" class="form-control form-control-sm bg-light" readonly value="0" placeholder="Vat Amt">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-end pe-2 py-1"><label class="form-label mb-0">AIT (%)</label></td>
                    <td class="py-1">
                        <div class="input-group input-group-sm">
                            <input type="number" name="ait_percent" id="fldAitPct" class="form-control form-control-sm" value="0" min="0" step="0.01" oninput="recalc()" onchange="recalc()">
                            <input type="number" name="ait_amount" id="fldAitAmt" class="form-control form-control-sm bg-light" readonly value="0" placeholder="AIT Amt">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-end pe-2 py-1"><label class="form-label mb-0 fw-bold" style="color:#dc3545">Total Amount</label></td>
                    <td class="py-1"><input type="number" name="total_amount" id="fldTotalAmt" class="form-control form-control-sm bg-light fw-bold" readonly value="0" style="color:#dc3545"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4 justify-content-end">
    <button type="submit" class="btn btn-success px-5" id="btnSave">
        <i class="fa fa-save me-1"></i> Save Booking
    </button>
    <a href="{{ route('nas-freights.bookings.index') }}" class="btn btn-outline-secondary px-4">
        <i class="fa fa-times me-1"></i> Cancel
    </a>
</div>

</form>

{{-- Quick Add Customer Modal --}}
<div class="modal fade" id="quickCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title"><i class="fa fa-user-plus me-2"></i>Quick Add Customer</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCusForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name <span class="req">*</span></label>
                        <input type="text" id="qcName" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mobile</label>
                        <input type="text" id="qcMobile" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" id="qcEmail" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address</label>
                        <textarea id="qcAddress" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btnQcSave"><i class="fa fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var suppliers    = @json($suppliers);
var qtyUnits     = @json($qtyUnits);
var weightUnits  = @json($weightUnits);
var rowCount     = 0;
var productCount = 0;

function recalc() {
    var rowTotal = 0;
    $('#rowsBody tr').each(function () {
        const qty  = parseFloat($(this).find('.row-qty').val()) || 0;
        const rate = parseFloat($(this).find('.row-cus-rate').val()) || 0;
        const amt  = qty * rate;
        $(this).find('.row-amount').val(amt.toFixed(2));
        rowTotal += amt;
    });
    const tds = (rowTotal * (parseFloat($('#fldTdsPct').val()) || 0)) / 100;
    const vat = (rowTotal * (parseFloat($('#fldVatPct').val()) || 0)) / 100;
    const ait = (rowTotal * (parseFloat($('#fldAitPct').val()) || 0)) / 100;
    $('#fldTdsAmt').val(tds.toFixed(2));
    $('#fldVatAmt').val(vat.toFixed(2));
    $('#fldAitAmt').val(ait.toFixed(2));
    $('#fldTotalAmt').val((rowTotal + tds + vat + ait).toFixed(2));
}

$(function () {

    // ── Select2: Sales Person ──
    $('#fldSalesPerson').select2({
        placeholder: 'Enter Employee Name Or Code (Minimum 3 characters)',
        minimumInputLength: 3, allowClear: true,
        ajax: { url: '{{ route('nas-freights.bookings.search-employees') }}', dataType: 'json', delay: 300,
                data: p => ({ q: p.term }), processResults: d => ({ results: d }) },
    }).on('select2:select', function (e) {
        $('#fldSalesPersonId').val(e.params.data.id);
        $('#fldSalesPersonName').val(e.params.data.name);
    }).on('select2:clear', function () {
        $('#fldSalesPersonId,#fldSalesPersonName').val('');
    });

    // ── Select2: Customer ──
    $('#fldCustomer').select2({
        placeholder: 'Search customer (min 3 chars)',
        minimumInputLength: 3, allowClear: true,
        ajax: { url: '{{ route('nas-freights.bookings.search-customers') }}', dataType: 'json', delay: 300,
                data: p => ({ q: p.term }), processResults: d => ({ results: d }) },
    }).on('select2:select', function (e) {
        $('#fldCustomerId').val(e.params.data.id);
        $('#fldCustomerName').val(e.params.data.name);
        $('#fldDeliveryAddress').val(e.params.data.address || '');
    }).on('select2:clear', function () {
        $('#fldCustomerId,#fldCustomerName').val('');
        $('#fldDeliveryAddress').val('');
    });

    // ── Select2: Cover Van (header) ──
    $('#fldCoverVan').select2({
        placeholder: 'Enter minimum 3 characters vehicle no / name',
        minimumInputLength: 3, allowClear: true,
        ajax: { url: '{{ route('nas-freights.bookings.search-vehicles') }}', dataType: 'json', delay: 300,
                data: p => ({ q: p.term }), processResults: d => ({ results: d }) },
    }).on('select2:select', function (e) {
        const d = e.params.data;
        addRow(d.id, d.vehicle_type || '');
        // Clear for next selection
        var $sel = $(this);
        setTimeout(function () { $sel.val(null).trigger('change'); }, 50);
    });

    // ── Product row management ──
    function addProductRow(item) {
        productCount++;
        const sl = $('#productsBody tr').length + 1;
        const p  = item || {};
        const qtyUnitOpts = '<option value="">--Unit--</option>' +
            qtyUnits.map(u => `<option value="${u}" ${u === (p.qty_unit||'') ? 'selected' : ''}>${u}</option>`).join('');
        const wtUnitOpts = '<option value="">--Unit--</option>' +
            weightUnits.map(u => `<option value="${u}" ${u === (p.weight_unit||'') ? 'selected' : ''}>${u}</option>`).join('');
        const row = `
        <tr data-prow="${productCount}">
            <td><button type="button" class="row-del-btn" onclick="delProductRow(this)"><i class="fa fa-times"></i></button></td>
            <td class="psl-no text-center fw-bold">${sl}</td>
            <td><input type="text" name="products[${productCount}][goods_name]" class="form-control form-control-sm prod-goods-name" value="${p.goods_name||''}" placeholder="Goods / Product name"></td>
            <td><input type="number" name="products[${productCount}][qty]" class="form-control form-control-sm" value="${p.qty||0}" min="0" step="0.01"></td>
            <td><select name="products[${productCount}][qty_unit]" class="form-select form-select-sm">${qtyUnitOpts}</select></td>
            <td><input type="number" name="products[${productCount}][net_weight]" class="form-control form-control-sm" value="${p.net_weight||0}" min="0" step="0.01"></td>
            <td><select name="products[${productCount}][weight_unit]" class="form-select form-select-sm">${wtUnitOpts}</select></td>
        </tr>`;
        $('#productsBody').append(row);
        reindexProductSL();
    }

    window.delProductRow = function (btn) {
        $(btn).closest('tr').remove();
        reindexProductSL();
    };

    function reindexProductSL() {
        $('#productsBody tr').each(function (i) { $(this).find('.psl-no').text(i + 1); });
        $('#totalProducts').text($('#productsBody tr').length);
    }

    $('#btnAddProduct').on('click', function () { addProductRow(); });

    // ── Row management (cover van) ──
    function addRow(vanNo, capacity) {
        rowCount++;
        const sl = $('#rowsBody tr').length + 1;
        const supplierOpts = '<option value="">--Select Supplier--</option>' +
            suppliers.map(s => `<option value="${s.id}" data-name="${s.company_name}">${s.code} — ${s.company_name}</option>`).join('');

        const row = `
        <tr data-row="${rowCount}">
            <td><button type="button" class="row-del-btn" onclick="delRow(this)"><i class="fa fa-times"></i></button></td>
            <td class="sl-no text-center fw-bold">${sl}</td>
            <td><input type="text" name="items[${rowCount}][cover_van_no]" class="form-control form-control-sm" value="${vanNo}"></td>
            <td><input type="text" name="items[${rowCount}][capacity]" class="form-control form-control-sm" value=""></td>
            <td>
                <select name="items[${rowCount}][supplier_id]" class="form-select form-select-sm row-supplier" data-row="${rowCount}">
                    ${supplierOpts}
                </select>
                <input type="hidden" name="items[${rowCount}][supplier_name]" class="row-supplier-name">
            </td>
            <td><input type="number" name="items[${rowCount}][qty]" class="form-control form-control-sm row-qty" value="1" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][supplier_rate]" class="form-control form-control-sm row-sup-rate" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][customer_rate]" class="form-control form-control-sm row-cus-rate" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][demurrage_days]" class="form-control form-control-sm" value="0" min="0"></td>
            <td><input type="number" name="items[${rowCount}][cus_demurrage_charge]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][sup_demurrage_charge]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][amount]" class="form-control form-control-sm row-amount bg-light" readonly value="0"></td>
            <td><input type="text" name="items[${rowCount}][location_from]" class="form-control form-control-sm"></td>
            <td><input type="text" name="items[${rowCount}][location_to]" class="form-control form-control-sm"></td>
        </tr>`;
        $('#rowsBody').append(row);
        reindexSL();
        recalc();
    }

    window.delRow = function (btn) {
        $(btn).closest('tr').remove();
        reindexSL();
        recalc();
    };

    function reindexSL() {
        $('#rowsBody tr').each(function (i) { $(this).find('.sl-no').text(i + 1); });
        $('#totalItems').text($('#rowsBody tr').length);
    }

    $(document).on('input change', '.row-qty, .row-cus-rate', recalc);

    $(document).on('change', '.row-supplier', function () {
        const name = $(this).find('option:selected').data('name') || '';
        $(this).closest('tr').find('.row-supplier-name').val(name);
    });

    // ── Quick Add Customer ──
    $('#quickCusForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#qcName').val().trim()) { $('#qcName').addClass('is-invalid'); return; }
        $('#btnQcSave').prop('disabled', true);
        $.post('{{ route('nas-freights.stakeholders.customers.store') }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id_prefix: 'CUS-', name: $('#qcName').val(),
            mobile: $('#qcMobile').val(), email: $('#qcEmail').val(), address: $('#qcAddress').val(),
            status: 'Active',
        }).done(function () {
            $('#quickCustomerModal').modal('hide');
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Customer added. Search to select.', showConfirmButton: false, timer: 2500 });
            $('#qcName,#qcMobile,#qcEmail,#qcAddress').val('');
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Failed to save customer.' });
        }).always(function () { $('#btnQcSave').prop('disabled', false); });
    });

    // ── Form submit ──
    $('#bookingForm').on('submit', function (e) {
        e.preventDefault();

        let valid = true;
        if (!$('#fldPrefix').val())        { $('#fldPrefix').addClass('is-invalid');       valid = false; }
        if (!$('#fldSalesType').val())     { $('#fldSalesType').addClass('is-invalid');     valid = false; }
        if (!$('#fldJobDate').val())       { $('#fldJobDate').addClass('is-invalid');       valid = false; }
        if (!$('#fldCustomerId').val())    { $('#fldCustomer').addClass('is-invalid'); $('#customerErr').show(); valid = false; }
        if (!$('#fldDeliveryDate').val())  { $('#fldDeliveryDate').addClass('is-invalid');  valid = false; }
        if ($('#rowsBody tr').length === 0)     { Swal.fire({ icon: 'warning', title: 'Add at least one Cover Van row.' }); valid = false; }
        if (!valid) return;

        // Store first row's van no in header field
        const firstVan = $('#rowsBody tr:first input[name*="[cover_van_no]"]').val() || '';
        $('#fldCoverVanNo').val(firstVan);

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: '{{ route('nas-freights.bookings.store') }}',
            method: 'POST',
            data: $(this).serializeArray(),
        })
        .done(function (r) {
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                .then(() => { window.location = r.redirect; });
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function (k, v) {
                    Swal.fire({ icon: 'error', title: v[0] });
                });
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Booking');
        });
    });

    // clear invalid on change
    $('select, input, textarea').on('change input', function () { $(this).removeClass('is-invalid'); });
});
</script>
@endpush

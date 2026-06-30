@extends('chevron.layouts.app')

@section('title', $bill ? 'Edit Bill' : 'New Bill')

@push('styles')
<style>
.form-header { background: linear-gradient(135deg,#0a4f3c,#14b8a6); color:#fff; padding:.5rem 1rem; border-radius:.35rem .35rem 0 0; font-weight:600; font-size:.78rem; }
.section-card { border:1px solid #dee2e6; border-radius:.35rem; margin-bottom:.75rem; }
.section-card .section-body { padding:.6rem .75rem; }
.bill-form-label { font-size:.7rem; font-weight:600; color:#495057; margin-bottom:.1rem; }
.bill-input { font-size:.75rem; height:28px; padding:.18rem .4rem; }
.bill-textarea { font-size:.75rem; padding:.18rem .4rem; resize:vertical; }
#rowsTable th { background:#f1f3f5; font-size:.68rem; font-weight:700; padding:.25rem .4rem; white-space:nowrap; }
#rowsTable td { padding:.2rem .3rem; vertical-align:middle; }
.totals-row { display:flex; justify-content:space-between; align-items:center; padding:.22rem .5rem; font-size:.75rem; border-bottom:1px solid #f0f0f0; }
.totals-row:last-child { border-bottom:none; }
.totals-label { font-weight:600; color:#374151; }
.totals-val { min-width:110px; }
.due-row { background:#fff5f5; }
.due-row .totals-label { color:#dc2626; font-weight:700; }
</style>
@endpush

@section('content')
{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
    <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
        Bill Entry @if($bill)<span class="ms-2 badge bg-light text-dark border">{{ $bill->bill_no }}</span>@endif
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
      action="{{ $bill ? route('chevron.cnf.bills.update', $bill->id) : route('chevron.cnf.bills.store') }}">
@csrf
@if($bill) @method('PUT') @endif

{{-- ═══ HEADER SECTION ═══ --}}
<div class="section-card">
    <div class="form-header"><i class="fa fa-file-invoice me-1"></i> Bill Information</div>
    <div class="section-body">
        {{-- Row 1 --}}
        <div class="row g-2 mb-2">
            <div class="col-md-3">
                <div class="bill-form-label">Bill Type</div>
                <select name="bill_type" class="form-select bill-input">
                    <option value="">-- Select --</option>
                    @foreach($billTypes as $t)
                    <option value="{{ $t }}" {{ old('bill_type', $bill?->bill_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="bill-form-label">Bill Date <span class="text-danger">*</span></div>
                <input type="date" name="bill_date" class="form-control bill-input" value="{{ old('bill_date', $bill?->bill_date?->format('Y-m-d') ?? $today) }}" required>
            </div>
            <div class="col-md-3">
                <div class="bill-form-label">Delivery Date</div>
                <input type="date" name="delivery_date" class="form-control bill-input" value="{{ old('delivery_date', $bill?->delivery_date?->format('Y-m-d')) }}">
            </div>
        </div>

        {{-- Row 2: Job No + Party --}}
        <div class="row g-2 mb-2">
            <div class="col-md-4">
                <div class="bill-form-label">Job No</div>
                <select name="job_id" id="jobSelect" class="form-select bill-input" style="width:100%">
                    @if($bill?->job_id)
                    <option value="{{ $bill->job_id }}" selected>{{ $bill->job_no }} — {{ $bill->party_name }}</option>
                    @endif
                </select>
                <input type="hidden" name="job_no" id="jobNo" value="{{ old('job_no', $bill?->job_no) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Party Name</div>
                <input type="text" name="party_name" id="partyName" class="form-control bill-input" value="{{ old('party_name', $bill?->party_name) }}" placeholder="Party name">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Address</div>
                <textarea name="party_address" id="partyAddress" class="form-control bill-textarea" rows="2">{{ old('party_address', $bill?->party_address) }}</textarea>
            </div>
        </div>

        {{-- Row 3 --}}
        <div class="row g-2 mb-2">
            <div class="col-md-4">
                <div class="bill-form-label">D. Goods (Description)</div>
                <input type="text" name="goods_description" id="goodsDesc" class="form-control bill-input" value="{{ old('goods_description', $bill?->goods_description) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Mate Code</div>
                <input type="text" name="mate_code" id="mateCode" class="form-control bill-input" value="{{ old('mate_code', $bill?->mate_code) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">P.O. No</div>
                <input type="text" name="po_no" id="poNo" class="form-control bill-input" value="{{ old('po_no', $bill?->po_no) }}">
            </div>
        </div>

        {{-- Row 4: Quantity + Gross Weight + LC No --}}
        <div class="row g-2 mb-2">
            <div class="col-md-4">
                <div class="bill-form-label">Quantity</div>
                <div class="input-group input-group-sm">
                    <input type="number" name="quantity" id="quantity" class="form-control bill-input" step="0.001" value="{{ old('quantity', $bill?->quantity) }}" placeholder="0">
                    <input type="text" name="quantity_unit" id="quantityUnit" class="form-control bill-input" style="max-width:55px;" value="{{ old('quantity_unit', $bill?->quantity_unit ?? 'KG') }}">
                    <input type="text" name="quantity_remark" class="form-control bill-input" value="{{ old('quantity_remark', $bill?->quantity_remark) }}" placeholder="Remark">
                </div>
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Gross Weight</div>
                <div class="input-group input-group-sm">
                    <input type="number" name="gross_weight" id="grossWeight" class="form-control bill-input" step="0.001" value="{{ old('gross_weight', $bill?->gross_weight) }}" placeholder="0">
                    <input type="text" name="gross_weight_unit" id="grossWeightUnit" class="form-control bill-input" style="max-width:55px;" value="{{ old('gross_weight_unit', $bill?->gross_weight_unit) }}" placeholder="Unit">
                </div>
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">L.C. No</div>
                <div class="input-group input-group-sm">
                    <input type="text" name="lc_no"  id="lcNo"  class="form-control bill-input" value="{{ old('lc_no',  $bill?->lc_no) }}" placeholder="LC No">
                    <input type="text" name="lc_ref" id="lcRef" class="form-control bill-input" value="{{ old('lc_ref', $bill?->lc_ref) }}" placeholder="LCA No / Ref">
                </div>
            </div>
        </div>

        {{-- Row 5: B/E + B/E Date + Assessable --}}
        <div class="row g-2 mb-2">
            <div class="col-md-4">
                <div class="bill-form-label">B/E No</div>
                <input type="text" name="be_no" id="beNo" class="form-control bill-input" value="{{ old('be_no', $bill?->be_no) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">B/E Date</div>
                <input type="date" name="be_date" id="beDate" class="form-control bill-input" value="{{ old('be_date', $bill?->be_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Assessable Value</div>
                <input type="number" name="assessable_value" id="assessableValue" class="form-control bill-input text-end" step="0.01" value="{{ old('assessable_value', $bill?->assessable_value) }}" placeholder="0.00">
            </div>
        </div>

        {{-- Row 6: Invoice + Invoice Date + Invoice BDT --}}
        <div class="row g-2 mb-2">
            <div class="col-md-4">
                <div class="bill-form-label">Invoice No</div>
                <div class="input-group input-group-sm">
                    <input type="text" name="invoice_no"  id="invoiceNo"  class="form-control bill-input" value="{{ old('invoice_no',  $bill?->invoice_no) }}" placeholder="Invoice No">
                    <input type="text" name="invoice_ref" id="invoiceRef" class="form-control bill-input" style="max-width:80px;" value="{{ old('invoice_ref', $bill?->invoice_ref) }}" placeholder="HBL/HAWB">
                </div>
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Invoice Date</div>
                <input type="date" name="invoice_date" id="invoiceDate" class="form-control bill-input" value="{{ old('invoice_date', $bill?->invoice_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <div class="bill-form-label">Invoice Value (BDT)</div>
                <input type="number" name="invoice_value_bdt" id="invoiceValueBdt" class="form-control bill-input text-end" step="0.01" value="{{ old('invoice_value_bdt', $bill?->invoice_value_bdt) }}" placeholder="0.00">
            </div>
        </div>

        {{-- Row 7: B/L + Remarks --}}
        <div class="row g-2">
            <div class="col-md-4">
                <div class="bill-form-label">B/L No</div>
                <div class="input-group input-group-sm">
                    <input type="text" name="bl_no"  id="blNo"  class="form-control bill-input" value="{{ old('bl_no',  $bill?->bl_no) }}" placeholder="B/L No">
                    <input type="text" name="bl_ref" id="blRef" class="form-control bill-input" style="max-width:80px;" value="{{ old('bl_ref', $bill?->bl_ref) }}" placeholder="MBL/MAWB">
                </div>
            </div>
            <div class="col-md-8">
                <div class="bill-form-label">Remarks</div>
                <input type="text" name="remarks" class="form-control bill-input" value="{{ old('remarks', $bill?->remarks) }}">
            </div>
        </div>
    </div>
</div>

{{-- ═══ EXPENSE ROWS + TOTALS ═══ --}}
<div class="row g-3">
    {{-- Expense Rows --}}
    <div class="col-lg-8">
        <div class="section-card">
            <div class="form-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-list me-1"></i> Expense Details</span>
                <button type="button" class="btn btn-sm btn-light py-0 px-2" id="addRow">
                    <i class="fa fa-plus me-1"></i> Add Row
                </button>
            </div>
            <div class="section-body p-0">
                <table class="table table-bordered mb-0" id="rowsTable">
                    <thead>
                        <tr>
                            <th style="width:35px">SL</th>
                            <th style="width:30px"></th>
                            <th>Expense Category</th>
                            <th>Particular Info (Head)</th>
                            <th style="width:110px">Amount</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Totals --}}
    <div class="col-lg-4">
        <div class="section-card">
            <div class="form-header"><i class="fa fa-calculator me-1"></i> Summary</div>
            <div class="section-body p-0">
                <div class="totals-row">
                    <span class="totals-label">SUB TOTAL</span>
                    <input type="number" name="sub_total" id="subTotal" class="form-control form-control-sm totals-val text-end" step="0.01" value="{{ old('sub_total', $bill?->sub_total ?? 0) }}" readonly>
                </div>
                <div class="totals-row">
                    <span class="totals-label">COMMISSION ON</span>
                    <div class="d-flex gap-1 totals-val">
                        <select name="commission_on" id="commissionOn" class="form-select form-select-sm" style="font-size:.7rem;">
                            @foreach($commissionOnOptions as $opt)
                            <option value="{{ $opt }}" {{ old('commission_on', $bill?->commission_on ?? 'ASSESSABLE') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="totals-row">
                    <span class="totals-label">COMM. RATE %</span>
                    <div class="d-flex gap-1 totals-val">
                        <input type="number" name="commission_rate" id="commissionRate" class="form-control form-control-sm text-end" step="0.01" value="{{ old('commission_rate', $bill?->commission_rate) }}" placeholder="0.00" style="max-width:60px;">
                        <input type="number" name="commission_amount" id="commissionAmount" class="form-control form-control-sm text-end" step="0.01" value="{{ old('commission_amount', $bill?->commission_amount ?? 0) }}" readonly>
                    </div>
                </div>
                <div class="totals-row">
                    <span class="totals-label">TOTAL PAYABLE</span>
                    <input type="number" name="total_payable" id="totalPayable" class="form-control form-control-sm totals-val text-end" step="0.01" value="{{ old('total_payable', $bill?->total_payable ?? 0) }}" readonly>
                </div>
                <div class="totals-row">
                    <span class="totals-label">LESS DUTY &amp; TAX</span>
                    <input type="number" name="less_customs_duty_tax" id="lessDutyTax" class="form-control form-control-sm totals-val text-end" step="0.01" value="{{ old('less_customs_duty_tax', $bill?->less_customs_duty_tax ?? 0) }}" placeholder="0.00">
                </div>
                <div class="totals-row">
                    <span class="totals-label">INCOME TAX C&amp;F</span>
                    <input type="number" name="income_tax_cnf_com" id="incomeTax" class="form-control form-control-sm totals-val text-end" step="0.01" value="{{ old('income_tax_cnf_com', $bill?->income_tax_cnf_com ?? 0) }}" placeholder="0.00">
                </div>
                <div class="totals-row">
                    <span class="totals-label">NET PAYABLE</span>
                    <input type="number" name="net_payable" id="netPayable" class="form-control form-control-sm totals-val text-end fw-bold" step="0.01" value="{{ old('net_payable', $bill?->net_payable ?? 0) }}" readonly>
                </div>
                <div class="totals-row">
                    <span class="totals-label">ADVANCE AMOUNT</span>
                    <input type="number" name="advance_amount" id="advanceAmount" class="form-control form-control-sm totals-val text-end" step="0.01" value="{{ old('advance_amount', $bill?->advance_amount ?? 0) }}" placeholder="0.00">
                </div>
                <div class="totals-row due-row">
                    <span class="totals-label">DUE AMOUNT</span>
                    <input type="number" name="due_amount" id="dueAmount" class="form-control form-control-sm totals-val text-end fw-bold text-danger" step="0.01" value="{{ old('due_amount', $bill?->due_amount ?? 0) }}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Submit --}}
<div class="d-flex justify-content-end gap-2 mt-3 mb-4">
    <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-times me-1"></i> Cancel
    </a>
    <button type="submit" class="btn btn-sm btn-success px-4">
        <i class="fa fa-save me-1"></i> {{ $bill ? 'Update Bill' : 'Save Bill' }}
    </button>
</div>

</form>

{{-- Row template --}}
<template id="rowTemplate">
<tr>
    <td class="sl-no text-center fw-bold" style="font-size:.72rem;"></td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-row"><i class="fa fa-times"></i></button>
    </td>
    <td>
        <select name="rows[0][expense_category_id]" class="form-select form-select-sm cat-select" style="font-size:.72rem;">
            <option value="">-- Category --</option>
            @foreach($expenseCategories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="rows[0][expense_head_id]" class="form-select form-select-sm head-select" style="font-size:.72rem;">
            <option value="">-- Head --</option>
            @foreach($expenseHeads as $h)
            <option value="{{ $h->id }}" data-cat="{{ $h->expense_category_id }}">{{ $h->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" name="rows[0][amount]" class="form-control form-control-sm row-amount text-end" step="0.01" value="0" style="font-size:.72rem;">
    </td>
    <td>
        <input type="text" name="rows[0][note]" class="form-control form-control-sm" style="font-size:.72rem;">
    </td>
</tr>
</template>
@endsection

@push('scripts')
<script>
var allHeads = @json($expenseHeadsJson);
var existingRows = @json($existingRows);

$(function () {
    // Job No Select2 AJAX
    $('#jobSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Enter min 3 characters...',
        minimumInputLength: 3,
        ajax: {
            url: '{{ route('chevron.cnf.bills.search-jobs') }}',
            dataType: 'json',
            delay: 300,
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d }),
        },
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#jobNo').val(d.job_no || '');
        $('#partyName').val(d.party_name || '');
        $('#partyAddress').val(d.party_address || '');
        $('#goodsDesc').val(d.goods_name || '');
        $('#mateCode').val(d.mate_code || '');
        $('#poNo').val(d.po_no || '');
        $('#quantity').val(d.quantity || '');
        $('#quantityUnit').val(d.quantity_unit || 'KG');
        $('#grossWeight').val(d.gross_weight || '');
        $('#grossWeightUnit').val(d.gross_weight_unit || '');
        $('#lcNo').val(d.lc_no || '');
        $('#lcRef').val(d.lc_ref || '');
        $('#beNo').val(d.be_no || '');
        $('#beDate').val(d.be_date || '');
        $('#assessableValue').val(d.assessable_value || '');
        $('#invoiceNo').val(d.invoice_no || '');
        $('#invoiceRef').val(d.invoice_ref || '');
        $('#invoiceDate').val(d.invoice_date || '');
        $('#invoiceValueBdt').val(d.invoice_value_bdt || '');
        $('#blNo').val(d.bl_no || '');
        $('#blRef').val(d.bl_ref || '');
        recalcAll();
    });

    // Load existing rows or start with 1 blank
    if (existingRows.length > 0) {
        existingRows.forEach(function(r) { addRow(r); });
    } else {
        addRow();
    }

    // Add row button
    $('#addRow').on('click', function () { addRow(); });

    // Remove row
    $(document).on('click', '.remove-row', function () {
        if ($('#rowsBody tr').length <= 1) return;
        $(this).closest('tr').remove();
        reindex();
        recalcAll();
    });

    // Amount change → recalc
    $(document).on('input', '.row-amount', recalcAll);

    // Commission rate / on change
    $('#commissionRate, #commissionOn, #assessableValue, #invoiceValueBdt').on('input change', recalcAll);

    // Less duty, income tax, advance change
    $('#lessDutyTax, #incomeTax, #advanceAmount').on('input', recalcAll);

    // Category filter heads
    $(document).on('change', '.cat-select', function () {
        filterHeads($(this).closest('tr'));
    });

    recalcAll();
});

function addRow(data) {
    var tmpl = document.getElementById('rowTemplate').content.cloneNode(true);
    var $tr = $(tmpl.querySelector('tr'));
    $('#rowsBody').append($tr);
    var $row = $('#rowsBody tr:last');

    if (data) {
        $row.find('.cat-select').val(data.expense_category_id);
        filterHeads($row);
        $row.find('.head-select').val(data.expense_head_id);
        $row.find('.row-amount').val(data.amount);
        $row.find('input[type=text]').last().val(data.note);
    }
    reindex();
}

function reindex() {
    $('#rowsBody tr').each(function (i) {
        var $tr = $(this);
        $tr.find('.sl-no').text(i + 1);
        $tr.find('[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/rows\[\d+\]/, 'rows[' + i + ']'));
        });
    });
}

function filterHeads($row) {
    var catId = parseInt($row.find('.cat-select').val());
    var $head = $row.find('.head-select');
    var curVal = $head.val();
    $head.empty().append('<option value="">-- Head --</option>');
    allHeads.forEach(function (h) {
        if (!catId || h.cat == catId) {
            $head.append('<option value="' + h.id + '">' + h.name + '</option>');
        }
    });
    $head.val(curVal);
}

function recalcAll() {
    // Sub total
    var subTotal = 0;
    $('.row-amount').each(function () { subTotal += parseFloat($(this).val()) || 0; });
    $('#subTotal').val(subTotal.toFixed(2));

    // Commission
    var commOn   = $('#commissionOn').val();
    var base     = commOn === 'INVOICE VALUE'
                   ? (parseFloat($('#invoiceValueBdt').val()) || 0)
                   : (parseFloat($('#assessableValue').val()) || 0);
    var rate     = parseFloat($('#commissionRate').val()) || 0;
    var commAmt  = base * rate / 100;
    $('#commissionAmount').val(commAmt.toFixed(2));

    // Total payable
    var totalPayable = subTotal + commAmt;
    $('#totalPayable').val(totalPayable.toFixed(2));

    // Net payable
    var lessDuty  = parseFloat($('#lessDutyTax').val()) || 0;
    var incomeTax = parseFloat($('#incomeTax').val()) || 0;
    var netPayable = totalPayable - lessDuty - incomeTax;
    $('#netPayable').val(netPayable.toFixed(2));

    // Due amount
    var advance   = parseFloat($('#advanceAmount').val()) || 0;
    var dueAmount = netPayable - advance;
    $('#dueAmount').val(dueAmount.toFixed(2));
}
</script>
@endpush

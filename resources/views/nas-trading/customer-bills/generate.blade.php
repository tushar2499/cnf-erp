@extends('nas-trading.layouts.app')
@section('title', 'Generate Bill — ' . $lc->lc_no_system)
@push('styles')
<style>
.bill-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.bill-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.bill-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.bill-table th { background:#e9ecef; font-size:.77rem; padding:.4rem .5rem; }
.bill-table td { padding:.3rem .5rem; vertical-align:middle; }
.bill-table .form-control-sm, .bill-table .form-select-sm { font-size:.78rem; }
.total-box { background:#f8f9fa; border:1px solid #dee2e6; border-radius:.4rem; padding:.75rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-file-invoice-dollar me-2 text-success"></i> Generate Customer Bill</h4>
    <a href="{{ route('nas-trading.lcs.show', $lc->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back to LC</a>
</div>

<form id="billForm">
    @csrf

    <input type="hidden" name="lc_id" value="{{ $lc->id }}">
    <input type="hidden" name="lc_no" value="{{ $lc->lc_no_system }}">
    <input type="hidden" name="pfi_no" value="{{ $lc->pfi_no }}">

    <div class="bill-card">
        <div class="bill-header"><i class="fa fa-id-card me-2"></i> Bill Header</div>
        <div class="bill-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Bill No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="[Auto-Generated]" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $lc->customer_name }}" readonly>
                    <input type="hidden" name="customer_id" value="{{ $lc->customer_id }}">
                    <input type="hidden" name="customer_name" value="{{ $lc->customer_name }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select form-select-sm">
                        @foreach(['BDT','USD','EUR','GBP'] as $c)
                        <option value="{{ $c }}" {{ $c === 'BDT' ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Exchange Rate</label>
                    <input type="number" name="exchange_rate" class="form-control form-control-sm" step="0.01" value="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer Address</label>
                    <input type="text" name="customer_address" class="form-control form-control-sm" value="{{ optional($lc->customer)->address }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    <div class="bill-card">
        <div class="bill-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-list-ul me-2"></i> Bill Line Items</span>
            <button type="button" class="btn btn-sm btn-light py-0 px-2" id="btnAddLine" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Line</button>
        </div>
        <div class="bill-body p-0">
            <div style="overflow-x:auto">
                <table class="table table-bordered bill-table mb-0 w-100" id="billTable">
                    <thead>
                        <tr>
                            <th style="width:35px">#</th>
                            <th style="min-width:200px">Description</th>
                            <th style="min-width:130px">Expense Head</th>
                            <th style="width:70px">Qty</th>
                            <th style="width:110px">Unit Price</th>
                            <th style="width:120px">Amount</th>
                            <th style="min-width:130px">Note</th>
                            <th style="width:35px"></th>
                        </tr>
                    </thead>
                    <tbody id="billBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Totals --}}
    <div class="row justify-content-end mb-4">
        <div class="col-md-4">
            <div class="total-box">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.85rem">Sub Total</span>
                    <strong id="dispSubTotal">0.00</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size:.85rem">VAT %</span>
                    <div class="d-flex align-items-center gap-1">
                        <input type="number" name="vat_pct" id="vatPct" class="form-control form-control-sm" style="width:70px" value="0" step="0.01" min="0">
                        <strong id="dispVat">0.00</strong>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total Amount</span>
                    <strong class="text-success" id="dispTotal" style="font-size:1rem">0.00</strong>
                </div>
                <input type="hidden" name="sub_total" id="subTotal">
                <input type="hidden" name="vat_amount" id="vatAmount">
                <input type="hidden" name="total_amount" id="totalAmount">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-file-invoice me-1"></i> Generate Bill</button>
        <a href="{{ route('nas-trading.lcs.show', $lc->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var lineIdx = 0;
var expenseHeads = @json($expenseHeads->pluck('name','id'));

function addLine(d) {
    d = d || {};
    var i = lineIdx++;
    var headOpts = '<option value="">-- None --</option>' + Object.entries(expenseHeads).map(([id, name]) =>
        `<option value="${id}" ${d.expense_head_id == id ? 'selected' : ''}>${name}</option>`
    ).join('');
    $('#billBody').append(`<tr>
        <td class="text-center row-num">${lineIdx}</td>
        <td><input type="text" name="items[${i}][description]" class="form-control form-control-sm" value="${d.description||''}" required></td>
        <td><select name="items[${i}][expense_head_id]" class="form-select form-select-sm">${headOpts}</select></td>
        <td><input type="number" name="items[${i}][qty]" class="form-control form-control-sm line-qty" data-row="${i}" value="${d.qty||1}" step="0.01" min="0"></td>
        <td><input type="number" name="items[${i}][unit_price]" class="form-control form-control-sm line-price" data-row="${i}" value="${d.unit_price||0}" step="0.01"></td>
        <td><input type="number" name="items[${i}][amount]" class="form-control form-control-sm line-amount" id="lineAmt_${i}" value="${d.amount||0}" step="0.01"></td>
        <td><input type="text" name="items[${i}][note]" class="form-control form-control-sm" value="${d.note||''}"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-line p-0" style="width:22px;height:22px"><i class="fa fa-times" style="font-size:.6rem"></i></button></td>
    </tr>`);
    recalc();
}

function recalc() {
    var sub = 0;
    $('.line-amount').each(function () { sub += parseFloat($(this).val()) || 0; });
    var vatPct = parseFloat($('#vatPct').val()) || 0;
    var vat = sub * vatPct / 100;
    var total = sub + vat;
    $('#dispSubTotal').text(sub.toFixed(2));
    $('#dispVat').text(vat.toFixed(2));
    $('#dispTotal').text(total.toFixed(2));
    $('#subTotal').val(sub.toFixed(2));
    $('#vatAmount').val(vat.toFixed(2));
    $('#totalAmount').val(total.toFixed(2));
}

$(function () {
    // Pre-populate from LC
    var lcLines = [
        @if($lc->pfi_value) { description: 'PFI Value ({{ $lc->currency }})', amount: {{ $lc->pfi_value }}, qty: 1, unit_price: {{ $lc->pfi_value }} },
        @endif
        @if($lc->lc_margin_amt) { description: 'LC Margin Amount', amount: {{ $lc->lc_margin_amt }}, qty: 1, unit_price: {{ $lc->lc_margin_amt }} },
        @endif
        @if($lc->lc_open_cost_bdt) { description: 'LC Opening Cost (BDT)', amount: {{ $lc->lc_open_cost_bdt }}, qty: 1, unit_price: {{ $lc->lc_open_cost_bdt }} },
        @endif
        @if($lc->freight_value) { description: 'Freight Value', amount: {{ $lc->freight_value }}, qty: 1, unit_price: {{ $lc->freight_value }} },
        @endif
        @if($lc->insurance_amt) { description: 'Insurance Amount', amount: {{ $lc->insurance_amt }}, qty: 1, unit_price: {{ $lc->insurance_amt }} },
        @endif
        @if($lc->customs_duty) { description: 'Customs Duty', amount: {{ $lc->customs_duty }}, qty: 1, unit_price: {{ $lc->customs_duty }} },
        @endif
        @if($lc->cnf_total_cost) { description: 'C&F Total Cost', amount: {{ $lc->cnf_total_cost }}, qty: 1, unit_price: {{ $lc->cnf_total_cost }} },
        @endif
        @if($lc->income_tax) { description: 'Income Tax', amount: {{ $lc->income_tax }}, qty: 1, unit_price: {{ $lc->income_tax }} },
        @endif
        @if($lc->lc_commission) { description: 'LC Commission', amount: {{ $lc->lc_commission }}, qty: 1, unit_price: {{ $lc->lc_commission }} },
        @endif
        @foreach($lc->expenses as $exp)
        { description: '{{ addslashes($exp->expense_head_name ?? $exp->expenseHead?->name ?? 'Expense') }} ({{ $exp->posting_type }})', amount: {{ $exp->amount }}, qty: 1, unit_price: {{ $exp->amount }}, expense_head_id: {{ $exp->expense_head_id ?? 'null' }} },
        @endforeach
    ];

    lcLines.forEach(l => { if (l.amount) addLine(l); });
    if (!lcLines.filter(l => l.amount).length) addLine();

    $(document).on('input', '.line-qty, .line-price', function () {
        var row = $(this).data('row');
        var qty = parseFloat($(`[name="items[${row}][qty]"]`).val()) || 0;
        var price = parseFloat($(`[name="items[${row}][unit_price]"]`).val()) || 0;
        $(`#lineAmt_${row}`).val((qty * price).toFixed(2));
        recalc();
    });
    $(document).on('input', '.line-amount', recalc);
    $('#vatPct').on('input', recalc);
    $('#btnAddLine').on('click', () => addLine());
    $(document).on('click', '.btn-remove-line', function () {
        $(this).closest('tr').remove();
        $('#billBody tr').each((i, tr) => $(tr).find('.row-num').text(i + 1));
        recalc();
    });

    $('#billForm').on('submit', function (e) {
        e.preventDefault();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Generating...');
        $.ajax({ url: '{{ route('nas-trading.customer-bills.store') }}', method: 'POST', data: $(this).serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-file-invoice me-1"></i> Generate Bill'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' }); });
    });
});
</script>
@endpush

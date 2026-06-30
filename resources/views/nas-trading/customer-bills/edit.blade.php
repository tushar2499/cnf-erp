@extends('nas-trading.layouts.app')
@section('title', 'Edit Bill — ' . $customerBill->bill_no)
@push('styles')
<style>
.bill-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.bill-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.bill-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.bill-table th { background:#e9ecef; font-size:.77rem; padding:.4rem .5rem; }
.bill-table td { padding:.3rem .5rem; vertical-align:middle; }
.total-box { background:#f8f9fa; border:1px solid #dee2e6; border-radius:.4rem; padding:.75rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-edit me-2 text-info"></i> Edit {{ $customerBill->bill_no }}</h4>
    <a href="{{ route('nas-trading.customer-bills.show', $customerBill->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<form id="billForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="lc_id" value="{{ $customerBill->lc_id }}">
    <input type="hidden" name="lc_no" value="{{ $customerBill->lc_no }}">
    <input type="hidden" name="pfi_no" value="{{ $customerBill->pfi_no }}">

    <div class="bill-card">
        <div class="bill-header"><i class="fa fa-id-card me-2"></i> Bill Header</div>
        <div class="bill-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Bill No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $customerBill->bill_no }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $customerBill->customer_name }}" readonly>
                    <input type="hidden" name="customer_id" value="{{ $customerBill->customer_id }}">
                    <input type="hidden" name="customer_name" value="{{ $customerBill->customer_name }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ $customerBill->bill_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select form-select-sm">
                        @foreach(['BDT','USD','EUR','GBP'] as $c)
                        <option value="{{ $c }}" {{ $customerBill->currency === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Exchange Rate</label>
                    <input type="number" name="exchange_rate" class="form-control form-control-sm" step="0.01" value="{{ $customerBill->exchange_rate }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer Address</label>
                    <input type="text" name="customer_address" class="form-control form-control-sm" value="{{ $customerBill->customer_address }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm" value="{{ $customerBill->note }}">
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
                <table class="table table-bordered bill-table mb-0 w-100">
                    <thead><tr>
                        <th style="width:35px">#</th>
                        <th style="min-width:200px">Description</th>
                        <th style="min-width:130px">Expense Head</th>
                        <th style="width:70px">Qty</th>
                        <th style="width:110px">Unit Price</th>
                        <th style="width:120px">Amount</th>
                        <th style="min-width:130px">Note</th>
                        <th style="width:35px"></th>
                    </tr></thead>
                    <tbody id="billBody"></tbody>
                </table>
            </div>
        </div>
    </div>

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
                        <input type="number" name="vat_pct" id="vatPct" class="form-control form-control-sm" style="width:70px" value="{{ $customerBill->vat_pct }}" step="0.01" min="0">
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
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Update Bill</button>
        <a href="{{ route('nas-trading.customer-bills.show', $customerBill->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var lineIdx = 0;
var expenseHeads = @json($expenseHeads->pluck('name','id'));
var existingItems = @json($customerBill->items);

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
    var vat = sub * (parseFloat($('#vatPct').val()) || 0) / 100;
    var total = sub + vat;
    $('#dispSubTotal').text(sub.toFixed(2)); $('#dispVat').text(vat.toFixed(2)); $('#dispTotal').text(total.toFixed(2));
    $('#subTotal').val(sub.toFixed(2)); $('#vatAmount').val(vat.toFixed(2)); $('#totalAmount').val(total.toFixed(2));
}

$(function () {
    existingItems.forEach(d => addLine(d));
    if (!existingItems.length) addLine();
    recalc();

    $(document).on('input', '.line-qty, .line-price', function () {
        var row = $(this).data('row');
        $(`#lineAmt_${row}`).val(((parseFloat($(`[name="items[${row}][qty]"]`).val())||0) * (parseFloat($(`[name="items[${row}][unit_price]"]`).val())||0)).toFixed(2));
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
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url: '{{ route('nas-trading.customer-bills.update', $customerBill->id) }}', method: 'POST', data: $(this).serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Update Bill'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' }); });
    });
});
</script>
@endpush

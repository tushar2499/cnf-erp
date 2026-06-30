@extends('chevron.layouts.app')

@section('title', isset($receipt) ? 'Edit Money Receipt' : 'New Money Receipt')

@section('content')
<div class="page-header">
    <h4>
        <i class="fa fa-money-bill-wave me-2 text-primary"></i>
        {{ isset($receipt) ? 'Edit Money Receipt — ' . $receipt->receipt_no : 'New Money Receipt' }}
    </h4>
    <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form method="POST"
      action="{{ isset($receipt) ? route('chevron.cnf.money-receipts.update', $receipt->id) : route('chevron.cnf.money-receipts.store') }}"
      id="mrForm">
    @csrf
    @if(isset($receipt)) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header fw-bold"><i class="fa fa-info-circle me-2"></i>Receipt Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Money Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receiptDate" class="form-control form-control-sm @error('receipt_date') is-invalid @enderror"
                           value="{{ old('receipt_date', isset($receipt) ? $receipt->receipt_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    @error('receipt_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Party Name <span class="text-danger">*</span></label>
                    <select id="partySelect" name="party_id" class="form-select form-select-sm @error('party_name') is-invalid @enderror" style="width:100%">
                        @if(isset($receipt) && $receipt->party_id)
                            <option value="{{ $receipt->party_id }}" selected>{{ $receipt->party_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="party_name" id="partyName" value="{{ old('party_name', $receipt->party_name ?? '') }}">
                    @error('party_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Pay Type <span class="text-danger">*</span></label>
                    <select name="pay_type" id="payType" class="form-select form-select-sm @error('pay_type') is-invalid @enderror" required>
                        <option value="">--Select Pay Type--</option>
                        @foreach($payTypes as $pt)
                            <option value="{{ $pt }}" {{ old('pay_type', $receipt->pay_type ?? '') == $pt ? 'selected' : '' }}>{{ $pt }}</option>
                        @endforeach
                    </select>
                    @error('pay_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Payable Amount</label>
                    <input type="text" id="payableAmountDisplay" class="form-control form-control-sm text-end bg-light" readonly value="{{ number_format($receipt->payable_amount ?? 0, 2) }}">
                    <input type="hidden" name="payable_amount" id="payableAmount" value="{{ old('payable_amount', $receipt->payable_amount ?? 0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Amount</label>
                    <input type="text" id="totalAmountDisplay" class="form-control form-control-sm text-end bg-light fw-bold" readonly value="{{ number_format($receipt->total_amount ?? 0, 2) }}">
                    <input type="hidden" name="total_amount" id="totalAmount" value="{{ old('total_amount', $receipt->total_amount ?? 0) }}">
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div id="amountMismatch" class="text-danger fw-semibold" style="display:none">
                        <i class="fa fa-exclamation-triangle me-1"></i>Selected Amount and Payable Amount is not matching
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Optional remarks...">{{ old('description', $receipt->description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Rows --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center fw-bold">
            <span><i class="fa fa-list me-2"></i>Payment Details</span>
            <button type="button" class="btn btn-sm btn-danger" id="btnAddRow">
                <i class="fa fa-plus me-1"></i> Add
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:160px">Type</th>
                            <th style="width:190px">Acc No</th>
                            <th>Cheque/Card Holder</th>
                            <th style="width:160px">Cheque/Card No</th>
                            <th style="width:130px">Amount</th>
                            <th style="width:150px">Cheque Date</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSave">
            <i class="fa fa-save me-1"></i> {{ isset($receipt) ? 'Update' : 'Save' }}
        </button>
        <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var accounts    = @json($accounts);
var rowPayTypes = @json($rowPayTypes);
var existing    = @json($existingItems);

function accountOptions(selected) {
    var html = '<option value="">--Select Acc No--</option>';
    accounts.forEach(function (a) {
        var sel = (selected && selected == a.id) ? 'selected' : '';
        html += '<option value="' + a.id + '" data-no="' + a.account_no + '" ' + sel + '>' + a.account_no + (a.account_name ? ' — ' + a.account_name : '') + '</option>';
    });
    return html;
}

function typeOptions(selected) {
    var html = '<option value="">--Select Payment--</option>';
    rowPayTypes.forEach(function (t) {
        html += '<option value="' + t + '"' + (t === selected ? ' selected' : '') + '>' + t + '</option>';
    });
    return html;
}

function addRow(data) {
    data = data || {};
    var idx = $('#itemsBody tr').length;
    var row = '<tr>' +
        '<td><select name="items[' + idx + '][payment_type]" class="form-select form-select-sm row-type" required>' + typeOptions(data.payment_type) + '</select></td>' +
        '<td>' +
            '<select name="items[' + idx + '][account_id]" class="form-select form-select-sm row-account">' + accountOptions(data.account_id) + '</select>' +
            '<input type="hidden" name="items[' + idx + '][account_no]" class="row-account-no" value="' + (data.account_no || '') + '">' +
        '</td>' +
        '<td><input type="text" name="items[' + idx + '][cheque_card_holder]" class="form-control form-control-sm" value="' + (data.cheque_card_holder || '') + '" placeholder="Holder name..."></td>' +
        '<td><input type="text" name="items[' + idx + '][cheque_card_no]" class="form-control form-control-sm" value="' + (data.cheque_card_no || '') + '" placeholder="No..."></td>' +
        '<td><input type="number" name="items[' + idx + '][amount]" class="form-control form-control-sm text-end row-amount" value="' + (data.amount || '') + '" min="0" step="0.01" required placeholder="0.00"></td>' +
        '<td><input type="date" name="items[' + idx + '][cheque_date]" class="form-control form-control-sm" value="' + (data.cheque_date || '') + '"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fa fa-times"></i></button></td>' +
        '</tr>';
    $('#itemsBody').append(row);
    reIndexRows();
}

function reIndexRows() {
    $('#itemsBody tr').each(function (i) {
        $(this).find('[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/items\[\d+\]/, 'items[' + i + ']'));
        });
    });
}

function recalcTotal() {
    var total = 0;
    $('.row-amount').each(function () {
        var v = parseFloat($(this).val()) || 0;
        total += v;
    });
    $('#totalAmount').val(total.toFixed(2));
    $('#totalAmountDisplay').val(number_fmt(total));
    checkMismatch();
}

function checkMismatch() {
    var payable = parseFloat($('#payableAmount').val()) || 0;
    var total   = parseFloat($('#totalAmount').val()) || 0;
    if (payable > 0 && Math.abs(payable - total) > 0.01) {
        $('#amountMismatch').show();
    } else {
        $('#amountMismatch').hide();
    }
}

function number_fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

$(function () {
    // Select2 for party
    $('#partySelect').select2({
        ajax: {
            url: '{{ route('chevron.cnf.money-receipts.search-parties') }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term }),
            processResults: d => ({ results: d.results }),
        },
        minimumInputLength: 2,
        placeholder: 'Enter minimum 2 characters',
        allowClear: true,
    });

    $('#partySelect').on('select2:select', function (e) {
        var name = e.params.data.text;
        $('#partyName').val(name);

        // Get payable amount from bills
        $.get('{{ route('chevron.cnf.money-receipts.party-payable') }}', { party_name: name }, function (r) {
            $('#payableAmount').val(r.payable_amount);
            $('#payableAmountDisplay').val(number_fmt(r.payable_amount));
            checkMismatch();
        });
    });

    $('#partySelect').on('select2:clear', function () {
        $('#partyName').val('');
        $('#payableAmount').val(0);
        $('#payableAmountDisplay').val('0.00');
        $('#amountMismatch').hide();
    });

    // Add row
    $('#btnAddRow').on('click', function () { addRow(); });

    // Remove row
    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('tr').remove();
        reIndexRows();
        recalcTotal();
    });

    // Recalc on amount change
    $(document).on('input', '.row-amount', recalcTotal);

    // Update hidden account_no when account changes
    $(document).on('change', '.row-account', function () {
        var no = $(this).find(':selected').data('no') || '';
        $(this).closest('td').find('.row-account-no').val(no);
    });

    // Load existing rows
    if (existing.length) {
        existing.forEach(function (item) { addRow(item); });
    } else {
        addRow(); // default one empty row
    }

    recalcTotal();

    // Form submit
    $('#mrForm').on('submit', function () {
        if ($('#itemsBody tr').length === 0) {
            Swal.fire({ icon: 'warning', title: 'Add at least one payment row.' });
            return false;
        }
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush

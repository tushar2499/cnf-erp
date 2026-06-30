@extends('nas-freights.layouts.app')

@section('title', 'New Supplier Payment')

@push('styles')
<style>
.form-label { font-size: .8rem; font-weight: 600; }
.info-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: .375rem; padding: .75rem 1rem; }
.info-box .label { font-size: .7rem; color: #6c757d; font-weight: 600; text-transform: uppercase; }
.info-box .value { font-size: .95rem; font-weight: 700; color: #0c2340; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-hand-holding-usd me-2 text-info"></i> New Supplier Payment</h4>
    <a href="{{ route('nas-freights.supplier-payments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="paymentForm">
@csrf
<div class="row g-3">
    {{-- Supplier & Bill selection --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2" style="background:#1a6b60;color:#fff;font-size:.82rem;font-weight:600">
                <i class="fa fa-search me-2"></i> Select Supplier & Payment Order
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Supplier</label>
                        <select id="fldSupplier" class="form-select form-select-sm" style="width:100%">
                            <option value="">-- Search supplier --</option>
                        </select>
                        <input type="hidden" id="fldSupplierId" name="supplier_id">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Select Payment Order <span class="text-danger">*</span></label>
                        <select id="fldBill" name="bill_id" class="form-select form-select-sm">
                            <option value="">-- Select confirmed payment order --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" id="btnLoadBill" class="btn btn-success btn-sm w-100">
                            <i class="fa fa-sync me-1"></i> Load Orders
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bill Info (auto-filled) --}}
    <div class="col-12" id="billInfoSection" style="display:none">
        <div class="card border-primary">
            <div class="card-header py-2" style="background:#0d6efd;color:#fff;font-size:.82rem;font-weight:600">
                <i class="fa fa-file-invoice me-2"></i> Payment Order Information
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="label">Pay Order No</div>
                            <div class="value" id="infoBillNo">—</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="label">Supplier</div>
                            <div class="value" id="infoSupName">—</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="label">Total Amount</div>
                            <div class="value text-danger" id="infoBillAmt">0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2" style="background:#0c2340;color:#fff;font-size:.82rem;font-weight:600">
                <i class="fa fa-money-check me-2"></i> Payment Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" id="fldAmountPaid" name="amount_paid" class="form-control form-control-sm" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select form-select-sm" required>
                            @foreach($paymentModes as $mode)
                            <option value="{{ $mode }}">{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference No (Cheque/Transaction)</label>
                        <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Note</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('nas-freights.supplier-payments.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
        <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSave">
            <i class="fa fa-save me-1"></i> Save Payment & Mark Order Paid
        </button>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
$('#fldSupplier').select2({
    theme: 'bootstrap-5', placeholder: 'Search supplier (min 3 chars)...', minimumInputLength: 3, allowClear: true,
    ajax: {
        url: '{{ route('nas-freights.supplier-payments.search-suppliers') }}',
        dataType: 'json', delay: 250,
        data: d => ({ q: d.term }),
        processResults: d => ({ results: d }),
    },
}).on('select2:select', e => $('#fldSupplierId').val(e.params.data.id))
  .on('select2:clear',  () => { $('#fldSupplierId').val(''); clearBillInfo(); });

$('#btnLoadBill').on('click', function () {
    $.getJSON('{{ route('nas-freights.supplier-payments.get-bills') }}', { supplier_id: $('#fldSupplierId').val() }, function (data) {
        var $s = $('#fldBill').empty().append('<option value="">-- Select confirmed payment order --</option>');
        data.forEach(b => $s.append('<option value="' + b.id + '" data-amt="' + b.total_amount + '" data-no="' + b.pay_order_no + '" data-sup="' + b.supplier_name + '">' + b.text + '</option>'));
        clearBillInfo();
    });
});

$('#fldBill').on('change', function () {
    const opt = $(this).find(':selected');
    if (!opt.val()) { clearBillInfo(); return; }
    const amt = parseFloat(opt.data('amt')) || 0;
    $('#infoBillNo').text(opt.data('no'));
    $('#infoSupName').text(opt.data('sup'));
    $('#infoBillAmt').text(amt.toLocaleString('en-BD', { minimumFractionDigits: 2 }));
    $('#fldAmountPaid').val(amt.toFixed(2));
    $('#billInfoSection').show();
});

function clearBillInfo() {
    $('#billInfoSection').hide();
    $('#fldAmountPaid').val('');
}

// Pre-select from URL param
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('bill_id')) {
    $.getJSON('{{ route('nas-freights.supplier-payments.get-bills') }}', {}, function (data) {
        var $s = $('#fldBill').empty().append('<option value="">-- Select confirmed payment order --</option>');
        data.forEach(b => $s.append('<option value="' + b.id + '" data-amt="' + b.total_amount + '" data-no="' + b.pay_order_no + '" data-sup="' + b.supplier_name + '">' + b.text + '</option>'));
        $('#fldBill').val(urlParams.get('bill_id')).trigger('change');
    });
}

$('#paymentForm').on('submit', function (e) {
    e.preventDefault();
    if (!$('[name="bill_id"]').val()) { Swal.fire({ icon: 'warning', title: 'Please select a payment order.' }); return; }

    const data = $(this).serialize();
    $('#btnSave').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

    $.ajax({
        url: '{{ route('nas-freights.supplier-payments.store') }}',
        method: 'POST', data,
        success: r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => window.location = r.redirect); },
        error: xhr => {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Payment & Mark Order Paid');
            const errs = xhr.responseJSON?.errors;
            if (errs) {
                Swal.fire({ icon: 'error', title: 'Validation Error', html: Object.values(errs).flat().join('<br>') });
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Save failed.' });
            }
        },
    });
});
</script>
@endpush

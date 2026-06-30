@extends('nas-trading.layouts.app')
@section('title', 'New Money Receipt')
@push('styles')
<style>
.mr-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.mr-header { background:#198754; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.mr-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.bill-info-box { background:#f0fff4; border:1px solid #c3e6cb; border-radius:.4rem; padding:.75rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-money-bill-wave me-2 text-success"></i> New Money Receipt</h4>
    <a href="{{ route('nas-trading.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <form id="mrForm">
            @csrf
            <div class="mr-card">
                <div class="mr-header"><i class="fa fa-search me-2"></i> Select Customer & Bill</div>
                <div class="mr-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select id="customerSelect" class="form-select form-select-sm"></select>
                            <input type="hidden" name="customer_id" id="customerId">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmed Bill <span class="text-danger">*</span></label>
                            <select id="billSelect" name="bill_id" class="form-select form-select-sm" required>
                                <option value="">— Select customer first —</option>
                            </select>
                            <input type="hidden" name="bill_amount" id="billAmount">
                        </div>
                    </div>

                    <div id="billInfoBox" class="bill-info-box mt-3" style="display:none">
                        <div class="row g-2">
                            <div class="col-4 text-center">
                                <div style="font-size:.72rem;color:#6c757d">Bill No</div>
                                <div class="fw-bold" id="infoBillNo">—</div>
                            </div>
                            <div class="col-4 text-center">
                                <div style="font-size:.72rem;color:#6c757d">Bill Amount</div>
                                <div class="fw-bold text-danger" id="infoBillAmt">—</div>
                            </div>
                            <div class="col-4 text-center">
                                <div style="font-size:.72rem;color:#6c757d">Status</div>
                                <div><span class="badge bg-success">Confirmed</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mr-card">
                <div class="mr-header"><i class="fa fa-receipt me-2"></i> Receipt Details</div>
                <div class="mr-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Receipt No</label>
                            <input type="text" class="form-control form-control-sm bg-light fw-bold" value="[Auto-Generated]" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount Received <span class="text-danger">*</span></label>
                            <input type="number" name="amount_received" id="amountReceived" class="form-control form-control-sm" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select form-select-sm">
                                @foreach(['Bank Transfer','Cash','Cheque','Mobile Banking'] as $m)
                                <option>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference / Cheque No</label>
                            <input type="text" name="reference_no" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Note</label>
                            <input type="text" name="note" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Save Receipt</button>
                <a href="{{ route('nas-trading.money-receipts.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // If bill_id in query string — pre-load
    var preloadBillId = new URLSearchParams(window.location.search).get('bill_id');

    $('#customerSelect').select2({
        width: '100%', placeholder: 'Search customer...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.money-receipts.search-customers') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#customerId').val(d.id);
        loadBills(d.id);
    }).on('select2:clear', function () {
        $('#customerId').val('');
        $('#billSelect').html('<option value="">— Select customer first —</option>');
        $('#billInfoBox').hide();
    });

    function loadBills(customerId) {
        $('#billSelect').html('<option value="">Loading...</option>');
        $.get('{{ route('nas-trading.money-receipts.get-bills') }}', { customer_id: customerId }, function (bills) {
            var opts = '<option value="">Select bill...</option>';
            bills.forEach(b => { opts += `<option value="${b.id}" data-amount="${b.total_amount}" data-text="${b.text}">${b.text}</option>`; });
            $('#billSelect').html(opts);
            if (preloadBillId) {
                $('#billSelect option[value="' + preloadBillId + '"]').prop('selected', true);
                $('#billSelect').trigger('change');
                preloadBillId = null;
            }
        });
    }

    $('#billSelect').on('change', function () {
        var selected = $(this).find('option:selected');
        var amt = selected.data('amount');
        var text = selected.data('text');
        if (amt) {
            var parts = (text || '').split(' | ');
            $('#infoBillNo').text(parts[0] || '');
            $('#infoBillAmt').text('BDT ' + parseFloat(amt).toLocaleString('en-BD', {minimumFractionDigits:2}));
            $('#billAmount').val(amt);
            $('#amountReceived').val(parseFloat(amt).toFixed(2));
            $('#billInfoBox').show();
        } else {
            $('#billInfoBox').hide();
            $('#billAmount').val('');
        }
    });

    if (preloadBillId) {
        // Need to load customer info for this bill — skip, user can select manually if needed
        $('#billSelect').html(`<option value="${preloadBillId}" selected>Pre-selected Bill ID: ${preloadBillId}</option>`);
        preloadBillId = null;
    }

    $('#mrForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#customerId').val() && !$('[name=bill_id]').val()) {
            Swal.fire({ icon: 'warning', title: 'Select customer and bill first.' }); return;
        }
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url: '{{ route('nas-trading.money-receipts.store') }}', method: 'POST', data: $(this).serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Receipt'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' }); });
    });
});
</script>
@endpush

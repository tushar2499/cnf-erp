@extends('nas-trading.layouts.app')
@section('title', 'New Delivery')
@push('styles')
<style>
.dlv-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.dlv-header { background:#1a6b60; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.dlv-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-truck me-2 text-info"></i> New Delivery</h4>
    <a href="{{ route('nas-trading.deliveries.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<form id="dlvForm">
    @csrf

    <div class="dlv-card">
        <div class="dlv-header"><i class="fa fa-file-invoice me-2"></i> Bill Reference</div>
        <div class="dlv-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select id="customerSelect" class="form-select form-select-sm"></select>
                    <input type="hidden" name="customer_name" id="customerNameHidden">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirmed Bill <span class="text-danger">*</span></label>
                    <select id="billSelect" name="bill_id" class="form-select form-select-sm" required>
                        <option value="">— Select customer first —</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Delivery No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="[Auto]" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="dlv-card">
        <div class="dlv-header"><i class="fa fa-map-marker-alt me-2"></i> Delivery Details</div>
        <div class="dlv-body">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                    <textarea name="delivery_address" id="deliveryAddress" class="form-control form-control-sm" rows="2" required></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transport Company</label>
                    <select name="transport_co_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($transportCos as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vehicle No</label>
                    <input type="text" name="vehicle_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Driver Name</label>
                    <input type="text" name="driver_name" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Driver Phone</label>
                    <input type="text" name="driver_phone" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Create Delivery</button>
        <a href="{{ route('nas-trading.deliveries.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('#customerSelect').select2({
        width: '100%', placeholder: 'Search customer with confirmed bills...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.money-receipts.search-customers') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#customerNameHidden').val(d.text.split(' | ')[1] || d.text);
        $('#billSelect').html('<option value="">Loading...</option>');
        $.get('{{ route('nas-trading.money-receipts.get-bills') }}', { customer_id: d.id }, function (bills) {
            var opts = '<option value="">Select bill...</option>';
            bills.forEach(b => { opts += `<option value="${b.id}" data-amount="${b.total_amount}">${b.text}</option>`; });
            $('#billSelect').html(opts);
        });
    }).on('select2:clear', function () {
        $('#billSelect').html('<option value="">— Select customer first —</option>');
        $('#deliveryAddress').val('');
    });

    // Pre-select bill if passed via query string
    var preloadBillId = new URLSearchParams(window.location.search).get('bill_id');
    if (preloadBillId) {
        $.get('{{ route('nas-trading.customer-bills.show', '') }}/' + preloadBillId, function () {}).fail(function () {});
        // Load bill info via the bills endpoint — need customer first
        // Instead just put bill_id directly
        $('#billSelect').html(`<option value="${preloadBillId}" selected>Bill #${preloadBillId} (pre-selected)</option>`);
    }

    $('#dlvForm').on('submit', function (e) {
        e.preventDefault();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url: '{{ route('nas-trading.deliveries.store') }}', method: 'POST', data: $(this).serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Create Delivery'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' }); });
    });
});
</script>
@endpush

@extends('nas-trading.layouts.app')
@section('title', 'New LC Bill Statement')
@push('styles')
<style>
.card-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.card-panel-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.82rem; font-weight:700; }
.card-panel-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.lc-table th { background:#e9ecef; font-size:.76rem; padding:.35rem .5rem; }
.lc-table td { font-size:.8rem; padding:.3rem .5rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-file-alt me-2 text-success"></i> New LC Bill Statement</h4>
    <a href="{{ route('nas-trading.lc-bill-statements.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="stmtForm">
    @csrf

    {{-- Header --}}
    <div class="card-panel">
        <div class="card-panel-header"><i class="fa fa-id-card me-2"></i> Statement Header</div>
        <div class="card-panel-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Bill No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="[Auto-Generated]" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select form-select-sm" required style="width:100%">
                        <option value="">-- Select Customer --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Address</label>
                    <input type="text" id="customerAddressDisplay" class="form-control form-control-sm" placeholder="Customer address" readonly style="background:#f8f9fa;color:#6c757d;font-size:.78rem;">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Bill statement note">
                </div>
            </div>
        </div>
    </div>

    {{-- LC Selection --}}
    <div class="card-panel">
        <div class="card-panel-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-file-contract me-2"></i> Select LC Entries</span>
        </div>
        <div class="card-panel-body">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Add LC <span class="text-danger">*</span></label>
                    <select id="lcSearch" class="form-select form-select-sm" style="width:100%" disabled>
                        <option value="">-- Select customer first --</option>
                    </select>
                    <div id="lcLoadingHint" class="text-muted mt-1" style="font-size:.75rem;display:none">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading LCs...
                    </div>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="table table-bordered lc-table w-100" id="lcTable">
                    <thead>
                        <tr>
                            <th style="width:35px">#</th>
                            <th>PFI No</th>
                            <th>LC/TT No</th>
                            <th>LC/TT Date</th>
                            <th>LC Retirement Date</th>
                            <th class="text-end">LC RT/Invoice Value (BDT)</th>
                            <th class="text-end">Commission %</th>
                            <th class="text-end">Commission Amt (BDT)</th>
                            <th style="width:35px"></th>
                        </tr>
                    </thead>
                    <tbody id="lcBody">
                        <tr id="emptyRow">
                            <td colspan="9" class="text-center text-muted py-3" style="font-size:.8rem">
                                Select a customer above to load available LCs.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave">
            <i class="fa fa-save me-1"></i> Save Statement
        </button>
        <a href="{{ route('nas-trading.lc-bill-statements.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var addedLcIds = [];
var customerLcs  = [];   // all LCs for the selected customer

$(function () {
    // ── Step 1: Customer ──────────────────────────────────────────
    $('#customerSelect').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Customer --',
        ajax: {
            url: '{{ route('nas-trading.lc-bill-statements.search-customers') }}',
            dataType: 'json', delay: 250,
            data: params => ({ q: params.term }),
            processResults: d => ({ results: d }),
        },
        minimumInputLength: 1,
    }).on('change', function () {
        resetLcSection();
        var customerId = $(this).val();
        var data = $(this).select2('data');
        var address = data.length ? (data[0].address || '') : '';
        if (address) {
            $('#customerAddressDisplay').val(address).show();
        } else {
            $('#customerAddressDisplay').val('').hide();
        }
        if (!customerId) { return; }
        loadCustomerLcs(customerId);
    });

    // ── Step 2: LC dropdown — initial disabled state ──────────────
    $('#lcSearch').select2({ theme: 'bootstrap-5', placeholder: '-- Select customer first --' });

    // ── Submit ────────────────────────────────────────────────────
    $('#stmtForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#customerSelect').val()) { Swal.fire({ icon: 'warning', title: 'Select a customer.' }); return; }
        if (addedLcIds.length === 0)    { Swal.fire({ icon: 'warning', title: 'Add at least one LC.' }); return; }

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        var data = $(this).serialize();
        addedLcIds.forEach(id => { data += '&lc_ids[]=' + id; });

        $.ajax({ url: '{{ route('nas-trading.lc-bill-statements.store') }}', method: 'POST', data })
            .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                .then(() => { if (r.redirect) window.location.href = r.redirect; }))
            .fail(xhr => {
                $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Statement');
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error saving statement.' });
            });
    });
});

function loadCustomerLcs(customerId) {
    $('#lcSearch').prop('disabled', true);
    $('#lcLoadingHint').show();

    $.getJSON('{{ route('nas-trading.lc-bill-statements.search-lcs') }}', { customer_id: customerId })
        .done(function (data) {
            customerLcs = data;

            // Destroy → rebuild DOM options (blank first) → reinit Select2
            // Using DOM <option> nodes instead of Select2 `data` option prevents
            // auto-selection of the first item on init.
            $('#lcSearch').select2('destroy').empty().append('<option value=""></option>');
            data.forEach(l => $('#lcSearch').append(new Option(l.text, l.id, false, false)));

            $('#lcSearch')
                .select2({ theme: 'bootstrap-5', placeholder: '-- Select LC to add --', allowClear: true })
                .prop('disabled', false)
                .off('select2:select')
                .on('select2:select', function (e) {
                    var lc = customerLcs.find(l => l.id == e.params.data.id);
                    if (!lc) { return; }
                    if (addedLcIds.includes(lc.id)) {
                        Swal.fire({ icon: 'warning', title: 'Already added', timer: 1200, showConfirmButton: false });
                        $(this).val(null).trigger('change'); return;
                    }
                    addLcRow(lc);
                    $(this).val(null).trigger('change');
                });

            $('#lcLoadingHint').hide();
            if (data.length === 0) {
                $('#lcBody').html(`<tr id="emptyRow"><td colspan="9" class="text-center text-muted py-3" style="font-size:.8rem">No LC entries found for this customer.</td></tr>`);
            }
        })
        .fail(function () {
            $('#lcLoadingHint').hide();
            Swal.fire({ icon: 'error', title: 'Failed to load LCs.' });
        });
}

function resetLcSection() {
    addedLcIds  = [];
    customerLcs = [];
    $('#lcSearch').select2('destroy').empty().append('<option value=""></option>')
        .select2({ theme: 'bootstrap-5', placeholder: '-- Select customer first --' })
        .prop('disabled', true);
    $('#lcBody').html(`<tr id="emptyRow"><td colspan="9" class="text-center text-muted py-3" style="font-size:.8rem">Select a customer above to load available LCs.</td></tr>`);
}

function formatCommissionPercent(val) {
    if (!val) { return '-'; }
    var n = parseFloat(val);
    // strip trailing zeros after decimal, like the show view does
    return n.toFixed(4).replace(/\.?0+$/, '') + '%';
}

function updateTotal() {
    var total = 0;
    $('#lcBody tr[data-lc-id]').each(function () {
        var raw = $(this).data('commission-flat');
        if (raw) { total += parseFloat(raw); }
    });
    if ($('#lcBody tr[data-lc-id]').length > 0) {
        // 9 columns total: # | PFI | LC/TT No | LC/TT Date | Retirement | RT Value | Comm% | Comm Amt | Remove
        // colspan=7 spans cols 1-7, value in col 8, empty cell for col 9 (remove button)
        $('#lcTfoot').remove();
        $('#lcTable').append(`<tfoot id="lcTfoot"><tr style="background:#f0f4f8">
            <td colspan="7" class="text-end fw-bold" style="font-size:.82rem">Total Commission (BDT)</td>
            <td class="text-end fw-bold text-success" style="font-size:.9rem">${total.toLocaleString('en-BD', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
            <td></td>
        </tr></tfoot>`);
    } else {
        $('#lcTfoot').remove();
    }
}

function addLcRow(lc) {
    $('#emptyRow').remove();
    addedLcIds.push(lc.id);
    var idx = addedLcIds.length;
    var commFlat = lc.lc_commission_flat ? parseFloat(lc.lc_commission_flat) : null;
    $('#lcBody').append(`
        <tr data-lc-id="${lc.id}" data-commission-flat="${commFlat || ''}">
            <td class="text-center">${idx}</td>
            <td>${lc.pfi_no || '-'}</td>
            <td>${lc.lc_no || '-'}</td>
            <td>${lc.lc_open_date || '-'}</td>
            <td>${lc.lc_retirement_date || '-'}</td>
            <td class="text-end fw-bold">${lc.lc_rt_value ? parseFloat(lc.lc_rt_value).toLocaleString('en-BD', {minimumFractionDigits:2}) : '-'}</td>
            <td class="text-end">${formatCommissionPercent(lc.lc_commission_percent)}</td>
            <td class="text-end fw-bold">${commFlat ? commFlat.toLocaleString('en-BD', {minimumFractionDigits:2}) : '-'}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 btn-remove-lc" style="width:22px;height:22px;line-height:1" title="Remove">
                    <i class="fa fa-times" style="font-size:.6rem"></i>
                </button>
            </td>
        </tr>
    `);
    updateTotal();
}

$(document).on('click', '.btn-remove-lc', function () {
    var row  = $(this).closest('tr');
    var lcId = parseInt(row.data('lc-id'));
    addedLcIds = addedLcIds.filter(id => id !== lcId);
    row.remove();
    if (addedLcIds.length === 0) {
        $('#lcBody').html(`<tr id="emptyRow"><td colspan="9" class="text-center text-muted py-3" style="font-size:.8rem">No LC entries added yet.</td></tr>`);
    }
    $('#lcBody tr[data-lc-id]').each((i, tr) => $(tr).find('td:first').text(i + 1));
    updateTotal();
});
</script>
@endpush

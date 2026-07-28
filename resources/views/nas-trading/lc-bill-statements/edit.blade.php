@extends('nas-trading.layouts.app')
@section('title', 'Edit LC Bill Statement — ' . $lcBillStatement->bill_no)
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
    <h4 class="mb-0"><i class="fa fa-edit me-2 text-primary"></i> Edit — {{ $lcBillStatement->bill_no }}</h4>
    <a href="{{ route('nas-trading.lc-bill-statements.show', $lcBillStatement->id) }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="stmtForm">
    @csrf
    @method('PUT')

    {{-- Header --}}
    <div class="card-panel">
        <div class="card-panel-header"><i class="fa fa-id-card me-2"></i> Statement Header</div>
        <div class="card-panel-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Bill No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $lcBillStatement->bill_no }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ $lcBillStatement->bill_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select form-select-sm" required style="width:100%">
                        <option value="{{ $lcBillStatement->customer_id }}" selected>
                            {{ $lcBillStatement->customer?->code }} | {{ $lcBillStatement->customer?->company_name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm" value="{{ $lcBillStatement->note }}">
                </div>
            </div>
        </div>
    </div>

    {{-- LC Selection --}}
    <div class="card-panel">
        <div class="card-panel-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-file-contract me-2"></i> LC Entries</span>
        </div>
        <div class="card-panel-body">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Search & Add LC</label>
                    <select id="lcSearch" class="form-select form-select-sm" style="width:100%">
                        <option value="">-- Search by LC No / PFI No --</option>
                    </select>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="table table-bordered lc-table w-100" id="lcTable">
                    <thead>
                        <tr>
                            <th style="width:35px">#</th>
                            <th>PFI No</th>
                            <th>LC No</th>
                            <th>LC Date</th>
                            <th>LC Retirement Date</th>
                            <th>LC RT Value (BDT)</th>
                            <th>Commission %</th>
                            <th>Commission Amt (BDT)</th>
                            <th style="width:35px"></th>
                        </tr>
                    </thead>
                    <tbody id="lcBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary px-5" id="btnSave">
            <i class="fa fa-save me-1"></i> Update Statement
        </button>
        <a href="{{ route('nas-trading.lc-bill-statements.show', $lcBillStatement->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var addedLcIds = [];
var existingLcs = @json($lcBillStatement->items->map(fn($item) => [
    'id'                    => $item->lc?->id,
    'text'                  => ($item->lc?->lc_no_system ?? '') . ' | ' . ($item->lc?->lc_no ?? '') . ' | ' . ($item->lc?->pfi_no ?? ''),
    'pfi_no'                => $item->lc?->pfi_no,
    'lc_no'                 => $item->lc?->lc_no,
    'lc_open_date'          => $item->lc?->lc_open_date?->format('d-M-Y'),
    'lc_retirement_date'    => $item->lc?->lc_retirement_date?->format('d-M-Y'),
    'lc_rt_value'           => $item->lc?->lc_rt_value,
    'lc_commission_percent' => $item->lc?->lc_commission_percent,
    'lc_commission'         => $item->lc?->lc_commission,
]));

$(function () {
    $('#customerSelect').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '{{ route('nas-trading.lc-bill-statements.search-customers') }}',
            dataType: 'json', delay: 250,
            data: params => ({ q: params.term }),
            processResults: d => ({ results: d }),
        },
        minimumInputLength: 1,
    }).on('change', function () {
        addedLcIds = [];
        $('#lcBody').empty();
        $('#lcSearch').val(null).trigger('change');
    });

    $('#lcSearch').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Search by LC No / PFI No --',
        ajax: {
            url: '{{ route('nas-trading.lc-bill-statements.search-lcs') }}',
            dataType: 'json', delay: 250,
            data: params => ({ q: params.term, customer_id: $('#customerSelect').val() }),
            processResults: d => ({ results: d }),
        },
        minimumInputLength: 0,
        allowClear: true,
    }).on('select2:select', function (e) {
        var lc = e.params.data;
        if (addedLcIds.includes(lc.id)) {
            Swal.fire({ icon: 'warning', title: 'Already added', timer: 1500, showConfirmButton: false });
            $(this).val(null).trigger('change'); return;
        }
        addLcRow(lc);
        $(this).val(null).trigger('change');
    });

    // Pre-populate existing items
    existingLcs.forEach(lc => { if (lc.id) addLcRow(lc); });

    $('#stmtForm').on('submit', function (e) {
        e.preventDefault();
        if (addedLcIds.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Please add at least one LC entry.' }); return;
        }
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');
        var data = $(this).serialize();
        addedLcIds.forEach(id => { data += '&lc_ids[]=' + id; });
        $.ajax({ url: '{{ route('nas-trading.lc-bill-statements.update', $lcBillStatement->id) }}', method: 'POST', data })
            .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                .then(() => { if (r.redirect) window.location.href = r.redirect; }))
            .fail(xhr => {
                $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Update Statement');
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
            });
    });
});

function addLcRow(lc) {
    addedLcIds.push(lc.id);
    var idx = addedLcIds.length;
    $('#lcBody').append(`
        <tr data-lc-id="${lc.id}">
            <td class="text-center">${idx}</td>
            <td>${lc.pfi_no || '-'}</td>
            <td>${lc.lc_no || '-'}</td>
            <td>${lc.lc_open_date || '-'}</td>
            <td>${lc.lc_retirement_date || '-'}</td>
            <td class="text-end">${lc.lc_rt_value ? parseFloat(lc.lc_rt_value).toLocaleString('en-BD', {minimumFractionDigits:2}) : '-'}</td>
            <td class="text-end">${lc.lc_commission_percent ? parseFloat(lc.lc_commission_percent).toFixed(2) + '%' : '-'}</td>
            <td class="text-end">${lc.lc_commission ? parseFloat(lc.lc_commission).toLocaleString('en-BD', {minimumFractionDigits:2}) : '-'}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 btn-remove-lc" style="width:22px;height:22px;line-height:1" title="Remove">
                    <i class="fa fa-times" style="font-size:.6rem"></i>
                </button>
            </td>
        </tr>
    `);
}

$(document).on('click', '.btn-remove-lc', function () {
    var row = $(this).closest('tr');
    var lcId = parseInt(row.data('lc-id'));
    addedLcIds = addedLcIds.filter(id => id !== lcId);
    row.remove();
    $('#lcBody tr[data-lc-id]').each((i, tr) => $(tr).find('td:first').text(i + 1));
});
</script>
@endpush

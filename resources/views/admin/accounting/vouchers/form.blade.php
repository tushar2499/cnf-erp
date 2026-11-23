@extends('admin.layouts.app')

@section('title', $voucher ? 'Edit Voucher '.$voucher->voucher_no : 'New Voucher')

@push('styles')
<style>
#linesTable th, #linesTable td { font-size: .82rem; vertical-align: middle; }
#linesTable input.amount { text-align: right; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice-dollar me-2 text-success"></i> {{ $voucher ? 'Edit Voucher '.$voucher->voucher_no : 'New Voucher' }}</h4>
    <a href="{{ route('admin.accounting.vouchers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Vouchers</a>
</div>

<form id="voucherForm" novalidate>
    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label" for="companyId">Company <span class="text-danger">*</span></label>
                <select id="companyId" class="form-select" {{ $voucher ? 'disabled' : '' }}>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected($voucher?->company_id === $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="voucherType">Voucher type <span class="text-danger">*</span></label>
                <select id="voucherType" class="form-select" {{ $voucher ? 'disabled' : '' }}>
                    @foreach(\App\Models\Accounting\AccVoucher::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected($voucher?->voucher_type === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="voucherDate">Date <span class="text-danger">*</span></label>
                <input type="date" id="voucherDate" class="form-control" value="{{ $voucher?->voucher_date?->toDateString() ?? now()->toDateString() }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Voucher No</label>
                <input type="text" class="form-control" value="{{ $voucher?->voucher_no ?? 'Auto' }}" readonly>
            </div>
            <div class="col-12">
                <label class="form-label" for="narration">Narration</label>
                <input type="text" id="narration" class="form-control" value="{{ $voucher?->narration }}" placeholder="e.g. Office rent for November 2026">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:32%">Account</th>
                        <th style="width:24%">Party (control accounts)</th>
                        <th>Line narration</th>
                        <th style="width:12%" class="text-end">Debit</th>
                        <th style="width:12%" class="text-end">Credit</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddLine"><i class="fa fa-plus me-1"></i> Add line</button>
                            <span id="balanceWarning" class="text-danger small ms-2 d-none"><i class="fa fa-triangle-exclamation"></i> Debit and credit must be equal.</span></td>
                        <td class="text-end fw-bold" id="totalDebit">0.00</td>
                        <td class="text-end fw-bold" id="totalCredit">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success btn-sm" id="btnSave"><i class="fa fa-save me-1"></i> Save Voucher</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const csrf = $('meta[name="csrf-token"]').attr('content');
const partyTypes = @json($partyTypes);
const existingLines = @json($existingLines);

function money(n) { return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function esc(s) { return $('<div>').text(s ?? '').html(); }

$(function () {
    function partyTypeOptions(selected) {
        const types = partyTypes[$('#companyId').val()] || {};
        let html = '<option value="">—</option>';
        $.each(types, function (k, label) { html += '<option value="' + k + '"' + (k === selected ? ' selected' : '') + '>' + esc(label) + '</option>'; });
        return html;
    }

    function addLine(line) {
        line = line || {};
        const $tr = $('<tr>' +
            '<td><select class="form-select form-select-sm line-account"></select></td>' +
            '<td><div class="input-group input-group-sm party-wrap">' +
                '<select class="form-select form-select-sm line-party-type" style="max-width:40%">' + partyTypeOptions(line.party_type) + '</select>' +
                '<select class="form-select form-select-sm line-party"></select></div></td>' +
            '<td><input type="text" class="form-control form-control-sm line-narration" value="' + esc(line.narration) + '"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm amount line-debit" value="' + (line.debit > 0 ? line.debit : '') + '"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm amount line-credit" value="' + (line.credit > 0 ? line.credit : '') + '"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-line" title="Remove"><i class="fa fa-xmark"></i></button></td>' +
        '</tr>');
        $('#linesTable tbody').append($tr);

        const $acc = $tr.find('.line-account').select2({
            theme: 'bootstrap-5', width: '100%', placeholder: 'Account…',
            ajax: { url: '{{ route('admin.accounting.accounts.ledgers') }}', delay: 300, data: p => ({ q: p.term }), processResults: d => d },
        });
        const $party = $tr.find('.line-party').select2({
            theme: 'bootstrap-5', width: '60%', placeholder: 'Party…', allowClear: true,
            ajax: {
                url: '{{ route('admin.accounting.vouchers.parties') }}', delay: 300,
                data: p => ({ q: p.term, company_id: $('#companyId').val(), type: $tr.find('.line-party-type').val() }),
                processResults: d => d,
            },
        });
        if (line.account_id) { $acc.append(new Option(line.account_text, line.account_id, true, true)).trigger('change'); }
        if (line.party_id) { $party.append(new Option(line.party_name, line.party_id, true, true)).trigger('change'); }
        $tr.find('.party-wrap').toggle(!!(line.party_control || line.party_id));

        $acc.on('select2:select', function (e) {
            const control = e.params.data.party_type;
            $tr.find('.party-wrap').toggle(!!control);
            if (control) { $tr.find('.line-party-type').val(control); } else { $tr.find('.line-party-type').val(''); }
            $party.val(null).trigger('change');
        });
        $tr.find('.line-party-type').on('change', function () { $party.val(null).trigger('change'); });
        totals();
    }

    function totals() {
        let dr = 0, cr = 0;
        $('#linesTable tbody tr').each(function () {
            dr += Number($(this).find('.line-debit').val() || 0);
            cr += Number($(this).find('.line-credit').val() || 0);
        });
        $('#totalDebit').text(money(dr));
        $('#totalCredit').text(money(cr));
        $('#balanceWarning').toggleClass('d-none', Math.abs(dr - cr) < 0.005);
    }

    $(document).on('input', '.line-debit, .line-credit', totals);
    $(document).on('click', '.btn-remove-line', function () { $(this).closest('tr').remove(); totals(); });
    $('#btnAddLine').on('click', function () { addLine(); });
    $('#companyId').on('change', function () {
        $('#linesTable tbody tr').each(function () {
            $(this).find('.line-party-type').html(partyTypeOptions($(this).find('.line-party-type').val()));
            $(this).find('.line-party').val(null).trigger('change');
        });
    });

    if (existingLines.length) { existingLines.forEach(addLine); } else { addLine(); addLine(); }

    $('#voucherForm').on('submit', function (e) {
        e.preventDefault();
        const lines = $('#linesTable tbody tr').map(function () {
            const party = $(this).find('.line-party').select2('data')[0];
            return {
                account_id: $(this).find('.line-account').val(),
                party_type: party ? $(this).find('.line-party-type').val() : null,
                party_id: party ? party.id : null,
                party_name: party ? party.text : null,
                narration: $(this).find('.line-narration').val(),
                debit: $(this).find('.line-debit').val() || 0,
                credit: $(this).find('.line-credit').val() || 0,
            };
        }).get();

        $('#btnSave').prop('disabled', true);
        $.ajax({
            url: '{{ $voucher ? route('admin.accounting.vouchers.update', $voucher) : route('admin.accounting.vouchers.store') }}',
            method: '{{ $voucher ? 'PUT' : 'POST' }}',
            contentType: 'application/json',
            data: JSON.stringify({
                _token: csrf,
                company_id: $('#companyId').val(),
                voucher_type: $('#voucherType').val(),
                voucher_date: $('#voucherDate').val(),
                narration: $('#narration').val(),
                lines: lines,
            }),
        })
        .done(function (r) { window.location = r.redirect; })
        .fail(function (xhr) {
            const errors = xhr.responseJSON?.errors;
            Swal.fire({ icon: 'error', title: errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Could not save the voucher.') });
            $('#btnSave').prop('disabled', false);
        });
    });
});
</script>
@endpush

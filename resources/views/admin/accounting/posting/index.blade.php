@extends('admin.layouts.app')

@section('title', 'Voucher Posting')

@push('styles')
<style>
#entriesTable th, #entriesTable td { font-size: .8rem; padding: .4rem .5rem; vertical-align: middle; }
#entriesTable thead th { background: #e9ecef; font-weight: 600; position: sticky; top: 0; z-index: 2; }
.entries-wrapper { max-height: 62vh; overflow: auto; }
#entriesTable .dr { color: #0b5cad; }
#entriesTable .cr { color: #a33b8f; }
#entriesTable small.line { display: block; white-space: nowrap; }
.status-filter .btn { font-size: .78rem; }
#linesTable th, #linesTable td { font-size: .8rem; vertical-align: middle; }
#linesTable input.amount { text-align: right; }
</style>
@endpush

@section('content')
@php $canPost = auth()->user()->hasPermission('admin.accounting.posting.post'); @endphp

<div class="page-header">
    <h4><i class="fa fa-file-import me-2 text-success"></i> Voucher Posting</h4>
    <a href="{{ route('admin.accounting.mappings.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-shuffle me-1"></i> Transaction Mapping</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="companyId">Company</label>
                <select id="companyId" class="form-select form-select-sm" required>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" data-slug="{{ $company->slug }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="from">From</label>
                <input type="date" id="from" class="form-control form-control-sm" value="{{ now()->startOfMonth()->toDateString() }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="to">To</label>
                <input type="date" id="to" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="sourceType">Document</label>
                <select id="sourceType" class="form-select form-select-sm"></select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100" id="btnLoad"><i class="fa fa-magnifying-glass me-1"></i> Load Entries</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="btn-group status-filter" role="group" aria-label="Status filter">
            <button type="button" class="btn btn-outline-secondary active" data-status="">All <span class="badge bg-secondary" data-count=""></span></button>
            <button type="button" class="btn btn-outline-success" data-status="ready">Ready <span class="badge bg-success" data-count="ready"></span></button>
            <button type="button" class="btn btn-outline-danger" data-status="unmapped">Unmapped <span class="badge bg-danger" data-count="unmapped"></span></button>
            <button type="button" class="btn btn-outline-warning" data-status="changed">Changed <span class="badge bg-warning text-dark" data-count="changed"></span></button>
            <button type="button" class="btn btn-outline-primary" data-status="posted">Posted <span class="badge bg-primary" data-count="posted"></span></button>
        </div>
        @if($canPost)
            <button class="btn btn-sm btn-success" id="btnPostSelected" disabled><i class="fa fa-check-double me-1"></i> Post selected</button>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="entries-wrapper">
            <table id="entriesTable" class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:30px" class="text-center"><input type="checkbox" id="checkAll" aria-label="Select all ready"></th>
                        <th style="width:85px">Date</th>
                        <th>Document</th>
                        <th>Doc No</th>
                        <th>Party</th>
                        <th class="text-end">Amount</th>
                        <th>Proposed Debit</th>
                        <th>Proposed Credit</th>
                        <th>Status</th>
                        <th style="width:80px" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="10" class="text-center text-muted py-4">Select company and period, then click <b>Load Entries</b>.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Review modal --}}
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="reviewModalLabel">Review entry</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 small mb-2" id="reviewInfo"></div>
                <div id="unmappedInfo" class="alert alert-danger small py-2 d-none"></div>
                <div class="mb-2">
                    <label class="form-label" for="reviewNarration">Narration</label>
                    <input type="text" id="reviewNarration" class="form-control form-control-sm">
                </div>
                <table class="table table-bordered table-sm" id="linesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:38%">Account</th>
                            <th>Party</th>
                            <th style="width:14%" class="text-end">Debit</th>
                            <th style="width:14%" class="text-end">Credit</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddLine"><i class="fa fa-plus me-1"></i> Add line</button></td>
                            <td class="text-end fw-bold" id="totalDebit">0.00</td>
                            <td class="text-end fw-bold" id="totalCredit">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div id="balanceWarning" class="text-danger small d-none"><i class="fa fa-triangle-exclamation me-1"></i> Debit and credit totals must be equal.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                @if($canPost)
                    <button type="button" class="btn btn-success btn-sm" id="btnPostOne"><i class="fa fa-check me-1"></i> Save &amp; Post Voucher</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = $('meta[name="csrf-token"]').attr('content');
const sources = @json($sources);
const statusBadge = {
    ready:    '<span class="badge bg-success">Ready</span>',
    unmapped: '<span class="badge bg-danger">Unmapped</span>',
    changed:  '<span class="badge bg-warning text-dark">Changed</span>',
    posted:   '<span class="badge bg-primary">Posted</span>',
};
let entries = [];
let statusFilter = '';
let current = null;
let loadedFilter = null;

function esc(s) { return $('<div>').text(s ?? '').html(); }
function money(n) { return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

$(function () {
    function fillSources() {
        const slug = $('#companyId option:selected').data('slug');
        let html = '<option value="">All documents</option>';
        sources.filter(s => s.company === slug).forEach(function (s) {
            html += '<option value="' + s.key + '">' + esc(s.label) + '</option>';
        });
        $('#sourceType').html(html);
    }
    $('#companyId').on('change', fillSources);
    fillSources();

    function lineList(lines, side) {
        return lines.filter(l => Number(l[side]) > 0)
            .map(l => '<small class="line ' + (side === 'debit' ? 'dr' : 'cr') + '">' + esc(l.account_code + ' ' + l.account_name) + ' — ' + money(l[side]) + '</small>')
            .join('');
    }

    function render() {
        const rows = entries.filter(e => !statusFilter || e.status === statusFilter);
        const counts = { '': entries.length };
        entries.forEach(e => counts[e.status] = (counts[e.status] || 0) + 1);
        $('[data-count]').each(function () { $(this).text(counts[$(this).data('count')] || 0); });

        if (!rows.length) {
            $('#entriesTable tbody').html('<tr><td colspan="10" class="text-center text-muted py-4">No entries.</td></tr>');
        } else {
            $('#entriesTable tbody').html(rows.map(function (e) {
                const idx = entries.indexOf(e);
                const selectable = e.status === 'ready' || e.status === 'changed';
                let proposed = lineList(e.lines, 'debit'), proposedCr = lineList(e.lines, 'credit');
                if (e.unmapped.length) {
                    proposedCr += e.unmapped.map(u => '<small class="line text-danger"><i class="fa fa-triangle-exclamation"></i> ' + esc(u.label) + ' — ' + money(u.amount) + '</small>').join('');
                }
                const voucher = e.voucher_no ? '<br><a href="{{ url('admin/accounting/vouchers') }}/' + e.voucher_id + '" target="_blank" class="small">' + esc(e.voucher_no) + '</a>' : '';
                return '<tr>' +
                    '<td class="text-center">' + (selectable ? '<input type="checkbox" class="row-check" data-idx="' + idx + '">' : '') + '</td>' +
                    '<td>' + esc(e.date) + '</td>' +
                    '<td><small>' + esc(e.source_label) + '</small></td>' +
                    '<td>' + esc(e.doc_no) + '</td>' +
                    '<td>' + esc(e.party_name) + '</td>' +
                    '<td class="text-end">' + money(e.amount) + '</td>' +
                    '<td>' + proposed + '</td>' +
                    '<td>' + proposedCr + '</td>' +
                    '<td>' + statusBadge[e.status] + voucher + '</td>' +
                    '<td class="text-center"><button class="btn btn-sm btn-outline-primary btn-review" data-idx="' + idx + '" title="Review / edit"><i class="fa fa-pen-to-square"></i></button></td>' +
                '</tr>';
            }).join(''));
        }
        $('#checkAll').prop('checked', false);
        updateSelected();
    }

    function updateSelected() {
        $('#btnPostSelected').prop('disabled', $('.row-check:checked').length === 0)
            .html('<i class="fa fa-check-double me-1"></i> Post selected (' + $('.row-check:checked').length + ')');
    }

    function load() {
        loadedFilter = { company_id: $('#companyId').val(), from: $('#from').val(), to: $('#to').val(), source_type: $('#sourceType').val() };
        $('#btnLoad').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
        $.get('{{ route('admin.accounting.posting.entries') }}', loadedFilter)
            .done(function (r) { entries = r.entries; render(); })
            .fail(function (xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire({ icon: 'error', title: errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Could not load entries.') });
            })
            .always(function () { $('#btnLoad').prop('disabled', false).html('<i class="fa fa-magnifying-glass me-1"></i> Load Entries'); });
    }

    $('#filterForm').on('submit', function (e) { e.preventDefault(); load(); });

    $('.status-filter .btn').on('click', function () {
        $('.status-filter .btn').removeClass('active');
        $(this).addClass('active');
        statusFilter = $(this).data('status');
        render();
    });

    $('#checkAll').on('change', function () { $('.row-check').prop('checked', this.checked); updateSelected(); });
    $(document).on('change', '.row-check', updateSelected);

    $('#btnPostSelected').on('click', function () {
        const items = $('.row-check:checked').map(function () {
            const e = entries[$(this).data('idx')];
            return { source_type: e.source_type, ref: e.ref };
        }).get();
        Swal.fire({ title: 'Post ' + items.length + ' voucher(s)?', text: 'Each document is posted with its mapping proposal.', icon: 'question', showCancelButton: true, confirmButtonText: 'Post' })
            .then(function (res) {
                if (!res.isConfirmed) { return; }
                $('#btnPostSelected').prop('disabled', true);
                $.ajax({ url: '{{ route('admin.accounting.posting.post-many') }}', method: 'POST', contentType: 'application/json',
                    data: JSON.stringify(Object.assign({ _token: csrf, items: items }, loadedFilter)) })
                    .done(function (r) {
                        Swal.fire({ icon: r.skipped.length ? 'warning' : 'success', title: r.message, html: r.skipped.map(esc).join('<br>') });
                        load();
                    })
                    .fail(function (xhr) { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Posting failed.' }); updateSelected(); });
            });
    });

    // ── Review / edit one entry ──────────────────────────
    function accountSelect($el) {
        $el.select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#reviewModal'),
            placeholder: 'Account…',
            width: '100%',
            ajax: {
                url: '{{ route('admin.accounting.accounts.ledgers') }}',
                delay: 300,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return data; },
            },
        });
    }

    function addLine(line) {
        line = line || {};
        const $tr = $('<tr>' +
            '<td><select class="form-select form-select-sm line-account"></select></td>' +
            '<td><small class="text-muted line-party">' + esc(line.party_name || '') + '</small></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm amount line-debit" value="' + (Number(line.debit) > 0 ? line.debit : '') + '"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm amount line-credit" value="' + (Number(line.credit) > 0 ? line.credit : '') + '"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-line" title="Remove"><i class="fa fa-xmark"></i></button></td>' +
        '</tr>');
        $tr.data('party', { party_type: line.party_type || null, party_id: line.party_id || null, party_name: line.party_name || null });
        $('#linesTable tbody').append($tr);
        const $sel = $tr.find('.line-account');
        accountSelect($sel);
        if (line.account_id) {
            $sel.append(new Option(line.account_code + ' – ' + line.account_name, line.account_id, true, true)).trigger('change');
        }
        // A new account that is not a party control account drops the party.
        $sel.on('select2:select', function (e) {
            if (!e.params.data.party_type) {
                $tr.data('party', { party_type: null, party_id: null, party_name: null });
                $tr.find('.line-party').text('');
            } else if (current && current.party_name) {
                const p = current.lines.find(l => l.party_id) || {};
                $tr.data('party', { party_type: p.party_type || null, party_id: p.party_id || null, party_name: p.party_name || current.party_name });
                $tr.find('.line-party').text(p.party_name || current.party_name);
            }
        });
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
        $('#balanceWarning').toggleClass('d-none', Math.abs(dr - cr) < 0.005 && dr > 0);
    }

    $(document).on('input', '.line-debit, .line-credit', totals);
    $(document).on('click', '.btn-remove-line', function () { $(this).closest('tr').remove(); totals(); });
    $('#btnAddLine').on('click', function () { addLine(); });

    $(document).on('click', '.btn-review', function () {
        current = entries[$(this).data('idx')];
        $('#reviewModalLabel').text(current.source_label + ' — ' + current.doc_no);
        $('#reviewInfo').html(
            '<div class="col-md-3"><b>Date:</b> ' + esc(current.date) + '</div>' +
            '<div class="col-md-5"><b>Party:</b> ' + esc(current.party_name || '—') + '</div>' +
            '<div class="col-md-2"><b>Amount:</b> ' + money(current.amount) + '</div>' +
            '<div class="col-md-2">' + statusBadge[current.status] + (current.voucher_no ? ' ' + esc(current.voucher_no) : '') + '</div>');
        $('#unmappedInfo').toggleClass('d-none', !current.unmapped.length)
            .html('<b>No mapping for:</b> ' + current.unmapped.map(u => esc(u.label) + ' (' + money(u.amount) + ')').join(', ') + '. Add the lines below or set up the mapping.');
        $('#reviewNarration').val(current.narration);
        $('#linesTable tbody').empty();
        current.lines.forEach(addLine);
        if (current.lines.length < 2) { addLine(); addLine(); }
        totals();
        $('#btnPostOne').toggleClass('d-none', current.status === 'posted');
        $('#reviewModal').modal('show');
    });

    $('#btnPostOne').on('click', function () {
        const lines = $('#linesTable tbody tr').map(function () {
            const party = $(this).data('party') || {};
            return {
                account_id: $(this).find('.line-account').val(),
                debit: $(this).find('.line-debit').val() || 0,
                credit: $(this).find('.line-credit').val() || 0,
                party_type: party.party_type, party_id: party.party_id, party_name: party.party_name,
            };
        }).get();

        const $btn = $(this).prop('disabled', true);
        $.ajax({ url: '{{ route('admin.accounting.posting.post') }}', method: 'POST', contentType: 'application/json',
            data: JSON.stringify(Object.assign({ _token: csrf, source_type: current.source_type, ref: current.ref, narration: $('#reviewNarration').val(), lines: lines }, loadedFilter)) })
            .done(function (r) {
                $('#reviewModal').modal('hide');
                Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false });
                load();
            })
            .fail(function (xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire({ icon: 'error', title: errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Posting failed.') });
            })
            .always(function () { $btn.prop('disabled', false); });
    });
});
</script>
@endpush

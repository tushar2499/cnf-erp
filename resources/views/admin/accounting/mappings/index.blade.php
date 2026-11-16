@extends('admin.layouts.app')

@section('title', 'Transaction Mapping')

@push('styles')
<style>
.mapping-table th, .mapping-table td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
.mapping-table thead th { background: #e9ecef; font-weight: 600; }
.mapping-table .dr { color: #0b5cad; }
.mapping-table .cr { color: #a33b8f; }
.source-card .card-header { background: #f8f9fa; font-weight: 600; font-size: .9rem; }
</style>
@endpush

@section('content')
@php
    use App\Services\Accounting\Sources\MatchOptions;
    $canCreate = auth()->user()->hasPermission('admin.accounting.mapping.create');
    $canEdit = auth()->user()->hasPermission('admin.accounting.mapping.edit');
    $canDelete = auth()->user()->hasPermission('admin.accounting.mapping.delete');
@endphp

<div class="page-header">
    <h4><i class="fa fa-shuffle me-2 text-success"></i> Transaction Mapping</h4>
</div>

<div class="alert alert-light border small">
    Set the <b>Debit</b> and <b>Credit</b> head for each amount part of every document. A rule with a specific value
    (expense head, payment mode, bank account…) overrides the part's <b>Any (default)</b> rule. Parts without a rule
    show as <span class="badge bg-danger">Unmapped</span> on the posting screen.
</div>

<ul class="nav nav-tabs" role="tablist">
    @foreach($companies as $i => $company)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ $company->slug }}" type="button" role="tab">
                {{ $company->name }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white p-3">
    @foreach($companies as $i => $company)
        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $company->slug }}" role="tabpanel">
            @foreach($sources->where('company', $company->slug) as $source)
                @php $rules = $mappings[$source['key']] ?? collect(); @endphp
                <div class="card source-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ $source['label'] }}</span>
                        @if($canCreate)
                            <button class="btn btn-sm btn-success btn-add" data-source="{{ $source['key'] }}"><i class="fa fa-plus me-1"></i> Add rule</button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mapping-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:24%">Amount part</th>
                                    <th style="width:20%">Matched by</th>
                                    <th>Debit head</th>
                                    <th>Credit head</th>
                                    <th style="width:70px" class="text-center">Status</th>
                                    <th style="width:90px" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($source['components'] as $component => $meta)
                                    @php $componentRules = $rules->where('component', $component); @endphp
                                    @forelse($componentRules as $rule)
                                        <tr>
                                            <td>{{ $meta['label'] }}</td>
                                            <td>
                                                @if(isset($meta['match']))
                                                    <small class="text-muted">{{ MatchOptions::LABELS[$meta['match']] ?? '' }}:</small>
                                                    {{ $matchOptions[$meta['match']]['options'][$rule->match_key] ?? ($rule->match_key === '' ? 'Any (default)' : '#'.$rule->match_key) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="dr">{{ $rule->debitAccount->code }} – {{ $rule->debitAccount->name }}</td>
                                            <td class="cr">{{ $rule->creditAccount->code }} – {{ $rule->creditAccount->name }}</td>
                                            <td class="text-center">{!! $rule->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Off</span>' !!}</td>
                                            <td class="text-center text-nowrap">
                                                @if($canEdit)
                                                    <button class="btn btn-sm btn-outline-secondary btn-edit" title="Edit"
                                                        data-id="{{ $rule->id }}" data-source="{{ $rule->source_type }}" data-component="{{ $rule->component }}"
                                                        data-match="{{ $rule->match_key }}" data-active="{{ (int) $rule->is_active }}"
                                                        data-debit-id="{{ $rule->debit_account_id }}" data-debit-text="{{ $rule->debitAccount->code }} – {{ $rule->debitAccount->name }}"
                                                        data-credit-id="{{ $rule->credit_account_id }}" data-credit-text="{{ $rule->creditAccount->code }} – {{ $rule->creditAccount->name }}">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                @endif
                                                @if($canDelete)
                                                    <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete" data-url="{{ route('admin.accounting.mappings.destroy', $rule) }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="table-warning">
                                            <td>{{ $meta['label'] }}</td>
                                            <td colspan="3" class="text-danger"><i class="fa fa-triangle-exclamation me-1"></i> No rule yet</td>
                                            <td class="text-center"><span class="badge bg-danger">Unmapped</span></td>
                                            <td class="text-center">
                                                @if($canCreate)
                                                    <button class="btn btn-sm btn-outline-success btn-add" data-source="{{ $source['key'] }}" data-component="{{ $component }}" title="Add rule"><i class="fa fa-plus"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

{{-- Rule modal --}}
<div class="modal fade" id="mappingModal" tabindex="-1" aria-labelledby="mappingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="mappingModalLabel">Mapping rule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="mappingForm" novalidate>
                <input type="hidden" id="mappingId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Document</label>
                            <input type="text" id="sourceLabel" class="form-control" readonly>
                            <input type="hidden" id="sourceKey">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="component">Amount part <span class="text-danger">*</span></label>
                            <select id="component" class="form-select"></select>
                        </div>
                        <div class="col-md-6" id="matchWrap">
                            <label class="form-label" for="matchKey" id="matchLabel">Matched by</label>
                            <select id="matchKey" class="form-select"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="debitAccount">Debit head <span class="text-danger">*</span></label>
                            <select id="debitAccount" class="form-select account-select" style="width:100%"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="creditAccount">Credit head <span class="text-danger">*</span></label>
                            <select id="creditAccount" class="form-select account-select" style="width:100%"></select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="mappingActive" checked>
                                <label class="form-check-label" for="mappingActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnSave"><i class="fa fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = $('meta[name="csrf-token"]').attr('content');
const sources = @json($sources->keyBy('key'));
const matchOptions = @json($matchOptions);

$(function () {
    $('.account-select').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#mappingModal'),
        placeholder: 'Search code or name…',
        ajax: {
            url: '{{ route('admin.accounting.accounts.ledgers') }}',
            delay: 300,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return data; },
        },
    });

    function setSelect($el, id, text) {
        $el.empty();
        if (id) { $el.append(new Option(text, id, true, true)); }
        $el.trigger('change');
    }

    function fillComponents(sourceKey, selected) {
        const comps = sources[sourceKey].components;
        $('#component').html(Object.keys(comps).map(function (k) {
            return '<option value="' + k + '">' + $('<div>').text(comps[k].label).html() + '</option>';
        }).join('')).val(selected || Object.keys(comps)[0]);
    }

    function fillMatch(selected) {
        const comp = sources[$('#sourceKey').val()].components[$('#component').val()];
        if (!comp.match) {
            $('#matchWrap').addClass('d-none');
            $('#matchKey').html('<option value="">Any (default)</option>').val('');
            return;
        }
        const list = matchOptions[comp.match];
        $('#matchWrap').removeClass('d-none');
        $('#matchLabel').text(list.label);
        let html = '<option value="">Any (default)</option>';
        $.each(list.options, function (key, label) {
            html += '<option value="' + key + '">' + $('<div>').text(label).html() + '</option>';
        });
        $('#matchKey').html(html).val(selected || '');
    }

    $('#component').on('change', function () { fillMatch(''); });

    function openModal(d) {
        $('#mappingForm .is-invalid').removeClass('is-invalid');
        $('#mappingForm .invalid-feedback').remove();
        $('#mappingId').val(d.id || '');
        $('#sourceKey').val(d.source);
        $('#sourceLabel').val(sources[d.source].label);
        fillComponents(d.source, d.component);
        fillMatch(d.match);
        $('#component').prop('disabled', !!d.id);
        setSelect($('#debitAccount'), d.debitId, d.debitText);
        setSelect($('#creditAccount'), d.creditId, d.creditText);
        $('#mappingActive').prop('checked', d.active === undefined ? true : d.active == 1);
        $('#mappingModalLabel').text(d.id ? 'Edit mapping rule' : 'Add mapping rule');
        $('#mappingModal').modal('show');
    }

    $('.btn-add').on('click', function () {
        openModal({ source: $(this).data('source'), component: $(this).data('component') });
    });

    $('.btn-edit').on('click', function () {
        const d = $(this).data();
        openModal({ id: d.id, source: d.source, component: d.component, match: String(d.match ?? ''), active: d.active,
            debitId: d.debitId, debitText: d.debitText, creditId: d.creditId, creditText: d.creditText });
    });

    $('.btn-delete').on('click', function () {
        const url = $(this).data('url');
        Swal.fire({ title: 'Delete this rule?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(function (res) {
                if (!res.isConfirmed) { return; }
                $.ajax({ url: url, method: 'DELETE', data: { _token: csrf } })
                    .done(function () { location.reload(); })
                    .fail(function (xhr) { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Delete failed.' }); });
            });
    });

    const fieldMap = { component: '#component', match_key: '#matchKey', debit_account_id: '#debitAccount', credit_account_id: '#creditAccount', source_type: '#sourceLabel' };

    $('#mappingForm').on('submit', function (e) {
        e.preventDefault();
        $('#mappingForm .is-invalid').removeClass('is-invalid');
        $('#mappingForm .invalid-feedback').remove();

        const id = $('#mappingId').val();
        $('#btnSave').prop('disabled', true);

        $.ajax({
            url: id ? '{{ url('admin/accounting/mappings') }}/' + id : '{{ route('admin.accounting.mappings.store') }}',
            method: id ? 'PUT' : 'POST',
            data: {
                _token: csrf,
                source_type: $('#sourceKey').val(),
                component: $('#component').val(),
                match_key: $('#matchKey').val(),
                debit_account_id: $('#debitAccount').val(),
                credit_account_id: $('#creditAccount').val(),
                is_active: $('#mappingActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function () { location.reload(); })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function (field, messages) {
                    const $el = $(fieldMap[field] || '#component');
                    const $anchor = $el.hasClass('select2-hidden-accessible') ? $el.next('.select2') : $el;
                    $el.addClass('is-invalid');
                    $anchor.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
                });
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () { $('#btnSave').prop('disabled', false); });
    });
});
</script>
@endpush

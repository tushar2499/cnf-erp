@extends('admin.layouts.app')

@section('title', 'Chart of Accounts')

@push('styles')
<style>
#accountsTable th, #accountsTable td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
#accountsTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#accountsTable thead tr:last-child th { background: #f8f9fa; }
#accountsTable thead tr:last-child th input.form-control { min-width: 80px; width: 100%; box-sizing: border-box; }
.accounts-table-wrapper { max-height: 65vh; overflow: auto; }
.accounts-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.accounts-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.accounts-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#accountsTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
#importIssues { max-height: 40vh; overflow: auto; font-size: .8rem; }
.import-stat { border: 1px solid #dee2e6; border-radius: 6px; padding: .5rem .75rem; text-align: center; }
.import-stat b { display: block; font-size: 1.15rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-sitemap me-2 text-success"></i> Chart of Accounts</h4>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('admin.accounting.coa.import'))
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal" id="btnImport">
            <i class="fa fa-file-import me-1"></i> Import Excel
        </button>
        @endif
        @if(auth()->user()->hasPermission('admin.accounting.coa.create'))
        <button class="btn btn-sm btn-success" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Add Account
        </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span><i class="fa fa-list me-2"></i> All Accounts</span>
            <select id="filterType" class="form-select form-select-sm" style="width:auto" aria-label="Filter by type">
                <option value="">All types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <select id="filterLevel" class="form-select form-select-sm" style="width:auto" aria-label="Filter by level">
                <option value="">All levels</option>
                @foreach(\App\Models\Accounting\AccAccount::LEVEL_NAMES as $level => $label)
                    <option value="{{ $level }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="accounts-table-wrapper">
            <table id="accountsTable" class="table table-hover table-striped table-bordered mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Code</th>
                        <th>Account Name</th>
                        <th>Level</th>
                        <th>Type</th>
                        <th>Dr/Cr</th>
                        <th>Parent</th>
                        <th>Flags</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add / Edit modal --}}
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="accountModalLabel"><i class="fa fa-plus me-2"></i>Add Account</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="accountForm" novalidate>
                <input type="hidden" id="accountId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12" id="parentCreateWrap">
                            <label class="form-label" for="accountParent">Parent group</label>
                            <select id="accountParent" class="form-select" style="width:100%"></select>
                            <div class="form-text">Leave empty to create a Master account (2-digit code).</div>
                        </div>
                        <div class="col-12 d-none" id="parentEditWrap">
                            <label class="form-label">Parent group</label>
                            <input type="text" id="accountParentText" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="accountCode">Code <span class="text-danger">*</span></label>
                            <input type="text" id="accountCode" class="form-control" inputmode="numeric" placeholder="e.g. 01">
                            <div class="form-text" id="codeHint"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="accountName">Head / Account name <span class="text-danger">*</span></label>
                            <input type="text" id="accountName" class="form-control" placeholder="e.g. Cash In Hand at Head Office">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="accountType">Type <span class="text-danger">*</span></label>
                            <select id="accountType" class="form-select">
                                @foreach($types as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="accountNature">Normal balance <span class="text-danger">*</span></label>
                            <select id="accountNature" class="form-select">
                                <option value="dr">Debit (Dr)</option>
                                <option value="cr">Credit (Cr)</option>
                            </select>
                        </div>
                        <div class="col-md-4 ledger-only">
                            <label class="form-label" for="accountPartyType">Party control</label>
                            <select id="accountPartyType" class="form-select">
                                <option value="">None</option>
                                <option value="customer">Customer sub-ledger</option>
                                <option value="supplier">Supplier sub-ledger</option>
                            </select>
                        </div>
                        <div class="col-md-6 ledger-only">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="accountCashBank">
                                <label class="form-check-label" for="accountCashBank">Cash / Bank account</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="accountActive" checked>
                                <label class="form-check-label" for="accountActive">Active</label>
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

{{-- Import modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="importModalLabel"><i class="fa fa-file-import me-2"></i>Import Chart of Accounts</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="importFile">Excel file</label>
                        <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls">
                        <div class="form-text">Needs columns Code_Master, Code2_Sub-1, Code3_Sub-2, Code4_Ledger, Type with cells like <code>0102010001_Cash In Hand</code>.</div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-primary w-100" id="btnPreview"><i class="fa fa-eye me-1"></i> Preview</button>
                    </div>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">
                    Customer / supplier ledgers (Accounts Receivable, Accounts Payable, Advance Receipts) are merged into one control account per group —
                    parties come from the ERP customer and supplier lists. Master 06 (Contra Assets) is skipped. Existing codes are updated; nothing is deleted.
                </div>
                <div id="importResult" class="d-none mt-3">
                    <div class="row g-2 mb-3" id="importStats"></div>
                    <h6 class="fw-600">Issues <small class="text-muted">(rows with errors are not imported)</small></h6>
                    <div id="importIssues" class="border rounded">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light"><tr><th style="width:70px">Row</th><th style="width:90px">Severity</th><th>Message</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success btn-sm" id="btnConfirmImport" disabled><i class="fa fa-check me-1"></i> Import</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;
const csrf = $('meta[name="csrf-token"]').attr('content');
const codeLengths = @json(\App\Models\Accounting\AccAccount::CODE_LENGTHS);
const levelNames = @json(\App\Models\Accounting\AccAccount::LEVEL_NAMES);

$(function () {
    table = $('#accountsTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        order: [[2, 'asc']],
        orderCellsTop: true,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, 200, 500, 1000], [25, 50, 100, 200, 500, 1000]],
        ajax: {
            url: '{{ route('admin.accounting.accounts.index') }}',
            data: function (d) {
                d.type = $('#filterType').val();
                d.level = $('#filterLevel').val();
            },
        },
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '45px', className: 'text-center' },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'code',         name: 'code', width: '110px' },
            { data: 'name',         name: 'name' },
            { data: 'level_name',   name: 'level', searchable: false, width: '70px' },
            { data: 'type',         name: 'acc_accounts.type', searchable: false, width: '80px' },
            { data: 'nature',       name: 'acc_accounts.nature', searchable: false, width: '55px', className: 'text-center' },
            { data: 'parent',       name: 'parent', orderable: false },
            { data: 'flags',        name: 'flags', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
        ],
        dom: "<'row mb-0'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' },
        ],
        initComplete: function () {
            var self = this.api();
            const firstRowH = $('#accountsTable thead tr:first-child').outerHeight();
            $('#accountsTable thead tr:last-child th').css('top', firstRowH + 'px');
            self.columns().every(function (i) {
                var col = this;
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($input.length) {
                    $input.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $input.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($input.val()).draw(); }, 400);
                    });
                }
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No accounts yet. Import the chart of accounts Excel or add accounts.</div>' },
    });

    $('#filterType, #filterLevel').on('change', function () { table.ajax.reload(); });

    $('#accountParent').select2({
        dropdownParent: $('#accountModal'),
        allowClear: true,
        placeholder: '— Master account (no parent) —',
        ajax: {
            url: '{{ route('admin.accounting.accounts.parents') }}',
            delay: 300,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return data; },
        },
    });

    function clearErrors() {
        $('#accountForm .is-invalid').removeClass('is-invalid');
        $('#accountForm .invalid-feedback').remove();
    }

    function setLevel(level) {
        const isLedger = level === 4;
        $('.ledger-only').toggleClass('d-none', !isLedger);
        $('#codeHint').text(levelNames[level] + ' — ' + codeLengths[level] + ' digits');
    }

    $('#accountParent').on('change', function () {
        const parentId = $(this).val();
        if (!parentId) {
            $('#accountType').prop('disabled', false);
            $('#accountCode').val('').attr('placeholder', 'e.g. 01');
            setLevel(1);
            return;
        }
        $.get('{{ route('admin.accounting.accounts.next-code') }}', { parent_id: parentId }, function (r) {
            $('#accountCode').val(r.code);
            $('#accountType').val(r.type).prop('disabled', true);
            $('#accountNature').val(r.nature);
            setLevel(r.level);
        });
    });

    $('#btnAdd').on('click', function () {
        clearErrors();
        $('#accountModalLabel').html('<i class="fa fa-plus me-2"></i>Add Account');
        $('#accountId').val('');
        $('#parentCreateWrap').removeClass('d-none');
        $('#parentEditWrap').addClass('d-none');
        $('#accountParent').val(null).trigger('change.select2');
        $('#accountCode').val('').prop('readonly', false);
        $('#accountName').val('');
        $('#accountType').val('asset').prop('disabled', false);
        $('#accountNature').val('dr');
        $('#accountPartyType').val('');
        $('#accountCashBank').prop('checked', false);
        $('#accountActive').prop('checked', true);
        setLevel(1);
        $('#accountModal').modal('show');
    });

    $('#accountType').on('change', function () {
        $('#accountNature').val(['asset', 'expense'].includes(this.value) ? 'dr' : 'cr');
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        clearErrors();
        $('#accountModalLabel').html('<i class="fa fa-edit me-2"></i>Edit Account');
        $('#accountId').val(d.id);
        $('#parentCreateWrap').addClass('d-none');
        $('#parentEditWrap').removeClass('d-none');
        $('#accountParentText').val(d.parent);
        $('#accountCode').val(String(d.code)).prop('readonly', true);
        $('#accountName').val(d.name);
        $('#accountType').val(d.type).prop('disabled', true);
        $('#accountNature').val(d.nature);
        $('#accountPartyType').val(d.party_type || '');
        $('#accountCashBank').prop('checked', d.is_cash_bank == 1);
        $('#accountActive').prop('checked', d.is_active == 1);
        $('.ledger-only').toggleClass('d-none', d.is_group == 1);
        $('#codeHint').text('Code cannot be changed.');
        $('#accountModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete',
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            $.ajax({ url: url, method: 'DELETE', data: { _token: csrf } })
                .done(function (r) {
                    Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Delete failed.' });
                });
        });
    });

    const fieldMap = { parent_id: '#accountParent', code: '#accountCode', name: '#accountName', type: '#accountType', nature: '#accountNature', party_type: '#accountPartyType' };

    $('#accountForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const id = $('#accountId').val();
        const url = id ? '{{ url('admin/accounting/accounts') }}/' + id : '{{ route('admin.accounting.accounts.store') }}';
        const data = {
            _token: csrf,
            name: $('#accountName').val(),
            nature: $('#accountNature').val(),
            party_type: $('#accountPartyType').val(),
            is_cash_bank: $('#accountCashBank').is(':checked') ? 1 : 0,
            is_active: $('#accountActive').is(':checked') ? 1 : 0,
        };
        if (!id) {
            data.parent_id = $('#accountParent').val() || '';
            data.code = $('#accountCode').val();
            data.type = $('#accountType').val();
        }

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({ url: url, method: id ? 'PUT' : 'POST', data: data })
            .done(function (r) {
                $('#accountModal').modal('hide');
                Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
                table.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        const $el = $(fieldMap[field] || '#accountName');
                        const $anchor = $el.hasClass('select2-hidden-accessible') ? $el.next('.select2') : $el;
                        $el.addClass('is-invalid');
                        $anchor.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
                    });
                } else {
                    Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            })
            .always(function () {
                $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
            });
    });

    // ── Import ──────────────────────────────────────────────
    function importForm() {
        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('file', $('#importFile')[0].files[0]);
        return fd;
    }

    function esc(s) { return $('<div>').text(s).html(); }

    $('#btnImport').on('click', function () {
        $('#importFile').val('');
        $('#importResult').addClass('d-none');
        $('#btnConfirmImport').prop('disabled', true);
    });

    $('#importFile').on('change', function () {
        $('#importResult').addClass('d-none');
        $('#btnConfirmImport').prop('disabled', true);
    });

    $('#btnPreview').on('click', function () {
        if (!$('#importFile')[0].files.length) {
            Swal.fire({ icon: 'warning', title: 'Choose an Excel file first.' });
            return;
        }
        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Reading...');

        $.ajax({ url: '{{ route('admin.accounting.accounts.import.preview') }}', method: 'POST', data: importForm(), processData: false, contentType: false })
            .done(function (r) {
                const s = r.stats;
                const stats = [
                    ['Groups', s.groups, 'text-secondary'],
                    ['Ledgers', s.ledgers, 'text-success'],
                    ['Already exist', s.existing, 'text-primary'],
                    ['Party rows merged', s.party_ledgers_collapsed, 'text-warning'],
                    ['Contra rows skipped', s.skipped_master_rows, 'text-muted'],
                    ['Errors', s.errors, 'text-danger'],
                    ['Warnings', s.warnings, 'text-warning'],
                ];
                $('#importStats').html(stats.map(function (x) {
                    return '<div class="col"><div class="import-stat"><b class="' + x[2] + '">' + x[1] + '</b><small>' + x[0] + '</small></div></div>';
                }).join(''));

                const badge = { error: 'bg-danger', warning: 'bg-warning text-dark' };
                $('#importIssues tbody').html(r.issues.length
                    ? r.issues.map(function (i) {
                        return '<tr><td>' + i.row + '</td><td><span class="badge ' + badge[i.severity] + '">' + i.severity + '</span></td><td>' + esc(i.message) + '</td></tr>';
                    }).join('')
                    : '<tr><td colspan="3" class="text-center text-muted py-2">No issues found.</td></tr>');

                $('#importResult').removeClass('d-none');
                $('#btnConfirmImport').prop('disabled', r.accounts.length === 0)
                    .html('<i class="fa fa-check me-1"></i> Import ' + r.accounts.length + ' accounts');
            })
            .fail(function (xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire({ icon: 'error', title: errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Could not read the file.') });
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fa fa-eye me-1"></i> Preview');
            });
    });

    $('#btnConfirmImport').on('click', function () {
        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Importing...');

        $.ajax({ url: '{{ route('admin.accounting.accounts.import') }}', method: 'POST', data: importForm(), processData: false, contentType: false })
            .done(function (r) {
                $('#importModal').modal('hide');
                Swal.fire({ icon: 'success', title: r.message });
                table.ajax.reload();
            })
            .fail(function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-check me-1"></i> Import');
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Import failed.' });
            });
    });
});
</script>
@endpush

@extends('chevron.layouts.app')

@section('title', 'Expense Heads')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-money-bill me-2 text-success"></i> Expense Heads</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.settings.expense-heads.sample') }}"
           class="btn btn-sm btn-outline-success">
            <i class="fa fa-file-excel me-1"></i> Sample File
        </a>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa fa-file-upload me-1"></i> Import Excel
        </button>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#expenseHeadModal" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Add Expense Head
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Expense Heads</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="expenseHeadsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="expenseHeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Expense Head</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseHeadForm">
                @csrf
                <input type="hidden" id="headId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="headName" class="form-control" placeholder="e.g. Port Handling Fee">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select id="headCategory" class="form-select select2">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="headType" class="form-select">
                                <option value="">-- Select Type --</option>
                                <option value="External">External</option>
                                <option value="Internal">Internal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount</label>
                            <input type="number" id="headAmount" class="form-control" placeholder="0.00" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="headActive" checked>
                                <label class="form-check-label" for="headActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnSave">
                        <i class="fa fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600"><i class="fa fa-file-upload me-2"></i>Import Expense Heads</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Step 1: Upload --}}
                <div id="importStep1">
                    <div class="alert alert-info d-flex gap-2 align-items-start" style="font-size:12px">
                        <i class="fa fa-info-circle mt-1"></i>
                        <div>
                            Upload an Excel file (.xlsx / .xls) with columns:
                            <strong>Name</strong>, <strong>Category</strong>, <strong>Type</strong> (Internal/External), <strong>Amount</strong>, <strong>Status</strong> (Active/Inactive).<br>
                            Category must match an existing category name exactly (case-insensitive).<br>
                            <a href="{{ route('chevron.settings.expense-heads.sample') }}" class="fw-semibold">
                                <i class="fa fa-download me-1"></i>Download sample file
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Excel File</label>
                        <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv">
                        <div class="invalid-feedback" id="importFileError"></div>
                    </div>
                    <button class="btn btn-primary btn-sm" id="btnPreview">
                        <i class="fa fa-eye me-1"></i> Preview
                    </button>
                </div>

                {{-- Step 2: Preview --}}
                <div id="importStep2" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <span class="badge bg-success me-1" id="newCount">0 New</span>
                            <span class="badge bg-warning text-dark me-1" id="existCount">0 Already Exist</span>
                            <span class="badge bg-danger" id="warnCount" style="display:none">0 Warnings</span>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="btnBackToUpload">
                            <i class="fa fa-arrow-left me-1"></i> Back
                        </button>
                    </div>
                    <div class="table-responsive" style="max-height:420px;overflow-y:auto">
                        <table class="table table-sm table-bordered" style="font-size:12px">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width:4%">#</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th style="width:10%">Type</th>
                                    <th style="width:9%">Amount</th>
                                    <th style="width:8%">Status</th>
                                    <th style="width:13%">Result</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted" style="font-size:11px">
                        Only <strong>New</strong> rows will be imported. Existing rows are skipped.
                        Rows with <span class="badge bg-danger" style="font-size:10px">Warning</span> have unmatched category or invalid type — they will still import with null category / default type.
                    </div>
                </div>

            </div>
            <div class="modal-footer d-none" id="importFooter">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnConfirmImport">
                    <i class="fa fa-check me-1"></i> Confirm Import
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;
var previewRows = [];

$(function () {
    $('#headCategory').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Category --',
        allowClear: true,
        dropdownParent: $('#expenseHeadModal'),
    });

    // ── DataTable ────────────────────────────────────────────────────────────
    table = $('#expenseHeadsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.settings.expense-heads.index') }}',
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name',          name: 'name' },
            { data: 'category_name', name: 'expenseCategory.name' },
            { data: 'type',          name: 'type' },
            { data: 'amount',        name: 'amount' },
            { data: 'status_badge',  name: 'is_active', searchable: false },
            { data: 'action',        name: 'action', orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv',   text: 'CSV' },
            { extend: 'excel', text: 'Excel' },
            { extend: 'pdf',   text: 'PDF' },
            { extend: 'print', text: 'Print' },
        ],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($input.length) {
                    $input.on('keyup change', () => this.search($input.val()).draw());
                }
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense heads yet.</div>' },
    });

    // ── Add/Edit modal ───────────────────────────────────────────────────────
    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Expense Head');
        $('#headId').val('');
        $('#headName').val('').removeClass('is-invalid');
        $('#headCategory').val('').trigger('change').removeClass('is-invalid');
        $('#headType').val('').removeClass('is-invalid');
        $('#headAmount').val('').removeClass('is-invalid');
        $('#headActive').prop('checked', true);
        $('.invalid-feedback').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Expense Head');
        $('#headId').val(d.id);
        $('#headName').val(d.name).removeClass('is-invalid');
        $('#headCategory').val(d.expense_category_id).trigger('change').removeClass('is-invalid');
        $('#headType').val(d.type).removeClass('is-invalid');
        $('#headAmount').val(d.amount).removeClass('is-invalid');
        $('#headActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#expenseHeadModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url  = $(this).data('url');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete',
        }).then(function (res) {
            if (res.isConfirmed) {
                $.ajax({ url: url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                    .done(function (r) {
                        Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload();
                    })
                    .fail(function () { Swal.fire({ icon: 'error', title: 'Delete failed.' }); });
            }
        });
    });

    function showFieldError($field, msg) {
        $field.addClass('is-invalid');
        const $after = $field.hasClass('select2') ? $field.next('.select2-container') : $field;
        $after.after('<div class="invalid-feedback d-block">' + msg + '</div>');
    }

    $('#expenseHeadForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        let valid = true;
        if (!$('#headName').val().trim())    { showFieldError($('#headName'),     'Name is required.');     valid = false; }
        if (!$('#headCategory').val())       { showFieldError($('#headCategory'), 'Category is required.'); valid = false; }
        if (!$('#headType').val())           { showFieldError($('#headType'),     'Type is required.');     valid = false; }
        if (!valid) return;

        const id  = $('#headId').val();
        const url = id
            ? '{{ url('chevron/settings/expense-heads') }}/' + id
            : '{{ route('chevron.settings.expense-heads.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:               $('meta[name="csrf-token"]').attr('content'),
                name:                 $('#headName').val(),
                expense_category_id:  $('#headCategory').val(),
                type:                 $('#headType').val(),
                amount:               $('#headAmount').val(),
                is_active:            $('#headActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#expenseHeadModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name)                { showFieldError($('#headName'),     errors.name[0]); }
                if (errors.expense_category_id) { showFieldError($('#headCategory'), errors.expense_category_id[0]); }
                if (errors.type)                { showFieldError($('#headType'),      errors.type[0]); }
                if (errors.amount)              { showFieldError($('#headAmount'),    errors.amount[0]); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
        });
    });

    // ── Import modal ─────────────────────────────────────────────────────────
    $('#importModal').on('hidden.bs.modal', resetImport);

    function resetImport() {
        previewRows = [];
        $('#importFile').val('').removeClass('is-invalid');
        $('#importFileError').text('');
        $('#importStep1').show();
        $('#importStep2').hide();
        $('#importFooter').addClass('d-none');
        $('#previewBody').empty();
    }

    $('#btnBackToUpload').on('click', function () {
        $('#importStep2').hide();
        $('#importStep1').show();
        $('#importFooter').addClass('d-none');
    });

    $('#btnPreview').on('click', function () {
        const file = $('#importFile')[0].files[0];
        if (!file) {
            $('#importFile').addClass('is-invalid');
            $('#importFileError').text('Please select an Excel file.');
            return;
        }
        $('#importFile').removeClass('is-invalid');

        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#btnPreview').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');

        $.ajax({
            url: '{{ route('chevron.settings.expense-heads.import.preview') }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
        })
        .done(function (r) {
            previewRows = r.rows;
            renderPreview(previewRows);
            $('#importStep1').hide();
            $('#importStep2').show();
            $('#importFooter').removeClass('d-none');
        })
        .fail(function (xhr) {
            Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed to parse file.' });
        })
        .always(function () {
            $('#btnPreview').prop('disabled', false).html('<i class="fa fa-eye me-1"></i> Preview');
        });
    });

    function renderPreview(rows) {
        let html = '', newCnt = 0, existCnt = 0, warnCnt = 0;

        rows.forEach(function (row, i) {
            const hasWarn = !row.category_found || !row.type_valid;

            if (row.exists) {
                existCnt++;
                html += `<tr class="table-warning">
                    <td class="text-center">${i+1}</td>
                    <td>${h(row.name)}</td>
                    <td>${h(row.category_name)}</td>
                    <td class="text-center">${h(row.type)}</td>
                    <td class="text-end">${row.amount ?? ''}</td>
                    <td class="text-center"><span class="badge bg-secondary">${h(row.status)}</span></td>
                    <td class="text-center"><span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>Exists</span></td>
                </tr>`;
            } else {
                newCnt++;
                if (hasWarn) warnCnt++;
                const rowClass = hasWarn ? 'table-danger' : '';
                const warns = [];
                if (!row.category_found && row.category_name) warns.push('Category not found');
                if (!row.type_valid) warns.push('Invalid type');
                const warnBadge = hasWarn
                    ? `<span class="badge bg-danger ms-1" title="${warns.join(', ')}"><i class="fa fa-exclamation-triangle me-1"></i>Warning</span>`
                    : '';
                html += `<tr class="${rowClass}">
                    <td class="text-center">${i+1}</td>
                    <td>${h(row.name)}</td>
                    <td>${h(row.category_name)}${!row.category_found && row.category_name ? ' <span class="text-danger small">(not found)</span>' : ''}</td>
                    <td class="text-center">${h(row.type)}${!row.type_valid ? ' <span class="text-danger small">(?)</span>' : ''}</td>
                    <td class="text-end">${row.amount ?? ''}</td>
                    <td class="text-center"><span class="badge ${row.status==='Active'?'bg-success':'bg-danger'}">${h(row.status)}</span></td>
                    <td class="text-center">
                        <span class="badge bg-success"><i class="fa fa-plus me-1"></i>New</span>${warnBadge}
                    </td>
                </tr>`;
            }
        });

        if (rows.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-muted py-3">No valid rows found in file.</td></tr>';
        }

        $('#previewBody').html(html);
        $('#newCount').text(newCnt + ' New');
        $('#existCount').text(existCnt + ' Already Exist');

        if (warnCnt > 0) {
            $('#warnCount').text(warnCnt + ' Warnings').show();
        } else {
            $('#warnCount').hide();
        }

        $('#btnConfirmImport').prop('disabled', newCnt === 0);
    }

    $('#btnConfirmImport').on('click', function () {
        const newRows = previewRows.filter(r => !r.exists);
        if (!newRows.length) return;

        $('#btnConfirmImport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Importing...');

        $.ajax({
            url: '{{ route('chevron.settings.expense-heads.import') }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ _token: $('meta[name="csrf-token"]').attr('content'), rows: newRows }),
        })
        .done(function (r) {
            $('#importModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 2000, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Import failed.' });
        })
        .always(function () {
            $('#btnConfirmImport').prop('disabled', false).html('<i class="fa fa-check me-1"></i> Confirm Import');
        });
    });

    function h(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>
@endpush

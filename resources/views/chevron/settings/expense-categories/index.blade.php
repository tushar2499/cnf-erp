@extends('chevron.layouts.app')

@section('title', 'Expense Categories')

@push('styles')
<style>
#expenseCategoriesTable th, #expenseCategoriesTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#expenseCategoriesTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#expenseCategoriesTable thead tr:last-child th { background: #f8f9fa; }
#expenseCategoriesTable thead tr:last-child th input.form-control { min-width: 72px; width: 100%; box-sizing: border-box; }
.expense-categories-table-wrapper { max-height: 65vh; overflow: auto; }
.expense-categories-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.expense-categories-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.expense-categories-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#expenseCategoriesTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-tags me-2 text-success"></i> Expense Categories</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.settings.expense-categories.sample') }}"
           class="btn btn-sm btn-outline-success">
            <i class="fa fa-file-excel me-1"></i> Sample File
        </a>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa fa-file-upload me-1"></i> Import Excel
        </button>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#expenseCategoryModal" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Add Category
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Expense Categories</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#expenseCategoriesTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#expenseCategoriesTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#expenseCategoriesTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#expenseCategoriesTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="expense-categories-table-wrapper">
            <table id="expenseCategoriesTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Applicable To</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
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
<div class="modal fade" id="expenseCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Expense Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseCategoryForm">
                @csrf
                <input type="hidden" id="categoryId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="categoryName" class="form-control" placeholder="e.g. Port Charges" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Applicable To <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="categoryIsBill">
                                    <label class="form-check-label" for="categoryIsBill">
                                        <span class="badge text-white" style="background-color:#0891b2">Bill</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="categoryIsJob">
                                    <label class="form-check-label" for="categoryIsJob">
                                        <span class="badge bg-primary">Job</span>
                                    </label>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block d-none" id="categoryTypeError"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea id="categoryDescription" class="form-control" rows="3" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="categoryActive" checked>
                                <label class="form-check-label" for="categoryActive">Active</label>
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
                <h6 class="modal-title fw-600"><i class="fa fa-file-upload me-2"></i>Import Expense Categories</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="btnCloseImport"></button>
            </div>
            <div class="modal-body">

                {{-- Step 1: Upload --}}
                <div id="importStep1">
                    <div class="alert alert-info d-flex gap-2 align-items-start" style="font-size:12px">
                        <i class="fa fa-info-circle mt-1"></i>
                        <div>
                            Upload an Excel file (.xlsx / .xls) with columns: <strong>Name</strong>, <strong>Type</strong> (bill / job / both), <strong>Description</strong>, <strong>Status</strong> (Active/Inactive).<br>
                            <a href="{{ route('chevron.settings.expense-categories.sample') }}" class="fw-semibold">
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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-success me-1" id="newCount">0 New</span>
                            <span class="badge bg-warning text-dark" id="existCount">0 Already Exist</span>
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
                                    <th style="width:10%">Applicable To</th>
                                    <th>Description</th>
                                    <th style="width:10%">Status</th>
                                    <th style="width:12%">Result</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted" style="font-size:11px">Only <strong>New</strong> rows will be imported. Already existing categories will be skipped.</div>
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
    // ── DataTable ────────────────────────────────────────────────────────────
    table = $('#expenseCategoriesTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        order: [],
        orderCellsTop: true,
        lengthMenu: [[15, 25, 50, 100, 200, 500], [15, 25, 50, 100, 200, 500]],
        ajax: '{{ route('chevron.settings.expense-categories.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px', className: 'text-center' },
            { data: 'name',         name: 'name' },
            { data: 'type_badge',   name: 'type_badge', orderable: false },
            { data: 'description',  name: 'description' },
            { data: 'status_badge', name: 'is_active', searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        initComplete: function () {
            const firstRowH = $('#expenseCategoriesTable thead tr:first-child').outerHeight();
            $('#expenseCategoriesTable thead tr:last-child th').css('top', firstRowH + 'px');

            var self = this.api();
            self.columns().every(function (i) {
                var col = this;
                var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($in.length) {
                    $in.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $in.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($in.val()).draw(); }, 400);
                    });
                }
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense categories yet.</div>' },
    });

    // ── Add/Edit modal ───────────────────────────────────────────────────────
    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Expense Category');
        $('#categoryId').val('');
        $('#categoryName').val('').removeClass('is-invalid');
        $('#categoryIsBill').prop('checked', true);
        $('#categoryIsJob').prop('checked', false);
        $('#categoryDescription').val('');
        $('#categoryActive').prop('checked', true);
        $('#categoryTypeError').text('').addClass('d-none');
        $('.invalid-feedback').not('#categoryTypeError').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Expense Category');
        $('#categoryId').val(d.id);
        $('#categoryName').val(d.name).removeClass('is-invalid');
        $('#categoryIsBill').prop('checked', d.is_bill == 1);
        $('#categoryIsJob').prop('checked', d.is_job == 1);
        $('#categoryDescription').val(d.description);
        $('#categoryActive').prop('checked', d.is_active == 1);
        $('#categoryTypeError').text('').addClass('d-none');
        $('.invalid-feedback').not('#categoryTypeError').remove();
        $('#expenseCategoryModal').modal('show');
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

    $('#expenseCategoryForm').on('submit', function (e) {
        e.preventDefault();

        const isBill = $('#categoryIsBill').is(':checked');
        const isJob  = $('#categoryIsJob').is(':checked');

        if (!isBill && !isJob) {
            $('#categoryTypeError').text('Select at least one: Bill or Job (or both).').removeClass('d-none');
            return;
        }
        $('#categoryTypeError').text('').addClass('d-none');

        const id  = $('#categoryId').val();
        const url = id
            ? '{{ url('chevron/settings/expense-categories') }}/' + id
            : '{{ route('chevron.settings.expense-categories.store') }}';

        $('#categoryName').removeClass('is-invalid');
        $('.invalid-feedback').not('#categoryTypeError').remove();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                name:        $('#categoryName').val(),
                is_bill:     isBill ? 1 : 0,
                is_job:      isJob  ? 1 : 0,
                description: $('#categoryDescription').val(),
                is_active:   $('#categoryActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#expenseCategoryModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors ?? {};
                if (errors.name) {
                    $('#categoryName').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.name[0] + '</div>');
                }
                if (errors.type) {
                    $('#categoryTypeError').text(errors.type[0]).removeClass('d-none');
                }
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
        $('#importFile').val('');
        $('#importFileError').text('');
        $('#importFile').removeClass('is-invalid');
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
            url: '{{ route('chevron.settings.expense-categories.import.preview') }}',
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

    function typeBadge(row) {
        if (row.is_bill && row.is_job) {
            return '<span class="badge bg-info text-white">Bill & Job</span>';
        }
        if (row.is_bill) {
            return '<span class="badge text-white" style="background-color:#0891b2">Bill</span>';
        }
        if (row.is_job) {
            return '<span class="badge bg-primary">Job</span>';
        }
        return '<span class="badge bg-secondary">None</span>';
    }

    function renderPreview(rows) {
        let html = '';
        let newCnt = 0, existCnt = 0;

        rows.forEach(function (row, i) {
            if (row.exists) {
                existCnt++;
                html += `<tr class="table-warning">
                    <td class="text-center">${i + 1}</td>
                    <td>${escHtml(row.name)}</td>
                    <td class="text-center">${typeBadge(row)}</td>
                    <td>${escHtml(row.description)}</td>
                    <td class="text-center"><span class="badge bg-secondary">${escHtml(row.status)}</span></td>
                    <td class="text-center"><span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>Exists</span></td>
                </tr>`;
            } else {
                newCnt++;
                html += `<tr>
                    <td class="text-center">${i + 1}</td>
                    <td>${escHtml(row.name)}</td>
                    <td class="text-center">${typeBadge(row)}</td>
                    <td>${escHtml(row.description)}</td>
                    <td class="text-center"><span class="badge ${row.status === 'Active' ? 'bg-success' : 'bg-danger'}">${escHtml(row.status)}</span></td>
                    <td class="text-center"><span class="badge bg-success"><i class="fa fa-plus me-1"></i>New</span></td>
                </tr>`;
            }
        });

        if (rows.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted py-3">No valid rows found in file.</td></tr>';
        }

        $('#previewBody').html(html);
        $('#newCount').text(newCnt + ' New');
        $('#existCount').text(existCnt + ' Already Exist');

        $('#btnConfirmImport').prop('disabled', newCnt === 0);
    }

    $('#btnConfirmImport').on('click', function () {
        const newRows = previewRows.filter(r => !r.exists);
        if (newRows.length === 0) return;

        $('#btnConfirmImport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Importing...');

        $.ajax({
            url: '{{ route('chevron.settings.expense-categories.import') }}',
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

    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>
@endpush

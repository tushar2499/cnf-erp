@extends('admin.layouts.app')

@section('title', 'Designations')

@push('styles')
<style>
#designationsTable th, #designationsTable td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
#designationsTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#designationsTable thead tr:last-child th { background: #f8f9fa; }
#designationsTable thead tr:last-child th input.form-control { min-width: 80px; width: 100%; box-sizing: border-box; }
.designations-table-wrapper { max-height: 65vh; overflow: auto; }
.designations-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.designations-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.designations-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#designationsTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-id-badge me-2 text-success"></i> Designations</h4>
    @if(auth()->user()->hasPermission('admin.designations.create'))
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#designationModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Designation
    </button>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Designations</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#designationsTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#designationsTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#designationsTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#designationsTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="designations-table-wrapper">
            <table id="designationsTable" class="table table-hover table-striped table-bordered mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
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

{{-- Modal --}}
<div class="modal fade" id="designationModal" tabindex="-1" aria-labelledby="designationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="designationModalLabel"><i class="fa fa-plus me-2"></i>Add Designation</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="designationForm">
                @csrf
                <input type="hidden" id="designationId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="designationName">Designation <span class="text-danger">*</span></label>
                            <input type="text" id="designationName" class="form-control" placeholder="e.g. Manager">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="designationActive" checked>
                                <label class="form-check-label" for="designationActive">Active</label>
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
@endsection

@push('scripts')
<script>
var table;

$(function () {
    table = $('#designationsTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        order: [],
        orderCellsTop: true,
        pageLength: 25,
        lengthMenu: [[15, 25, 50, 100, 200, 500], [15, 25, 50, 100, 200, 500]],
        ajax: '{{ route('admin.designations.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '45px', className: 'text-center' },
            { data: 'name',         name: 'name' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '100px', className: 'text-center' },
        ],
        dom: "<'row mb-0'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' },
        ],
        initComplete: function () {
            var self = this.api();
            const firstRowH = $('#designationsTable thead tr:first-child').outerHeight();
            $('#designationsTable thead tr:last-child th').css('top', firstRowH + 'px');
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No designations yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#designationModalLabel').html('<i class="fa fa-plus me-2"></i>Add Designation');
        $('#designationId').val('');
        $('#designationName').val('').removeClass('is-invalid');
        $('#designationActive').prop('checked', true);
        $('.invalid-feedback').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#designationModalLabel').html('<i class="fa fa-edit me-2"></i>Edit Designation');
        $('#designationId').val(d.id);
        $('#designationName').val(d.name).removeClass('is-invalid');
        $('#designationActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#designationModal').modal('show');
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
                    .fail(function (xhr) {
                        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Delete failed.' });
                    });
            }
        });
    });

    $('#designationForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        if (!$('#designationName').val().trim()) {
            $('#designationName').addClass('is-invalid').after('<div class="invalid-feedback">Designation is required.</div>');
            return;
        }

        const id  = $('#designationId').val();
        const url = id
            ? '{{ url('admin/designations') }}/' + id
            : '{{ route('admin.designations.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:    $('meta[name="csrf-token"]').attr('content'),
                name:      $('#designationName').val(),
                is_active: $('#designationActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#designationModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                $('#designationName').addClass('is-invalid').after('<div class="invalid-feedback">' + (xhr.responseJSON.errors.name?.[0] ?? '') + '</div>');
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
        });
    });
});
</script>
@endpush

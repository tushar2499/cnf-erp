@extends('chevron.layouts.app')

@section('title', 'Branches')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-code-branch me-2 text-success"></i> Branches</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#branchModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Branch
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Branches</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#branchesTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#branchesTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#branchesTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#branchesTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="branchesTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Address</th>
                        <th>Phone</th>
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

{{-- Modal --}}
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Branch</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchForm">
                @csrf
                <input type="hidden" id="branchId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" id="branchName" class="form-control" placeholder="e.g. Chittagong Branch">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" id="branchCode" class="form-control" placeholder="e.g. CGP" maxlength="20" style="text-transform:uppercase">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="branchAddress" class="form-control" placeholder="e.g. 123 Port Road, Chittagong">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="text" id="branchPhone" class="form-control" placeholder="e.g. +880 31 123456">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="branchActive" checked>
                                <label class="form-check-label" for="branchActive">Active</label>
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
    table = $('#branchesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.settings.branches.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name',         name: 'name' },
            { data: 'code',         name: 'code' },
            { data: 'address',      name: 'address' },
            { data: 'phone',        name: 'phone' },
            { data: 'status_badge', name: 'is_active', searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '90px' },
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No branches yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Branch');
        $('#branchId').val('');
        $('#branchName').val('').removeClass('is-invalid');
        $('#branchCode').val('');
        $('#branchAddress').val('');
        $('#branchPhone').val('');
        $('#branchActive').prop('checked', true);
        $('.invalid-feedback').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Branch');
        $('#branchId').val(d.id);
        $('#branchName').val(d.name).removeClass('is-invalid');
        $('#branchCode').val(d.code);
        $('#branchAddress').val(d.address);
        $('#branchPhone').val(d.phone);
        $('#branchActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#branchModal').modal('show');
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

    $('#branchForm').on('submit', function (e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        if (!$('#branchName').val().trim()) {
            $('#branchName').addClass('is-invalid').after('<div class="invalid-feedback">Name is required.</div>');
            return;
        }

        const id  = $('#branchId').val();
        const url = id
            ? '{{ url('chevron/settings/branches') }}/' + id
            : '{{ route('chevron.settings.branches.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:    $('meta[name="csrf-token"]').attr('content'),
                name:      $('#branchName').val(),
                code:      $('#branchCode').val(),
                address:   $('#branchAddress').val(),
                phone:     $('#branchPhone').val(),
                is_active: $('#branchActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#branchModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name) { $('#branchName').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.name[0] + '</div>'); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
        });
    });

    $('#branchCode').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush

@extends('chevron.layouts.app')

@section('title', 'Ports')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-anchor me-2 text-success"></i> Ports</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#portModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Port
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Ports</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#portsTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#portsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#portsTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#portsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="portsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Port Name</th>
                        <th>Code</th>
                        <th>Prefix</th>
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

{{-- Modal --}}
<div class="modal fade" id="portModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Port</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="portForm">
                @csrf
                <input type="hidden" id="portId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Port Name <span class="text-danger">*</span></label>
                            <input type="text" id="portName" class="form-control" placeholder="e.g. Chittagong Port" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Port Code</label>
                            <input type="text" id="portCode" class="form-control" placeholder="e.g. CGP" maxlength="10" style="text-transform:uppercase">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Prefix</label>
                            <input type="text" id="portPrefix" class="form-control" placeholder="e.g. CGP-" maxlength="20">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="portActive" checked>
                                <label class="form-check-label" for="portActive">Active</label>
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
    table = $('#portsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.settings.ports.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name',         name: 'name' },
            { data: 'code',         name: 'code' },
            { data: 'prefix',       name: 'prefix' },
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No ports yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Port');
        $('#portId').val('');
        $('#portName').val('').removeClass('is-invalid');
        $('#portCode').val('');
        $('#portPrefix').val('');
        $('#portActive').prop('checked', true);
        $('.invalid-feedback').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Port');
        $('#portId').val(d.id);
        $('#portName').val(d.name).removeClass('is-invalid');
        $('#portCode').val(d.code);
        $('#portPrefix').val(d.prefix);
        $('#portActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#portModal').modal('show');
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

    $('#portForm').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#portId').val();
        const url = id
            ? '{{ url('chevron/settings/ports') }}/' + id
            : '{{ route('chevron.settings.ports.store') }}';

        $('#portName').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:    $('meta[name="csrf-token"]').attr('content'),
                name:      $('#portName').val(),
                code:      $('#portCode').val(),
                prefix:    $('#portPrefix').val(),
                is_active: $('#portActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#portModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                $('#portName').addClass('is-invalid').after('<div class="invalid-feedback">' + (xhr.responseJSON.errors.name?.[0] ?? '') + '</div>');
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
        });
    });

    // Auto uppercase port code
    $('#portCode').on('input', function () {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush

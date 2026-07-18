@extends('nas-freights.layouts.app')
@section('title', 'Container Types')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-box me-2 text-info"></i> Container Types</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#containerTypeModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Container Type
    </button>
</div>

<div class="card">
    <div class="card-header"><span><i class="fa fa-list me-2"></i> All Container Types</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="containerTypesTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="containerTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Container Type</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="containerTypeForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control text-uppercase" placeholder="e.g. 20GP" maxlength="50">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" id="fieldDescription" class="form-control" placeholder="e.g. 20ft General Purpose">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" id="fieldSortOrder" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-8 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fieldActive" checked>
                                <label class="form-check-label" for="fieldActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white" id="btnSave">
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
    table = $('#containerTypesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('nas-freights.settings.container-types.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name' },
            { data: 'description' },
            { data: 'sort_order', width: '90px' },
            { data: 'status_badge', searchable: false, width: '80px' },
            { data: 'action', orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No container types yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Container Type');
        $('#recordId').val('');
        $('#fieldName, #fieldDescription').val('');
        $('#fieldSortOrder').val(0);
        $('#fieldActive').prop('checked', true);
        $('#fieldName').removeClass('is-invalid');
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Container Type');
        $('#recordId').val(d.id);
        $('#fieldName').val(d.name).removeClass('is-invalid');
        $('#fieldDescription').val(d.description);
        $('#fieldSortOrder').val(d.sort_order);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#containerTypeModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
    });

    $('#containerTypeForm').on('submit', function (e) {
        e.preventDefault();
        const name = $('#fieldName').val().trim();
        if (!name) { $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.'); return; }
        $('#fieldName').removeClass('is-invalid');

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/container-types') }}/' + id
            : '{{ route('nas-freights.settings.container-types.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:      $('meta[name="csrf-token"]').attr('content'),
            name:        name,
            description: $('#fieldDescription').val(),
            sort_order:  $('#fieldSortOrder').val(),
            is_active:   $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#containerTypeModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(xhr => {
            const errors = xhr.responseJSON?.errors;
            if (errors?.name) { $('#fieldName').addClass('is-invalid'); $('#nameError').text(errors.name[0]); }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });

    $('#fieldName').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush

@extends('nas-freights.layouts.app')
@section('title', 'Shipping Carriers')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-ship me-2 text-info"></i> Shipping Carriers</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#carrierModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Carrier
    </button>
</div>

<div class="card">
    <div class="card-header"><span><i class="fa fa-list me-2"></i> All Shipping Carriers</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="carriersTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>SCAC Code</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="carrierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Shipping Carrier</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="carrierForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Carrier Code</label>
                            <input type="text" id="fieldCode" class="form-control form-control-sm bg-light" readonly placeholder="Auto Generated">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Carrier Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control form-control-sm" placeholder="e.g. Maersk Line">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SCAC Code</label>
                            <input type="text" id="fieldScacCode" class="form-control form-control-sm text-uppercase" placeholder="e.g. MAEU" maxlength="20">
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

function clearForm() {
    $('#recordId, #fieldCode').val('');
    $('#fieldName, #fieldScacCode').val('');
    $('#fieldActive').prop('checked', true);
    $('#fieldName').removeClass('is-invalid');
}

$(function () {
    table = $('#carriersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('nas-freights.settings.shipping-carriers.index') }}',
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false, width: '45px' },
            { data: 'carrier_code', width: '110px' },
            { data: 'name' },
            { data: 'scac_code',    width: '110px' },
            { data: 'status_badge', searchable: false, width: '80px' },
            { data: 'action',       orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No shipping carriers yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Shipping Carrier');
        clearForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Shipping Carrier');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldCode').val(d.carrier_code);
        $('#fieldName').val(d.name);
        $('#fieldScacCode').val(d.scac_code);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#carrierModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r  => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
    });

    $('#carrierForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#fieldName').val().trim()) {
            $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.');
            return;
        }
        $('#fieldName').removeClass('is-invalid');

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/shipping-carriers') }}/' + id
            : '{{ route('nas-freights.settings.shipping-carriers.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:    $('meta[name="csrf-token"]').attr('content'),
            name:      $('#fieldName').val(),
            scac_code: $('#fieldScacCode').val(),
            is_active: $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#carrierModal').modal('hide');
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

    $('#fieldScacCode').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush

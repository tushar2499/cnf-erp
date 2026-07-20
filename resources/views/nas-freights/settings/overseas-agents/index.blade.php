@extends('nas-freights.layouts.app')
@section('title', 'Overseas Agents')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-globe me-2 text-primary"></i> Overseas Agents</h4>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#agentModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Agent
    </button>
</div>

<div class="card">
    <div class="card-header"><span><i class="fa fa-list me-2"></i> All Overseas Agents</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="agentsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="agentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Overseas Agent</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="agentForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">

                    {{-- Identity --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Agent Code</label>
                            <input type="text" id="fieldCode" class="form-control form-control-sm bg-light" readonly placeholder="Auto Generated">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Agent Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control form-control-sm" placeholder="Company name">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Country <span class="text-danger">*</span></label>
                            <input type="text" id="fieldCountry" class="form-control form-control-sm" placeholder="e.g. Singapore">
                            <div class="invalid-feedback" id="countryError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">City</label>
                            <input type="text" id="fieldCity" class="form-control form-control-sm" placeholder="e.g. Singapore City">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Address</label>
                            <textarea id="fieldAddress" class="form-control form-control-sm" rows="2" placeholder="Full address"></textarea>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Contact Person</label>
                            <input type="text" id="fieldContactPerson" class="form-control form-control-sm" placeholder="Primary contact name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Designation</label>
                            <input type="text" id="fieldDesignation" class="form-control form-control-sm" placeholder="e.g. Sales Manager">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Email</label>
                            <input type="email" id="fieldEmail" class="form-control form-control-sm" placeholder="agent@email.com">
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Phone</label>
                            <input type="text" id="fieldPhone" class="form-control form-control-sm" placeholder="Office phone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Mobile</label>
                            <input type="text" id="fieldMobile" class="form-control form-control-sm" placeholder="Mobile number">
                        </div>
                    </div>

                    {{-- Business --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Payment Terms</label>
                            <input type="text" id="fieldPaymentTerms" class="form-control form-control-sm" placeholder="e.g. 30 days net">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:.78rem;">Remarks</label>
                            <input type="text" id="fieldRemarks" class="form-control form-control-sm" placeholder="Any notes">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" id="fieldActive" checked>
                        <label class="form-check-label" for="fieldActive" style="font-size:.78rem;">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSave">
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
    $('#recordId').val('');
    $('#fieldCode').val('');
    $('#fieldName, #fieldCountry, #fieldCity, #fieldAddress').val('');
    $('#fieldContactPerson, #fieldDesignation, #fieldEmail, #fieldPhone, #fieldMobile').val('');
    $('#fieldPaymentTerms, #fieldRemarks').val('');
    $('#fieldActive').prop('checked', true);
    $('#fieldName, #fieldCountry, #fieldEmail').removeClass('is-invalid');
}

$(function () {
    table = $('#agentsTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: '{{ route('nas-freights.settings.overseas-agents.index') }}',
        columns: [
            { data: 'DT_RowIndex',    orderable: false, searchable: false, width: '45px' },
            { data: 'agent_code',     width: '100px' },
            { data: 'name' },
            { data: 'country',        width: '110px' },
            { data: 'city',           width: '100px' },
            { data: 'contact_person', width: '130px' },
            { data: 'email' },
            { data: 'phone',          width: '110px' },
            { data: 'status_badge',   searchable: false, width: '80px' },
            { data: 'action',         orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No overseas agents yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Overseas Agent');
        clearForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Overseas Agent');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldCode').val(d.agent_code);
        $('#fieldName').val(d.name);
        $('#fieldCountry').val(d.country);
        $('#fieldCity').val(d.city);
        $('#fieldAddress').val(d.address);
        $('#fieldContactPerson').val(d.contact_person);
        $('#fieldDesignation').val(d.designation);
        $('#fieldEmail').val(d.email);
        $('#fieldPhone').val(d.phone);
        $('#fieldMobile').val(d.mobile);
        $('#fieldPaymentTerms').val(d.payment_terms);
        $('#fieldRemarks').val(d.remarks);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#agentModal').modal('show');
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

    $('#agentForm').on('submit', function (e) {
        e.preventDefault();
        let valid = true;

        if (!$('#fieldName').val().trim()) {
            $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.');
            valid = false;
        } else { $('#fieldName').removeClass('is-invalid'); }

        if (!$('#fieldCountry').val().trim()) {
            $('#fieldCountry').addClass('is-invalid'); $('#countryError').text('Country is required.');
            valid = false;
        } else { $('#fieldCountry').removeClass('is-invalid'); }

        if (!valid) return;

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/overseas-agents') }}/' + id
            : '{{ route('nas-freights.settings.overseas-agents.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:          $('meta[name="csrf-token"]').attr('content'),
            name:            $('#fieldName').val(),
            country:         $('#fieldCountry').val(),
            city:            $('#fieldCity').val(),
            address:         $('#fieldAddress').val(),
            contact_person:  $('#fieldContactPerson').val(),
            designation:     $('#fieldDesignation').val(),
            email:           $('#fieldEmail').val(),
            phone:           $('#fieldPhone').val(),
            mobile:          $('#fieldMobile').val(),
            payment_terms:   $('#fieldPaymentTerms').val(),
            remarks:         $('#fieldRemarks').val(),
            is_active:       $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#agentModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(xhr => {
            const errors = xhr.responseJSON?.errors;
            if (errors?.name)    { $('#fieldName').addClass('is-invalid');    $('#nameError').text(errors.name[0]); }
            if (errors?.country) { $('#fieldCountry').addClass('is-invalid'); $('#countryError').text(errors.country[0]); }
            if (errors?.email)   { $('#fieldEmail').addClass('is-invalid');   $('#emailError').text(errors.email[0]); }
            if (!errors) Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });
});
</script>
@endpush

@extends('nas-freights.layouts.app')

@section('title', 'Vehicle List')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
.veh-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.veh-panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.veh-tabs .nav-link { font-size:.82rem; font-weight:500; color:#6b7280; border:none; padding:.5rem 1rem; border-bottom:3px solid transparent; border-radius:0; }
.veh-tabs .nav-link.active { color:#0ea5e9; border-bottom-color:#0ea5e9; background:transparent; font-weight:600; }
.veh-tabs .nav-link:hover:not(.active) { color:#0284c7; background:rgba(14,165,233,.06); }
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
.req { color:#dc3545; }
.veh-list-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.veh-list-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.select2-container .select2-selection--single { height:31px; border:1px solid #ced4da; border-radius:.375rem; }
.select2-container .select2-selection--single .select2-selection__rendered { line-height:29px; font-size:.875rem; }
.select2-container .select2-selection--single .select2-selection__arrow { height:29px; }
.img-preview-box { width:100%; height:180px; border:2px dashed #ced4da; border-radius:.5rem; display:flex; align-items:center; justify-content:center; cursor:pointer; background:#f9fafb; overflow:hidden; }
.img-preview-box img { max-width:100%; max-height:100%; object-fit:contain; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-truck me-2 text-info"></i> Vehicle List</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew">
        <i class="fa fa-plus me-1"></i> Add New Vehicle
    </button>
</div>

<div class="row g-3">
    {{-- ── Left: Form ── --}}
    <div class="col-lg-5">
        <div class="veh-panel">
            <div class="veh-panel-header" id="formPanelTitle">
                <i class="fa fa-plus me-2"></i> Add New Vehicle
            </div>

            {{-- Tabs --}}
            <ul class="nav veh-tabs border-bottom px-2 pt-1" style="background:#f0f8ff;">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tabEntry"><i class="fa fa-car me-1"></i> Product Entry</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabImage"><i class="fa fa-image me-1"></i> Upload Image</a>
                </li>
            </ul>

            <form id="vehicleForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="vehId">

                <div class="tab-content p-3">

                    {{-- ── Product Entry Tab ── --}}
                    <div class="tab-pane fade show active" id="tabEntry">
                        <div class="row g-2">

                            <div class="col-md-6">
                                <label class="form-label"><span class="req">*</span> Vehicle Number</label>
                                <input type="text" id="vehNumber" class="form-control form-control-sm" placeholder="e.g. DHA-1234-A">
                                <div class="invalid-feedback" id="vehNumberErr"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vehicle Name</label>
                                <input type="text" id="vehName" class="form-control form-control-sm" placeholder="e.g. Truck 01">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><span class="req">*</span> Vehicle Class</label>
                                <select id="vehClass" class="form-select form-select-sm">
                                    <option value="">--Select Item Group--</option>
                                    @foreach($vehicleClasses as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="vehClassErr"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea id="vehDesc" class="form-control form-control-sm" rows="1" placeholder="Short description..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><span class="req">*</span> Vehicle Type</label>
                                <select id="vehType" class="form-select form-select-sm">
                                    <option value="">--Select Vehicle Type--</option>
                                    @foreach($vehicleTypes as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="vehTypeErr"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Purchase Unit</label>
                                <select id="vehUnit" class="form-select form-select-sm">
                                    <option value="">--Select Issue Unit--</option>
                                    @foreach($purchaseUnits as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="vehUnitErr"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <select id="vehSupplier" class="form-select form-select-sm" style="width:100%">
                                    <option value="">Enter (Code or Name) minimum 3 chars</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="number" id="vehPrice" class="form-control form-control-sm" value="0" min="0" step="0.01">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select id="vehStatus" class="form-select form-select-sm">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <input type="text" id="vehRemarks" class="form-control form-control-sm">
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="vehAvailPO" checked>
                                        <label class="form-check-label fw-600" for="vehAvailPO" style="font-size:.82rem;font-weight:600">Availability In PO</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="vehAvailSO" checked>
                                        <label class="form-check-label fw-600" for="vehAvailSO" style="font-size:.82rem;font-weight:600">Availability In SO</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Upload Image Tab ── --}}
                    <div class="tab-pane fade" id="tabImage">
                        <div class="text-center mb-2">
                            <label class="form-label">Vehicle Image</label>
                            <div class="img-preview-box" id="imgPreviewBox" onclick="$('#vehImage').click()">
                                <div id="imgPlaceholder" class="text-muted text-center">
                                    <i class="fa fa-cloud-upload-alt fa-2x mb-2 d-block" style="color:#9ca3af"></i>
                                    <small>Click to upload image<br><span style="font-size:.7rem">JPG, PNG, GIF — max 2MB</span></small>
                                </div>
                                <img id="imgPreview" src="" style="display:none">
                            </div>
                            <input type="file" id="vehImage" name="image" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none" id="btnRemoveImg">
                                <i class="fa fa-times me-1"></i> Remove Image
                            </button>
                        </div>
                    </div>

                </div>

                <div class="px-3 pb-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm px-4" id="btnSave">
                        <i class="fa fa-save me-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnCancel">
                        <i class="fa fa-times me-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Right: List ── --}}
    <div class="col-lg-7">
        <div class="veh-panel">
            <div class="veh-panel-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-list me-2"></i> Vehicle List</span>
                <div class="d-flex gap-1">
                    <button onclick="$('#vehiclesTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-csv me-1"></i>CSV</button>
                    <button onclick="$('#vehiclesTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-excel me-1"></i>Excel</button>
                    <button onclick="$('#vehiclesTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div style="overflow-x:auto">
                <table id="vehiclesTable" class="table table-hover table-striped veh-list-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle No.</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var table;

$(function () {
    // DataTable
    table = $('#vehiclesTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-freights.vehicles.index') }}',
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px' },
            { data: 'vehicle_number', name: 'vehicle_number' },
            { data: 'vehicle_name',   name: 'vehicle_name' },
            { data: 'vehicle_class',  name: 'vehicle_class' },
            { data: 'vehicle_type',   name: 'vehicle_type' },
            { data: 'purchase_unit',  name: 'purchase_unit' },
            { data: 'status_badge',   name: 'status', orderable: false, searchable: false },
            { data: 'action',         name: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2 mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-truck fa-2x mb-2 d-block"></i>No vehicles yet.</div>' },
    });

    // Supplier Select2
    $('#vehSupplier').select2({
        placeholder: 'Enter Code or Name (min 3 chars)',
        minimumInputLength: 3,
        allowClear: true,
        ajax: {
            url: '{{ route('nas-freights.vehicles.suppliers-search') }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
        },
    });

    // Image preview
    $('#vehImage').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#imgPreview').attr('src', e.target.result).show();
            $('#imgPlaceholder').hide();
            $('#btnRemoveImg').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    $('#btnRemoveImg').on('click', function () {
        $('#vehImage').val('');
        $('#imgPreview').attr('src', '').hide();
        $('#imgPlaceholder').show();
        $(this).addClass('d-none');
    });

    function clearErrors() {
        $('#vehicleForm .is-invalid').removeClass('is-invalid');
        $('#vehNumberErr,#vehClassErr,#vehTypeErr,#vehUnitErr').text('');
    }

    function resetForm() {
        $('#vehId').val('');
        $('#vehNumber').val('').removeClass('is-invalid');
        $('#vehName,#vehDesc,#vehRemarks').val('');
        $('#vehClass,#vehType,#vehUnit,#vehStatus').val('').trigger('change');
        $('#vehClass').val('').trigger('change');
        $('#vehType').val('').trigger('change');
        $('#vehUnit').val('').trigger('change');
        $('#vehStatus').val('Active');
        $('#vehPrice').val('0');
        $('#vehAvailPO,#vehAvailSO').prop('checked', true);
        $('#vehSupplier').val(null).trigger('change');
        $('#btnRemoveImg').trigger('click');
        clearErrors();
        $('#formPanelTitle').html('<i class="fa fa-plus me-2"></i> Add New Vehicle');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
        $('[href="#tabEntry"]').tab('show');
    }

    $('#btnAddNew, #btnCancel').on('click', resetForm);

    // Edit
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('nas-freights/vehicles') }}/' + id, function (r) {
            resetForm();
            $('#vehId').val(r.id);
            $('#vehNumber').val(r.vehicle_number);
            $('#vehName').val(r.vehicle_name || '');
            $('#vehClass').val(r.vehicle_class || '');
            $('#vehType').val(r.vehicle_type || '');
            $('#vehUnit').val(r.purchase_unit || '');
            $('#vehStatus').val(r.status);
            $('#vehPrice').val(r.price);
            $('#vehDesc').val(r.description || '');
            $('#vehRemarks').val(r.remarks || '');
            $('#vehAvailPO').prop('checked', r.availability_in_po == 1);
            $('#vehAvailSO').prop('checked', r.availability_in_so == 1);

            if (r.supplier_id && r.supplier_name) {
                const option = new Option(r.supplier_name, r.supplier_id, true, true);
                $('#vehSupplier').append(option).trigger('change');
            }

            if (r.image) {
                $('#imgPreview').attr('src', '/storage/' + r.image).show();
                $('#imgPlaceholder').hide();
                $('#btnRemoveImg').removeClass('d-none');
            }

            $('#formPanelTitle').html('<i class="fa fa-edit me-2"></i> Edit Vehicle');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html, body').animate({ scrollTop: 0 }, 200);
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    // Save
    $('#vehicleForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;
        if (!$('#vehNumber').val().trim()) { $('#vehNumber').addClass('is-invalid'); $('#vehNumberErr').text('Vehicle number is required.'); valid = false; }
        if (!$('#vehClass').val())          { $('#vehClass').addClass('is-invalid');  $('#vehClassErr').text('Vehicle class is required.');  valid = false; }
        if (!$('#vehType').val())           { $('#vehType').addClass('is-invalid');   $('#vehTypeErr').text('Vehicle type is required.');    valid = false; }

        if (!valid) return;

        const id  = $('#vehId').val();
        const url = id ? '{{ url('nas-freights/vehicles') }}/' + id : '{{ route('nas-freights.vehicles.store') }}';

        const fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        if (id) fd.append('_method', 'PUT');
        fd.append('vehicle_number',     $('#vehNumber').val());
        fd.append('vehicle_name',       $('#vehName').val());
        fd.append('vehicle_class',      $('#vehClass').val());
        fd.append('vehicle_type',       $('#vehType').val());
        fd.append('purchase_unit',      $('#vehUnit').val());
        fd.append('supplier_id',        $('#vehSupplier').val() || '');
        fd.append('supplier_name',      $('#vehSupplier option:selected').text());
        fd.append('price',              $('#vehPrice').val());
        fd.append('description',        $('#vehDesc').val());
        fd.append('remarks',            $('#vehRemarks').val());
        fd.append('status',             $('#vehStatus').val());
        fd.append('availability_in_po', $('#vehAvailPO').is(':checked') ? '1' : '0');
        fd.append('availability_in_so', $('#vehAvailSO').is(':checked') ? '1' : '0');
        const imgFile = $('#vehImage')[0].files[0];
        if (imgFile) fd.append('image', imgFile);

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({ url, method: 'POST', data: fd, processData: false, contentType: false })
        .done(function (r) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            resetForm();
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.vehicle_number) { $('#vehNumber').addClass('is-invalid'); $('#vehNumberErr').text(errors.vehicle_number[0]); }
                if (errors.vehicle_class)  { $('#vehClass').addClass('is-invalid');  $('#vehClassErr').text(errors.vehicle_class[0]); }
                if (errors.vehicle_type)   { $('#vehType').addClass('is-invalid');   $('#vehTypeErr').text(errors.vehicle_type[0]); }
                if (errors.purchase_unit)  { $('#vehUnit').addClass('is-invalid');   $('#vehUnitErr').text(errors.purchase_unit[0]); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            const isEdit = $('#vehId').val();
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save'));
        });
    });
});
</script>
@endpush

@extends('chevron.layouts.app')

@section('title', 'Items')

@push('styles')
<style>
.item-section { border: 1px solid #e9ecef; border-radius: .4rem; overflow: hidden; margin-bottom: 1rem; }
.item-section-header { padding: .4rem .75rem; font-size: .78rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; display: flex; align-items: center; gap: .4rem; }
.item-section-header span { color: #374151; }
.item-section .row { padding: .25rem .75rem .75rem; }
.item-tabs .item-tab { font-size: .82rem; font-weight: 500; color: #6b7280; border: none; padding: .5rem 1.25rem; border-bottom: 3px solid transparent; }
.item-tabs .item-tab.active { color: #1565c0; border-bottom-color: #1565c0; background: transparent; font-weight: 600; }
.item-tabs .item-tab:hover:not(.active) { color: #1565c0; background: rgba(21,101,192,.05); }
#imagePreview { width: 100%; max-height: 220px; object-fit: contain; border: 2px dashed #dee2e6; border-radius: .5rem; padding: .5rem; background: #f8f9fa; display: none; }
#imagePlaceholder { border: 2px dashed #dee2e6; border-radius: .5rem; padding: 2rem; text-align: center; color: #adb5bd; background: #f8f9fa; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-boxes me-2 text-success"></i> Items</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#itemModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Item
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Items</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#itemsTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#itemsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#itemsTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#itemsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="itemsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Availability</th>
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
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border:none; border-radius:.75rem; overflow:hidden;">
            <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#1a2a6c 0%,#1565c0 60%,#1e88e5 100%); padding:1rem 1.25rem;">
                <div>
                    <h6 class="modal-title fw-700 mb-0" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Item</h6>
                    <small class="opacity-75">Fill in item details</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="itemForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" id="itemId">
                <div class="modal-body p-0">

                    <ul class="nav item-tabs border-bottom px-3 pt-2" id="itemTabs" style="background:#f0f4ff;">
                        <li class="nav-item">
                            <a class="nav-link item-tab active" data-bs-toggle="tab" href="#tabItemEntry">
                                <i class="fa fa-tag me-1"></i> Item Entry
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link item-tab" data-bs-toggle="tab" href="#tabUploadImage">
                                <i class="fa fa-image me-1"></i> Upload Image
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">

                        {{-- Item Entry Tab --}}
                        <div class="tab-pane fade show active" id="tabItemEntry">

                            {{-- Identity --}}
                            <div class="item-section">
                                <div class="item-section-header" style="background:#e8f4fd; border-left:4px solid #1565c0;">
                                    <i class="fa fa-barcode" style="color:#1565c0;"></i><span>Item Identity</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Item Code <span class="text-danger">*</span></label>
                                        <input type="text" id="itemCode" name="item_code" class="form-control form-control-sm" placeholder="e.g. ITEM-001" style="text-transform:uppercase">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Item Name</label>
                                        <input type="text" id="itemName" name="item_name" class="form-control form-control-sm" placeholder="Full item name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Purchase Unit <span class="text-danger">*</span></label>
                                        <select id="itemUnit" name="purchase_unit" class="form-select form-select-sm">
                                            <option value="">-- Select Unit --</option>
                                            @foreach($units as $group => $options)
                                                <optgroup label="{{ $group }}">
                                                    @foreach($options as $val => $label)
                                                        <option value="{{ $val }}">{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" id="itemSupplier" name="supplier" class="form-control form-control-sm" placeholder="Supplier code or name">
                                    </div>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="item-section">
                                <div class="item-section-header" style="background:#fef3e2; border-left:4px solid #f59e0b;">
                                    <i class="fa fa-align-left" style="color:#f59e0b;"></i><span>Details</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea id="itemDescription" name="description" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <input type="text" id="itemRemarks" name="remarks" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            {{-- Pricing & Settings --}}
                            <div class="item-section mb-0">
                                <div class="item-section-header" style="background:#e0f7f4; border-left:4px solid #14b8a6;">
                                    <i class="fa fa-dollar-sign" style="color:#14b8a6;"></i><span>Pricing &amp; Settings</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Item Price <span class="text-danger">*</span></label>
                                        <input type="number" id="itemPrice" name="item_price" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <select id="itemStatus" name="status" class="form-select form-select-sm">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end gap-3 pb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="itemAvailPO" name="availability_in_po" checked>
                                            <label class="form-check-label fw-500" for="itemAvailPO">Availability in PO</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="itemAvailSO" name="availability_in_so" checked>
                                            <label class="form-check-label fw-500" for="itemAvailSO">Availability in SO</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Upload Image Tab --}}
                        <div class="tab-pane fade" id="tabUploadImage">
                            <div class="item-section mb-0">
                                <div class="item-section-header" style="background:#f3e8ff; border-left:4px solid #7c3aed;">
                                    <i class="fa fa-image" style="color:#7c3aed;"></i><span>Item Image</span>
                                </div>
                                <div class="row g-3 pt-2 pb-2">
                                    <div class="col-12">
                                        <div id="imagePlaceholder">
                                            <i class="fa fa-cloud-upload-alt fa-3x mb-2 d-block"></i>
                                            <p class="mb-0 small">No image uploaded</p>
                                        </div>
                                        <img id="imagePreview" src="" alt="Item Image">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Choose Image <small class="text-muted">(JPG, PNG, WEBP — max 2MB)</small></label>
                                        <input type="file" id="itemImage" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                    <div class="col-12" id="removeImageWrap" style="display:none;">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveImage">
                                            <i class="fa fa-trash me-1"></i> Remove Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="background:#f0f4ff;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm px-4 text-white" id="btnSave" style="background:#1565c0; border-color:#1565c0;">
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
    table = $('#itemsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.settings.items.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'item_code',    name: 'item_code' },
            { data: 'item_name',    name: 'item_name' },
            { data: 'purchase_unit',name: 'purchase_unit' },
            { data: 'item_price',   name: 'item_price' },
            { data: 'po_so',        name: 'po_so', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv', text: 'CSV' }, { extend: 'excel', text: 'Excel' },
            { extend: 'pdf', text: 'PDF' }, { extend: 'print', text: 'Print' },
        ],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($input.length) $input.on('keyup change', () => this.search($input.val()).draw());
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No items yet.</div>' },
    });

    function resetForm() {
        $('#itemId').val('');
        $('#formMethod').val('POST');
        $('#itemCode').val('').removeClass('is-invalid').prop('readonly', false);
        $('#itemName, #itemSupplier, #itemDescription, #itemRemarks').val('');
        $('#itemUnit').val('');
        $('#itemPrice').val('0');
        $('#itemStatus').val('Active');
        $('#itemAvailPO, #itemAvailSO').prop('checked', true);
        $('#itemImage').val('');
        $('#imagePreview').hide().attr('src', '');
        $('#imagePlaceholder').show();
        $('#removeImageWrap').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#itemTabs a:first').tab('show');
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Item');
        resetForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Item');
        $.getJSON('{{ url('chevron/settings/items') }}/' + id, function (r) {
            resetForm();
            $('#itemId').val(r.id);
            $('#formMethod').val('PUT');
            $('#itemCode').val(r.item_code).prop('readonly', true);
            $('#itemName').val(r.item_name);
            $('#itemSupplier').val(r.supplier);
            $('#itemUnit').val(r.purchase_unit);
            $('#itemDescription').val(r.description);
            $('#itemRemarks').val(r.remarks);
            $('#itemPrice').val(r.item_price);
            $('#itemStatus').val(r.status);
            $('#itemAvailPO').prop('checked', r.availability_in_po);
            $('#itemAvailSO').prop('checked', r.availability_in_so);
            if (r.image) {
                $('#imagePreview').attr('src', '/storage/' + r.image).show();
                $('#imagePlaceholder').hide();
                $('#removeImageWrap').show();
            }
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#itemModal').modal('show');
        });
    });

    $('#itemImage').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#imagePreview').attr('src', e.target.result).show();
            $('#imagePlaceholder').hide();
            $('#removeImageWrap').show();
        };
        reader.readAsDataURL(file);
    });

    $('#btnRemoveImage').on('click', function () {
        $('#itemImage').val('');
        $('#imagePreview').hide().attr('src', '');
        $('#imagePlaceholder').show();
        $('#removeImageWrap').hide();
    });

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

    $('#itemForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        let valid = true;
        if (!$('#itemCode').val().trim()) { $('#itemCode').addClass('is-invalid').after('<div class="invalid-feedback">Item code is required.</div>'); $('#itemTabs a[href="#tabItemEntry"]').tab('show'); valid = false; }
        if (!$('#itemUnit').val())        { $('#itemUnit').addClass('is-invalid').after('<div class="invalid-feedback">Purchase unit is required.</div>'); valid = false; }
        if (!valid) return;

        const id  = $('#itemId').val();
        const url = id ? '{{ url('chevron/settings/items') }}/' + id : '{{ route('chevron.settings.items.store') }}';

        const fd = new FormData(this);
        fd.set('availability_in_po', $('#itemAvailPO').is(':checked') ? 1 : 0);
        fd.set('availability_in_so', $('#itemAvailSO').is(':checked') ? 1 : 0);
        fd.set('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
        })
        .done(function (r) {
            $('#itemModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.item_code)     { $('#itemCode').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.item_code[0] + '</div>'); $('#itemTabs a[href="#tabItemEntry"]').tab('show'); }
                if (errors.purchase_unit) { $('#itemUnit').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.purchase_unit[0] + '</div>'); }
                if (errors.item_price)    { $('#itemPrice').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.item_price[0] + '</div>'); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });

    $('#itemCode').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush

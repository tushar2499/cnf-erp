@extends('nas-freights.layouts.app')
@section('title', 'Expense Heads')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        #headTable th, #headTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
        #headTable thead tr:first-child th { background: #e9ecef; font-weight: 600; position: sticky; top: 0; z-index: 2; }
        #headTable thead tr:last-child th { background: #f8f9fa; font-weight: normal; position: sticky; z-index: 2; }
        #headTable thead tr:last-child th input.form-control { min-width: 100px; width: 100%; font-size: .78rem; padding: .3rem .5rem; }
        .head-table-wrapper { max-height: 65vh; overflow: auto; }
        .head-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
        .head-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
        .head-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #headTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }

        /* Select2 — match Bootstrap sm sizing */
        .select2-container .select2-selection--single { height: 31px; border-color: #dee2e6; border-radius: .25rem; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 29px; font-size: .875rem; padding-left: .5rem; color: #212529; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 29px; }
        .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #6c757d; }
        .select2-container--default.select2-container--focus .select2-selection--single { border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); }
        .select2-dropdown { border-color: #dee2e6; font-size: .875rem; }
        .select2-results__option { padding: .3rem .75rem; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #0d6efd; }
        .select2-is-invalid + .select2-container .select2-selection--single { border-color: #dc3545; }
    </style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-list-ul me-2 text-info"></i> Expense Heads</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#headModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Expense Head
    </button>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="fa fa-list me-2"></i> All Expense Heads</span>
    </div>
    <div class="card-body p-0">
        <div class="head-table-wrapper">
            <table id="headTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Type"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Category"></th>
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

<div class="modal fade" id="headModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Expense Head</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="headForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control form-control-sm" placeholder="e.g. Port Handling Fee">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="fieldType" class="form-select form-select-sm">
                                <option value="">-- Select Type --</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="typeError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select id="fieldCategory" class="form-select form-select-sm">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" id="fieldAmount" class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var table;

function initSelect2() {
    $('#fieldType').select2({
        dropdownParent: $('#headModal'),
        placeholder: '-- Select Type --',
        allowClear: true,
        width: '100%',
    });
    $('#fieldCategory').select2({
        dropdownParent: $('#headModal'),
        placeholder: '-- Select Category --',
        allowClear: true,
        width: '100%',
    });
}

function clearForm() {
    $('#recordId').val('');
    $('#fieldName').val('').removeClass('is-invalid');
    $('#fieldType').val(null).trigger('change').removeClass('is-invalid select2-is-invalid');
    $('#typeError').hide();
    $('#fieldCategory').val(null).trigger('change');
    $('#fieldAmount').val('');
    $('#fieldActive').prop('checked', true);
}

$(function () {
    initSelect2();

    $('#headModal').on('shown.bs.modal', function () {
        initSelect2();
    });

    table = $('#headTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        ajax: '{{ route('nas-freights.settings.expense-heads.index') }}',
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'name',          name: 'name' },
            { data: 'type',          name: 'type',           width: '160px' },
            { data: 'category_name', name: 'category_name',  orderable: false },
            { data: 'amount',        name: 'amount',         width: '110px', className: 'text-end', render: v => v ? parseFloat(v).toLocaleString('en-US', {minimumFractionDigits:2}) : '—' },
            { data: 'status_badge',  name: 'status_badge',   orderable: false, searchable: false, width: '80px', className: 'text-center' },
            { data: 'action',        name: 'action',         orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense heads yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#headTable thead tr:first-child').outerHeight();
            $('#headTable thead tr:last-child th').css('top', firstRowH + 'px');
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
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Expense Head');
        clearForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Expense Head');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldName').val(d.name);
        $('#fieldType').val(d.type).trigger('change');
        $('#fieldCategory').val(d.expense_category_id || null).trigger('change');
        $('#fieldAmount').val(d.amount || '');
        $('#fieldActive').prop('checked', d.status === 'Active');
        $('#headModal').modal('show');
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

    $('#headForm').on('submit', function (e) {
        e.preventDefault();
        var valid = true;
        if (!$('#fieldName').val().trim()) {
            $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.'); valid = false;
        } else { $('#fieldName').removeClass('is-invalid'); }
        if (!$('#fieldType').val()) {
            $('#fieldType').addClass('select2-is-invalid'); $('#typeError').text('Type is required.').show(); valid = false;
        } else { $('#fieldType').removeClass('select2-is-invalid'); $('#typeError').hide(); }
        if (!valid) { return; }

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/expense-heads') }}/' + id
            : '{{ route('nas-freights.settings.expense-heads.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:              $('meta[name="csrf-token"]').attr('content'),
            name:                $('#fieldName').val(),
            type:                $('#fieldType').val(),
            expense_category_id: $('#fieldCategory').val() || null,
            amount:              $('#fieldAmount').val() || null,
            status:              $('#fieldActive').is(':checked') ? 'Active' : 'Inactive',
        }})
        .done(r => {
            $('#headModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(xhr => {
            const errors = xhr.responseJSON?.errors;
            if (errors?.name) { $('#fieldName').addClass('is-invalid'); $('#nameError').text(errors.name[0]); }
            else if (errors?.type) { $('#fieldType').addClass('is-invalid'); $('#typeError').text(errors.type[0]); }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });
});
</script>
@endpush

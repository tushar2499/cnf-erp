@extends('chevron.layouts.app')

@section('title', 'Expense Heads')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-money-bill me-2 text-success"></i> Expense Heads</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#expenseHeadModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Expense Head
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Expense Heads</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#expenseHeadsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="expenseHeadsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Amount</th>
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
<div class="modal fade" id="expenseHeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Expense Head</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseHeadForm">
                @csrf
                <input type="hidden" id="headId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="headName" class="form-control" placeholder="e.g. Port Handling Fee">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select id="headCategory" class="form-select select2">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="headType" class="form-select">
                                <option value="">-- Select Type --</option>
                                <option value="External">External</option>
                                <option value="Internal">Internal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount</label>
                            <input type="number" id="headAmount" class="form-control" placeholder="0.00" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="headActive" checked>
                                <label class="form-check-label" for="headActive">Active</label>
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
    $('#headCategory').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Category --',
        allowClear: true,
        dropdownParent: $('#expenseHeadModal'),
    });

    table = $('#expenseHeadsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.settings.expense-heads.index') }}',
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name',          name: 'name' },
            { data: 'category_name', name: 'expenseCategory.name' },
            { data: 'type',          name: 'type' },
            { data: 'amount',        name: 'amount' },
            { data: 'status_badge',  name: 'is_active', searchable: false },
            { data: 'action',        name: 'action', orderable: false, searchable: false, width: '90px' },
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense heads yet.</div>' },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Expense Head');
        $('#headId').val('');
        $('#headName').val('').removeClass('is-invalid');
        $('#headCategory').val('').trigger('change').removeClass('is-invalid');
        $('#headType').val('').removeClass('is-invalid');
        $('#headAmount').val('').removeClass('is-invalid');
        $('#headActive').prop('checked', true);
        $('.invalid-feedback').remove();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Expense Head');
        $('#headId').val(d.id);
        $('#headName').val(d.name).removeClass('is-invalid');
        $('#headCategory').val(d.expense_category_id).trigger('change').removeClass('is-invalid');
        $('#headType').val(d.type).removeClass('is-invalid');
        $('#headAmount').val(d.amount).removeClass('is-invalid');
        $('#headActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#expenseHeadModal').modal('show');
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

    function showFieldError($field, msg) {
        $field.addClass('is-invalid');
        const $after = $field.hasClass('select2') ? $field.next('.select2-container') : $field;
        $after.after('<div class="invalid-feedback d-block">' + msg + '</div>');
    }

    $('#expenseHeadForm').on('submit', function (e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        let valid = true;
        if (!$('#headName').val().trim())    { showFieldError($('#headName'),     'Name is required.');     valid = false; }
        if (!$('#headCategory').val())       { showFieldError($('#headCategory'), 'Category is required.'); valid = false; }
        if (!$('#headType').val())           { showFieldError($('#headType'),     'Type is required.');     valid = false; }
        if (!valid) return;

        const id  = $('#headId').val();
        const url = id
            ? '{{ url('chevron/settings/expense-heads') }}/' + id
            : '{{ route('chevron.settings.expense-heads.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: id ? 'PUT' : 'POST',
            data: {
                _token:               $('meta[name="csrf-token"]').attr('content'),
                name:                 $('#headName').val(),
                expense_category_id:  $('#headCategory').val(),
                type:                 $('#headType').val(),
                amount:               $('#headAmount').val(),
                is_active:            $('#headActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#expenseHeadModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name)                { showFieldError($('#headName'),     errors.name[0]); }
                if (errors.expense_category_id) { showFieldError($('#headCategory'), errors.expense_category_id[0]); }
                if (errors.type)                { showFieldError($('#headType'),      errors.type[0]); }
                if (errors.amount)              { showFieldError($('#headAmount'),    errors.amount[0]); }
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

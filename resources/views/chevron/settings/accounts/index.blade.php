@extends('chevron.layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-university me-2 text-primary"></i> Account Numbers</h4>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Account
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Accounts</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#accountsTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#accountsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#accountsTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#accountsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="accountsTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account No</th>
                        <th>Account Name</th>
                        <th>Bank Name</th>
                        <th>Branch</th>
                        <th>Type</th>
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
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="accountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Account</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="accountForm">
                @csrf
                <input type="hidden" id="accountId">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Account No <span class="text-danger">*</span></label>
                            <input type="text" id="fldAccountNo" class="form-control form-control-sm" placeholder="e.g. 12345678901" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select id="fldAccountType" class="form-select form-select-sm">
                                @foreach($accountTypes as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" id="fldAccountName" class="form-control form-control-sm" placeholder="Account holder name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" id="fldBankName" class="form-control form-control-sm" placeholder="e.g. Dutch-Bangla Bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Name</label>
                            <input type="text" id="fldBranchName" class="form-control form-control-sm" placeholder="e.g. Agrabad Branch">
                        </div>
                        <div class="col-12 mt-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fldActive" checked>
                                <label class="form-check-label" for="fldActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
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
    table = $('#accountsTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('chevron.settings.accounts.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '45px' },
            { data: 'account_no',   name: 'account_no' },
            { data: 'account_name', name: 'account_name' },
            { data: 'bank_name',    name: 'bank_name' },
            { data: 'branch_name',  name: 'branch_name' },
            { data: 'account_type', name: 'account_type' },
            { data: 'status_badge', name: 'is_active', searchable: false, orderable: false },
            { data: 'action',       name: 'action',    orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No accounts yet.</div>' },
    });

    // Reset on Add
    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Account');
        $('#accountId').val('');
        $('#accountForm')[0].reset();
        $('#fldActive').prop('checked', true);
        $('#accountForm .is-invalid').removeClass('is-invalid');
        $('#accountForm .invalid-feedback').remove();
    });

    // Edit
    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Account');
        $('#accountId').val(d.id);
        $('#fldAccountNo').val(d.account_no);
        $('#fldAccountName').val(d.account_name);
        $('#fldBankName').val(d.bank_name);
        $('#fldBranchName').val(d.branch_name);
        $('#fldAccountType').val(d.account_type);
        $('#fldActive').prop('checked', d.is_active == 1);
        $('#accountForm .is-invalid').removeClass('is-invalid');
        $('#accountForm .invalid-feedback').remove();
        $('#accountModal').modal('show');
    });

    // Delete
    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(r => {
                if (r.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(d => { Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    // Save
    $('#accountForm').on('submit', function (e) {
        e.preventDefault();
        const id  = $('#accountId').val();
        const url = id
            ? '{{ url('chevron/settings/accounts') }}/' + id
            : '{{ route('chevron.settings.accounts.store') }}';

        $('#accountForm .is-invalid').removeClass('is-invalid');
        $('#accountForm .invalid-feedback').remove();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: id ? 'PUT' : 'POST',
            data: {
                _token:       $('meta[name="csrf-token"]').attr('content'),
                account_no:   $('#fldAccountNo').val(),
                account_name: $('#fldAccountName').val(),
                bank_name:    $('#fldBankName').val(),
                branch_name:  $('#fldBranchName').val(),
                account_type: $('#fldAccountType').val(),
                is_active:    $('#fldActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#accountModal').modal('hide');
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const map = { account_no: '#fldAccountNo', account_name: '#fldAccountName', bank_name: '#fldBankName', branch_name: '#fldBranchName' };
                $.each(xhr.responseJSON.errors, function (field, msgs) {
                    const $el = $(map[field]);
                    if ($el.length) $el.addClass('is-invalid').after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                });
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

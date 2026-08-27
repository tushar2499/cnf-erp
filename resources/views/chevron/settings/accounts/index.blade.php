@extends('chevron.layouts.app')

@section('title', 'Accounts')

@push('styles')
<style>
#accountsTable th, #accountsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#accountsTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#accountsTable thead tr:last-child th { background: #f8f9fa; }
#accountsTable thead tr:last-child th input.form-control { min-width: 72px; width: 100%; box-sizing: border-box; }
.accounts-table-wrapper { max-height: 65vh; overflow: auto; }
.accounts-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.accounts-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.accounts-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#accountsTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-university me-2 text-primary"></i> Account Numbers</h4>
    @if(auth()->user()->hasPermission('cnf.account.create'))
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Account
    </button>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Accounts</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#accountsTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="accounts-table-wrapper">
            <table id="accountsTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Account No</th>
                        <th>Account Name</th>
                        <th>Bank Name</th>
                        <th>Branch</th>
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
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        order: [],
        orderCellsTop: true,
        lengthMenu: [[15, 25, 50, 100, 200, 500], [15, 25, 50, 100, 200, 500]],
        ajax: '{{ route('chevron.settings.accounts.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '45px', className: 'text-center' },
            { data: 'account_type', name: 'account_type' },
            { data: 'account_no',   name: 'account_no' },
            { data: 'account_name', name: 'account_name' },
            { data: 'bank_name',    name: 'bank_name' },
            { data: 'branch_name',  name: 'branch_name' },
            { data: 'status_badge', name: 'is_active', searchable: false, orderable: false },
            { data: 'action',       name: 'action',    orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        initComplete: function () {
            const firstRowH = $('#accountsTable thead tr:first-child').outerHeight();
            $('#accountsTable thead tr:last-child th').css('top', firstRowH + 'px');

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

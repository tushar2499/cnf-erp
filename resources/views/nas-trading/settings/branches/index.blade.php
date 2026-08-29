@extends('nas-trading.layouts.app')
@section('title', 'Branches')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.dt-table th { font-size:.78rem; padding:.45rem .6rem; white-space: nowrap; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; white-space: nowrap; }
.dt-table thead tr:first-child th { position: sticky; top: 0; z-index: 2; }
.dt-table thead tr:last-child th { position: sticky; z-index: 2; background: #fff; }
.dt-table thead tr:last-child th input.form-control {
    min-width: 120px; width: 100%; box-sizing: border-box; font-size: .78rem; padding: .3rem .5rem;
}
.dt-scroll { max-height: 65vh; overflow: auto; }
.dt-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.dt-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
.dt-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#branchesTable_wrapper>.row:last-child {
    position: sticky; bottom: 0; background: #fff; z-index: 3;
    border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px;
}
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-code-branch me-2 text-info"></i> Branches</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#branchModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Branch
    </button>
</div>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list me-2"></i> All Branches</span>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:2px 8px;font-size:.72rem">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#branchesTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#branchesTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#branchesTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item" onclick="$('#branchesTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="dt-scroll">
        <table id="branchesTable" class="table table-hover table-striped table-bordered dt-table mb-0 w-100">
            <thead>
            <tr>
                <th>#</th><th>Name</th><th>Code</th><th>Address</th><th>Phone</th><th>Status</th><th>Action</th>
            </tr>
            <tr>
                <th></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Code"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Address"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Phone"></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Branch</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchForm">
                @csrf
                <input type="hidden" id="branchId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" id="branchName" class="form-control form-control-sm" placeholder="e.g. Chittagong Branch">
                            <div class="invalid-feedback" id="branchNameErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" id="branchCode" class="form-control form-control-sm" placeholder="e.g. CGP" maxlength="20">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="branchAddress" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <input type="text" id="branchPhone" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="branchActive" checked>
                                <label class="form-check-label" for="branchActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white" id="btnSave"><i class="fa fa-save me-1"></i> Save</button>
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
    table = $('#branchesTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [
            [10, 15, 25, 50, 100, 200],
            [10, 15, 25, 50, 100, 200]
        ],
        ajax: '{{ route('nas-trading.settings.branches.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'name',         name: 'name' },
            { data: 'code',         name: 'code',         width: '90px', className: 'text-center' },
            { data: 'address',      name: 'address' },
            { data: 'phone',        name: 'phone',        width: '120px' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'action',       name: 'action',       orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No branches yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#branchesTable thead tr:first-child').outerHeight();
            $('#branchesTable thead tr:last-child th').css('top', firstRowH + 'px');

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
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Branch');
        $('#branchId, #branchName, #branchCode, #branchAddress, #branchPhone').val('');
        $('#branchName').removeClass('is-invalid'); $('#branchNameErr').text('');
        $('#branchActive').prop('checked', true);
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Branch');
        $('#branchId').val(d.id); $('#branchName').val(d.name); $('#branchCode').val(d.code);
        $('#branchAddress').val(d.address); $('#branchPhone').val(d.phone);
        $('#branchName').removeClass('is-invalid'); $('#branchNameErr').text('');
        $('#branchActive').prop('checked', d.is_active == 1);
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
        $('#branchModal').modal('show');
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

    $('#branchForm').on('submit', function (e) {
        e.preventDefault();
        $('#branchName').removeClass('is-invalid'); $('#branchNameErr').text('');
        if (!$('#branchName').val().trim()) { $('#branchName').addClass('is-invalid'); $('#branchNameErr').text('Branch name is required.'); return; }
        var id = $('#branchId').val();
        var url = id ? '{{ url('nas-trading/settings/branches') }}/' + id : '{{ route('nas-trading.settings.branches.store') }}';
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#branchName').val(), code: $('#branchCode').val(),
            address: $('#branchAddress').val(), phone: $('#branchPhone').val(),
            is_active: $('#branchActive').is(':checked') ? 1 : 0,
        }})
        .done(r => { $('#branchModal').modal('hide'); Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON.errors.name) { $('#branchName').addClass('is-invalid'); $('#branchNameErr').text(xhr.responseJSON.errors.name[0]); }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + ($('#branchId').val() ? 'Update' : 'Save')));
    });

    $('#branchCode').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush
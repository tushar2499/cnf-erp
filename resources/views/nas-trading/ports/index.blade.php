@extends('nas-trading.layouts.app')
@section('title', 'Ports')
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
#portTable_wrapper>.row:last-child {
    position: sticky; bottom: 0; background: #fff; z-index: 3;
    border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px;
}
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-anchor me-2 text-info"></i> Ports</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#portModal" id="btnAddNew">
        <i class="fa fa-plus me-1"></i> Add New
    </button>
</div>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list me-2"></i> Port List</span>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:2px 8px;font-size:.72rem">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#portTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#portTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#portTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item" onclick="$('#portTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="dt-scroll">
        <table id="portTable" class="table table-hover table-striped table-bordered dt-table mb-0 w-100">
            <thead>
            <tr>
                <th>#</th><th>Name</th><th>Country</th><th>Type</th><th>Status</th><th>Action</th>
            </tr>
            <tr>
                <th></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Country"></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="portModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="portModalTitle"><i class="fa fa-plus me-2"></i> Add New Port</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="portForm">
                @csrf
                <input type="hidden" id="recId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Port Name <span class="text-danger">*</span></label>
                            <input type="text" id="fName" class="form-control form-control-sm">
                            <div class="invalid-feedback" id="fNameErr"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" id="fCountry" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="fType" class="form-select form-select-sm">
                                <option value="Sea">Sea</option>
                                <option value="Air">Air</option>
                                <option value="Land">Land</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="fStatus" class="form-select form-select-sm">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white px-4" id="btnSave"><i class="fa fa-save me-1"></i> Save</button>
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
    table = $('#portTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [
            [10, 15, 25, 50, 100, 200],
            [10, 15, 25, 50, 100, 200]
        ],
        ajax: '{{ route('nas-trading.ports.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'name',         name: 'name' },
            { data: 'country',      name: 'country' },
            { data: 'type_badge',   name: 'type_badge',   orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'action',       name: 'action',       orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No ports yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#portTable thead tr:first-child').outerHeight();
            $('#portTable thead tr:last-child th').css('top', firstRowH + 'px');

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

    function resetForm() {
        $('#recId').val('');
        $('#fName,#fCountry').val('');
        $('#fName').removeClass('is-invalid'); $('#fNameErr').text('');
        $('#fType').val('Sea'); $('#fStatus').val('Active');
        $('#portModalTitle').html('<i class="fa fa-plus me-2"></i> Add New Port');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }

    $('#btnAddNew').on('click', resetForm);

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('nas-trading/ports') }}/' + id, function (r) {
            resetForm();
            $('#recId').val(r.id); $('#fName').val(r.name); $('#fCountry').val(r.country || ''); $('#fType').val(r.type); $('#fStatus').val(r.status);
            $('#portModalTitle').html('<i class="fa fa-edit me-2"></i> Edit Port');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('#portModal').modal('show');
        });
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

    $('#portForm').on('submit', function (e) {
        e.preventDefault();
        $('#fName').removeClass('is-invalid'); $('#fNameErr').text('');
        if (!$('#fName').val().trim()) { $('#fName').addClass('is-invalid'); $('#fNameErr').text('Name is required.'); return; }
        const id = $('#recId').val();
        const url = id ? '{{ url('nas-trading/ports') }}/' + id : '{{ route('nas-trading.ports.store') }}';
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#fName').val(), country: $('#fCountry').val(), type: $('#fType').val(), status: $('#fStatus').val(),
        }})
            .done(function (r) {
                $('#portModal').modal('hide');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
                resetForm(); table.ajax.reload();
            })
            .fail(function (xhr) {
                if (xhr.status === 422) { const e = xhr.responseJSON.errors; if (e.name) { $('#fName').addClass('is-invalid'); $('#fNameErr').text(e.name[0]); } }
                else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            })
            .always(() => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + ($('#recId').val() ? 'Update' : 'Save')); });
    });
});
</script>
@endpush
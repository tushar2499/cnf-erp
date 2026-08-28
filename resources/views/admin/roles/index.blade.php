@extends('admin.layouts.app')

@section('title', 'Roles')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-shield me-2 text-primary"></i> Roles</h4>
    @if(auth()->user()->hasPermission('admin.roles.create'))
    <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus me-1"></i> Add Role
    </a>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Roles</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#rolesTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#rolesTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#rolesTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#rolesTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="roles-table-wrapper">
            <table id="rolesTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Role Name</th>
                        <th>Companies</th>
                        <th style="width:100px">Permissions</th>
                        <th style="width:100px">Action</th>
                    </tr>
                    <tr>
                        <th></th>
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
@endsection

@push('styles')
<style>
#rolesTable th, #rolesTable td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
#rolesTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#rolesTable thead tr:last-child th { background: #f8f9fa; }
#rolesTable thead tr:last-child th input.form-control { min-width: 80px; width: 100%; box-sizing: border-box; }
.roles-table-wrapper { max-height: 65vh; overflow: auto; }
.roles-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.roles-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.roles-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#rolesTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var table = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        order: [],
        orderCellsTop: true,
        pageLength: 25,
        lengthMenu: [[15, 25, 50, 100, 200, 500], [15, 25, 50, 100, 200, 500]],
        ajax: '{{ route('admin.roles.index') }}',
        columns: [
            { data: 'DT_RowIndex',       name: 'DT_RowIndex',      orderable: false, searchable: false, className: 'text-center' },
            { data: 'name',              name: 'name' },
            { data: 'companies_badges',  name: 'companies_badges', orderable: false },
            { data: 'permissions_count', name: 'permissions_count', searchable: false, className: 'text-center' },
            { data: 'action',            name: 'action',            orderable: false, searchable: false, className: 'text-center' },
        ],
        dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        initComplete: function () {
            var self = this.api();
            const firstRowH = $('#rolesTable thead tr:first-child').outerHeight();
            $('#rolesTable thead tr:last-child th').css('top', firstRowH + 'px');
            self.columns().every(function (i) {
                var col = this;
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($input.length) {
                    $input.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $input.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($input.val()).draw(); }, 400);
                    });
                }
            });
        },
        language: {
            emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-user-shield fa-2x mb-2 d-block"></i>No roles yet.</div>'
        },
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({
            title: 'Delete "' + name + '"?',
            text: 'Users assigned this role will lose it.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete',
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') }
                })
                .done(function (d) {
                    Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload();
                })
                .fail(function () {
                    Swal.fire({ icon: 'error', title: 'Delete failed.' });
                });
            }
        });
    });
});
</script>
@endpush

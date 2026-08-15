{{-- Shared user management panel. Requires $routePrefix, $urlPrefix variables. --}}

@push('styles')
<style>
.usr-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.usr-panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; display:flex; align-items:center; justify-content:space-between; }
.usr-list-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.usr-list-table th { font-size:.78rem; padding:.45rem .6rem; }
.usr-list-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-users-cog me-2 text-info"></i> Users</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-info text-white" target="_blank">
        <i class="fa fa-plus me-1"></i> Add User (Admin Panel)
    </a>
</div>

<div class="usr-panel">
    <div class="usr-panel-header">
        <span><i class="fa fa-list me-2"></i> User List</span>
        <span class="badge bg-secondary" style="font-size:.7rem; font-weight:500;">
            <i class="fa fa-lock me-1"></i> Create &amp; edit users in Admin Panel
        </span>
    </div>
    <div style="overflow-x:auto">
        <table id="usersTable" class="table table-hover table-striped usr-list-table mb-0 w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
var usersTable;
var indexRoute = '{{ route($routePrefix . '.users.index') }}';
var baseUrl    = '{{ url($urlPrefix . '/users') }}';

$(function () {
    usersTable = $('#usersTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        ajax: indexRoute,
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'name',         name: 'name' },
            { data: 'username',     name: 'username' },
            { data: 'email',        name: 'email', defaultContent: '—' },
            { data: 'employee_name',name: 'employee_name', orderable: false, searchable: false },
            { data: 'role_badge',   name: 'role', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2 mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-users fa-2x mb-2 d-block"></i>No users yet.</div>' },
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Remove "' + name + '" from this company?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, remove' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); usersTable.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
                }
            });
    });
});
</script>
@endpush

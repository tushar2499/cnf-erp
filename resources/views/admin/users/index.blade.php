@extends('admin.layouts.app')

@section('title', 'Admin Users')

@section('content')

<div class="page-header">
    <h4><i class="fa fa-users me-2 text-success"></i> Admin Users</h4>
    @if(auth()->user()->hasPermission('admin.users.create'))
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
        <i class="fa fa-plus me-1"></i> Add New User
    </a>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header"><i class="fa fa-list me-2"></i> All Users</div>
    <div class="card-body p-0">
        <table id="usersTable" class="table table-hover mb-0 w-100">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Companies</th>
                    <th style="width:80px">Super</th>
                    <th style="width:80px">Status</th>
                    <th style="width:100px">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $('#usersTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        ajax: '{{ route('admin.users.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name',             name: 'name' },
            { data: 'username',         name: 'username' },
            { data: 'email',            name: 'email', defaultContent: '—' },
            { data: 'employee_badge',   name: 'employee', orderable: false, searchable: false },
            { data: 'role_badge',       name: 'role', orderable: false, searchable: false },
            { data: 'companies_badges', name: 'companies', orderable: false, searchable: false },
            { data: 'super_badge',      name: 'is_super',  orderable: false, searchable: false },
            { data: 'status_badge',     name: 'is_active', orderable: false, searchable: false },
            { data: 'action',           name: 'action',    orderable: false, searchable: false },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 25,
        language: {
            emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-users fa-2x mb-2 d-block opacity-25"></i>No users found.</div>',
        },
    });

    $(document).on('click', '.btn-delete', function () {
        const url  = $(this).data('url');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete "' + name + '"?',
            text: 'This will permanently delete the user and all company access.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete',
        }).then(res => {
            if (!res.isConfirmed) { return; }
            $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r => {
                    Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false });
                    $('#usersTable').DataTable().ajax.reload();
                })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
        });
    });
});
</script>
@endpush

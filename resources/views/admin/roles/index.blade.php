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
    <div class="card-header"><i class="fa fa-list me-2"></i> All Roles</div>
    <div class="card-body p-0">
        <div style="overflow-x:auto">
            <table id="rolesTable" class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Role Name</th>
                        <th>Companies</th>
                        <th style="width:100px">Permissions</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: '{{ route('admin.roles.index') }}',
        columns: [
            { data: 'DT_RowIndex',       name: 'DT_RowIndex',      orderable: false, searchable: false },
            { data: 'name',              name: 'name' },
            { data: 'companies_badges',  name: 'companies_badges',  orderable: false, searchable: false },
            { data: 'permissions_count', name: 'permissions_count', searchable: false, className: 'text-center' },
            { data: 'action',            name: 'action',            orderable: false, searchable: false },
        ],
        dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 25,
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

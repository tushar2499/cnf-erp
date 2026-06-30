{{-- Shared user management panel. Requires $routePrefix, $urlPrefix, $employees variables. --}}

@push('styles')
<style>
.usr-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.usr-panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.usr-list-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.usr-list-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-users-cog me-2 text-info"></i> Users</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew">
        <i class="fa fa-plus me-1"></i> Add New User
    </button>
</div>

<div class="row g-3">
    {{-- Left: Form --}}
    <div class="col-md-4">
        <div class="usr-panel">
            <div class="usr-panel-header" id="formPanelTitle">
                <i class="fa fa-plus me-2"></i> Add New User
            </div>
            <div class="p-3">
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="userId">

                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="uName" class="form-control form-control-sm" placeholder="Full name">
                        <div class="invalid-feedback" id="uNameErr"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="uEmail" class="form-control form-control-sm" placeholder="email@example.com">
                        <div class="invalid-feedback" id="uEmailErr"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" id="passLabel">Password <span class="text-danger" id="passRequired">*</span></label>
                        <input type="password" id="uPassword" class="form-control form-control-sm" placeholder="Min 6 characters">
                        <div class="invalid-feedback" id="uPasswordErr"></div>
                        <div class="form-text text-muted" id="passHint" style="display:none;font-size:.75rem">Leave blank to keep current password.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Link to Employee</label>
                        <select id="uEmployeeId" class="form-select form-select-sm">
                            <option value="">— None —</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->emp_code }} — {{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select id="uRole" class="form-select form-select-sm">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-2" id="statusRow" style="display:none">
                        <label class="form-label">Account Status</label>
                        <select id="uIsActive" class="form-select form-select-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3" id="companyStatusRow" style="display:none">
                        <label class="form-label">Access to This Company</label>
                        <select id="uCompanyActive" class="form-select form-select-sm">
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm px-4" id="btnSave">
                            <i class="fa fa-save me-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnCancel">
                            <i class="fa fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: List --}}
    <div class="col-md-8">
        <div class="usr-panel">
            <div class="usr-panel-header">
                <span><i class="fa fa-list me-2"></i> User List</span>
            </div>
            <div style="overflow-x:auto">
                <table id="usersTable" class="table table-hover table-striped usr-list-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
var usersTable;
var indexRoute = '{{ route($routePrefix . '.users.index') }}';
var baseUrl    = '{{ url($urlPrefix . '/users') }}';

$(function () {
    $('#uEmployeeId').select2({
        theme: 'bootstrap-5',
        placeholder: '— None —',
        allowClear: true,
        width: '100%',
    });

    usersTable = $('#usersTable').DataTable({
        processing: true, serverSide: true,
        ajax: indexRoute,
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'name',         name: 'name' },
            { data: 'email',        name: 'email' },
            { data: 'employee_name',name: 'employee_name', orderable: false, searchable: false },
            { data: 'role_badge',   name: 'role', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2 mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-users fa-2x mb-2 d-block"></i>No users yet.</div>' },
    });

    function resetForm() {
        $('#userId').val('');
        $('#uName').val('').removeClass('is-invalid'); $('#uNameErr').text('');
        $('#uEmail').val('').removeClass('is-invalid'); $('#uEmailErr').text('');
        $('#uPassword').val('').removeClass('is-invalid'); $('#uPasswordErr').text('');
        $('#uEmployeeId').val('').trigger('change');
        $('#uRole').val('user');
        $('#uIsActive').val('1');
        $('#uCompanyActive').val('1');
        $('#statusRow, #companyStatusRow').hide();
        $('#passHint').hide();
        $('#passRequired').show();
        $('#formPanelTitle').html('<i class="fa fa-plus me-2"></i> Add New User');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }

    $('#btnAddNew, #btnCancel').on('click', resetForm);

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON(baseUrl + '/' + id, function (r) {
            resetForm();
            $('#userId').val(r.id);
            $('#uName').val(r.name);
            $('#uEmail').val(r.email);
            $('#uEmployeeId').val(r.employee_id || '').trigger('change');
            $('#uRole').val(r.role);
            $('#uIsActive').val(r.is_active ? '1' : '0');
            $('#uCompanyActive').val(r.company_active ? '1' : '0');
            $('#statusRow, #companyStatusRow').show();
            $('#passHint').show();
            $('#passRequired').hide();
            $('#formPanelTitle').html('<i class="fa fa-edit me-2"></i> Edit User');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html, body').animate({ scrollTop: 0 }, 200);
        });
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

    $('#userForm').on('submit', function (e) {
        e.preventDefault();

        $('#uName,#uEmail,#uPassword').removeClass('is-invalid');
        $('#uNameErr,#uEmailErr,#uPasswordErr').text('');

        const id  = $('#userId').val();
        const url = id ? baseUrl + '/' + id : baseUrl;

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        const data = {
            _token:         $('meta[name="csrf-token"]').attr('content'),
            name:           $('#uName').val(),
            email:          $('#uEmail').val(),
            password:       $('#uPassword').val(),
            employee_id:    $('#uEmployeeId').val(),
            role:           $('#uRole').val(),
            is_active:      $('#uIsActive').val(),
            company_active: $('#uCompanyActive').val(),
        };
        if (id) data._method = 'PUT';

        $.ajax({ url, method: 'POST', data })
        .done(function (r) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            resetForm();
            usersTable.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors || {};
                if (errors.name)     { $('#uName').addClass('is-invalid');     $('#uNameErr').text(errors.name[0]); }
                if (errors.email)    { $('#uEmail').addClass('is-invalid');    $('#uEmailErr').text(errors.email[0]); }
                if (errors.password) { $('#uPassword').addClass('is-invalid'); $('#uPasswordErr').text(errors.password[0]); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            const isEdit = $('#userId').val();
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i>' + (isEdit ? ' Update' : ' Save'));
        });
    });
});
</script>
@endpush

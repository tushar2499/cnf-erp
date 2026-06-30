@extends('nas-freights.layouts.app')

@section('title', 'Employees')

@push('styles')
<style>
.emp-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.emp-panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.emp-list-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.emp-list-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-tie me-2 text-info"></i> Employees</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew">
        <i class="fa fa-plus me-1"></i> Add New Employee
    </button>
</div>

<div class="row g-3">
    {{-- ── Left: Form ── --}}
    <div class="col-md-4">
        <div class="emp-panel">
            <div class="emp-panel-header" id="formPanelTitle">
                <i class="fa fa-plus me-2"></i> Add New Employee
            </div>
            <div class="p-3">
                <form id="employeeForm">
                    @csrf
                    <input type="hidden" id="empId">

                    <div class="mb-2">
                        <label class="form-label">Employee ID</label>
                        <input type="text" id="empCode" class="form-control form-control-sm bg-light fw-bold" readonly placeholder="Auto-generated">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="empName" class="form-control form-control-sm" placeholder="Full name">
                        <div class="invalid-feedback" id="empNameErr"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Designation</label>
                        <input type="text" id="empDesignation" class="form-control form-control-sm" placeholder="e.g. Manager, Driver">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" id="empPhone" class="form-control form-control-sm" placeholder="+880...">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" id="empEmail" class="form-control form-control-sm" placeholder="email@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="empStatus" class="form-select form-select-sm">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
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

    {{-- ── Right: List ── --}}
    <div class="col-md-8">
        <div class="emp-panel">
            <div class="emp-panel-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-list me-2"></i> Employee List</span>
                <div class="d-flex gap-1">
                    <button onclick="$('#employeesTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-csv me-1"></i>CSV</button>
                    <button onclick="$('#employeesTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-excel me-1"></i>Excel</button>
                    <button onclick="$('#employeesTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div style="overflow-x:auto">
                <table id="employeesTable" class="table table-hover table-striped emp-list-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Phone</th>
                            <th>Email</th>
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
var table;

$(function () {
    table = $('#employeesTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-freights.employees.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '45px' },
            { data: 'code',         name: 'code' },
            { data: 'name',         name: 'name' },
            { data: 'designation',  name: 'designation' },
            { data: 'phone',        name: 'phone' },
            { data: 'email',        name: 'email' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2 mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-users fa-2x mb-2 d-block"></i>No employees yet.</div>' },
    });

    function resetForm() {
        $('#empId').val('');
        $('#empCode').val('');
        $('#empName').val('').removeClass('is-invalid');
        $('#empDesignation,#empPhone,#empEmail').val('');
        $('#empStatus').val('Active');
        $('#empNameErr').text('');
        $('#formPanelTitle').html('<i class="fa fa-plus me-2"></i> Add New Employee');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }

    $('#btnAddNew, #btnCancel').on('click', resetForm);

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('nas-freights/employees') }}/' + id, function (r) {
            resetForm();
            $('#empId').val(r.id);
            $('#empCode').val(r.code);
            $('#empName').val(r.name);
            $('#empDesignation').val(r.designation || '');
            $('#empPhone').val(r.phone || '');
            $('#empEmail').val(r.email || '');
            $('#empStatus').val(r.status);
            $('#formPanelTitle').html('<i class="fa fa-edit me-2"></i> Edit Employee');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html, body').animate({ scrollTop: 0 }, 200);
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

    $('#employeeForm').on('submit', function (e) {
        e.preventDefault();
        $('#empName').removeClass('is-invalid');
        $('#empNameErr').text('');

        if (!$('#empName').val().trim()) {
            $('#empName').addClass('is-invalid');
            $('#empNameErr').text('Name is required.');
            return;
        }

        const id  = $('#empId').val();
        const url = id
            ? '{{ url('nas-freights/employees') }}/' + id
            : '{{ route('nas-freights.employees.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: id ? 'PUT' : 'POST',
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                name:        $('#empName').val(),
                designation: $('#empDesignation').val(),
                phone:       $('#empPhone').val(),
                email:       $('#empEmail').val(),
                status:      $('#empStatus').val(),
            },
        })
        .done(function (r) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            resetForm();
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name) { $('#empName').addClass('is-invalid'); $('#empNameErr').text(errors.name[0]); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            const isEdit = $('#empId').val();
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save'));
        });
    });
});
</script>
@endpush

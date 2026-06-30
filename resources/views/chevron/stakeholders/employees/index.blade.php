@extends('chevron.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-tie me-2 text-success"></i> Employees</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Employee
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Employees</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#employeesTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#employeesTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#employeesTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#employeesTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="employeesTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Branch</th>
                        <th>Joining Date</th>
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
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Employee</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="employeeForm">
                @csrf
                <input type="hidden" id="employeeId">
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Prefix + ID --}}
                        <div class="col-md-4">
                            <label class="form-label">Employee Prefix <span class="text-danger">*</span></label>
                            <select id="empPrefix" class="form-select">
                                <option value="EMP-">EMP- :- Employee Prefix</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="empEmployeeId" class="form-control" readonly>
                                <button type="button" class="btn btn-success btn-sm" id="btnGenId" title="Generate ID">
                                    <i class="fa fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Name --}}
                        <div class="col-12">
                            <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                            <input type="text" id="empName" class="form-control" placeholder="Full name">
                        </div>

                        {{-- Designation + Joining Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Designation <span class="text-danger">*</span></label>
                            <select id="empDesignation" class="form-select select2-designation">
                                <option value="">-- Select Designation --</option>
                                @foreach($designations as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" id="empJoiningDate" class="form-control">
                        </div>

                        {{-- Short Name --}}
                        <div class="col-md-4">
                            <label class="form-label">Short Name</label>
                            <input type="text" id="empShortName" class="form-control" placeholder="e.g. John">
                        </div>

                        {{-- Father / Mother --}}
                        <div class="col-md-4">
                            <label class="form-label">Father's Name</label>
                            <input type="text" id="empFatherName" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" id="empMotherName" class="form-control">
                        </div>

                        {{-- Status + Branch --}}
                        <div class="col-md-4">
                            <label class="form-label">Current Status</label>
                            <select id="empCurrentStatus" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Branch</label>
                            <select id="empBranch" class="form-select select2-branch">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empActive" checked>
                                <label class="form-check-label" for="empActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="btnSave">
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
    $('.select2-designation').select2({ theme: 'bootstrap-5', placeholder: '-- Select Designation --', allowClear: true, dropdownParent: $('#employeeModal') });
    $('.select2-branch').select2({ theme: 'bootstrap-5', placeholder: '-- Select Branch --', allowClear: true, dropdownParent: $('#employeeModal') });

    table = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.stakeholders.employees.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'employee_id',      name: 'employee_id' },
            { data: 'name',             name: 'name' },
            { data: 'designation_name', name: 'designation.name' },
            { data: 'branch_name',      name: 'branch.name' },
            { data: 'joining_date',     name: 'joining_date' },
            { data: 'status_badge',     name: 'current_status', searchable: false },
            { data: 'action',           name: 'action', orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv',   text: 'CSV' },
            { extend: 'excel', text: 'Excel' },
            { extend: 'pdf',   text: 'PDF' },
            { extend: 'print', text: 'Print' },
        ],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($input.length) {
                    $input.on('keyup change', () => this.search($input.val()).draw());
                }
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No employees yet.</div>' },
    });

    function generateId() {
        const prefix = $('#empPrefix').val();
        $.getJSON('{{ route('chevron.stakeholders.employees.next-id') }}', { prefix }, function (r) {
            $('#empEmployeeId').val(r.employee_id);
        });
    }

    $('#empPrefix').on('change', function () { if (!$('#employeeId').val()) generateId(); });
    $('#btnGenId').on('click', generateId);

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Employee');
        $('#employeeId').val('');
        $('#empPrefix').val('EMP-').trigger('change');
        $('#empName').val('').removeClass('is-invalid');
        $('#empDesignation').val('').trigger('change').removeClass('is-invalid');
        $('#empJoiningDate').val('').removeClass('is-invalid');
        $('#empShortName, #empFatherName, #empMotherName').val('');
        $('#empCurrentStatus').val('Active');
        $('#empBranch').val('').trigger('change');
        $('#empActive').prop('checked', true);
        $('.invalid-feedback').remove();
        generateId();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Employee');
        $('#employeeId').val(d.id);
        $('#empPrefix').val(d.employee_prefix);
        $('#empEmployeeId').val(d.employee_id);
        $('#empName').val(d.name).removeClass('is-invalid');
        $('#empDesignation').val(d.designation_id).trigger('change').removeClass('is-invalid');
        $('#empJoiningDate').val(d.joining_date).removeClass('is-invalid');
        $('#empShortName').val(d.short_name);
        $('#empFatherName').val(d.father_name);
        $('#empMotherName').val(d.mother_name);
        $('#empCurrentStatus').val(d.current_status);
        $('#empBranch').val(d.branch_id || '').trigger('change');
        $('#empActive').prop('checked', d.is_active == 1);
        $('.invalid-feedback').remove();
        $('#employeeModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url  = $(this).data('url');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Delete "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete',
        }).then(function (res) {
            if (res.isConfirmed) {
                $.ajax({ url: url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                    .done(function (r) {
                        Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload();
                    })
                    .fail(function () { Swal.fire({ icon: 'error', title: 'Delete failed.' }); });
            }
        });
    });

    function showErr($el, msg) {
        $el.addClass('is-invalid');
        const $after = $el.hasClass('select2-designation') || $el.hasClass('select2-branch')
            ? $el.next('.select2-container') : $el;
        $after.after('<div class="invalid-feedback d-block">' + msg + '</div>');
    }

    $('#employeeForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        let valid = true;
        if (!$('#empName').val().trim())       { showErr($('#empName'),        'Name is required.');        valid = false; }
        if (!$('#empDesignation').val())        { showErr($('#empDesignation'), 'Designation is required.'); valid = false; }
        if (!$('#empJoiningDate').val())        { showErr($('#empJoiningDate'), 'Joining date is required.'); valid = false; }
        if (!valid) return;

        const id  = $('#employeeId').val();
        const url = id
            ? '{{ url('chevron/stakeholders/employees') }}/' + id
            : '{{ route('chevron.stakeholders.employees.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: id ? 'PUT' : 'POST',
            data: {
                _token:           $('meta[name="csrf-token"]').attr('content'),
                employee_prefix:  $('#empPrefix').val(),
                name:             $('#empName').val(),
                designation_id:   $('#empDesignation').val(),
                joining_date:     $('#empJoiningDate').val(),
                short_name:       $('#empShortName').val(),
                father_name:      $('#empFatherName').val(),
                mother_name:      $('#empMotherName').val(),
                current_status:   $('#empCurrentStatus').val(),
                branch_id:        $('#empBranch').val(),
                is_active:        $('#empActive').is(':checked') ? 1 : 0,
            },
        })
        .done(function (r) {
            $('#employeeModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const e = xhr.responseJSON.errors;
                if (e.name)           showErr($('#empName'),        e.name[0]);
                if (e.designation_id) showErr($('#empDesignation'), e.designation_id[0]);
                if (e.joining_date)   showErr($('#empJoiningDate'), e.joining_date[0]);
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

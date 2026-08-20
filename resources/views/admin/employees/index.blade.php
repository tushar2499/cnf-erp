@extends('admin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-tie me-2 text-success"></i> Employees</h4>
    @if(auth()->user()->hasPermission('admin.employees.create'))
    <button class="btn btn-sm btn-success" id="btnAdd" data-bs-toggle="modal" data-bs-target="#employeeModal">
        <i class="fa fa-plus me-1"></i> Add Employee
    </button>
    @endif
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Team Leader</th>
                        <th>Designation</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th>
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

{{-- Employee Create / Edit Modal --}}
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="employeeModalLabel"><i class="fa fa-plus me-2"></i>Add Employee</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="employeeForm">
                @csrf
                <input type="hidden" id="empId">
                <div class="modal-body p-3" style="background:#F8FAFC;">
                    <div class="d-flex flex-column gap-2">

                        {{-- Identity --}}
                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-id-badge"></i> Employee Identity</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Employee Code</label>
                                    <input type="text" id="empCode" class="form-control form-control-sm" placeholder="e.g. EMP-000001">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Short Name</label>
                                    <input type="text" id="empShortName" class="form-control form-control-sm" placeholder="e.g. Himal">
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="empName" class="form-control form-control-sm" placeholder="e.g. John Doe">
                                </div>
                            </div>
                        </div>

                        {{-- Role & Assignment --}}
                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-sitemap"></i> Role & Assignment</div>
                            <div class="d-flex gap-2 mb-2">
                                <label class="emp-type-card active flex-fill" for="typeTeamLeader">
                                    <input type="radio" id="typeTeamLeader" name="empTypeRadio" value="team_leader" class="d-none" checked>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="type-icon"><i class="fa fa-user-tie"></i></div>
                                        <div>
                                            <div class="fw-600" style="font-size:.84rem;">Team Leader</div>
                                            <div class="text-muted" style="font-size:.72rem;line-height:1.35;">Manages a team</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="emp-type-card flex-fill" for="typePrepare">
                                    <input type="radio" id="typePrepare" name="empTypeRadio" value="prepare" class="d-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="type-icon"><i class="fa fa-user-check"></i></div>
                                        <div>
                                            <div class="fw-600" style="font-size:.84rem;">Prepare</div>
                                            <div class="text-muted" style="font-size:.72rem;line-height:1.35;">Reports to a team leader</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div id="teamLeaderField" style="display:none;">
                                <label class="form-label form-label-sm">Team Leader <span class="text-danger">*</span></label>
                                <select id="empTeamLeader" class="form-select form-select-sm select2-team-leader">
                                    <option value="">— Select Team Leader —</option>
                                </select>
                            </div>
                        </div>

                        {{-- Work Details --}}
                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-briefcase"></i> Work Details</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Designation <span class="text-danger">*</span></label>
                                    <select id="empDesignation" class="form-select form-select-sm select2-designation">
                                        <option value="">— Select Designation —</option>
                                        @foreach($designations as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Joining Date</label>
                                    <input type="date" id="empJoiningDate" class="form-control form-control-sm">
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Current Status</label>
                                    <select id="empCurrentStatus" class="form-select form-select-sm">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Resigned">Resigned</option>
                                        <option value="Terminated">Terminated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Personal Details --}}
                        <div class="emp-section">
                            <div class="emp-section-header">
                                <i class="fa fa-user"></i> Personal Details
                                <span class="text-muted ms-1" style="font-size:.68rem;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Father's Name</label>
                                    <input type="text" id="empFatherName" class="form-control form-control-sm" placeholder="e.g. Rahman Ali">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Mother's Name</label>
                                    <input type="text" id="empMotherName" class="form-control form-control-sm" placeholder="e.g. Fatema Begum">
                                </div>
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="emp-section">
                            <div class="emp-section-header">
                                <i class="fa fa-phone"></i> Contact
                                <span class="text-muted ms-1" style="font-size:.68rem;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Phone</label>
                                    <input type="text" id="empPhone" class="form-control form-control-sm" placeholder="+880...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Email</label>
                                    <input type="email" id="empEmail" class="form-control form-control-sm" placeholder="email@example.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Address</label>
                                    <textarea id="empAddress" class="form-control form-control-sm" rows="2" placeholder="Street, City, District..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 px-1">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="empActive" checked role="switch">
                                <label class="form-check-label" for="empActive" style="font-size:.875rem;">Active Employee</label>
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

{{-- Branch Access Modal --}}
<div class="modal fade" id="branchAccessModal" tabindex="-1" aria-labelledby="branchAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchAccessModalLabel"><i class="fa fa-code-branch me-2"></i> Branch Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="branchAccessModalBody">
                <div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveBranchAccess">
                    <i class="fa fa-save me-1"></i> Save Access
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.emp-section {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 12px 14px;
}
.emp-section-header {
    font-size: .67rem;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: #0369A1;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.emp-section-header::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #E2E8F0;
}
#employeeModal form { display: flex; flex-direction: column; overflow: hidden; min-height: 0; flex: 1 1 auto; }
#employeeModal .modal-body { flex: 1 1 auto; overflow-y: auto; min-height: 0; }
.emp-type-card {
    cursor: pointer; border: 2px solid #E2E8F0; border-radius: 8px; padding: 9px 12px;
    transition: border-color .18s, background .18s, box-shadow .18s;
    background: #fff; margin-bottom: 0; user-select: none;
}
.emp-type-card:hover { border-color: #93C5FD; background: #F0F9FF; }
.emp-type-card.active { border-color: #0369A1; background: #EFF6FF; box-shadow: 0 0 0 3px rgba(3,105,161,.09); }
.emp-type-card .type-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    background: #E0F2FE; color: #0369A1; font-size: .82rem; flex-shrink: 0;
    transition: background .18s, color .18s;
}
.emp-type-card.active .type-icon { background: #0369A1; color: #fff; }
</style>
@endpush

@push('scripts')
<script>
var empTable;

$(function () {
    // Designation select2
    $('.select2-designation').select2({ theme: 'bootstrap-5', placeholder: '— Select Designation —', allowClear: true, dropdownParent: $('#employeeModal') });

    // ── Type toggle ───────────────────────────────────────────────────────────
    function setEmpType(type) {
        $('input[name="empTypeRadio"]').each(function () {
            const match = $(this).val() === type;
            $(this).prop('checked', match);
            $(this).closest('.emp-type-card').toggleClass('active', match);
        });
        if (type === 'team_leader') {
            $('#teamLeaderField').hide();
            $('#empTeamLeader').val(null).trigger('change');
        } else {
            $('#teamLeaderField').show();
        }
    }
    $('input[name="empTypeRadio"]').on('change', function () { setEmpType($(this).val()); });
    setEmpType('team_leader');

    // Team leader select2 — AJAX search
    $('.select2-team-leader').select2({
        theme: 'bootstrap-5',
        placeholder: '— None —',
        allowClear: true,
        dropdownParent: $('#employeeModal'),
        ajax: {
            url: '{{ route('admin.employees.search') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, exclude: $('#empId').val() || 0 }; },
            processResults: function (data) {
                return { results: data.map(function (e) { return { id: e.id, text: e.name + (e.code ? ' (' + e.code + ')' : '') }; }) };
            },
            cache: true,
        },
    });

    empTable = $('#employeesTable').DataTable({
        processing: true, serverSide: true, autoWidth: false,
        ajax: '{{ route('admin.employees.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex',      orderable: false, searchable: false, width: '50px' },
            { data: 'code',             name: 'code' },
            { data: 'name',             name: 'name' },
            { data: 'type_badge',       name: 'type',              orderable: false, searchable: false },
            { data: 'team_leader_name', name: 'teamLeader.name',   defaultContent: '—' },
            { data: 'designation_name', name: 'designation.name',  defaultContent: '—' },
            { data: 'joining_date',     name: 'joining_date',      defaultContent: '—' },
            { data: 'status_badge',     name: 'current_status',    orderable: false, searchable: false },
            { data: 'action',           name: 'action',            orderable: false, searchable: false, width: '120px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 25,
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($input.length) {
                    $input.on('click mousedown', e => e.stopPropagation());
                    $input.on('keyup change', () => this.search($input.val()).draw());
                }
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No employees yet.</div>' },
    });

    function resetForm() {
        $('#empId').val('');
        $('#empCode, #empName, #empShortName, #empJoiningDate').val('');
        $('#empDesignation').val('').trigger('change');
        $('#empFatherName, #empMotherName, #empPhone, #empEmail, #empAddress').val('');
        $('#empCurrentStatus').val('Active');
        setEmpType('team_leader');
        $('#empActive').prop('checked', true);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    $('#btnAdd').on('click', function () {
        $('#employeeModalLabel').html('<i class="fa fa-plus me-2"></i>Add Employee');
        resetForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#employeeModalLabel').html('<i class="fa fa-edit me-2"></i>Edit Employee');
        resetForm();
        $('#empId').val(d.id);
        $('#empCode').val(d.code || '');
        $('#empName').val(d.name);
        $('#empShortName').val(d.short_name || '');
        $('#empDesignation').val(d.designation_id || '').trigger('change');
        $('#empJoiningDate').val(d.joining_date || '');
        $('#empFatherName').val(d.father_name || '');
        $('#empMotherName').val(d.mother_name || '');
        $('#empPhone').val(d.phone || '');
        $('#empEmail').val(d.email || '');
        $('#empAddress').val(d.address || '');
        $('#empCurrentStatus').val(d.current_status || 'Active');
        setEmpType(d.type || 'team_leader');
        $('#empActive').prop('checked', d.is_active == 1);
        // Pre-load team leader option if set
        if (d.team_leader_id) {
            $.get('{{ route('admin.employees.search') }}', { q: '', exclude: d.id }, function (res) {
                var match = res.find(function (e) { return e.id == d.team_leader_id; });
                if (match) {
                    var opt = new Option(match.name + (match.code ? ' (' + match.code + ')' : ''), match.id, true, true);
                    $('#empTeamLeader').append(opt).trigger('change');
                }
            });
        }
        $('#employeeModal').modal('show');
    });

    $('#employeeForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        if (!$('#empName').val().trim()) {
            $('#empName').addClass('is-invalid').after('<div class="invalid-feedback">Name is required.</div>');
            return;
        }

        const id  = $('#empId').val();
        const url = id ? '{{ url('admin/employees/unified') }}/' + id : '{{ route('admin.employees.store') }}';

        const type = $('input[name="empTypeRadio"]:checked').val() || 'team_leader';

        const payload = {
            _token:         $('meta[name="csrf-token"]').attr('content'),
            name:           $('#empName').val().trim(),
            code:           $('#empCode').val().trim(),
            type:           type,
            designation_id: $('#empDesignation').val() || null,
            joining_date:   $('#empJoiningDate').val(),
            short_name:     $('#empShortName').val().trim(),
            father_name:    $('#empFatherName').val().trim(),
            mother_name:    $('#empMotherName').val().trim(),
            phone:          $('#empPhone').val().trim(),
            email:          $('#empEmail').val().trim(),
            address:        $('#empAddress').val().trim(),
            current_status: $('#empCurrentStatus').val(),
            team_leader_id: type === 'prepare' ? ($('#empTeamLeader').val() || '') : '',
            is_active:      $('#empActive').is(':checked') ? 1 : 0,
        };

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: payload })
            .done(function (r) {
                $('#employeeModal').modal('hide');
                Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
                empTable.ajax.reload();
            })
            .fail(function (xhr) {
                if (xhr.status === 422) {
                    const errs = xhr.responseJSON?.errors || {};
                    if (errs.name)           $('#empName').addClass('is-invalid').after('<div class="invalid-feedback">' + errs.name[0] + '</div>');
                    if (errs.team_leader_id) $('#empTeamLeader').next('.select2-container').after('<div class="invalid-feedback d-block">' + errs.team_leader_id[0] + '</div>');
                } else {
                    Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            })
            .always(function () {
                $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
            });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (!res.isConfirmed) { return; }
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                    .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); empTable.ajax.reload(); })
                    .fail(xhr => Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Delete failed.' }));
            });
    });

    // ── Branch Access ─────────────────────────────────────────────────────────
    var currentEmpId = null;

    $(document).on('click', '.btn-manage-access', function () {
        currentEmpId = $(this).data('id');
        var name = $(this).data('name');
        $('#branchAccessModalLabel').html('<i class="fa fa-code-branch me-2"></i> Branch Access — ' + name);
        $('#branchAccessModalBody').html('<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</div>');
        $('#branchAccessModal').modal('show');

        $.get('{{ url('admin/employees/unified') }}/' + currentEmpId + '/branch-access')
            .done(function (res) {
                var html = '';
                res.companies.forEach(function (co) {
                    var typeColors = { cnf: 'success', freight: 'info', trading: 'warning' };
                    var color = typeColors[co.type] || 'secondary';
                    html += '<div class="mb-3 border rounded overflow-hidden">';
                    html += '<div class="p-2 bg-light border-bottom fw-semibold small"><span class="badge bg-' + color + ' me-2">' + co.type.toUpperCase() + '</span>' + co.name + '</div>';
                    html += '<div class="p-2 d-flex flex-wrap gap-3">';
                    if (co.branches.length === 0) {
                        html += '<span class="text-muted small">No branches configured</span>';
                    }
                    co.branches.forEach(function (br) {
                        var checked = co.granted.indexOf(br.id) !== -1 ? 'checked' : '';
                        html += '<label class="d-flex align-items-center gap-1 small" style="cursor:pointer">';
                        html += '<input type="checkbox" class="branch-access-cb" data-company="' + co.id + '" value="' + br.id + '" ' + checked + ' style="cursor:pointer">';
                        html += ' ' + br.name + '</label>';
                    });
                    html += '</div></div>';
                });
                $('#branchAccessModalBody').html(html || '<p class="text-muted">No companies found.</p>');
            })
            .fail(function () {
                $('#branchAccessModalBody').html('<p class="text-danger">Failed to load branch access data.</p>');
            });
    });

    $('#btnSaveBranchAccess').on('click', function () {
        if (!currentEmpId) { return; }
        var byCompany = {};
        $('.branch-access-cb:checked').each(function () {
            var co = $(this).data('company');
            if (!byCompany[co]) { byCompany[co] = []; }
            byCompany[co].push(parseInt($(this).val()));
        });
        var access = Object.keys(byCompany).map(function (co) {
            return { company_id: parseInt(co), branch_ids: byCompany[co] };
        });

        $('#btnSaveBranchAccess').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        var payload = { _token: $('meta[name="csrf-token"]').attr('content') };
        access.forEach(function (entry, i) {
            payload['access[' + i + '][company_id]'] = entry.company_id;
            (entry.branch_ids || []).forEach(function (bid, j) {
                payload['access[' + i + '][branch_ids][' + j + ']'] = bid;
            });
        });

        $.ajax({ url: '{{ url('admin/employees/unified') }}/' + currentEmpId + '/branch-access', method: 'POST', data: payload })
            .done(function (r) {
                $('#branchAccessModal').modal('hide');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            })
            .fail(function (xhr) {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Save failed.' });
            })
            .always(function () {
                $('#btnSaveBranchAccess').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Access');
            });
    });
});
</script>
@endpush

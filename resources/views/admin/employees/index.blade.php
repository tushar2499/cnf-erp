@extends('admin.layouts.app')

@section('title', 'Employees')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-tie me-2 text-success"></i> Employees</h4>
</div>

{{-- Company tabs --}}
<ul class="nav nav-tabs mb-3" id="empCompanyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-chevron-btn" data-bs-toggle="tab" data-bs-target="#tab-chevron" type="button" role="tab">
            <i class="fa fa-building me-1 text-success"></i> Chevron Lines
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-nf-btn" data-bs-toggle="tab" data-bs-target="#tab-nf" type="button" role="tab">
            <i class="fa fa-ship me-1 text-info"></i> NAS Freights
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-nt-btn" data-bs-toggle="tab" data-bs-target="#tab-nt" type="button" role="tab">
            <i class="fa fa-exchange-alt me-1 text-warning"></i> NAS Trading
        </button>
    </li>
</ul>

<div class="tab-content" id="empCompanyTabsContent">

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: Chevron Lines                                                  --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-chevron" role="tabpanel">

        @if(auth()->user()->hasPermission('admin.employees.create'))
        <div class="d-flex justify-content-end gap-2 mb-3 flex-wrap">
            <a href="{{ route('admin.employees.sample') }}" class="btn btn-sm btn-outline-success">
                <i class="fa fa-file-excel me-1"></i> Sample File
            </a>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa fa-file-upload me-1"></i> Import Excel
            </button>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal" id="btnAdd">
                <i class="fa fa-plus me-1"></i> Add Employee
            </button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fa fa-list me-2"></i> All Employees — Chevron Lines</span>
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
                                <th>Type</th>
                                <th>Team Leader</th>
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
                                <th></th>
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
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: NAS Freights                                                   --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-nf" role="tabpanel">

        <div class="row g-3">
            {{-- Form Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" id="nfFormTitle" style="background:#0c4a6e;color:#fff;font-size:.85rem;font-weight:600;">
                        <i class="fa fa-plus me-2"></i> Add Employee
                    </div>
                    <div class="card-body">
                        <form id="nfForm">
                            @csrf
                            <input type="hidden" id="nfId">

                            <div class="mb-2">
                                <label class="form-label form-label-sm">Employee ID</label>
                                <input type="text" id="nfCode" class="form-control form-control-sm bg-light fw-bold" readonly placeholder="Auto-generated">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                                <input type="text" id="nfName" class="form-control form-control-sm">
                                <div class="invalid-feedback" id="nfNameErr"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Designation</label>
                                <input type="text" id="nfDesignation" class="form-control form-control-sm" placeholder="e.g. Manager">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Phone</label>
                                <input type="text" id="nfPhone" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Email</label>
                                <input type="email" id="nfEmail" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-sm">Status</label>
                                <select id="nfStatus" class="form-select form-select-sm">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-sm px-4" id="nfBtnSave">
                                    <i class="fa fa-save me-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="nfBtnCancel">
                                    <i class="fa fa-times me-1"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#0c4a6e;color:#fff;font-size:.85rem;font-weight:600;">
                        <span><i class="fa fa-list me-2"></i> All Employees — NAS Freights</span>
                        @if(auth()->user()->hasPermission('admin.employees.create'))
                        <button class="btn btn-sm btn-light" id="nfBtnAdd"><i class="fa fa-plus me-1"></i> Add New</button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="nfTable" class="table table-hover table-striped mb-0 w-100">
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
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 3: NAS Trading                                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-nt" role="tabpanel">

        <div class="row g-3">
            {{-- Form Panel --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" id="ntFormTitle" style="background:#713f12;color:#fff;font-size:.85rem;font-weight:600;">
                        <i class="fa fa-plus me-2"></i> Add Employee
                    </div>
                    <div class="card-body">
                        <form id="ntForm">
                            @csrf
                            <input type="hidden" id="ntId">

                            <div class="mb-2">
                                <label class="form-label form-label-sm">Employee ID</label>
                                <input type="text" id="ntCode" class="form-control form-control-sm bg-light fw-bold" readonly placeholder="Auto-generated">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                                <input type="text" id="ntName" class="form-control form-control-sm">
                                <div class="invalid-feedback" id="ntNameErr"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Designation</label>
                                <input type="text" id="ntDesignation" class="form-control form-control-sm" placeholder="e.g. Manager">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Phone</label>
                                <input type="text" id="ntPhone" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Email</label>
                                <input type="email" id="ntEmail" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Address</label>
                                <input type="text" id="ntAddress" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label-sm">Joining Date</label>
                                <input type="date" id="ntJoinDate" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-sm">Status</label>
                                <select id="ntStatus" class="form-select form-select-sm">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-sm px-4" id="ntBtnSave">
                                    <i class="fa fa-save me-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="ntBtnCancel">
                                    <i class="fa fa-times me-1"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#713f12;color:#fff;font-size:.85rem;font-weight:600;">
                        <span><i class="fa fa-list me-2"></i> All Employees — NAS Trading</span>
                        @if(auth()->user()->hasPermission('admin.employees.create'))
                        <button class="btn btn-sm btn-light" id="ntBtnAdd"><i class="fa fa-plus me-1"></i> Add New</button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="ntTable" class="table table-hover table-striped mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Phone</th>
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
        </div>
    </div>

</div>{{-- /tab-content --}}

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Chevron: Employee Form Modal                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="modalTitle" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Employee</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="employeeForm">
                @csrf
                <input type="hidden" id="employeeId">
                <select id="empType" class="d-none">
                    <option value="team_leader">Team Leader</option>
                    <option value="prepare">Prepare</option>
                </select>

                <div class="modal-body p-3" style="background:#F8FAFC;">
                    <div class="d-flex flex-column gap-2">

                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-id-badge"></i> Employee Identity</div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Prefix <span class="text-danger">*</span></label>
                                    <select id="empPrefix" class="form-select form-select-sm">
                                        <option value="EMP-">EMP- (Employee)</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label form-label-sm">Employee ID <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="empEmployeeId" class="form-control" readonly placeholder="Auto-generated">
                                        <button type="button" class="btn btn-outline-secondary" id="btnGenId" title="Regenerate ID">
                                            <i class="fa fa-sync-alt" style="font-size:.75rem;"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="empName" class="form-control form-control-sm" placeholder="e.g. John Doe">
                                </div>
                            </div>
                        </div>

                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-sitemap"></i> Role & Assignment</div>

                            <div class="d-flex gap-2 mb-2">
                                <label class="emp-type-card active flex-fill" for="typeTeamLeader">
                                    <input type="radio" id="typeTeamLeader" name="empTypeRadio" value="team_leader" class="d-none" checked>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="type-icon"><i class="fa fa-user-tie"></i></div>
                                        <div>
                                            <div class="fw-600" style="font-size:.84rem;">Team Leader</div>
                                            <div class="text-muted" style="font-size:.72rem;line-height:1.35;">Can be assigned multiple customers</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="emp-type-card flex-fill" for="typePrepare">
                                    <input type="radio" id="typePrepare" name="empTypeRadio" value="prepare" class="d-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="type-icon"><i class="fa fa-user-check"></i></div>
                                        <div>
                                            <div class="fw-600" style="font-size:.84rem;">Prepare</div>
                                            <div class="text-muted" style="font-size:.72rem;line-height:1.35;">Must report to a team leader</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div id="teamLeaderField" style="display:none;">
                                <label class="form-label form-label-sm">Team Leader <span class="text-danger">*</span></label>
                                <select id="empTeamLeader" class="form-select form-select-sm select2-team-leader">
                                    <option value="">-- Select Team Leader --</option>
                                    @foreach($teamLeaders as $tl)
                                        <option value="{{ $tl->id }}">{{ $tl->name }} ({{ $tl->employee_id }}){{ $tl->designation ? ' — ' . $tl->designation->name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="customersField">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label form-label-sm mb-0">
                                        Assigned Customers
                                        <span class="ms-1 text-muted" style="font-size:.7rem;font-weight:400;">(optional)</span>
                                    </label>
                                    <div class="d-flex gap-1">
                                        <button type="button" id="btnSelectAllCustomers"
                                                style="font-size:.7rem;padding:2px 9px;border-radius:4px;border:1px solid #0369A1;color:#0369A1;background:#EFF6FF;cursor:pointer;font-weight:600;transition:all .15s;">
                                            <i class="fa fa-check me-1"></i>Select All
                                        </button>
                                        <button type="button" id="btnClearCustomers"
                                                style="font-size:.7rem;padding:2px 9px;border-radius:4px;border:1px solid #dc2626;color:#dc2626;background:#FFF5F5;cursor:pointer;font-weight:600;transition:all .15s;">
                                            <i class="fa fa-times me-1"></i>Clear
                                        </button>
                                    </div>
                                </div>
                                <select id="empCustomers" class="form-select form-select-sm select2-customers" multiple>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="emp-section">
                            <div class="emp-section-header"><i class="fa fa-briefcase"></i> Work Details</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Designation <span class="text-danger">*</span></label>
                                    <select id="empDesignation" class="form-select form-select-sm select2-designation">
                                        <option value="">-- Select Designation --</option>
                                        @foreach($designations as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Joining Date <span class="text-danger">*</span></label>
                                    <input type="date" id="empJoiningDate" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label form-label-sm">Branch</label>
                                    <select id="empBranch" class="form-select form-select-sm select2-branch">
                                        <option value="">-- Select Branch --</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
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

                        <div class="emp-section">
                            <div class="emp-section-header">
                                <i class="fa fa-user"></i> Personal Details
                                <span class="text-muted ms-1" style="font-size:.68rem;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Short Name</label>
                                    <input type="text" id="empShortName" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Father's Name</label>
                                    <input type="text" id="empFatherName" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">Mother's Name</label>
                                    <input type="text" id="empMotherName" class="form-control form-control-sm">
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

{{-- Chevron: Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600"><i class="fa fa-file-upload me-2"></i>Import Employees — Chevron Lines</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="importStep1">
                    <div class="alert alert-info d-flex gap-2 align-items-start" style="font-size:12px">
                        <i class="fa fa-info-circle mt-1"></i>
                        <div>
                            Upload an Excel file with columns:
                            <strong>Employee Prefix</strong>, <strong>Employee ID</strong> (optional — auto-generated if blank),
                            <strong>Name</strong>, <strong>Designation</strong>, <strong>Branch</strong>,
                            <strong>Joining Date</strong> (YYYY-MM-DD), Short Name, Father Name, Mother Name,
                            <strong>Status</strong> (Active/Inactive/Resigned/Terminated).<br>
                            <a href="{{ route('admin.employees.sample') }}" class="fw-semibold">
                                <i class="fa fa-download me-1"></i>Download sample file
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Excel File</label>
                        <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv">
                        <div class="invalid-feedback" id="importFileError"></div>
                    </div>
                    <button class="btn btn-primary btn-sm" id="btnPreview">
                        <i class="fa fa-eye me-1"></i> Preview
                    </button>
                </div>
                <div id="importStep2" style="display:none">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <span class="badge bg-success me-1" id="newCount">0 New</span>
                            <span class="badge bg-warning text-dark me-1" id="existCount">0 Already Exist</span>
                            <span class="badge bg-danger" id="warnCount" style="display:none">0 Warnings</span>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="btnBackToUpload">
                            <i class="fa fa-arrow-left me-1"></i> Back
                        </button>
                    </div>
                    <div class="table-responsive" style="max-height:440px;overflow-y:auto">
                        <table class="table table-sm table-bordered" style="font-size:11.5px">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width:3%">#</th>
                                    <th style="width:9%">Emp ID</th>
                                    <th>Name</th>
                                    <th style="width:15%">Designation</th>
                                    <th style="width:13%">Branch</th>
                                    <th style="width:10%">Joining Date</th>
                                    <th style="width:8%">Status</th>
                                    <th style="width:13%">Result</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-none" id="importFooter">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnConfirmImport">
                    <i class="fa fa-check me-1"></i> Confirm Import
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
#employeeModal form {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
    flex: 1 1 auto;
}
#employeeModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0;
}
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
.emp-type-card {
    cursor: pointer;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    padding: 9px 12px;
    transition: border-color .18s, background .18s, box-shadow .18s;
    background: #fff;
    margin-bottom: 0;
    user-select: none;
}
.emp-type-card:hover { border-color: #93C5FD; background: #F0F9FF; }
.emp-type-card.active {
    border-color: #0369A1;
    background: #EFF6FF;
    box-shadow: 0 0 0 3px rgba(3,105,161,.09);
}
.emp-type-card .type-icon {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    background: #E0F2FE; color: #0369A1;
    font-size: .82rem; flex-shrink: 0;
    transition: background .18s, color .18s;
}
.emp-type-card.active .type-icon { background: #0369A1; color: #fff; }
#empEmployeeId { font-family: monospace; font-weight: 600; letter-spacing: .05em; color: #0F172A; background: #F8FAFC; }
</style>
@endpush

@push('scripts')
<script>
var chevronTable, nfTable, ntTable;

$(function () {

    // ── Chevron Lines DataTable ───────────────────────────────────────────────
    $('.select2-designation').select2({ theme: 'bootstrap-5', placeholder: '-- Select Designation --', allowClear: true, dropdownParent: $('#employeeModal') });
    $('.select2-branch').select2({ theme: 'bootstrap-5', placeholder: '-- Select Branch --', allowClear: true, dropdownParent: $('#employeeModal') });
    $('.select2-team-leader').select2({ theme: 'bootstrap-5', placeholder: '-- Select Team Leader --', allowClear: true, dropdownParent: $('#employeeModal') });
    $('.select2-customers').select2({ theme: 'bootstrap-5', placeholder: '-- Select Customers --', allowClear: true, dropdownParent: $('#employeeModal') });
    setEmpType($('#empType').val());

    $('#btnSelectAllCustomers').on('click', function () {
        $('#empCustomers option').prop('selected', true);
        $('#empCustomers').trigger('change');
    });
    $('#btnClearCustomers').on('click', function () {
        $('#empCustomers').val([]).trigger('change');
    });

    chevronTable = $('#employeesTable').DataTable({
        processing: true, serverSide: true, autoWidth: false,
        ajax: '{{ route('admin.employees.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex',    orderable: false, searchable: false, width: '50px' },
            { data: 'employee_id',      name: 'employee_id' },
            { data: 'name',             name: 'name' },
            { data: 'type_badge',       name: 'type',           orderable: false, searchable: false },
            { data: 'team_leader_name', name: 'teamLeader.name' },
            { data: 'designation_name', name: 'designation.name' },
            { data: 'branch_name',      name: 'branch.name' },
            { data: 'joining_date',     name: 'joining_date' },
            { data: 'status_badge',     name: 'current_status', searchable: false },
            { data: 'action',           name: 'action',         orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
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

    function toggleTypeFields(type) {
        if (type === 'team_leader') {
            $('#teamLeaderField').hide(); $('#empTeamLeader').val('').trigger('change');
            $('#customersField').show();
        } else {
            $('#teamLeaderField').show(); $('#customersField').hide();
            $('#empCustomers').val([]).trigger('change');
        }
    }
    function setEmpType(type) {
        $('#empType').val(type);
        $('input[name="empTypeRadio"]').each(function () {
            const match = $(this).val() === type;
            $(this).prop('checked', match);
            $(this).closest('.emp-type-card').toggleClass('active', match);
        });
        toggleTypeFields(type);
    }
    $('input[name="empTypeRadio"]').on('change', function () { setEmpType($(this).val()); });

    function generateId() {
        $.getJSON('{{ route('admin.employees.next-id') }}', { prefix: $('#empPrefix').val() }, function (r) {
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
        setEmpType('team_leader');
        $('#empTeamLeader').val('').trigger('change');
        $('#empCustomers').val([]).trigger('change');
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
        setEmpType(d.type || 'team_leader');
        $('#empTeamLeader').val(d.team_leader_id || '').trigger('change');
        const custIds = d.customer_ids ? String(d.customer_ids).split(',').filter(Boolean) : [];
        $('#empCustomers').val(custIds).trigger('change');
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
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); chevronTable.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    function showErr($el, msg) {
        $el.addClass('is-invalid');
        const isSelect2 = $el.is('.select2-designation,.select2-branch,.select2-team-leader,.select2-customers');
        const $after = isSelect2 ? $el.next('.select2-container') : $el;
        $after.after('<div class="invalid-feedback d-block" style="font-size:.78rem;">' + msg + '</div>');
    }

    $('#employeeForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid'); $('.invalid-feedback').remove();
        const type = $('#empType').val();
        let valid = true;
        if (!$('#empName').val().trim())                        { showErr($('#empName'),        'Name is required.');         valid = false; }
        if (!$('#empDesignation').val())                        { showErr($('#empDesignation'), 'Designation is required.');  valid = false; }
        if (!$('#empJoiningDate').val())                        { showErr($('#empJoiningDate'), 'Joining date is required.'); valid = false; }
        if (type === 'prepare' && !$('#empTeamLeader').val())   { showErr($('#empTeamLeader'),  'Team leader is required.');  valid = false; }
        if (!valid) return;

        const id  = $('#employeeId').val();
        const url = id ? '{{ url('admin/employees') }}/' + id : '{{ route('admin.employees.store') }}';
        const customerIds = $('#empCustomers').val() || [];
        const payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            employee_prefix: $('#empPrefix').val(), name: $('#empName').val(),
            designation_id: $('#empDesignation').val(), joining_date: $('#empJoiningDate').val(),
            short_name: $('#empShortName').val(), father_name: $('#empFatherName').val(),
            mother_name: $('#empMotherName').val(), current_status: $('#empCurrentStatus').val(),
            branch_id: $('#empBranch').val(), is_active: $('#empActive').is(':checked') ? 1 : 0,
            type, team_leader_id: type === 'prepare' ? $('#empTeamLeader').val() : '',
        };
        customerIds.forEach((cid, i) => { payload['customer_ids[' + i + ']'] = cid; });

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: payload })
            .done(r => { $('#employeeModal').modal('hide'); Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); chevronTable.ajax.reload(); })
            .fail(xhr => {
                if (xhr.status === 422) {
                    const e = xhr.responseJSON.errors;
                    if (e.name)           showErr($('#empName'),        e.name[0]);
                    if (e.designation_id) showErr($('#empDesignation'), e.designation_id[0]);
                    if (e.joining_date)   showErr($('#empJoiningDate'), e.joining_date[0]);
                    if (e.team_leader_id) showErr($('#empTeamLeader'),  e.team_leader_id[0]);
                } else {
                    Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            })
            .always(() => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'); });
    });

    // Import
    var previewRows = [];
    $('#importModal').on('hidden.bs.modal', function () {
        previewRows = [];
        $('#importFile').val('').removeClass('is-invalid'); $('#importFileError').text('');
        $('#importStep1').show(); $('#importStep2').hide(); $('#importFooter').addClass('d-none');
        $('#previewBody').empty();
    });
    $('#btnBackToUpload').on('click', function () { $('#importStep2').hide(); $('#importStep1').show(); $('#importFooter').addClass('d-none'); });
    $('#btnPreview').on('click', function () {
        const file = $('#importFile')[0].files[0];
        if (!file) { $('#importFile').addClass('is-invalid'); $('#importFileError').text('Please select an Excel file.'); return; }
        $('#importFile').removeClass('is-invalid');
        const fd = new FormData();
        fd.append('file', file); fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        $('#btnPreview').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
        $.ajax({ url: '{{ route('admin.employees.import.preview') }}', method: 'POST', data: fd, processData: false, contentType: false })
            .done(r => { previewRows = r.rows; renderImportPreview(previewRows); $('#importStep1').hide(); $('#importStep2').show(); $('#importFooter').removeClass('d-none'); })
            .fail(xhr => { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed to parse file.' }); })
            .always(() => { $('#btnPreview').prop('disabled', false).html('<i class="fa fa-eye me-1"></i> Preview'); });
    });
    function renderImportPreview(rows) {
        let html = '', newCnt = 0, existCnt = 0, warnCnt = 0;
        rows.forEach((row, i) => {
            const hasWarn = row.warnings && row.warnings.length > 0;
            if (row.exists) {
                existCnt++;
                html += `<tr class="table-warning"><td class="text-center">${i+1}</td><td>${h(row.employee_id||'(auto)')}</td><td>${h(row.name)}</td><td>${h(row.designation_name)}</td><td>${h(row.branch_name)}</td><td class="text-center">${h(row.joining_date||'')}</td><td class="text-center"><span class="badge bg-secondary">${h(row.status)}</span></td><td class="text-center"><span class="badge bg-warning text-dark">Exists</span></td></tr>`;
            } else {
                newCnt++; if (hasWarn) warnCnt++;
                const warnTip = row.warnings.join(', ');
                html += `<tr class="${hasWarn?'table-danger':''}"><td class="text-center">${i+1}</td><td>${h(row.employee_id||'<em class="text-muted">auto</em>')}</td><td>${h(row.name)}</td><td>${h(row.designation_name)}${!row.designation_found&&row.designation_name?' <span class="text-danger small">(not found)</span>':''}</td><td>${h(row.branch_name)}</td><td class="text-center">${h(row.joining_date||'')}</td><td class="text-center"><span class="badge ${row.status==='Active'?'bg-success':'bg-secondary'}">${h(row.status)}</span></td><td class="text-center"><span class="badge bg-success">New</span>${hasWarn?`<span class="badge bg-danger ms-1" title="${warnTip}"><i class="fa fa-exclamation-triangle"></i></span>`:''}</td></tr>`;
            }
        });
        if (!rows.length) html = '<tr><td colspan="8" class="text-center text-muted py-3">No valid rows found.</td></tr>';
        $('#previewBody').html(html);
        $('#newCount').text(newCnt + ' New'); $('#existCount').text(existCnt + ' Already Exist');
        warnCnt > 0 ? $('#warnCount').text(warnCnt + ' Warnings').show() : $('#warnCount').hide();
        $('#btnConfirmImport').prop('disabled', newCnt === 0);
    }
    function h(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    $('#btnConfirmImport').on('click', function () {
        const newRows = previewRows.filter(r => !r.exists);
        if (!newRows.length) return;
        $('#btnConfirmImport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Importing...');
        $.ajax({ url: '{{ route('admin.employees.import') }}', method: 'POST', contentType: 'application/json', data: JSON.stringify({ _token: $('meta[name="csrf-token"]').attr('content'), rows: newRows }) })
            .done(r => { $('#importModal').modal('hide'); Swal.fire({ icon: 'success', title: r.message, timer: 2000, showConfirmButton: false }); chevronTable.ajax.reload(); })
            .fail(xhr => { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Import failed.' }); })
            .always(() => { $('#btnConfirmImport').prop('disabled', false).html('<i class="fa fa-check me-1"></i> Confirm Import'); });
    });

    // ── NAS Freights DataTable ────────────────────────────────────────────────
    nfTable = $('#nfTable').DataTable({
        processing: true, serverSide: true, autoWidth: false,
        ajax: '{{ route('admin.employees.nas-freights.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '45px' },
            { data: 'code',        name: 'code' },
            { data: 'name',        name: 'name' },
            { data: 'designation', name: 'designation' },
            { data: 'phone',       name: 'phone' },
            { data: 'email',       name: 'email' },
            { data: 'status_badge',orderable: false, searchable: false },
            { data: 'action',      orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted">No employees yet.</div>' },
    });

    function nfReset() {
        $('#nfId,#nfCode').val('');
        $('#nfName,#nfDesignation,#nfPhone,#nfEmail').val('');
        $('#nfName').removeClass('is-invalid'); $('#nfNameErr').text('');
        $('#nfStatus').val('Active');
        $('#nfFormTitle').html('<i class="fa fa-plus me-2"></i> Add Employee');
        $('#nfBtnSave').html('<i class="fa fa-save me-1"></i> Save');
    }
    $('#nfBtnAdd,#nfBtnCancel').on('click', nfReset);

    $(document).on('click', '.btn-edit-nf', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('admin/employees/nas-freights') }}/' + id, function (r) {
            nfReset();
            $('#nfId').val(r.id); $('#nfCode').val(r.code); $('#nfName').val(r.name);
            $('#nfDesignation').val(r.designation||''); $('#nfPhone').val(r.phone||''); $('#nfEmail').val(r.email||'');
            $('#nfStatus').val(r.status);
            $('#nfFormTitle').html('<i class="fa fa-edit me-2"></i> Edit Employee');
            $('#nfBtnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html,body').animate({ scrollTop: 0 }, 200);
        });
    });

    $(document).on('click', '.btn-delete-nf', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); nfTable.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    $('#nfForm').on('submit', function (e) {
        e.preventDefault();
        $('#nfName').removeClass('is-invalid'); $('#nfNameErr').text('');
        if (!$('#nfName').val().trim()) { $('#nfName').addClass('is-invalid'); $('#nfNameErr').text('Name is required.'); return; }
        const id  = $('#nfId').val();
        const url = id ? '{{ url('admin/employees/nas-freights') }}/' + id : '{{ route('admin.employees.nas-freights.store') }}';
        $('#nfBtnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#nfName').val(), designation: $('#nfDesignation').val(),
            phone: $('#nfPhone').val(), email: $('#nfEmail').val(), status: $('#nfStatus').val(),
        }})
        .done(r => { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true }); nfReset(); nfTable.ajax.reload(); })
        .fail(xhr => {
            if (xhr.status === 422) { const e = xhr.responseJSON.errors; if (e.name) { $('#nfName').addClass('is-invalid'); $('#nfNameErr').text(e.name[0]); } }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
        })
        .always(() => { const isEdit = $('#nfId').val(); $('#nfBtnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save')); });
    });

    // ── NAS Trading DataTable ─────────────────────────────────────────────────
    ntTable = $('#ntTable').DataTable({
        processing: true, serverSide: true, autoWidth: false,
        ajax: '{{ route('admin.employees.nas-trading.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '45px' },
            { data: 'code',        name: 'code' },
            { data: 'name',        name: 'name' },
            { data: 'designation', name: 'designation' },
            { data: 'phone',       name: 'phone' },
            { data: 'status_badge',orderable: false, searchable: false },
            { data: 'action',      orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted">No employees yet.</div>' },
    });

    function ntReset() {
        $('#ntId,#ntCode').val('');
        $('#ntName,#ntDesignation,#ntPhone,#ntEmail,#ntAddress,#ntJoinDate').val('');
        $('#ntName').removeClass('is-invalid'); $('#ntNameErr').text('');
        $('#ntStatus').val('Active');
        $('#ntFormTitle').html('<i class="fa fa-plus me-2"></i> Add Employee');
        $('#ntBtnSave').html('<i class="fa fa-save me-1"></i> Save');
    }
    $('#ntBtnAdd,#ntBtnCancel').on('click', ntReset);

    $(document).on('click', '.btn-edit-nt', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('admin/employees/nas-trading') }}/' + id, function (r) {
            ntReset();
            $('#ntId').val(r.id); $('#ntCode').val(r.code); $('#ntName').val(r.name);
            $('#ntDesignation').val(r.designation||''); $('#ntPhone').val(r.phone||'');
            $('#ntEmail').val(r.email||''); $('#ntAddress').val(r.address||'');
            $('#ntJoinDate').val(r.joining_date||''); $('#ntStatus').val(r.status);
            $('#ntFormTitle').html('<i class="fa fa-edit me-2"></i> Edit Employee');
            $('#ntBtnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html,body').animate({ scrollTop: 0 }, 200);
        });
    });

    $(document).on('click', '.btn-delete-nt', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); ntTable.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    $('#ntForm').on('submit', function (e) {
        e.preventDefault();
        $('#ntName').removeClass('is-invalid'); $('#ntNameErr').text('');
        if (!$('#ntName').val().trim()) { $('#ntName').addClass('is-invalid'); $('#ntNameErr').text('Name is required.'); return; }
        const id  = $('#ntId').val();
        const url = id ? '{{ url('admin/employees/nas-trading') }}/' + id : '{{ route('admin.employees.nas-trading.store') }}';
        $('#ntBtnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#ntName').val(), designation: $('#ntDesignation').val(),
            phone: $('#ntPhone').val(), email: $('#ntEmail').val(),
            address: $('#ntAddress').val(), joining_date: $('#ntJoinDate').val(),
            status: $('#ntStatus').val(),
        }})
        .done(r => { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true }); ntReset(); ntTable.ajax.reload(); })
        .fail(xhr => {
            if (xhr.status === 422) { const e = xhr.responseJSON.errors; if (e.name) { $('#ntName').addClass('is-invalid'); $('#ntNameErr').text(e.name[0]); } }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
        })
        .always(() => { const isEdit = $('#ntId').val(); $('#ntBtnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save')); });
    });

    // Remember active tab across page loads
    const savedTab = localStorage.getItem('adminEmpTab');
    if (savedTab) {
        const btn = document.querySelector('#empCompanyTabs button[data-bs-target="' + savedTab + '"]');
        if (btn) { new bootstrap.Tab(btn).show(); }
    }
    document.querySelectorAll('#empCompanyTabs button').forEach(btn => {
        btn.addEventListener('shown.bs.tab', () => localStorage.setItem('adminEmpTab', btn.getAttribute('data-bs-target')));
    });

});
</script>
@endpush

@extends('chevron.layouts.app')

@section('title', 'Customers')

@push('styles')
<style>
.cus-section { border: 1px solid #e9ecef; border-radius: .4rem; overflow: hidden; }
.cus-section-header { padding: .4rem .75rem; font-size: .78rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; display: flex; align-items: center; }
.cus-section-header span { color: #374151; }
.cus-section .row { padding: 0 .75rem .75rem; }
.cus-tabs .cus-tab { font-size: .82rem; font-weight: 500; color: #6b7280; border: none; padding: .5rem 1rem; border-bottom: 3px solid transparent; }
.cus-tabs .cus-tab.active { color: #14b8a6; border-bottom-color: #14b8a6; background: transparent; font-weight: 600; }
.cus-tabs .cus-tab:hover:not(.active) { color: #0d9488; background: rgba(20,184,166,.06); }
.text-teal { color: #14b8a6 !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-users me-2 text-success"></i> Customers</h4>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#customerModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Customer
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Customers</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#customersTable').DataTable().button('.buttons-csv').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#customersTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#customersTable').DataTable().button('.buttons-pdf').trigger()" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#customersTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="customersTable" class="table table-hover table-striped mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Branch</th>
                        <th>Pay Type</th>
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
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border:none; border-radius:.75rem; overflow:hidden;">
            <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#0d2626 0%,#1a3d3d 60%,#14b8a6 100%); padding:1rem 1.25rem;">
                <div>
                    <h6 class="modal-title fw-700 mb-0" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Customer</h6>
                    <small class="opacity-75" id="modalSubtitle">Fill in customer details below</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="customerForm">
                @csrf
                <input type="hidden" id="cusId">
                <div class="modal-body p-0">

                    {{-- Tab Nav --}}
                    <ul class="nav cus-tabs border-bottom px-3 pt-2" id="customerTabs" style="background:#f8fffe;">
                        <li class="nav-item">
                            <a class="nav-link cus-tab active" data-bs-toggle="tab" href="#tabBasic">
                                <i class="fa fa-user me-1"></i> Basic Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cus-tab" data-bs-toggle="tab" href="#tabLocation">
                                <i class="fa fa-map-marker-alt me-1"></i> Location
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link cus-tab" data-bs-toggle="tab" href="#tabFinancial">
                                <i class="fa fa-dollar-sign me-1"></i> Financial
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">

                        {{-- ══ Basic Info ══ --}}
                        <div class="tab-pane fade show active" id="tabBasic">

                            {{-- ID Section --}}
                            <div class="cus-section mb-3">
                                <div class="cus-section-header" style="background:#e0f7f4; border-left:4px solid #14b8a6;">
                                    <i class="fa fa-id-card me-2 text-teal"></i><span>Identity</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-3">
                                        <label class="form-label">ID Prefix <span class="text-danger">*</span></label>
                                        <select id="cusPrefix" class="form-select form-select-sm">
                                            <option value="CUS-">CUS-</option>
                                            <option value="CLI-">CLI-</option>
                                            <option value="CT-">CT-</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Customer ID</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="cusCustomerId" class="form-control bg-light fw-600" readonly>
                                            <button type="button" class="btn btn-sm" id="btnGenCusId" style="background:#14b8a6;color:#fff;" title="Regenerate"><i class="fa fa-sync-alt"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select id="cusStatus" class="form-select form-select-sm">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Branch</label>
                                        <select id="cusBranch" class="form-select select2-branch">
                                            <option value="">-- Select --</option>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- General Section --}}
                            <div class="cus-section mb-3">
                                <div class="cus-section-header" style="background:#e8f4fd; border-left:4px solid #1565c0;">
                                    <i class="fa fa-building me-2" style="color:#1565c0;"></i><span>General</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-12">
                                        <label class="form-label">Customer / Company Name <span class="text-danger">*</span></label>
                                        <input type="text" id="cusName" class="form-control form-control-sm" placeholder="Full name or company name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Owner Name</label>
                                        <input type="text" id="cusOwnerName" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sales Person</label>
                                        <input type="text" id="cusSalesPerson" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Customer Account</label>
                                        <input type="text" id="cusAccount" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Person Details</label>
                                        <input type="text" id="cusContactPerson" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea id="cusAddress" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Section --}}
                            <div class="cus-section mb-3">
                                <div class="cus-section-header" style="background:#fef3e2; border-left:4px solid #f59e0b;">
                                    <i class="fa fa-phone me-2" style="color:#f59e0b;"></i><span>Contact</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Phone</label>
                                        <input type="text" id="cusPhone" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fax</label>
                                        <input type="text" id="cusFax" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mobile</label>
                                        <input type="text" id="cusMobile" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" id="cusEmail" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            {{-- Tax / Identity Section --}}
                            <div class="cus-section">
                                <div class="cus-section-header" style="background:#f3e8ff; border-left:4px solid #7c3aed;">
                                    <i class="fa fa-file-invoice me-2" style="color:#7c3aed;"></i><span>Tax &amp; Identity</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">VAT ID</label>
                                        <input type="text" id="cusVatId" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Identity Type</label>
                                        <select id="cusIdentityType" class="form-select form-select-sm">
                                            <option value="">-- None --</option>
                                            <option value="BIN">BIN</option>
                                            <option value="TIN">TIN</option>
                                            <option value="NID">NID</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">TIN / BIN / NID</label>
                                        <input type="text" id="cusTinBinNid" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Prefix</label>
                                        <input type="text" id="cusOtherPrefix" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══ Location ══ --}}
                        <div class="tab-pane fade" id="tabLocation">
                            <div class="cus-section">
                                <div class="cus-section-header" style="background:#e0f7f4; border-left:4px solid #14b8a6;">
                                    <i class="fa fa-globe me-2 text-teal"></i><span>Location Details</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Country</label>
                                        <input type="text" id="cusCountry" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Division</label>
                                        <input type="text" id="cusDivision" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">District</label>
                                        <input type="text" id="cusDistrict" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">City</label>
                                        <input type="text" id="cusCity" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Region</label>
                                        <input type="text" id="cusRegion" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" id="cusPostalCode" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Customer ID Reference</label>
                                        <input type="text" id="cusCusIdRef" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══ Financial ══ --}}
                        <div class="tab-pane fade" id="tabFinancial">

                            {{-- Payment & Tax --}}
                            <div class="cus-section mb-3">
                                <div class="cus-section-header" style="background:#e8f4fd; border-left:4px solid #1565c0;">
                                    <i class="fa fa-credit-card me-2" style="color:#1565c0;"></i><span>Payment &amp; Tax</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Pay Type</label>
                                        <select id="cusPayType" class="form-select form-select-sm">
                                            <option value="Cash">Cash</option>
                                            <option value="Credit">Credit</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Taxscope</label>
                                        <select id="cusTaxscope" class="form-select form-select-sm">
                                            <option value="Exempted">Exempted</option>
                                            <option value="Taxable">Taxable</option>
                                            <option value="Zero-Rated">Zero-Rated</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Discount (%)</label>
                                        <input type="number" id="cusDiscount" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Commission (%)</label>
                                        <input type="number" id="cusCommission" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            {{-- Credit --}}
                            <div class="cus-section mb-3">
                                <div class="cus-section-header" style="background:#fef3e2; border-left:4px solid #f59e0b;">
                                    <i class="fa fa-hand-holding-usd me-2" style="color:#f59e0b;"></i><span>Credit &amp; Limits</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-3">
                                        <label class="form-label">Credit Limit</label>
                                        <input type="number" id="cusCreditLimit" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Limit Days</label>
                                        <input type="number" id="cusLimitDays" class="form-control form-control-sm" value="0" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Security Deposit</label>
                                        <input type="number" id="cusSecurityDeposit" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Maturity Days</label>
                                        <input type="number" id="cusMaturityDays" class="form-control form-control-sm" value="0" min="0">
                                    </div>
                                </div>
                            </div>

                            {{-- Portal Access --}}
                            <div class="cus-section">
                                <div class="cus-section-header" style="background:#f3e8ff; border-left:4px solid #7c3aed;">
                                    <i class="fa fa-lock me-2" style="color:#7c3aed;"></i><span>Portal Access</span>
                                </div>
                                <div class="row g-2 pt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Password <small class="text-muted">(leave blank to keep)</small></label>
                                        <input type="password" id="cusPassword" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" id="cusPasswordConfirm" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top" style="background:#f8fffe;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm px-4 text-white" id="btnSave" style="background:#14b8a6; border-color:#14b8a6;">
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
    function initSelect2() {
        $('#cusBranch').select2('destroy');
        $('#cusBranch').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Select Branch --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#customerModal'),
        });
    }

    $('#customerModal').on('shown.bs.modal', function () {
        initSelect2();
        const pendingBranch = $('#customerModal').data('pending-branch');
        if (pendingBranch !== undefined) {
            $('#cusBranch').val(pendingBranch).trigger('change');
            $('#customerModal').removeData('pending-branch');
        }
    });

    table = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.stakeholders.customers.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'customer_id',  name: 'customer_id' },
            { data: 'name',         name: 'name' },
            { data: 'mobile',       name: 'mobile' },
            { data: 'branch_name',  name: 'branch.name' },
            { data: 'pay_type',     name: 'pay_type' },
            { data: 'status_badge', name: 'status', searchable: false },
            { data: 'action',       name: 'action', orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv', text: 'CSV' }, { extend: 'excel', text: 'Excel' },
            { extend: 'pdf', text: 'PDF' }, { extend: 'print', text: 'Print' },
        ],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($input.length) $input.on('keyup change', () => this.search($input.val()).draw());
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No customers yet.</div>' },
    });

    function generateCusId() {
        $.getJSON('{{ route('chevron.stakeholders.customers.next-id') }}', { prefix: $('#cusPrefix').val() }, function (r) {
            $('#cusCustomerId').val(r.customer_id);
        });
    }

    $('#cusPrefix').on('change', function () { if (!$('#cusId').val()) generateCusId(); });
    $('#btnGenCusId').on('click', generateCusId);

    function resetForm() {
        $('#cusId').val('');
        $('#cusPrefix').val('CUS-').trigger('change');
        $('#customerModal').data('pending-branch', '');
        $('#cusName, #cusOwnerName, #cusSalesPerson, #cusAddress, #cusPhone, #cusFax, #cusMobile').val('');
        $('#cusEmail, #cusAccount, #cusVatId, #cusTinBinNid, #cusContactPerson, #cusOtherPrefix').val('');
        $('#cusCountry, #cusDivision, #cusDistrict, #cusCity, #cusRegion, #cusPostalCode, #cusCusIdRef').val('');
        $('#cusPassword, #cusPasswordConfirm').val('');
        $('#cusIdentityType').val('');
        $('#cusStatus').val('Active');
        $('#cusPayType').val('Cash');
        $('#cusTaxscope').val('Exempted');
        $('#cusDiscount, #cusCommission').val('0');
        $('#cusCreditLimit, #cusLimitDays, #cusSecurityDeposit, #cusMaturityDays').val('0');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#customerTabs a:first').tab('show');
        generateCusId();
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Customer');
        resetForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Customer');
        $.getJSON('{{ url('chevron/stakeholders/customers') }}/' + id, function (r) {
            $('#cusId').val(r.id);
            $('#cusPrefix').val(r.id_prefix);
            $('#cusCustomerId').val(r.customer_id);
            $('#customerModal').data('pending-branch', r.branch_id || '');
            $('#cusName').val(r.name);
            $('#cusOwnerName').val(r.owner_name);
            $('#cusSalesPerson').val(r.sales_person);
            $('#cusAddress').val(r.address);
            $('#cusPhone').val(r.phone);
            $('#cusFax').val(r.fax);
            $('#cusMobile').val(r.mobile);
            $('#cusEmail').val(r.email);
            $('#cusAccount').val(r.customer_account);
            $('#cusVatId').val(r.vat_id);
            $('#cusIdentityType').val(r.identity_type || '');
            $('#cusTinBinNid').val(r.tin_bin_nid);
            $('#cusContactPerson').val(r.contact_person_details);
            $('#cusOtherPrefix').val(r.prefix);
            $('#cusStatus').val(r.status);
            $('#cusCountry').val(r.country);
            $('#cusDivision').val(r.division);
            $('#cusDistrict').val(r.district);
            $('#cusCity').val(r.city);
            $('#cusRegion').val(r.region);
            $('#cusPostalCode').val(r.postal_code);
            $('#cusCusIdRef').val(r.customer_id_reference);
            $('#cusPayType').val(r.pay_type);
            $('#cusTaxscope').val(r.taxscope);
            $('#cusDiscount').val(r.discount);
            $('#cusCommission').val(r.commission);
            $('#cusCreditLimit').val(r.credit_limit);
            $('#cusLimitDays').val(r.limit_days);
            $('#cusSecurityDeposit').val(r.security_deposit);
            $('#cusMaturityDays').val(r.maturity_days);
            $('#cusPassword, #cusPasswordConfirm').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#customerTabs a:first').tab('show');
            $('#customerModal').modal('show');
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

    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        let valid = true;
        if (!$('#cusName').val().trim()) {
            $('#cusName').addClass('is-invalid').after('<div class="invalid-feedback">Name is required.</div>');
            $('#customerTabs a[href="#tabBasic"]').tab('show');
            valid = false;
        }
        if ($('#cusPassword').val() && $('#cusPassword').val() !== $('#cusPasswordConfirm').val()) {
            $('#cusPasswordConfirm').addClass('is-invalid').after('<div class="invalid-feedback">Passwords do not match.</div>');
            $('#customerTabs a[href="#tabFinancial"]').tab('show');
            valid = false;
        }
        if (!valid) return;

        const id  = $('#cusId').val();
        const url = id ? '{{ url('chevron/stakeholders/customers') }}/' + id : '{{ route('chevron.stakeholders.customers.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: id ? 'PUT' : 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id_prefix: $('#cusPrefix').val(), name: $('#cusName').val(),
                branch_id: $('#cusBranch').val(), owner_name: $('#cusOwnerName').val(),
                address: $('#cusAddress').val(), phone: $('#cusPhone').val(),
                fax: $('#cusFax').val(), mobile: $('#cusMobile').val(),
                email: $('#cusEmail').val(), sales_person: $('#cusSalesPerson').val(),
                customer_account: $('#cusAccount').val(), vat_id: $('#cusVatId').val(),
                identity_type: $('#cusIdentityType').val(), tin_bin_nid: $('#cusTinBinNid').val(),
                contact_person_details: $('#cusContactPerson').val(), prefix: $('#cusOtherPrefix').val(),
                status: $('#cusStatus').val(), country: $('#cusCountry').val(),
                division: $('#cusDivision').val(), district: $('#cusDistrict').val(),
                city: $('#cusCity').val(), region: $('#cusRegion').val(),
                postal_code: $('#cusPostalCode').val(), customer_id_reference: $('#cusCusIdRef').val(),
                pay_type: $('#cusPayType').val(), taxscope: $('#cusTaxscope').val(),
                discount: $('#cusDiscount').val(), commission: $('#cusCommission').val(),
                credit_limit: $('#cusCreditLimit').val(), limit_days: $('#cusLimitDays').val(),
                security_deposit: $('#cusSecurityDeposit').val(), maturity_days: $('#cusMaturityDays').val(),
                portal_password: $('#cusPassword').val(), portal_password_confirm: $('#cusPasswordConfirm').val(),
            },
        })
        .done(function (r) {
            $('#customerModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name) { $('#cusName').addClass('is-invalid').after('<div class="invalid-feedback">' + errors.name[0] + '</div>'); $('#customerTabs a[href="#tabBasic"]').tab('show'); }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'); });
    });
});
</script>
@endpush

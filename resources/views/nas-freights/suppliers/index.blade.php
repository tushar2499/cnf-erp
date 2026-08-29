@extends('nas-freights.layouts.app')

@section('title', 'Suppliers')

@push('styles')
    <style>
        #suppliersTable th,
        #suppliersTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #suppliersTable thead th {
            background: #1a6b60;
            color: #fff;
            font-weight: 600;
            position: sticky;
            z-index: 2;
            top: 0;
        }

        #suppliersTable thead tr:last-child th {
            background: #e9ecef;
            color: #212529;
        }

        #suppliersTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .suppliers-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .suppliers-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .suppliers-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .suppliers-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #suppliersTable_wrapper>.row:last-child {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 3;
            border-top: 1px solid #dee2e6;
            margin: 0;
            padding: 6px 12px;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h4><i class="fa fa-truck-loading me-2 text-info"></i> Suppliers</h4>
        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#supplierModal" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Add Supplier
        </button>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa fa-list me-2"></i> All Suppliers</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item"
                            onclick="$('#suppliersTable').DataTable().button('.buttons-csv').trigger()"><i
                                class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#suppliersTable').DataTable().button('.buttons-excel').trigger()"><i
                                class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#suppliersTable').DataTable().button('.buttons-pdf').trigger()"><i
                                class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><button class="dropdown-item"
                            onclick="$('#suppliersTable').DataTable().button('.buttons-print').trigger()"><i
                                class="fa fa-print me-2"></i>Print</button></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="suppliers-table-wrapper">
                <table id="suppliersTable" class="table table-hover table-striped mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Supplier ID</th>
                            <th>Company Name</th>
                            <th>Owner Name</th>
                            <th>Phone</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Group</th>
                            <th>Taxscope</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Supplier ID"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Company Name"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Owner Name"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Phone"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Mobile"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Email"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Address"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Group"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Taxscope"></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalTitle"><i class="fa fa-plus me-2"></i>Add Supplier</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="supplierForm">
                    @csrf
                    <input type="hidden" id="supplierId">
                    <div class="modal-body">
                        <div class="row g-2">

                            {{-- Supplier ID (readonly, auto-gen) --}}
                            <div class="col-md-3">
                                <label class="form-label">Supplier ID</label>
                                <input type="text" id="fldCode" class="form-control form-control-sm bg-light" readonly
                                    placeholder="Auto-generated">
                            </div>

                            {{-- Company Name --}}
                            <div class="col-md-9">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" id="fldCompanyName" class="form-control form-control-sm"
                                    placeholder="Company / Organisation name" required>
                            </div>

                            {{-- Owner Name --}}
                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" id="fldOwnerName" class="form-control form-control-sm"
                                    placeholder="Owner name">
                            </div>

                            {{-- Address --}}
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <textarea id="fldAddress" class="form-control form-control-sm" rows="2" placeholder="Full address..."></textarea>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" id="fldPhoneNo" class="form-control form-control-sm"
                                    placeholder="+880...">
                            </div>

                            {{-- Fax --}}
                            <div class="col-md-3">
                                <label class="form-label">Fax</label>
                                <input type="text" id="fldFax" class="form-control form-control-sm"
                                    placeholder="Fax no">
                            </div>

                            {{-- URL --}}
                            <div class="col-md-6">
                                <label class="form-label">URL</label>
                                <input type="text" id="fldUrl" class="form-control form-control-sm"
                                    placeholder="https://...">
                            </div>

                            {{-- Mobile --}}
                            <div class="col-md-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" id="fldMobileNo" class="form-control form-control-sm"
                                    placeholder="+880...">
                            </div>

                            {{-- Email --}}
                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" id="fldEmail" class="form-control form-control-sm"
                                    placeholder="email@example.com">
                            </div>

                            {{-- Contact --}}
                            <div class="col-md-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" id="fldContact" class="form-control form-control-sm"
                                    placeholder="Contact name">
                            </div>

                            {{-- Designation --}}
                            <div class="col-md-3">
                                <label class="form-label">Designation</label>
                                <input type="text" id="fldDesignation" class="form-control form-control-sm"
                                    placeholder="e.g. Manager">
                            </div>

                            {{-- Supplier Group --}}
                            <div class="col-md-4">
                                <label class="form-label">Supplier Group</label>
                                <select id="fldSupplierGroup" class="form-select form-select-sm">
                                    <option value="">----- Select Supplier Group -----</option>
                                    @foreach ($supplierGroups as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Taxscope --}}
                            <div class="col-md-4">
                                <label class="form-label">Taxscope <span class="text-danger">*</span></label>
                                <select id="fldTaxscope" class="form-select form-select-sm" required>
                                    @foreach ($taxscopes as $ts)
                                        <option value="{{ $ts }}" {{ $ts === 'Exempted' ? 'selected' : '' }}>
                                            {{ $ts }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="fldStatus" class="form-select form-select-sm" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white" id="btnSave">
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

        $(function() {
            table = $('#suppliersTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [],
                orderCellsTop: true,
                pageLength: 15,
                lengthMenu: [
                    [15, 25, 50, 100, 200, 500, 1000],
                    [15, 25, 50, 100, 200, 500, 1000]
                ],
                ajax: '{{ route('nas-freights.stakeholders.suppliers.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '45px'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'owner_name',
                        name: 'owner_name'
                    },
                    {
                        data: 'phone_no',
                        name: 'phone_no'
                    },
                    {
                        data: 'mobile_no',
                        name: 'mobile_no'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'group_badge',
                        name: 'supplier_group',
                        orderable: false
                    },
                    {
                        data: 'taxscope',
                        name: 'taxscope'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '90px'
                    },
                ],
                dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [{
                    extend: 'csv'
                }, {
                    extend: 'excel'
                }, {
                    extend: 'pdf'
                }, {
                    extend: 'print'
                }],
                initComplete: function() {
                    const firstRowH = $('#suppliersTable thead tr:first-child').outerHeight();
                    $('#suppliersTable thead tr:last-child th').css('top', firstRowH + 'px');

                    var self = this.api();
                    self.columns().every(function(i) {
                        var col = this;
                        var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table()
                            .container());
                        if ($in.length) {
                            $in.on('click mousedown keydown', function(e) {
                                e.stopPropagation();
                            });
                            var timer;
                            $in.on('input', function() {
                                clearTimeout(timer);
                                timer = setTimeout(function() {
                                    col.search($in.val()).draw();
                                }, 400);
                            });
                        }
                    });
                },
                language: {
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No suppliers yet.</div>'
                },
            });

            // Reset on Add
            $('#btnAdd').on('click', function() {
                $('#modalTitle').html('<i class="fa fa-plus me-2"></i>Add Supplier');
                $('#supplierId').val('');
                $('#fldCode').val('').attr('placeholder', 'Auto-generated on save');
                $('#supplierForm')[0].reset();
                $('#fldTaxscope').val('Exempted');
                $('#fldStatus').val('Active');
                clearErrors();
            });

            // Edit
            $(document).on('click', '.btn-edit', function() {
                const d = $(this).data();
                $('#modalTitle').html('<i class="fa fa-edit me-2"></i>Edit Supplier');
                $('#supplierId').val(d.id);
                $('#fldCode').val(d.code);
                $('#fldCompanyName').val(d.company_name);
                $('#fldOwnerName').val(d.owner_name);
                $('#fldAddress').val(d.address);
                $('#fldPhoneNo').val(d.phone_no);
                $('#fldFax').val(d.fax);
                $('#fldUrl').val(d.url);
                $('#fldMobileNo').val(d.mobile_no);
                $('#fldEmail').val(d.email);
                $('#fldContact').val(d.contact);
                $('#fldDesignation').val(d.designation);
                $('#fldSupplierGroup').val(d.supplier_group);
                $('#fldTaxscope').val(d.taxscope || 'Exempted');
                $('#fldStatus').val(d.is_active == 1 ? 'Active' : 'Inactive');
                clearErrors();
                $('#supplierModal').modal('show');
            });

            // Delete
            $(document).on('click', '.btn-delete', function() {
                const url = $(this).data('url'),
                    name = $(this).data('name');
                Swal.fire({
                        title: 'Delete "' + name + '"?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Yes, delete'
                    })
                    .then(r => {
                        if (r.isConfirmed) {
                            $.ajax({
                                    url,
                                    method: 'DELETE',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    }
                                })
                                .done(d => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: d.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                })
                                .fail(() => Swal.fire({
                                    icon: 'error',
                                    title: 'Delete failed.'
                                }));
                        }
                    });
            });

            // Save
            $('#supplierForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#supplierId').val();
                const url = id ?
                    '{{ url('nas-freights/stakeholders/suppliers') }}/' + id :
                    '{{ route('nas-freights.stakeholders.suppliers.store') }}';

                clearErrors();
                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                        url,
                        method: id ? 'PUT' : 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            company_name: $('#fldCompanyName').val(),
                            owner_name: $('#fldOwnerName').val(),
                            address: $('#fldAddress').val(),
                            phone_no: $('#fldPhoneNo').val(),
                            fax: $('#fldFax').val(),
                            url: $('#fldUrl').val(),
                            mobile_no: $('#fldMobileNo').val(),
                            email: $('#fldEmail').val(),
                            contact: $('#fldContact').val(),
                            designation: $('#fldDesignation').val(),
                            supplier_group: $('#fldSupplierGroup').val(),
                            taxscope: $('#fldTaxscope').val(),
                            status: $('#fldStatus').val(),
                        },
                    })
                    .done(function(r) {
                        $('#supplierModal').modal('hide');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: r.message,
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        table.ajax.reload();
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422) {
                            const map = {
                                company_name: '#fldCompanyName',
                                owner_name: '#fldOwnerName',
                                address: '#fldAddress',
                                phone_no: '#fldPhoneNo',
                                fax: '#fldFax',
                                url: '#fldUrl',
                                mobile_no: '#fldMobileNo',
                                email: '#fldEmail',
                                contact: '#fldContact',
                                designation: '#fldDesignation',
                                supplier_group: '#fldSupplierGroup',
                                taxscope: '#fldTaxscope',
                            };
                            $.each(xhr.responseJSON.errors, function(field, msgs) {
                                const $el = $(map[field]);
                                if ($el.length) $el.addClass('is-invalid').after(
                                    '<div class="invalid-feedback">' + msgs[0] + '</div>');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Something went wrong.'
                            });
                        }
                    })
                    .always(function() {
                        $('#btnSave').prop('disabled', false).html(
                            '<i class="fa fa-save me-1"></i> Save');
                    });
            });

            function clearErrors() {
                $('#supplierForm .is-invalid').removeClass('is-invalid');
                $('#supplierForm .invalid-feedback').remove();
            }
        });
    </script>
@endpush

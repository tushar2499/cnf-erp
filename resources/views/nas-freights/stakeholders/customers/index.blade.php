@extends('nas-freights.layouts.app')

@section('title', 'Customers')

@push('styles')
    <style>
        #customersTable th,
        #customersTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #customersTable thead th {
            background: #1a6b60;
            color: #fff;
            font-weight: 600;
            position: sticky;
            z-index: 2;
            top: 0;
        }

        #customersTable thead tr:last-child th {
            background: #e9ecef;
            color: #212529;
        }

        #customersTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .cus-list-table tbody tr:hover {
            background: #e8f8f5;
        }

        .customers-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .customers-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .customers-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .customers-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #customersTable_wrapper>.row:last-child {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 3;
            border-top: 1px solid #dee2e6;
            margin: 0;
            padding: 6px 12px;
        }

        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .25rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h4><i class="fa fa-users me-2 text-info"></i> Customers</h4>
        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#customerModal" id="btnAddNew">
            <i class="fa fa-plus me-1"></i> Add New Customer
        </button>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa fa-list me-2"></i> Customer List</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item"
                            onclick="$('#customersTable').DataTable().button('.buttons-csv').trigger()"><i
                                class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#customersTable').DataTable().button('.buttons-excel').trigger()"><i
                                class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#customersTable').DataTable().button('.buttons-pdf').trigger()"><i
                                class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><button class="dropdown-item"
                            onclick="$('#customersTable').DataTable().button('.buttons-print').trigger()"><i
                                class="fa fa-print me-2"></i>Print</button></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="customers-table-wrapper">
                <table id="customersTable" class="table table-hover table-striped cus-list-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>BIN No</th>
                            <th>Group</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Code"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Name"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search BIN No"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Group"></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search Mobile"></th>
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
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalTitle"><i class="fa fa-plus me-2"></i>Add New Customer</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="customerForm">
                    @csrf
                    <input type="hidden" id="cusId">
                    <div class="modal-body">
                        <div class="row g-2">

                            <div class="col-md-4">
                                <label class="form-label">Customer Prefix <span class="text-danger">*</span></label>
                                <select id="cusPrefix" class="form-select form-select-sm">
                                    <option value="CUS-">CUS-</option>
                                    <option value="CLI-">CLI-</option>
                                    <option value="CT-">CT-</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Customer group</label>
                                <select id="cusGroup" class="form-select form-select-sm">
                                    <option value="">-- Select Group --</option>
                                    @foreach ($customerGroups as $g)
                                        <option value="{{ $g }}">{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="cusName" class="form-control form-control-sm"
                                    placeholder="Customer / Company name">
                                <div class="invalid-feedback" id="cusNameErr"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea id="cusAddress" class="form-control form-control-sm" rows="2" placeholder="Full address..."></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">BIN No</label>
                                <input type="text" id="cusBin" class="form-control form-control-sm"
                                    placeholder="Business Identification Number">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mobile</label>
                                <input type="text" id="cusMobile" class="form-control form-control-sm"
                                    placeholder="+880...">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" id="cusEmail" class="form-control form-control-sm"
                                    placeholder="email@example.com">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                            <i class="fa fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-success px-4" id="btnSave">
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
            table = $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                pageLength: 15,
                order: [],
                orderCellsTop: true,
                lengthMenu: [
                    [15, 25, 50, 100, 200, 500, 1000],
                    [15, 25, 50, 100, 200, 500, 1000]
                ],
                ajax: '{{ route('nas-freights.stakeholders.customers.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '40px'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'tin_bin_nid',
                        name: 'tin_bin_nid'
                    },
                    {
                        data: 'customer_group',
                        name: 'customer_group'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '70px'
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
                    const firstRowH = $('#customersTable thead tr:first-child').outerHeight();
                    $('#customersTable thead tr:last-child th').css('top', firstRowH + 'px');

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
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No customers yet.</div>'
                },
            });

            function setFormMode(mode) {
                if (mode === 'edit') {
                    $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Customer');
                    $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
                } else {
                    $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add New Customer');
                    $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
                }
            }

            function resetForm() {
                $('#cusId').val('');
                $('#cusPrefix').val('CUS-');
                $('#cusGroup').val('');
                $('#cusName').val('').removeClass('is-invalid');
                $('#cusAddress').val('');
                $('#cusBin').val('');
                $('#cusMobile').val('');
                $('#cusEmail').val('');
                $('#cusNameErr').text('');
                setFormMode('add');
            }

            $('#btnAddNew').on('click', function() {
                resetForm();
                $('#customerModal').modal('show');
            });

            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                $.getJSON('{{ url('nas-freights/stakeholders/customers') }}/' + id, function(r) {
                    resetForm();
                    $('#cusId').val(r.id);
                    $('#cusPrefix').val(r.id_prefix);
                    $('#cusGroup').val(r.customer_group || '');
                    $('#cusName').val(r.name);
                    $('#cusAddress').val(r.address || '');
                    $('#cusBin').val(r.tin_bin_nid || '');
                    $('#cusMobile').val(r.mobile || '');
                    $('#cusEmail').val(r.email || '');
                    setFormMode('edit');
                    $('#customerModal').modal('show');
                }).fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could not load customer details.'
                    });
                });
            });

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
                    .then(res => {
                        if (res.isConfirmed) {
                            $.ajax({
                                    url,
                                    method: 'DELETE',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    }
                                })
                                .done(r => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: r.message,
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

            $('#customerForm').on('submit', function(e) {
                e.preventDefault();
                $('#cusName').removeClass('is-invalid');
                $('#cusNameErr').text('');

                if (!$('#cusName').val().trim()) {
                    $('#cusName').addClass('is-invalid');
                    $('#cusNameErr').text('Please enter customer name.');
                    return;
                }

                const id = $('#cusId').val();
                const url = id ?
                    '{{ url('nas-freights/stakeholders/customers') }}/' + id :
                    '{{ route('nas-freights.stakeholders.customers.store') }}';

                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                        url,
                        method: id ? 'PUT' : 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id_prefix: $('#cusPrefix').val(),
                            customer_group: $('#cusGroup').val(),
                            name: $('#cusName').val(),
                            address: $('#cusAddress').val(),
                            tin_bin_nid: $('#cusBin').val(),
                            mobile: $('#cusMobile').val(),
                            email: $('#cusEmail').val(),
                        },
                    })
                    .done(function(r) {
                        $('#customerModal').modal('hide');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: r.message,
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        resetForm();
                        table.ajax.reload();
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.name) {
                                $('#cusName').addClass('is-invalid');
                                $('#cusNameErr').text(errors.name[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Something went wrong.'
                            });
                        }
                    })
                    .always(function() {
                        const isEdit = $('#cusId').val();
                        $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' +
                            (isEdit ? 'Update' : 'Save'));
                    });
            });
        });
    </script>
@endpush

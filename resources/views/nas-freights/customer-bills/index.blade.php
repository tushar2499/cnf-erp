@extends('nas-freights.layouts.app')

@section('title', 'Customer Bills')

@push('styles')
    <style>
        #billsTable th,
        #billsTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #billsTable thead tr:first-child th {
            background: #1a6b60;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #billsTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #billsTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .bills-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .bills-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .bills-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bills-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #billsTable_wrapper>.row:last-child {
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
        <h4><i class="fa fa-file-invoice-dollar me-2 text-info"></i> Customer Bills</h4>
        <a href="{{ route('nas-freights.customer-bills.create') }}" class="btn btn-sm btn-info text-white">
            <i class="fa fa-plus me-1"></i> New Bill
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
            style="background:#0c2340;color:#fff;">
            <span><i class="fa fa-list me-2"></i> Bill List</span>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="date" id="fromDate" class="form-control form-control-sm" style="width:145px"
                    title="From Date">
                <span class="text-white-50 small fw-semibold">to</span>
                <input type="date" id="toDate" class="form-control form-control-sm" style="width:145px"
                    title="To Date">
                <button id="btnFilter" class="btn btn-sm btn-info text-white"><i class="fa fa-filter me-1"></i>Filter</button>
                <button id="btnReset" class="btn btn-sm btn-outline-light"><i class="fa fa-times me-1"></i>Reset</button>
                <div class="vr bg-light"></div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item"
                                onclick="$('#billsTable').DataTable().button('.buttons-csv').trigger()"><i
                                    class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#billsTable').DataTable().button('.buttons-excel').trigger()"><i
                                    class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#billsTable').DataTable().button('.buttons-pdf').trigger()"><i
                                    class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button class="dropdown-item"
                                onclick="$('#billsTable').DataTable().button('.buttons-print').trigger()"><i
                                    class="fa fa-print me-2"></i>Print</button></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="bills-table-wrapper">
                <table id="billsTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Bill No</th>
                            <th>Bill Date</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Customer</th>
                            <th>Delivery Type</th>
                            <th>Bill Type</th>
                            <th>Sub Total</th>
                            <th>TDS Amt</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
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
        $(function() {
            var table = $('#billsTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                orderCellsTop: true,
                pageLength: 15,
                order: [],
                lengthMenu: [
                    [15, 25, 50, 100, 200, 500, 1000],
                    [15, 25, 50, 100, 200, 500, 1000]
                ],
                ajax: {
                    url: '{{ route('nas-freights.customer-bills.index') }}',
                    data: function(d) {
                        d.from_date = $('#fromDate').val();
                        d.to_date = $('#toDate').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '45px',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '140px',
                        className: 'text-center'
                    },
                    {
                        data: 'bill_no',
                        name: 'bill_no'
                    },
                    {
                        data: 'bill_date',
                        name: 'bill_date'
                    },
                    {
                        data: 'from_date',
                        name: 'from_date'
                    },
                    {
                        data: 'to_date',
                        name: 'to_date'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'delivery_type',
                        name: 'delivery_type'
                    },
                    {
                        data: 'bill_type',
                        name: 'bill_type'
                    },
                    {
                        data: 'sub_total',
                        name: 'sub_total',
                        className: 'text-end'
                    },
                    {
                        data: 'tds_amount',
                        name: 'tds_amount',
                        className: 'text-end'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        className: 'text-end fw-bold'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
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
                language: {
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-file-invoice fa-2x mb-2 d-block"></i>No bills yet.</div>'
                },
                initComplete: function() {
                    const firstRowH = $('#billsTable thead tr:first-child').outerHeight();
                    $('#billsTable thead tr:last-child th').css('top', firstRowH + 'px');

                    var self = this.api();
                    self.columns().every(function(i) {
                        var col = this;
                        var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
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
            });

            $('#fromDate').on('change', function() {
                const from = $(this).val();
                if (from) {
                    $('#toDate').attr('min', from);
                    if ($('#toDate').val() && $('#toDate').val() < from) {
                        $('#toDate').val('');
                    }
                } else {
                    $('#toDate').removeAttr('min');
                }
            });

            $('#toDate').on('change', function() {
                const to = $(this).val();
                if (to) {
                    $('#fromDate').attr('max', to);
                    if ($('#fromDate').val() && $('#fromDate').val() > to) {
                        $('#fromDate').val('');
                    }
                } else {
                    $('#fromDate').removeAttr('max');
                }
            });

            $('#btnFilter').on('click', function() {
                if (!$('#fromDate').val() && !$('#toDate').val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select at least one date.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#fromDate, #toDate').val('');
                table.ajax.reload();
            });

            $(document).on('click', '.btn-confirm', function() {
                const url = $(this).data('url'),
                    name = $(this).data('name');
                Swal.fire({
                        title: 'Confirm bill "' + name + '"?',
                        text: 'Bill will be marked as Confirmed and appear in Due List.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'Yes, confirm'
                    })
                    .then(res => {
                        if (res.isConfirmed) {
                            $.ajax({
                                    url,
                                    method: 'PATCH',
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
                                    title: 'Action failed.'
                                }));
                        }
                    });
            });

            $(document).on('click', '.btn-delete', function() {
                const url = $(this).data('url'),
                    name = $(this).data('name');
                Swal.fire({
                        title: 'Delete bill "' + name + '"?',
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
        });
    </script>
@endpush

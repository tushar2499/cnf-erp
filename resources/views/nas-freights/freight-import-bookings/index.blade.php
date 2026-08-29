@extends('nas-freights.layouts.app')

@section('title', 'Freight Import Bookings')

@push('styles')
    <style>
        #freightBookingsTable th,
        #freightBookingsTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #freightBookingsTable thead tr:first-child th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #freightBookingsTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #freightBookingsTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .freight-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .freight-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .freight-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .freight-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #freightBookingsTable_wrapper>.row:last-child {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 3;
            border-top: 1px solid #dee2e6;
            margin: 0;
            padding: 6px 12px;
        }

        .status-tab {
            cursor: pointer;
        }

        .status-tab.active {
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h4><i class="fa fa-ship me-2 text-primary"></i> Freight Import Bookings</h4>
        <a href="{{ route('nas-freights.freight-import-bookings.create') }}" class="btn btn-sm btn-primary">
            <i class="fa fa-plus me-1"></i> New Import Booking
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex gap-1 flex-wrap align-items-center">
                <span class="me-2 fw-semibold"><i class="fa fa-list me-1"></i> All Import Bookings</span>
                <button class="btn btn-sm btn-outline-secondary status-tab active" data-status="">All</button>
                <button class="btn btn-sm btn-outline-secondary status-tab" data-status="Draft">Draft</button>
                <button class="btn btn-sm btn-outline-success status-tab" data-status="Confirmed">Confirmed</button>
                <button class="btn btn-sm btn-outline-info status-tab" data-status="In-Transit">In-Transit</button>
                <button class="btn btn-sm btn-outline-primary status-tab" data-status="Delivered">Delivered</button>
                <button class="btn btn-sm btn-outline-danger status-tab" data-status="Cancelled">Cancelled</button>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="date" id="fromDate" class="form-control form-control-sm" style="width:145px"
                    title="From Date">
                <span class="text-muted small fw-semibold">to</span>
                <input type="date" id="toDate" class="form-control form-control-sm" style="width:145px"
                    title="To Date">
                <button id="btnFilter" class="btn btn-sm btn-primary"><i class="fa fa-filter me-1"></i>Filter</button>
                <button id="btnReset" class="btn btn-sm btn-outline-secondary"><i
                        class="fa fa-times me-1"></i>Reset</button>
                <div class="vr"></div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item"
                                onclick="$('#freightBookingsTable').DataTable().button('.buttons-csv').trigger()"><i
                                    class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#freightBookingsTable').DataTable().button('.buttons-excel').trigger()"><i
                                    class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#freightBookingsTable').DataTable().button('.buttons-pdf').trigger()"><i
                                    class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button class="dropdown-item"
                                onclick="$('#freightBookingsTable').DataTable().button('.buttons-print').trigger()"><i
                                    class="fa fa-print me-2"></i>Print</button></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="freight-table-wrapper">
                <table id="freightBookingsTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>IGM No</th>
                            <th>Service</th>
                            <th>POL → POD</th>
                            <th>Carrier</th>
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
@endsection

@push('scripts')
    <script>
        $(function() {
            var currentStatus = '';

            var table = $('#freightBookingsTable').DataTable({
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
                    url: '{{ route('nas-freights.freight-import-bookings.index') }}',
                    data: function(d) {
                        d.status_filter = currentStatus;
                        d.from_date = $('#fromDate').val();
                        d.to_date = $('#toDate').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '40px',
                        className: 'text-center'
                    },
                    {
                        data: 'freight_booking_no',
                        name: 'freight_booking_no'
                    },
                    {
                        data: 'booking_date',
                        name: 'booking_date'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'igm_no',
                        name: 'igm_no'
                    },
                    {
                        data: 'service_type',
                        name: 'service_type'
                    },
                    {
                        data: 'route',
                        name: 'route',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'carrier',
                        name: 'shippingCarrier.name',
                        defaultContent: '—'
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
                        width: '90px',
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
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No import bookings yet.</div>'
                },
                initComplete: function() {
                    const firstRowH = $('#freightBookingsTable thead tr:first-child').outerHeight();
                    $('#freightBookingsTable thead tr:last-child th').css('top', firstRowH + 'px');

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

            $('.status-tab').on('click', function() {
                $('.status-tab').removeClass('active');
                $(this).addClass('active');
                currentStatus = $(this).data('status');
                table.ajax.reload();
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
        });
    </script>
@endpush
@extends('nas-freights.layouts.app')

@section('title', 'Money Receipts')

@push('styles')
    <style>
        #receiptTable th,
        #receiptTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #receiptTable thead tr:first-child th {
            background: #1a6b60;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #receiptTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #receiptTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .receipt-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .receipt-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .receipt-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .receipt-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #receiptTable_wrapper>.row:last-child {
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
        <h4><i class="fa fa-money-bill-wave me-2 text-info"></i> Money Receipts</h4>
        <a href="{{ route('nas-freights.money-receipts.create') }}" class="btn btn-sm btn-info text-white">
            <i class="fa fa-plus me-1"></i> New Receipt
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
            style="background:#0c2340;color:#fff;">
            <span><i class="fa fa-list me-2"></i> Receipt List</span>
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
                                onclick="$('#receiptTable').DataTable().button('.buttons-csv').trigger()"><i
                                    class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#receiptTable').DataTable().button('.buttons-excel').trigger()"><i
                                    class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                        <li><button class="dropdown-item"
                                onclick="$('#receiptTable').DataTable().button('.buttons-pdf').trigger()"><i
                                    class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button class="dropdown-item"
                                onclick="$('#receiptTable').DataTable().button('.buttons-print').trigger()"><i
                                    class="fa fa-print me-2"></i>Print</button></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="receipt-table-wrapper">
                <table id="receiptTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Hard Copy No</th>
                            <th>Receipt Date</th>
                            <th>Customer</th>
                            <th>Bill No</th>
                            <th>Bill Amount</th>
                            <th>Amount Received</th>
                            <th>Payment Mode</th>
                            <th>Reference No</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th></th>
                            <th></th>
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
@endsection

@push('scripts')
    <script>
        $(function() {
            var table = $('#receiptTable').DataTable({
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
                    url: '{{ route('nas-freights.money-receipts.index') }}',
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
                        data: 'receipt_no',
                        name: 'receipt_no'
                    },
                    {
                        data: 'hard_copy_no',
                        name: 'hard_copy_no'
                    },
                    {
                        data: 'receipt_date',
                        name: 'receipt_date'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'bill_no',
                        name: 'bill_no'
                    },
                    {
                        data: 'bill_amount',
                        name: 'bill_amount',
                        className: 'text-end'
                    },
                    {
                        data: 'amount_received',
                        name: 'amount_received',
                        className: 'text-end fw-bold text-success'
                    },
                    {
                        data: 'payment_mode',
                        name: 'payment_mode'
                    },
                    {
                        data: 'reference_no',
                        name: 'reference_no'
                    },
                    {
                        data: 'action',
                        name: 'action',
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
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-money-bill-wave fa-2x mb-2 d-block"></i>No receipts yet.</div>'
                },
                initComplete: function() {
                    const firstRowH = $('#receiptTable thead tr:first-child').outerHeight();
                    $('#receiptTable thead tr:last-child th').css('top', firstRowH + 'px');

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
        });
    </script>
@endpush

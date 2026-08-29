@extends('nas-freights.layouts.app')

@section('title', 'Supplier Due List')

@push('styles')
    <style>
        #supDueTable th,
        #supDueTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #supDueTable thead tr:first-child th {
            background: #1a6b60;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #supDueTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #supDueTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        #supDueTable tfoot td {
            background: #e8f4f1;
            font-weight: 700;
            font-size: .8rem;
        }

        .sup-due-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .sup-due-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .sup-due-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sup-due-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #supDueTable_wrapper>.row:last-child {
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
        <h4><i class="fa fa-truck-loading me-2 text-info"></i> Supplier Due List</h4>
    </div>

    {{-- Filter card --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">From Date</label>
                    <input type="date" id="fldFrom" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">To Date</label>
                    <input type="date" id="fldTo" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;font-weight:600">Supplier</label>
                    <select id="fldSupplier" class="form-select form-select-sm" style="width:100%">
                        <option value="">All Suppliers</option>
                    </select>
                    <input type="hidden" id="fldSupplierId">
                </div>
                <div class="col-md-2">
                    <button id="btnFilter" class="btn btn-success btn-sm w-100">
                        <i class="fa fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <div class="dropdown w-100">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item"
                                    onclick="$('#supDueTable').DataTable().button('.buttons-csv').trigger()"><i
                                        class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                            <li><button class="dropdown-item"
                                    onclick="$('#supDueTable').DataTable().button('.buttons-excel').trigger()"><i
                                        class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                            <li><button class="dropdown-item"
                                    onclick="$('#supDueTable').DataTable().button('.buttons-pdf').trigger()"><i
                                        class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><button class="dropdown-item"
                                    onclick="$('#supDueTable').DataTable().button('.buttons-print').trigger()"><i
                                        class="fa fa-print me-2"></i>Print</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"
            style="background:#0c2340;color:#fff;font-size:.85rem;">
            <span><i class="fa fa-list me-2"></i> Confirmed Payment Orders — Awaiting Payment</span>
            <span class="badge bg-warning text-dark" id="totalBadge">Total Due: 0.00</span>
        </div>
        <div class="card-body p-0">
            <div class="sup-due-table-wrapper">
                <table id="supDueTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pay Order No</th>
                            <th>Bill Date</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Supplier</th>
                            <th>Bill By</th>
                            <th>Total Amount</th>
                            <th>Overdue Days</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th></th>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" class="text-end">Total Due Amount:</td>
                            <td class="text-end text-success" id="totalAmt">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#fldSupplier').select2({
            theme: 'bootstrap-5',
            placeholder: 'All Suppliers',
            minimumInputLength: 3,
            allowClear: true,
            ajax: {
                url: '{{ route('nas-freights.due-lists.supplier-search') }}',
                dataType: 'json',
                delay: 250,
                data: d => ({
                    q: d.term
                }),
                processResults: d => ({
                    results: d
                }),
            },
        }).on('select2:select', e => $('#fldSupplierId').val(e.params.data.id))
            .on('select2:clear', () => $('#fldSupplierId').val(''));

        var table = $('#supDueTable').DataTable({
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
                url: '{{ route('nas-freights.due-lists.supplier') }}',
                data: d => {
                    d.from_date = $('#fldFrom').val();
                    d.to_date = $('#fldTo').val();
                    d.supplier_id = $('#fldSupplierId').val();
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '45px'
                },
                {
                    data: 'pay_order_no',
                    name: 'pay_order_no'
                },
                {
                    data: 'bill_date',
                    name: 'bill_date'
                },
                {
                    data: 'from_date',
                    name: 'from_date',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'to_date',
                    name: 'to_date',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'bill_by',
                    name: 'bill_by'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'text-end fw-bold'
                },
                {
                    data: 'overdue_days',
                    name: 'overdue_days',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: v => v > 0 ?
                        '<span class="badge bg-danger">' + v + 'd</span>' :
                        '<span class="badge bg-secondary">Today</span>'
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
                emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-check-circle fa-2x mb-2 d-block text-success"></i>No confirmed outstanding payment orders.</div>'
            },
            initComplete: function() {
                const firstRowH = $('#supDueTable thead tr:first-child').outerHeight();
                $('#supDueTable thead tr:last-child th').css('top', firstRowH + 'px');

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
            drawCallback: function() {
                var total = 0;
                this.api().rows({
                    search: 'applied'
                }).data().each(function(r) {
                    total += parseFloat((r.total_amount + '').replace(/,/g, '')) || 0;
                });
                var fmt = total.toLocaleString('en-BD', {
                    minimumFractionDigits: 2
                });
                $('#totalAmt').text(fmt);
                $('#totalBadge').text('Total Due: ' + fmt);
            },
        });

        $('#btnFilter').on('click', function() {
            table.ajax.reload();
        });
        $('#fldFrom, #fldTo').on('change', function() {
            table.ajax.reload();
        });
    </script>
@endpush

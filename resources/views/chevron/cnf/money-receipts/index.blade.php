@extends('chevron.layouts.app')

@section('title', 'Money Receipts')

@push('styles')
    <style>
        #mrTable th,
        #mrTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #mrTable thead th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            z-index: 2;
            top: 0;
        }

        #mrTable thead tr:last-child th {
            background: #f8f9fa;
        }

        #mrTable thead tr:last-child th input.form-control {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
        }

        .mr-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .mr-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .mr-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .mr-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #mrTable_wrapper>.row:last-child {
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
        <h4><i class="fa fa-money-bill-wave me-2 text-primary"></i> Money Receipts</h4>
        @if (auth()->user()->hasPermission('cnf.money-receipt.create'))
            <a href="{{ route('chevron.cnf.money-receipts.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> New Receipt
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa fa-list me-2"></i> All Money Receipts</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa fa-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item"
                            onclick="$('#mrTable').DataTable().button('.buttons-csv').trigger()"><i
                                class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#mrTable').DataTable().button('.buttons-excel').trigger()"><i
                                class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                    <li><button class="dropdown-item"
                            onclick="$('#mrTable').DataTable().button('.buttons-pdf').trigger()"><i
                                class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><button class="dropdown-item"
                            onclick="$('#mrTable').DataTable().button('.buttons-print').trigger()"><i
                                class="fa fa-print me-2"></i>Print</button></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="mr-table-wrapper">
                <table id="mrTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Party Name</th>
                            <th>Pay Type</th>
                            <th>Payable Amt</th>
                            <th>Total Amt</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
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
            var table = $('#mrTable').DataTable({
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
                ajax: '{{ route('chevron.cnf.money-receipts.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '40px',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '90px',
                        className: 'text-center'
                    },
                    {
                        data: 'receipt_no',
                        name: 'receipt_no'
                    },
                    {
                        data: 'receipt_date',
                        name: 'receipt_date'
                    },
                    {
                        data: 'party_name',
                        name: 'party_name'
                    },
                    {
                        data: 'pay_type',
                        name: 'pay_type'
                    },
                    {
                        data: 'payable_amount',
                        name: 'payable_amount',
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                ],
                dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
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
                    emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No money receipts yet.</div>'
                },
                initComplete: function() {
                    const firstRowH = $('#mrTable thead tr:first-child').outerHeight();
                    $('#mrTable thead tr:last-child th').css('top', firstRowH + 'px');

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
        });
    </script>
@endpush

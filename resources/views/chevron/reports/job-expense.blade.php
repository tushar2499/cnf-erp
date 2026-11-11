@extends('chevron.layouts.app')

@section('title', 'Job Expense')

@push('styles')
    <style>
        .filter-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: 16px 20px 8px;
            margin-bottom: 18px;
        }

        .report-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .report-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .report-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .report-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #jobExpenseTable th,
        #jobExpenseTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #jobExpenseTable thead th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            z-index: 2;
            top: 0;
        }

        #jobExpenseTable thead tr:last-child th {
            background: #f8f9fa;
        }

        #jobExpenseTable thead tr:last-child th input,
        #jobExpenseTable thead tr:last-child th select {
            min-width: 72px;
            width: 100%;
            box-sizing: border-box;
            font-size: .7rem;
            padding: .15rem .3rem;
        }

        #jobExpenseTable tfoot td {
            background: #d4e8ff;
            font-weight: 700;
            position: sticky;
            bottom: 0;
            z-index: 2;
        }

        .head-total {
            display: block;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #9bb8d6;
            font-size: 11px;
            font-weight: 700;
            color: #12405f;
        }

        .badge-yes {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 11px;
        }

        .badge-no {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 11px;
        }

        .summary-box {
            background: #fff;
            border: 1px solid #b0ccee;
            border-radius: 6px;
            padding: 14px 18px;
            margin-top: 16px;
        }

        .summary-box table {
            width: 100%;
            font-size: 13px;
        }

        .summary-box td {
            padding: 4px 8px;
        }

        .summary-box .lbl {
            color: #555;
            width: 55%;
        }

        .summary-box .val {
            font-weight: 700;
            text-align: right;
        }

        #jobExpenseTable_wrapper>.row:last-child {
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
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-bold"><i class="fa fa-money-check-alt me-2" style="color:#1a4a6b"></i>Job Expense</h5>
    </div>

    <div class="filter-card">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:12px">From Date</label>
                <input type="date" id="fromDate" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:12px">To Date</label>
                <input type="date" id="toDate" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:12px">Job No</label>
                <select id="jobSelect" class="form-select form-select-sm" style="width:100%">
                    @if (request('job_no'))
                        <option value="{{ request('job_no') }}" selected>{{ request('job_no') }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2 pb-1 flex-wrap">
                <button type="button" id="btnFilter" class="btn btn-primary btn-sm px-3">
                    <i class="fa fa-search me-1"></i>Filter
                </button>
                <button type="button" id="btnReset" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="fa fa-times me-1"></i>Reset
                </button>
                <button type="button" id="btnPrint" class="btn btn-outline-dark btn-sm px-3">
                    <i class="fa fa-print me-1"></i>Print
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm px-3 dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-download me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item export-btn" data-export="csv">
                                <i class="fa fa-file-csv me-2 text-secondary"></i>CSV
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item export-btn" data-export="excel">
                                <i class="fa fa-file-excel me-2 text-success"></i>Excel
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item export-btn" data-export="pdf">
                                <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                            </button>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button type="button" class="dropdown-item export-btn" data-export="print">
                                <i class="fa fa-print me-2"></i>Print
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="noFilterPrompt" class="text-center text-muted py-5 {{ $hasFilters ? 'd-none' : '' }}">
        <i class="fa fa-filter fa-2x mb-3 d-block"></i>
        Select filters above and click Filter.
    </div>

    <div id="reportCard" class="card {{ $hasFilters ? '' : 'd-none' }}">
        <div class="card-body p-0">
            <div class="report-table-wrapper">
                <table id="jobExpenseTable" class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width:4%">SL</th>
                            <th>Branch</th>
                            <th>Job No</th>
                            <th>Party Name</th>
                            <th style="width:10%">Expense Date</th>
                            <th style="width:10%">Expense No</th>
                            <th>Expense By</th>
                            <th>Expense Category</th>
                            <th>Expense Head</th>
                            <th style="width:9%">Receiptable</th>
                            <th style="width:11%;text-align:right">Amount<span class="head-total">Total <span
                                        id="headTotalExpense">0.00</span></span></th>
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
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                            <th>
                                <select class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" class="text-end">Total</td>
                            <td class="text-end"><span id="footTotalExpense">0.00</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div id="summaryBox" class="summary-box {{ $hasFilters ? '' : 'd-none' }}">
        <div class="row">
            <div class="col-md-6">
                <table>
                    <tr>
                        <td class="lbl">Total Job Expense Amount</td>
                        <td class="val" id="sumTotalExpense">0.00</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table>
                    <tr>
                        <td class="lbl">Total Expense Rows</td>
                        <td class="val" id="sumRows">0</td>
                    </tr>
                    <tr>
                        <td class="lbl">Jobs Covered</td>
                        <td class="val" id="sumJobs">0</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#jobSelect').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search Job No...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('chevron.reports.job-expense.search-jobs') }}',
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term
                    }),
                    processResults: d => ({
                        results: d
                    }),
                },
            });

            function currentFilters() {
                return {
                    from_date: $('#fromDate').val(),
                    to_date: $('#toDate').val(),
                    job_no: $('#jobSelect').val(),
                };
            }

            function hasFilters() {
                const filters = currentFilters();

                return !!(filters.from_date || filters.to_date || filters.job_no);
            }

            function setReportVisible(visible) {
                $('#reportCard, #summaryBox').toggleClass('d-none', !visible);
                $('#noFilterPrompt').toggleClass('d-none', visible);
            }

            function resetTotals() {
                $('#headTotalExpense, #footTotalExpense, #sumTotalExpense').text('0.00');
                $('#sumRows, #sumJobs').text('0');
            }

            var table = null;

            function loadReport() {
                if (table) {
                    table.ajax.reload();

                    return;
                }

                table = $('#jobExpenseTable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    pageLength: 100,
                    orderCellsTop: true,
                    order: [
                        [2, 'asc'],
                        [4, 'asc']
                    ],
                    lengthMenu: [
                        [25, 50, 100, 200, 500, 1000, -1],
                        [25, 50, 100, 200, 500, 1000, 'All']
                    ],
                    buttons: [
                        { extend: 'csv' },
                        { extend: 'excel' },
                        { extend: 'pdf' },
                        { extend: 'print' },
                    ],
                    ajax: {
                        url: '{{ route('chevron.reports.job-expense') }}',
                        data: function(d) {
                            Object.assign(d, currentFilters());
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
                            data: 'branch_name',
                            name: 'branch_name'
                        },
                        {
                            data: 'job_no',
                            name: 'job_no'
                        },
                        {
                            data: 'party_name',
                            name: 'party_name'
                        },
                        {
                            data: 'expense_date',
                            name: 'expense_date',
                            className: 'text-center'
                        },
                        {
                            data: 'expense_no',
                            name: 'expense_no'
                        },
                        {
                            data: 'expense_by',
                            name: 'expense_by'
                        },
                        {
                            data: 'expense_category',
                            name: 'expense_category'
                        },
                        {
                            data: 'expense_head',
                            name: 'expense_head'
                        },
                        {
                            data: 'receiptable_badge',
                            name: 'receiptable_badge',
                            className: 'text-center'
                        },
                        {
                            data: 'expense_amount',
                            name: 'expense_amount',
                            className: 'text-end'
                        },
                    ],
                    dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                    language: {
                        emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense records found for the selected filters.</div>'
                    },
                    initComplete: function() {
                        const firstRowH = $('#jobExpenseTable thead tr:first-child').outerHeight();
                        $('#jobExpenseTable thead tr:last-child th').css('top', firstRowH + 'px');

                        var self = this.api();

                        self.columns().every(function(i) {
                            var col = this;
                            var $field = $('thead tr:eq(1) th:eq(' + i + ')', self.table().container())
                                .find('input, select');

                            if (!$field.length) {
                                return;
                            }

                            $field.on('click mousedown keydown', function(e) {
                                e.stopPropagation();
                            });

                            if ($field.is('select')) {
                                $field.on('change', function() {
                                    col.search(this.value).draw();
                                });
                            } else {
                                var timer;
                                $field.on('input', function() {
                                    clearTimeout(timer);
                                    timer = setTimeout(function() {
                                        col.search($field.val()).draw();
                                    }, 400);
                                });
                            }
                        });
                    },
                });

                table.on('xhr.dt', function(e, settings, json) {
                    if (!json) {
                        return;
                    }

                    $('#headTotalExpense, #footTotalExpense, #sumTotalExpense').text(json.total_expense);
                    $('#sumRows').text(json.total_rows);
                    $('#sumJobs').text(json.total_jobs);
                });
            }

            if (hasFilters()) {
                loadReport();
            } else {
                resetTotals();
            }

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
                if (!hasFilters()) {
                    setReportVisible(false);
                    resetTotals();

                    if (table) {
                        table.search('').columns().search('');
                        table.ajax.reload();
                    }

                    return;
                }

                setReportVisible(true);
                loadReport();
            });

            $('#btnReset').on('click', function() {
                $('#fromDate, #toDate').val('').removeAttr('min').removeAttr('max');
                $('#jobSelect').val(null).trigger('change');
                setReportVisible(false);
                resetTotals();

                if (table) {
                    table.search('').columns().search('');
                    table.ajax.reload();
                }
            });

            $('#btnPrint').on('click', function() {
                if (!hasFilters()) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Select at least one filter.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }

                    return;
                }

                window.open('{{ route('chevron.reports.job-expense.print') }}?' + $.param(currentFilters()), '_blank');
            });

            $(document).on('click', '.export-btn', function() {
                if (!table) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Apply a filter first.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }

                    return;
                }

                table.button('.buttons-' + $(this).data('export')).trigger();
            });
        });
    </script>
@endpush

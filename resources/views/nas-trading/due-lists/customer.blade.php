@extends('nas-trading.layouts.app')
@section('title', 'Customer Due List')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.dt-table th { font-size:.78rem; padding:.45rem .6rem; white-space: nowrap; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; white-space: nowrap; }
.filter-bar { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; padding:.75rem 1rem; margin-bottom:1rem; }
.dt-table thead tr:first-child th { position: sticky; top: 0; z-index: 2; }
.dt-table thead tr:last-child th { position: sticky; z-index: 2; background: #fff; }
.dt-table thead tr:last-child th input.form-control {
    min-width: 120px; width: 100%; box-sizing: border-box; font-size: .78rem; padding: .3rem .5rem;
}
.dt-scroll { max-height: 65vh; overflow: auto; }
.dt-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.dt-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
.dt-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#dueTable_wrapper>.row:last-child {
    position: sticky; bottom: 0; background: #fff; z-index: 3;
    border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px;
}
</style>
@endpush

@section('content')
<h4 class="mb-3"><i class="fa fa-money-check-alt me-2 text-warning"></i> Customer Due List</h4>

<div class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;font-weight:600">Customer</label>
            <select id="filterCustomer" class="form-select form-select-sm"></select>
            <input type="hidden" id="filterCustomerId">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;font-weight:600">From Date</label>
            <input type="date" id="filterFrom" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;font-weight:600">To Date</label>
            <input type="date" id="filterTo" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-primary w-100" id="btnFilter"><i class="fa fa-search me-1"></i>Search</button>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnReset">Reset</button>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list me-2"></i> Pending Bills</span>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:2px 8px;font-size:.72rem">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#dueTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#dueTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#dueTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item" onclick="$('#dueTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="dt-scroll">
        <table id="dueTable" class="table table-hover table-striped table-bordered dt-table mb-0 w-100">
            <thead>
            <tr>
                <th>#</th><th>Bill No</th><th>Customer</th><th>Bill Date</th><th>Total Amount</th><th>Overdue Days</th><th>Action</th>
            </tr>
            <tr>
                <th></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Bill No"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Customer"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Date"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Amount"></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#filterCustomer').select2({
        width: '100%', placeholder: 'All customers', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.due-lists.search-customers') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', e => $('#filterCustomerId').val(e.params.data.id)).on('select2:clear', () => $('#filterCustomerId').val(''));

    var table = $('#dueTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 20,
        order: [[3, 'asc']],
        lengthMenu: [
            [10, 20, 50, 100, 200],
            [10, 20, 50, 100, 200]
        ],
        ajax: {
            url: '{{ route('nas-trading.due-lists.customer') }}',
            data: d => {
                d.from_date   = $('#filterFrom').val();
                d.to_date     = $('#filterTo').val();
                d.customer_id = $('#filterCustomerId').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'bill_date',     name: 'bill_date' },
            { data: 'total_amount',  name: 'total_amount', className: 'text-end fw-bold' },
            { data: 'overdue_days',  name: 'overdue_days', orderable: false, searchable: false, className: 'text-center',
              render: d => d > 0 ? `<span class="badge bg-danger">${d} days</span>` : `<span class="badge bg-success">Today</span>` },
            { data: 'action',        name: 'action',        orderable: false, searchable: false, width: '80px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No pending bills.</div>' },
        initComplete: function () {
            const firstRowH = $('#dueTable thead tr:first-child').outerHeight();
            $('#dueTable thead tr:last-child th').css('top', firstRowH + 'px');

            var self = this.api();
            self.columns().every(function (i) {
                var col = this;
                var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($in.length) {
                    $in.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $in.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($in.val()).draw(); }, 400);
                    });
                }
            });
        },
    });

    $('#btnFilter').on('click', () => table.ajax.reload());
    $('#btnReset').on('click', () => {
        $('#filterCustomer').val(null).trigger('change');
        $('#filterCustomerId').val('');
        $('#filterFrom, #filterTo').val('');
        table.ajax.reload();
    });
});
</script>
@endpush

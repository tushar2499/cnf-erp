@extends('nas-trading.layouts.app')
@section('title', 'Money Receipts')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.dt-table th { font-size:.78rem; padding:.45rem .6rem; white-space: nowrap; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; white-space: nowrap; }
.dt-table thead tr:first-child th { position: sticky; top: 0; z-index: 2; }
.dt-table thead tr:last-child th { position: sticky; z-index: 2; background: #fff; }
.dt-table thead tr:last-child th input.form-control {
    min-width: 120px; width: 100%; box-sizing: border-box; font-size: .78rem; padding: .3rem .5rem;
}
.dt-scroll { max-height: 65vh; overflow: auto; }
.dt-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.dt-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
.dt-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#mrTable_wrapper>.row:last-child {
    position: sticky; bottom: 0; background: #fff; z-index: 3;
    border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px;
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-money-bill-wave me-2 text-success"></i> Money Receipts</h4>
    <a href="{{ route('nas-trading.money-receipts.create') }}" class="btn btn-sm btn-success"><i class="fa fa-plus me-1"></i>New Receipt</a>
</div>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list me-2"></i> Receipt List</span>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:2px 8px;font-size:.72rem">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#mrTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#mrTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#mrTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item" onclick="$('#mrTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="dt-scroll">
        <table id="mrTable" class="table table-hover table-striped table-bordered dt-table mb-0 w-100">
            <thead>
            <tr>
                <th>#</th><th>Receipt No</th><th>Receipt Date</th><th>Customer</th><th>Bill No</th><th>Bill Amount</th><th>Amount Received</th><th>Payment Mode</th><th>Action</th>
            </tr>
            <tr>
                <th></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Receipt No"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Date"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Customer"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Bill No"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Amount"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Amount"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Mode"></th>
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
    $('#mrTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 20,
        order: [],
        lengthMenu: [
            [10, 20, 50, 100, 200],
            [10, 20, 50, 100, 200]
        ],
        ajax: '{{ route('nas-trading.money-receipts.index') }}',
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',     orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'receipt_no',      name: 'receipt_no' },
            { data: 'receipt_date',    name: 'receipt_date' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'bill_no',         name: 'bill_no' },
            { data: 'bill_amount',     name: 'bill_amount', className: 'text-end' },
            { data: 'amount_received', name: 'amount_received', className: 'text-end fw-bold' },
            { data: 'payment_mode',    name: 'payment_mode' },
            { data: 'action',          name: 'action',          orderable: false, searchable: false, width: '60px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No money receipts yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#mrTable thead tr:first-child').outerHeight();
            $('#mrTable thead tr:last-child th').css('top', firstRowH + 'px');

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
});
</script>
@endpush

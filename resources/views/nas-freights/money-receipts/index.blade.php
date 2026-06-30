@extends('nas-freights.layouts.app')

@section('title', 'Money Receipts')

@push('styles')
<style>
#receiptTable th, #receiptTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#receiptTable thead tr:first-child th { background: #1a6b60; color: #fff; font-weight: 600; }
#receiptTable thead tr:last-child th { background: #e9ecef; }
#receiptTable_wrapper { overflow-x: auto; }
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
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:#0c2340;color:#fff;">
        <span><i class="fa fa-list me-2"></i> Receipt List</span>
        <div class="d-flex gap-2">
            <button onclick="$('#receiptTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#receiptTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light"><i class="fa fa-file-pdf me-1"></i>PDF</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="receiptTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt No</th>
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
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#receiptTable').DataTable({
        processing: true, serverSide: true, orderCellsTop: true,
        ajax: '{{ route('nas-freights.money-receipts.index') }}',
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',     orderable: false, searchable: false, width: '45px' },
            { data: 'receipt_no',      name: 'receipt_no' },
            { data: 'receipt_date',    name: 'receipt_date' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'bill_no',         name: 'bill_no' },
            { data: 'bill_amount',     name: 'bill_amount',     className: 'text-end' },
            { data: 'amount_received', name: 'amount_received', className: 'text-end fw-bold text-success' },
            { data: 'payment_mode',    name: 'payment_mode' },
            { data: 'reference_no',    name: 'reference_no' },
            { data: 'action',          name: 'action',          orderable: false, searchable: false, className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'excel' }, { extend: 'pdf' }],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-money-bill-wave fa-2x mb-2 d-block"></i>No receipts yet.</div>' },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });
});
</script>
@endpush

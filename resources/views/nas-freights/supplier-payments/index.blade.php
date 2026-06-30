@extends('nas-freights.layouts.app')

@section('title', 'Supplier Payments')

@push('styles')
<style>
#payTable th, #payTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#payTable thead tr:first-child th { background: #1a6b60; color: #fff; font-weight: 600; }
#payTable thead tr:last-child th { background: #e9ecef; }
#payTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-hand-holding-usd me-2 text-info"></i> Supplier Payments</h4>
    <a href="{{ route('nas-freights.supplier-payments.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New Payment
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:#0c2340;color:#fff;">
        <span><i class="fa fa-list me-2"></i> Payment List</span>
        <div class="d-flex gap-2">
            <button onclick="$('#payTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#payTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light"><i class="fa fa-file-pdf me-1"></i>PDF</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="payTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier</th>
                    <th>Bill No</th>
                    <th>Bill Amount</th>
                    <th>Amount Paid</th>
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
    var table = $('#payTable').DataTable({
        processing: true, serverSide: true, orderCellsTop: true,
        ajax: '{{ route('nas-freights.supplier-payments.index') }}',
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '45px' },
            { data: 'payment_no',    name: 'payment_no' },
            { data: 'payment_date',  name: 'payment_date' },
            { data: 'supplier_name', name: 'supplier_name' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'bill_amount',   name: 'bill_amount',   className: 'text-end' },
            { data: 'amount_paid',   name: 'amount_paid',   className: 'text-end fw-bold text-primary' },
            { data: 'payment_mode',  name: 'payment_mode' },
            { data: 'reference_no',  name: 'reference_no' },
            { data: 'action',        name: 'action',        orderable: false, searchable: false, className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'excel' }, { extend: 'pdf' }],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-hand-holding-usd fa-2x mb-2 d-block"></i>No payments yet.</div>' },
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

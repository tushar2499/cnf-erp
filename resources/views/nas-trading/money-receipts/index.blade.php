@extends('nas-trading.layouts.app')
@section('title', 'Money Receipts')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-money-bill-wave me-2 text-success"></i> Money Receipts</h4>
    <a href="{{ route('nas-trading.money-receipts.create') }}" class="btn btn-sm btn-success"><i class="fa fa-plus me-1"></i>New Receipt</a>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> Receipt List</div>
    <div style="overflow-x:auto">
        <table id="mrTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Receipt No</th><th>Receipt Date</th><th>Customer</th><th>Bill No</th><th>Bill Amount</th><th>Amount Received</th><th>Payment Mode</th><th>Action</th>
            </tr></thead>
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
        ajax: '{{ route('nas-trading.money-receipts.index') }}',
        columns: [
            { data: 'DT_RowIndex',     orderable: false, searchable: false, width: '40px' },
            { data: 'receipt_no',      name: 'receipt_no' },
            { data: 'receipt_date',    name: 'receipt_date' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'bill_no',         name: 'bill_no' },
            { data: 'bill_amount',     name: 'bill_amount', className: 'text-end' },
            { data: 'amount_received', name: 'amount_received', className: 'text-end fw-bold' },
            { data: 'payment_mode',    name: 'payment_mode' },
            { data: 'action',          orderable: false, searchable: false, width: '60px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 20,
    });
});
</script>
@endpush

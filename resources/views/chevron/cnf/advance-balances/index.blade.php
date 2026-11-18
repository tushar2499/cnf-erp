@extends('chevron.layouts.app')

@section('title', 'Customer Advance Balances')

@push('styles')
<style>
    .ab-label { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .ab-stat { border-left: 4px solid var(--bs-secondary); }
    .ab-stat.is-received { border-left-color: var(--bs-success); }
    .ab-stat.is-adjusted { border-left-color: var(--bs-warning); }
    .ab-stat.is-balance { border-left-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
    .ab-amount { font-variant-numeric: tabular-nums; font-weight: 700; }
    #abTable th, #abTable td { white-space: nowrap; font-size: .78rem; padding: .35rem .6rem; vertical-align: middle; }
    #abTable thead th { background: #e9ecef; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-wallet me-2 text-primary"></i> Customer Advance Balances</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Money Receipts
        </a>
        @if (auth()->user()->hasPermission('cnf.money-receipt.create'))
            <a href="{{ route('chevron.cnf.money-receipts.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> New Receipt
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card ab-stat is-received h-100"><div class="card-body py-3">
            <div class="ab-label">Advance Received</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($received, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card ab-stat is-adjusted h-100"><div class="card-body py-3">
            <div class="ab-label">Adjusted Against Bills</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($adjusted, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card ab-stat is-balance h-100"><div class="card-body py-3">
            <div class="ab-label">Available Balance</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($balance, 2) }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="fa fa-list me-2"></i> Customers with advance received</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="abTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer ID</th>
                        <th>Customer</th>
                        <th class="text-end">Advance Received</th>
                        <th class="text-end">Adjusted</th>
                        <th class="text-end">Balance</th>
                        <th>Statement</th>
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
$(function () {
    $('#abTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        order: [[2, 'asc']],
        ajax: '{{ route('chevron.cnf.advance-balances.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'customer_id', name: 'chevron_customers.customer_id' },
            { data: 'name', name: 'chevron_customers.name' },
            { data: 'advance_received', name: 'advance_received', searchable: false, className: 'text-end' },
            { data: 'advance_adjusted', name: 'advance_adjusted', searchable: false, className: 'text-end' },
            { data: 'balance', name: 'balance', orderable: false, searchable: false, className: 'text-end fw-semibold' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No advance has been received yet.</div>'
        }
    });
});
</script>
@endpush

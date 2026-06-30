@extends('nas-trading.layouts.app')
@section('title', 'Customer Due List')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.filter-bar { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; padding:.75rem 1rem; margin-bottom:1rem; }
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
    <div class="panel-header"><i class="fa fa-list me-2"></i> Pending Bills</div>
    <div style="overflow-x:auto">
        <table id="dueTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Bill No</th><th>Customer</th><th>Bill Date</th><th>Total Amount</th><th>Overdue Days</th><th>Action</th>
            </tr></thead>
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
        ajax: {
            url: '{{ route('nas-trading.due-lists.customer') }}',
            data: d => {
                d.from_date   = $('#filterFrom').val();
                d.to_date     = $('#filterTo').val();
                d.customer_id = $('#filterCustomerId').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'bill_date',     name: 'bill_date' },
            { data: 'total_amount',  name: 'total_amount', className: 'text-end fw-bold' },
            { data: 'overdue_days',  name: 'overdue_days', className: 'text-center',
              render: d => d > 0 ? `<span class="badge bg-danger">${d} days</span>` : `<span class="badge bg-success">Today</span>` },
            { data: 'action',        orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 20,
        order: [[5, 'desc']],
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

@extends('nas-freights.layouts.app')

@section('title', 'Customer Due List')

@push('styles')
<style>
#dueTable th, #dueTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#dueTable thead tr:first-child th { background: #1a6b60; color: #fff; font-weight: 600; }
#dueTable thead tr:last-child th  { background: #e9ecef; }
#dueTable tfoot td { background: #e8f4f1; font-weight: 700; font-size: .8rem; }
#dueTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-user-clock me-2 text-info"></i> Customer Due List</h4>
</div>

{{-- Filter --}}
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
                <label class="form-label" style="font-size:.8rem;font-weight:600">Customer</label>
                <select id="fldCustomer" class="form-select form-select-sm" style="width:100%">
                    <option value="">All Customers</option>
                </select>
                <input type="hidden" id="fldCustomerId">
            </div>
            <div class="col-md-2">
                <button id="btnFilter" class="btn btn-success btn-sm w-100">
                    <i class="fa fa-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button onclick="$('#dueTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success w-100"><i class="fa fa-file-excel me-1"></i>Excel</button>
                <button onclick="$('#dueTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger w-100"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background:#0c2340;color:#fff;font-size:.85rem;">
        <i class="fa fa-list me-2"></i> Confirmed Bills — Awaiting Payment
        <span class="badge bg-warning text-dark ms-2" id="totalBadge">Total Due: 0.00</span>
    </div>
    <div class="card-body p-0">
        <table id="dueTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bill No</th>
                    <th>Bill Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Customer</th>
                    <th>Delivery Type</th>
                    <th>Bill Type</th>
                    <th>Sub Total</th>
                    <th>TDS Amt</th>
                    <th>Total</th>
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
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="12" class="text-end">Total Due Amount:</td>
                    <td class="text-end text-success" id="totalAmt">—</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#fldCustomer').select2({
    theme: 'bootstrap-5', placeholder: 'All Customers', minimumInputLength: 3, allowClear: true,
    ajax: {
        url: '{{ route('nas-freights.due-lists.customer-search') }}',
        dataType: 'json', delay: 250,
        data: d => ({ q: d.term }),
        processResults: d => ({ results: d }),
    },
}).on('select2:select', e => $('#fldCustomerId').val(e.params.data.id))
  .on('select2:clear',  () => $('#fldCustomerId').val(''));

var table = $('#dueTable').DataTable({
    processing: true, serverSide: true, orderCellsTop: true,
    ajax: {
        url: '{{ route('nas-freights.due-lists.customer') }}',
        data: d => {
            d.from_date   = $('#fldFrom').val();
            d.to_date     = $('#fldTo').val();
            d.customer_id = $('#fldCustomerId').val();
        },
    },
    columns: [
        { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '45px' },
        { data: 'bill_no',       name: 'bill_no' },
        { data: 'bill_date',     name: 'bill_date' },
        { data: 'from_date',     name: 'from_date',     orderable: false, searchable: false },
        { data: 'to_date',       name: 'to_date',       orderable: false, searchable: false },
        { data: 'customer_name', name: 'customer_name' },
        { data: 'delivery_type', name: 'delivery_type' },
        { data: 'bill_type',     name: 'bill_type' },
        { data: 'sub_total',     name: 'sub_total',     className: 'text-end' },
        { data: 'tds_amount',    name: 'tds_amount',    className: 'text-end' },
        { data: 'total_amount',  name: 'total_amount',  className: 'text-end fw-bold' },
        { data: 'overdue_days',  name: 'overdue_days',  orderable: false, searchable: false, className: 'text-center',
          render: v => v > 0 ? '<span class="badge bg-danger">' + v + 'd</span>' : '<span class="badge bg-secondary">Today</span>' },
        { data: 'action',        name: 'action',        orderable: false, searchable: false, className: 'text-center' },
    ],
    dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [{ extend: 'excel' }, { extend: 'pdf' }],
    language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-check-circle fa-2x mb-2 d-block text-success"></i>No confirmed outstanding bills.</div>' },
    initComplete: function () {
        this.api().columns().every(function (i) {
            const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
            if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
        });
    },
    drawCallback: function () {
        var total = 0;
        this.api().rows({ search: 'applied' }).data().each(function (r) {
            total += parseFloat((r.total_amount + '').replace(/,/g, '')) || 0;
        });
        var fmt = total.toLocaleString('en-BD', { minimumFractionDigits: 2 });
        $('#totalAmt').text(fmt);
        $('#totalBadge').text('Total Due: ' + fmt);
    },
});

$('#btnFilter').on('click', function () { table.ajax.reload(); });
$('#fldFrom, #fldTo').on('change', function () { table.ajax.reload(); });
</script>
@endpush

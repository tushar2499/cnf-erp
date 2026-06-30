@extends('chevron.layouts.app')

@section('title', 'Job Expenses')

@push('styles')
<style>
#expTable th, #expTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#expTable thead th { background: #e9ecef; font-weight: 600; }
#expTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-money-check-alt me-2 text-warning"></i> Job Expenses</h4>
    <a href="{{ route('chevron.cnf.job-expenses.create') }}" class="btn btn-sm btn-warning text-white">
        <i class="fa fa-plus me-1"></i> New Expense
    </a>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Job Expenses</span>
        <div class="d-flex gap-2">
            <button onclick="$('#expTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#expTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#expTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#expTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="expTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Expense No</th>
                    <th>Job No</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>B/E No</th>
                    <th>Invoice No</th>
                    <th>Invoice Val (USD)</th>
                    <th>B/L No</th>
                    <th>Total Expense</th>
                    <th>Total Approved</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Action</th>
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
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
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
    var table = $('#expTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.cnf.job-expenses.index') }}',
        columns: [
            { data: 'DT_RowIndex',               name: 'DT_RowIndex',            orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'expense_no',                 name: 'expense_no' },
            { data: 'job_no',                     name: 'job_no' },
            { data: 'employee_name',              name: 'employee_name' },
            { data: 'date',                       name: 'date' },
            { data: 'be_no',                      name: 'be_no' },
            { data: 'invoice_no',                 name: 'invoice_no' },
            { data: 'invoice_value_usd_fmt',      name: 'invoice_value_usd',      className: 'text-end' },
            { data: 'bl_no',                      name: 'bl_no' },
            { data: 'total_expense_amount_fmt',   name: 'total_expense_amount',   className: 'text-end' },
            { data: 'total_approved_amount_fmt',  name: 'total_approved_amount',  className: 'text-end' },
            { data: 'remarks',                    name: 'remarks' },
            { data: 'status_badge',               name: 'status',                 orderable: false, searchable: false },
            { data: 'action',                     name: 'action',                 orderable: false, searchable: false, width: '70px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        language: {
            emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expenses yet.</div>'
        },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(r => {
                if (r.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(d => { Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });
});
</script>
@endpush

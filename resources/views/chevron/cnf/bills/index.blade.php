@extends('chevron.layouts.app')

@section('title', 'Bills')

@push('styles')
<style>
#billsTable th, #billsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#billsTable thead th { background: #e9ecef; font-weight: 600; }
#billsTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice me-2 text-success"></i> Bills</h4>
    <a href="{{ route('chevron.cnf.bills.create') }}" class="btn btn-sm btn-success">
        <i class="fa fa-plus me-1"></i> New Bill
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Bills</span>
        <div class="d-flex gap-2">
            <button onclick="$('#billsTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#billsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#billsTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#billsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="billsTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bill No</th>
                    <th>Bill Type</th>
                    <th>Bill Date</th>
                    <th>Job No</th>
                    <th>Party Name</th>
                    <th>B/E No</th>
                    <th>Invoice No</th>
                    <th>Assessable Val</th>
                    <th>Sub Total</th>
                    <th>Net Payable</th>
                    <th>Advance</th>
                    <th>Due Amount</th>
                    <th>Delivery Date</th>
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
                    <th></th><th></th><th></th><th></th><th></th>
                    <th></th><th></th><th></th>
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
    var table = $('#billsTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('chevron.cnf.bills.index') }}',
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',        orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'bill_no',         name: 'bill_no' },
            { data: 'bill_type',       name: 'bill_type' },
            { data: 'bill_date',       name: 'bill_date' },
            { data: 'job_no',          name: 'job_no' },
            { data: 'party_name',      name: 'party_name' },
            { data: 'be_no',           name: 'be_no' },
            { data: 'invoice_no',      name: 'invoice_no' },
            { data: 'assessable_value',name: 'assessable_value',    searchable: false, className: 'text-end' },
            { data: 'sub_total_fmt',   name: 'sub_total',           searchable: false, className: 'text-end' },
            { data: 'net_payable_fmt', name: 'net_payable',         searchable: false, className: 'text-end' },
            { data: 'advance_amount',  name: 'advance_amount',      searchable: false, className: 'text-end' },
            { data: 'due_amount_fmt',  name: 'due_amount',          searchable: false, className: 'text-end' },
            { data: 'delivery_date',   name: 'delivery_date',       searchable: false },
            { data: 'status_badge',    name: 'status',              orderable: false, searchable: false },
            { data: 'action',          name: 'action',              orderable: false, searchable: false, width: '70px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No bills yet.</div>' },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete Bill "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
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

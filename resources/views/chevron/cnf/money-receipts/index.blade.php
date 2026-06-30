@extends('chevron.layouts.app')

@section('title', 'Money Receipts')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-money-bill-wave me-2 text-primary"></i> Money Receipts</h4>
    <a href="{{ route('chevron.cnf.money-receipts.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus me-1"></i> New Receipt
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Money Receipts</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#mrTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#mrTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#mrTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#mrTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="mrTable_wrapper" style="overflow-x:auto">
            <table id="mrTable" class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt No</th>
                        <th>Date</th>
                        <th>Party Name</th>
                        <th>Pay Type</th>
                        <th>Payable Amt</th>
                        <th>Total Amt</th>
                        <th>Status</th>
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
                        <th></th>
                        <th></th>
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
    var table = $('#mrTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('chevron.cnf.money-receipts.index') }}',
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',     orderable: false, searchable: false, width: '45px' },
            { data: 'receipt_no',      name: 'receipt_no' },
            { data: 'receipt_date',    name: 'receipt_date' },
            { data: 'party_name',      name: 'party_name' },
            { data: 'pay_type',        name: 'pay_type' },
            { data: 'payable_amount',  name: 'payable_amount',  searchable: false, className: 'text-end' },
            { data: 'total_amount',    name: 'total_amount',    searchable: false, className: 'text-end' },
            { data: 'status_badge',    name: 'status',          orderable: false, searchable: false },
            { data: 'action',          name: 'action',          orderable: false, searchable: false, width: '90px' },
        ],
        dom: "<'row mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        order: [[0, 'desc']],
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No money receipts yet.</div>' },
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

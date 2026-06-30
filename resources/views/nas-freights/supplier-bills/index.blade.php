@extends('nas-freights.layouts.app')

@section('title', 'Supplier Bills')

@push('styles')
<style>
#billsTable th, #billsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#billsTable thead tr:first-child th { background: #1a6b60; color: #fff; font-weight: 600; }
#billsTable thead tr:last-child th { background: #e9ecef; }
#billsTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice me-2 text-info"></i> Supplier Bills</h4>
    <a href="{{ route('nas-freights.supplier-bills.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New Payment Order
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:#0c2340;color:#fff;">
        <span><i class="fa fa-list me-2"></i> Payment Order List</span>
        <div class="d-flex gap-2">
            <button onclick="$('#billsTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-light"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#billsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#billsTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light"><i class="fa fa-file-pdf me-1"></i>PDF</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="billsTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pay Order No</th>
                    <th>Bill Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Supplier</th>
                    <th>Bill By</th>
                    <th>Total Amount</th>
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
                    <th></th>
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
    var table = $('#billsTable').DataTable({
        processing: true, serverSide: true, orderCellsTop: true,
        ajax: '{{ route('nas-freights.supplier-bills.index') }}',
        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '45px' },
            { data: 'pay_order_no',  name: 'pay_order_no' },
            { data: 'bill_date',     name: 'bill_date' },
            { data: 'from_date',     name: 'from_date' },
            { data: 'to_date',       name: 'to_date' },
            { data: 'supplier_name', name: 'supplier_name' },
            { data: 'bill_by',       name: 'bill_by' },
            { data: 'total_amount',  name: 'total_amount',  className: 'text-end fw-bold' },
            { data: 'status_badge',  name: 'status',        orderable: false, searchable: false, className: 'text-center' },
            { data: 'action',        name: 'action',        orderable: false, searchable: false, width: '80px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-file-invoice fa-2x mb-2 d-block"></i>No payment orders yet.</div>' },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });

    $(document).on('click', '.btn-confirm', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Confirm payment order "' + name + '"?', text: 'Will be marked as Confirmed and appear in Due List.', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, confirm' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'PATCH', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Action failed.' }));
                }
            });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });
});
</script>
@endpush

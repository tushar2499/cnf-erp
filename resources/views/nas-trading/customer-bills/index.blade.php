@extends('nas-trading.layouts.app')
@section('title', 'Customer Bills')
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
    <h4 class="mb-0"><i class="fa fa-file-invoice-dollar me-2 text-success"></i> Customer Bills</h4>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> Bill List</div>
    <div style="overflow-x:auto">
        <table id="billTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Bill No</th><th>LC No</th><th>Customer</th><th>Bill Date</th><th>Total Amount</th><th>Status</th><th>Action</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#billTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-trading.customer-bills.index') }}',
        columns: [
            { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'lc_no',         name: 'lc_no' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'bill_date',     name: 'bill_date' },
            { data: 'total_amount',  name: 'total_amount' },
            { data: 'status_badge',  orderable: false, searchable: false },
            { data: 'action',        orderable: false, searchable: false, width: '130px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });

    $(document).on('click', '.btn-confirm', function () {
        const url = $(this).data('url');
        Swal.fire({ title: 'Confirm this bill?', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, Confirm' })
            .then(res => {
                if (res.isConfirmed) {
                    $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
                }
            });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete ' + name + '?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(xhr => Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed.' }));
                }
            });
    });
});
</script>
@endpush

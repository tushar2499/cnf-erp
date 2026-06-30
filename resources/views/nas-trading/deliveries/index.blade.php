@extends('nas-trading.layouts.app')
@section('title', 'Delivery')
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
    <h4 class="mb-0"><i class="fa fa-truck me-2 text-info"></i> Delivery</h4>
    <a href="{{ route('nas-trading.deliveries.create') }}" class="btn btn-sm btn-info text-white"><i class="fa fa-plus me-1"></i>New Delivery</a>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> Delivery List</div>
    <div style="overflow-x:auto">
        <table id="dlvTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Delivery No</th><th>Bill No</th><th>Customer</th><th>Delivery Date</th><th>Address</th><th>Status</th><th>Action</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#dlvTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-trading.deliveries.index') }}',
        columns: [
            { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px' },
            { data: 'delivery_no',   name: 'delivery_no' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'delivery_date', name: 'delivery_date' },
            { data: 'delivery_address', name: 'delivery_address' },
            { data: 'status_badge',  orderable: false, searchable: false },
            { data: 'action',        orderable: false, searchable: false, width: '110px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });

    function statusAction(url, msg, icon, color) {
        Swal.fire({ title: msg, icon: 'question', showCancelButton: true, confirmButtonColor: color, confirmButtonText: 'Yes' })
        .then(res => {
            if (res.isConfirmed) {
                $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
                .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
            }
        });
    }

    $(document).on('click', '.btn-dispatch', function () { statusAction($(this).data('url'), 'Mark as Dispatched?', 'question', '#ffc107'); });
    $(document).on('click', '.btn-deliver',  function () { statusAction($(this).data('url'), 'Mark as Delivered?',  'question', '#198754'); });
});
</script>
@endpush

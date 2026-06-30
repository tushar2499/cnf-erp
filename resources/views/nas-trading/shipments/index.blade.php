@extends('nas-trading.layouts.app')
@section('title', 'Shipment Entry')
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
    <h4 class="mb-0"><i class="fa fa-ship me-2 text-info"></i> Shipment Entry</h4>
    <a href="{{ route('nas-trading.shipments.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New Shipment
    </a>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> Shipment List</div>
    <div style="overflow-x:auto">
        <table id="shipTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Shipment No</th><th>LC No</th><th>Customer</th><th>Vessel</th><th>Arrival Date</th><th>Mode</th><th>Status</th><th>Action</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#shipTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-trading.shipments.index') }}',
        columns: [
            { data: 'DT_RowIndex',     orderable: false, searchable: false, width: '40px' },
            { data: 'shipment_no',     name: 'shipment_no' },
            { data: 'lc_no',           name: 'lc_no' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'vessel',          name: 'vessel' },
            { data: 'arrival_date',    name: 'arrival_date' },
            { data: 'shipping_mode',   name: 'shipping_mode' },
            { data: 'status_badge',    orderable: false, searchable: false },
            { data: 'action',          orderable: false, searchable: false, width: '110px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete ' + name + '?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
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

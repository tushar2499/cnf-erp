@extends('nas-freights.layouts.app')

@section('title', 'Freight Export Bookings')

@push('styles')
<style>
.status-pill { padding:.2rem .55rem; border-radius:.35rem; font-size:.7rem; font-weight:600; }
.status-Draft { background:#e2e8f0; color:#334155; }
.status-Confirmed { background:#dcfce7; color:#166534; }
.status-In-Transit { background:#cffafe; color:#155e75; }
.status-Delivered { background:#dbeafe; color:#1e40af; }
.status-Cancelled { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-ship me-2 text-primary"></i> Freight Export Bookings</h4>
    <a href="{{ route('nas-freights.freight-export-bookings.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus me-1"></i> New Export Booking
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-1 flex-wrap align-items-center">
            <span class="me-2"><i class="fa fa-list me-1"></i> All Export Bookings</span>
            <button class="btn btn-sm btn-outline-secondary status-tab" data-status="">All</button>
            <button class="btn btn-sm btn-outline-secondary status-tab" data-status="Draft">Draft</button>
            <button class="btn btn-sm btn-outline-success status-tab" data-status="Confirmed">Confirmed</button>
            <button class="btn btn-sm btn-outline-primary status-tab" data-status="In-Transit">In-Transit</button>
            <button class="btn btn-sm btn-outline-info status-tab" data-status="Delivered">Delivered</button>
            <button class="btn btn-sm btn-outline-danger status-tab" data-status="Cancelled">Cancelled</button>
        </div>
    </div>
    <div class="card-body p-2">
        <table id="exportBookingsTable" class="table table-sm table-hover table-striped nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Export B/L No</th>
                    <th>Service</th>
                    <th>POL → POD</th>
                    <th>Carrier</th>
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
var currentStatus = '';
$(function () {
    var table = $('#exportBookingsTable').DataTable({
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('nas-freights.freight-export-bookings.index') }}',
            data: function (d) { d.status_filter = currentStatus; }
        },
        columns: [
            { data: 'DT_RowIndex',           name: 'DT_RowIndex',   orderable: false, searchable: false },
            { data: 'export_booking_no',     name: 'export_booking_no' },
            { data: 'booking_date',          name: 'booking_date' },
            { data: 'customer_name',         name: 'customer_name' },
            { data: 'export_bl_no',          name: 'export_bl_no' },
            { data: 'service_type',          name: 'service_type' },
            { data: 'route',                 name: 'route',         orderable: false, searchable: false },
            { data: 'carrier',               name: 'shippingCarrier.name', defaultContent: '—' },
            { data: 'status_badge',          name: 'status',        orderable: false, searchable: false },
            { data: 'action',                name: 'action',        orderable: false, searchable: false },
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        dom: "<'row'<'col-sm-5'><'col-sm-7'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No export bookings yet.</div>'
        },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) {
                    $in.on('click mousedown', e => e.stopPropagation());
                    $in.on('keyup change', () => this.search($in.val()).draw());
                }
            });
        },
    });

    $('.status-tab').on('click', function () {
        currentStatus = $(this).data('status');
        table.ajax.reload();
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
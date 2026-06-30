@extends('nas-freights.layouts.app')

@section('title', 'TMS Bookings')

@push('styles')
<style>
.btn-xs { padding: .12rem .38rem; font-size: .72rem; line-height: 1.4; border-radius: .25rem; }
#bookingsTable th, #bookingsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#bookingsTable thead tr:first-child th { background: #1a6b60; color: #fff; font-weight: 600; }
#bookingsTable thead tr:last-child th  { background: #e9ecef; font-weight: normal; }
#bookingsTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-clipboard-list me-2 text-info"></i> TMS Bookings</h4>
    <a href="{{ route('nas-freights.bookings.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New Booking
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:#0c2340; color:#fff;">
        <span><i class="fa fa-list me-2"></i> Booking List</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#bookingsTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-xs btn-outline-light"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#bookingsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-xs btn-outline-light"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#bookingsTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-xs btn-outline-light"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#bookingsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-xs btn-outline-light"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="bookingsTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                {{-- Row 1: titles --}}
                <tr>
                    <th>Action</th>
                    <th>Job No</th>
                    <th>Job Date</th>
                    <th>Delivery Date</th>
                    <th>Entry</th>
                    <th>Branch</th>
                    <th>Customer</th>
                    <th>Item Details</th>
                    <th>T. Qty</th>
                    <th>Item Amount</th>
                    <th>AIT Amt</th>
                    <th>TDS Amt</th>
                    <th>VAT Amt</th>
                    <th>T. Amount</th>
                    <th>Dis.</th>
                    <th>F.Feited Amt</th>
                    <th>Status</th>
                    <th>Billed</th>
                    <th>Note</th>
                </tr>
                {{-- Row 2: search inputs (empty = not searchable) --}}
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
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th></th>
                    <th></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function fmtDate(d) {
    if (!d) return '';
    var dt = new Date(d);
    return ('0' + dt.getDate()).slice(-2) + '-' + MONTHS[dt.getMonth()] + '-' + dt.getFullYear();
}

$(function () {
    var table = $('#bookingsTable').DataTable({
        processing: true,
        serverSide: true,
        orderCellsTop: true,
        ajax: '{{ route('nas-freights.bookings.index') }}',
        columns: [
            { data: 'action',          name: 'action',           orderable: false, searchable: false, width: '160px' },
            { data: 'job_no',          name: 'job_no' },
            { data: 'job_date',        name: 'job_date',         render: fmtDate },
            { data: 'delivery_date',   name: 'delivery_date',    render: fmtDate },
            { data: 'entry_by',        name: 'entry_by' },
            { data: 'branch',          name: 'branch' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'item_details',    name: 'item_details',     orderable: false, searchable: false },
            { data: 't_qty',           name: 't_qty',            orderable: false, searchable: false, className: 'text-end' },
            { data: 'item_amount',     name: 'item_amount',      orderable: false, searchable: false, className: 'text-end' },
            { data: 'ait_amount',      name: 'ait_amount',       className: 'text-end' },
            { data: 'tds_amount',      name: 'tds_amount',       className: 'text-end' },
            { data: 'vat_amount',      name: 'vat_amount',       className: 'text-end' },
            { data: 'total_amount',    name: 'total_amount',     className: 'text-end fw-bold' },
            { data: 'discount',        name: 'discount',         className: 'text-end' },
            { data: 'forfeited_amount',name: 'forfeited_amount', className: 'text-end' },
            { data: 'status_badge',    name: 'status',           orderable: false, searchable: false, className: 'text-center' },
            { data: 'billed_badge',    name: 'billed',           orderable: false, searchable: false, className: 'text-center' },
            { data: 'note',            name: 'note' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        language: {
            emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-clipboard fa-2x mb-2 d-block"></i>No bookings yet.</div>',
        },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });

    /* ── Confirm ── */
    $(document).on('click', '.btn-confirm', function () {
        const url = $(this).data('url'), no = $(this).data('no');
        Swal.fire({ title: 'Confirm booking ' + no + '?', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, confirm' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'PATCH', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false }); table.ajax.reload(null, false); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Action failed.' }));
                }
            });
    });

    /* ── Reject ── */
    $(document).on('click', '.btn-reject', function () {
        const url = $(this).data('url'), no = $(this).data('no');
        Swal.fire({ title: 'Reject booking ' + no + '?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, reject' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'PATCH', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false }); table.ajax.reload(null, false); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Action failed.' }));
                }
            });
    });
});
</script>
@endpush

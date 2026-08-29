@extends('nas-trading.layouts.app')
@section('title', 'Shipment Entry')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.dt-table th { font-size:.78rem; padding:.45rem .6rem; white-space: nowrap; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; white-space: nowrap; }
.dt-table thead tr:first-child th { position: sticky; top: 0; z-index: 2; }
.dt-table thead tr:last-child th { position: sticky; z-index: 2; background: #fff; }
.dt-table thead tr:last-child th input.form-control {
    min-width: 120px; width: 100%; box-sizing: border-box; font-size: .78rem; padding: .3rem .5rem;
}
.dt-scroll { max-height: 65vh; overflow: auto; }
.dt-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.dt-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
.dt-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#shipTable_wrapper>.row:last-child {
    position: sticky; bottom: 0; background: #fff; z-index: 3;
    border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px;
}
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
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-list me-2"></i> Shipment List</span>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:2px 8px;font-size:.72rem">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#shipTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#shipTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#shipTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item" onclick="$('#shipTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="dt-scroll">
        <table id="shipTable" class="table table-hover table-striped table-bordered dt-table mb-0 w-100">
            <thead>
            <tr>
                <th>#</th><th>Shipment No</th><th>LC No</th><th>Customer</th><th>Vessel</th><th>Arrival Date</th><th>Mode</th><th>Status</th><th>Action</th>
            </tr>
            <tr>
                <th></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search No"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search LC No"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Customer"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Vessel"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Date"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Search Mode"></th>
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
    var table = $('#shipTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [
            [10, 15, 25, 50, 100, 200],
            [10, 15, 25, 50, 100, 200]
        ],
        ajax: '{{ route('nas-trading.shipments.index') }}',
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',     orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'shipment_no',     name: 'shipment_no' },
            { data: 'lc_no',           name: 'lc_no' },
            { data: 'customer_name',   name: 'customer_name' },
            { data: 'vessel',          name: 'vessel' },
            { data: 'arrival_date',    name: 'arrival_date' },
            { data: 'shipping_mode',   name: 'shipping_mode' },
            { data: 'status_badge',    name: 'status_badge',    orderable: false, searchable: false },
            { data: 'action',          name: 'action',          orderable: false, searchable: false, width: '110px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No shipments yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#shipTable thead tr:first-child').outerHeight();
            $('#shipTable thead tr:last-child th').css('top', firstRowH + 'px');

            var self = this.api();
            self.columns().every(function (i) {
                var col = this;
                var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($in.length) {
                    $in.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $in.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($in.val()).draw(); }, 400);
                    });
                }
            });
        },
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

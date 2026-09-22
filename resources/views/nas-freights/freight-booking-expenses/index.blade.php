@extends('nas-freights.layouts.app')
@section('title', $bookingType === 'import' ? 'Import Expenses' : 'Export Expenses')

@push('styles')
    <style>
        #expTable th, #expTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
        #expTable thead tr:first-child th { background: #e9ecef; font-weight: 600; position: sticky; top: 0; z-index: 2; }
        #expTable thead tr:last-child th { background: #f8f9fa; font-weight: normal; position: sticky; z-index: 2; }
        #expTable thead tr:last-child th input.form-control { min-width: 100px; width: 100%; font-size: .78rem; padding: .3rem .5rem; }
        .exp-table-wrapper { max-height: 65vh; overflow: auto; }
        .exp-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
        .exp-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
        .exp-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #expTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
    </style>
@endpush

@section('content')
<div class="page-header">
    <h4>
        <i class="fa fa-money-bill-wave me-2 text-info"></i>
        {{ $bookingType === 'import' ? 'Import Expenses' : 'Export Expenses' }}
    </h4>
    <a href="{{ route($routePrefix.'.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New Expense
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">From Date</label>
                <input type="date" id="filterFrom" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">To Date</label>
                <input type="date" id="filterTo" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Status</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button id="btnFilter" class="btn btn-sm btn-info text-white me-1"><i class="fa fa-filter me-1"></i> Filter</button>
                <button id="btnReset"  class="btn btn-sm btn-outline-secondary"><i class="fa fa-redo me-1"></i> Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="fa fa-list me-2"></i>
            {{ $bookingType === 'import' ? 'Import Expenses' : 'Export Expenses' }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="exp-table-wrapper">
            <table id="expTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Expense No</th>
                        <th>Date</th>
                        <th>Booking No</th>
                        <th>Employee</th>
                        <th>Expense Amt</th>
                        <th>Approved Amt</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search No"></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Booking"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Employee"></th>
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
var table;

$(function () {
    table = $('#expTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [[0, 'desc']],
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        ajax: {
            url: '{{ route($routePrefix.'.index') }}',
            data: function (d) {
                d.from_date     = $('#filterFrom').val();
                d.to_date       = $('#filterTo').val();
                d.status_filter = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',               name: 'DT_RowIndex',               orderable: false, searchable: false, width: '40px',  className: 'text-center' },
            { data: 'expense_no',                name: 'expense_no',                width: '130px' },
            { data: 'date',                      name: 'date',                      width: '100px' },
            { data: 'booking_no',                name: 'booking_no',                width: '130px', render: v => v || '—' },
            { data: 'employee_name',             name: 'employee_name',             width: '150px', searchable: false },
            { data: 'total_expense_amount_fmt',  name: 'total_expense_amount',      width: '120px', className: 'text-end', orderable: false, searchable: false },
            { data: 'total_approved_amount_fmt', name: 'total_approved_amount',     width: '120px', className: 'text-end', orderable: false, searchable: false },
            { data: 'status_badge',              name: 'status_badge',              orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'action',                    name: 'action',                    orderable: false, searchable: false, width: '110px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expenses found.</div>' },
        initComplete: function () {
            const firstRowH = $('#expTable thead tr:first-child').outerHeight();
            $('#expTable thead tr:last-child th').css('top', firstRowH + 'px');
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

    $('#btnFilter').on('click', function () { table.ajax.reload(); });
    $('#btnReset').on('click', function () {
        $('#filterFrom, #filterTo').val('');
        $('#filterStatus').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r  => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
    });
});
</script>
@endpush

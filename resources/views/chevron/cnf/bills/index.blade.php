@extends('chevron.layouts.app')

@section('title', 'Bills')

@push('styles')
<style>
#billsTable th, #billsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#billsTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#billsTable thead tr:last-child th { background: #f8f9fa; }
#billsTable thead tr:last-child th input.form-control { min-width: 72px; width: 100%; box-sizing: border-box; }
.bills-table-wrapper { max-height: 65vh; overflow: auto; }
.bills-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.bills-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.bills-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#billsTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice me-2 text-success"></i> Bills</h4>
    @if(auth()->user()->hasPermission('cnf.bill.create'))
    <a href="{{ route('chevron.cnf.bills.create') }}" class="btn btn-sm btn-success">
        <i class="fa fa-plus me-1"></i> New Bill
    </a>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Bills</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#billsTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#billsTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#billsTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#billsTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="bills-table-wrapper">
        <table id="billsTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Action</th>
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
                </tr>
                <tr>
                    <th></th>
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
                    <th></th>
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
    var table = $('#billsTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        order: [],
        orderCellsTop: true,
        lengthMenu: [[15, 25, 50, 100, 200, 500, -1], [15, 25, 50, 100, 200, 500, 'All']],
        ajax: '{{ route('chevron.cnf.bills.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex',     orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'action',           name: 'action',          orderable: false, searchable: false, width: '70px', className: 'text-center' },
            { data: 'bill_no',          name: 'bill_no' },
            { data: 'bill_type',        name: 'bill_type' },
            { data: 'bill_date',        name: 'bill_date' },
            { data: 'job_no',           name: 'job_no' },
            { data: 'party_name',       name: 'party_name' },
            { data: 'be_no',            name: 'be_no' },
            { data: 'invoice_no',       name: 'invoice_no' },
            { data: 'assessable_value', name: 'assessable_value', searchable: false, className: 'text-end' },
            { data: 'sub_total_fmt',    name: 'sub_total',        searchable: false, className: 'text-end' },
            { data: 'net_payable_fmt',  name: 'net_payable',      searchable: false, className: 'text-end' },
            { data: 'advance_amount',   name: 'advance_amount',   searchable: false, className: 'text-end' },
            { data: 'due_amount_fmt',   name: 'due_amount',       searchable: false, className: 'text-end' },
            { data: 'delivery_date',    name: 'delivery_date',    searchable: false },
            { data: 'status_badge',     name: 'status',           orderable: false, searchable: false },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No bills yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#billsTable thead tr:first-child').outerHeight();
            $('#billsTable thead tr:last-child th').css('top', firstRowH + 'px');

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

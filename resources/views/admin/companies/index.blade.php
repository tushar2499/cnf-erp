@extends('admin.layouts.app')

@section('title', 'Companies')

@push('styles')
<style>
#companiesTable th, #companiesTable td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
#companiesTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#companiesTable thead tr:last-child th { background: #f8f9fa; }
#companiesTable thead tr:last-child th input.form-control { min-width: 80px; width: 100%; box-sizing: border-box; }
.companies-table-wrapper { max-height: 65vh; overflow: auto; }
.companies-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
.companies-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.companies-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
#companiesTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-building me-2 text-primary"></i> Companies</h4>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Companies</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" onclick="$('#companiesTable').DataTable().button('.buttons-csv').trigger()"><i class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item" onclick="$('#companiesTable').DataTable().button('.buttons-excel').trigger()"><i class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item" onclick="$('#companiesTable').DataTable().button('.buttons-pdf').trigger()"><i class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="$('#companiesTable').DataTable().button('.buttons-print').trigger()"><i class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="companies-table-wrapper">
            <table id="companiesTable" class="table table-hover table-striped table-bordered mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width:70px">Logo</th>
                        <th>Company Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:90px">Status</th>
                        <th style="width:90px">Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
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
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#companiesTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        order: [],
        orderCellsTop: true,
        pageLength: 25,
        lengthMenu: [[15, 25, 50, 100, 200, 500], [15, 25, 50, 100, 200, 500]],
        ajax: '{{ route('admin.companies.index') }}',
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px', className: 'text-center' },
            { data: 'logo',             name: 'logo',          orderable: false, searchable: false, width: '70px', className: 'text-center', render: function (data) {
                return data
                    ? '<img src="{{ asset('assets/logos') }}/' + data + '" alt="logo" style="height:36px; width:36px; object-fit:contain; border-radius:4px;">'
                    : '<span class="text-muted"><i class="fa fa-building fa-lg"></i></span>';
            } },
            { data: 'name',             name: 'name' },
            { data: 'type_badge',       name: 'type_badge',   orderable: false, className: 'text-center' },
            { data: 'email',            name: 'email', defaultContent: '—' },
            { data: 'phone',            name: 'phone', defaultContent: '—' },
            { data: 'status_badge',     name: 'status_badge', orderable: false, className: 'text-center' },
            { data: 'action',           name: 'action',       orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        initComplete: function () {
            var self = this.api();
            const firstRowH = $('#companiesTable thead tr:first-child').outerHeight();
            $('#companiesTable thead tr:last-child th').css('top', firstRowH + 'px');
            self.columns().every(function (i) {
                var col = this;
                const $input = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($input.length) {
                    $input.on('click mousedown keydown', function (e) { e.stopPropagation(); });
                    var timer;
                    $input.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () { col.search($input.val()).draw(); }, 400);
                    });
                }
            });
        },
        language: {
            emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-building fa-2x mb-2 d-block"></i>No companies found.</div>'
        },
    });
});
</script>
@endpush

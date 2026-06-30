@extends('chevron.layouts.app')

@section('title', 'C&F Jobs')

@push('styles')
<style>
#jobsTable th, #jobsTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
#jobsTable thead th { background: #e9ecef; font-weight: 600; }
#jobsTable_wrapper { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-alt me-2 text-primary"></i> C&amp;F Jobs</h4>
    <a href="{{ route('chevron.cnf.jobs.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus me-1"></i> New Job
    </a>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Jobs</span>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="$('#jobsTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-secondary"><i class="fa fa-file-csv me-1"></i>CSV</button>
            <button onclick="$('#jobsTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel me-1"></i>Excel</button>
            <button onclick="$('#jobsTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf me-1"></i>PDF</button>
            <button onclick="$('#jobsTable').DataTable().button('.buttons-print').trigger()" class="btn btn-sm btn-outline-secondary"><i class="fa fa-print me-1"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="jobsTable" class="table table-hover table-striped table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job No</th>
                    <th>Job Date</th>
                    <th>Party Name</th>
                    <th>Goods Name</th>
                    <th>Service</th>
                    <th>Job Type</th>
                    <th>Port</th>
                    <th>Country</th>
                    <th>B/E No</th>
                    <th>B/E Date</th>
                    <th>B/L No</th>
                    <th>Invoice No</th>
                    <th>Inv Date</th>
                    <th>Inv (FCY)</th>
                    <th>Inv (BDT)</th>
                    <th>Assess. Val</th>
                    <th>Currency</th>
                    <th>Rate</th>
                    <th>Assess. BDT</th>
                    <th>Duty</th>
                    <th>VAT</th>
                    <th>Net Pay (FCY)</th>
                    <th>Net Pay (BDT)</th>
                    <th>Vessel</th>
                    <th>ETA</th>
                    <th>Delivery</th>
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
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
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
    var table = $('#jobsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('chevron.cnf.jobs.index') }}',
        columns: [
            { data: 'DT_RowIndex',             name: 'DT_RowIndex',          orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'job_no',                  name: 'job_no' },
            { data: 'job_date',                name: 'job_date' },
            { data: 'party_name',              name: 'party_name' },
            { data: 'goods_name',              name: 'goods_name' },
            { data: 'service_name',            name: 'service_name' },
            { data: 'job_type_name',           name: 'job_type_name' },
            { data: 'port_name',               name: 'port_name' },
            { data: 'country_of_origin',       name: 'country_of_origin' },
            { data: 'be_no',                   name: 'be_no' },
            { data: 'be_date',                 name: 'be_date' },
            { data: 'bl_no',                   name: 'bl_no' },
            { data: 'invoice_no',              name: 'invoice_no' },
            { data: 'invoice_date',            name: 'invoice_date' },
            { data: 'invoice_value_1_fmt',     name: 'invoice_value_1',      className: 'text-end' },
            { data: 'invoice_value_2_fmt',     name: 'invoice_value_2',      className: 'text-end' },
            { data: 'assessable_value_fmt',    name: 'assessable_value',     className: 'text-end' },
            { data: 'currency_type',           name: 'currency_type' },
            { data: 'currency_rate',           name: 'currency_rate',        className: 'text-end' },
            { data: 'assessable_value_bdt_fmt',name: 'assessable_value_bdt', className: 'text-end' },
            { data: 'duty_amount_fmt',         name: 'duty_amount',          className: 'text-end' },
            { data: 'vat_amount_fmt',          name: 'vat_amount',           className: 'text-end' },
            { data: 'net_payable_1_fmt',       name: 'net_payable_1',        className: 'text-end' },
            { data: 'net_payable_2_fmt',       name: 'net_payable_2',        className: 'text-end' },
            { data: 'vessel_name',             name: 'vessel_name' },
            { data: 'eta_date',                name: 'eta_date' },
            { data: 'delivery_date',           name: 'delivery_date' },
            { data: 'status_badge',            name: 'status',               orderable: false, searchable: false },
            { data: 'action',                  name: 'action',               orderable: false, searchable: false, width: '70px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }
        ],
        language: {
            emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No jobs yet.</div>'
        },
        initComplete: function () {
            this.api().columns().every(function (i) {
                const $in = $('thead tr:eq(1) th:eq(' + i + ') input', this.table().container());
                if ($in.length) $in.on('keyup change', () => this.search($in.val()).draw());
            });
        },
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete Job "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
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

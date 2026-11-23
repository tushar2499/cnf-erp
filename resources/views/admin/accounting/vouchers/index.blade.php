@extends('admin.layouts.app')

@section('title', 'Vouchers')

@push('styles')
<style>
#vouchersTable th, #vouchersTable td { font-size: .8rem; padding: .4rem .55rem; vertical-align: middle; }
#vouchersTable thead th { background: #e9ecef; font-weight: 600; position: sticky; z-index: 2; top: 0; }
#vouchersTable thead tr:last-child th { background: #f8f9fa; }
#vouchersTable thead tr:last-child th input.form-control { min-width: 80px; width: 100%; box-sizing: border-box; }
.vouchers-table-wrapper { max-height: 65vh; overflow: auto; }
#vouchersTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice-dollar me-2 text-success"></i> Vouchers</h4>
    @if(auth()->user()->hasPermission('admin.accounting.voucher.create'))
        <a href="{{ route('admin.accounting.vouchers.create') }}" class="btn btn-sm btn-success"><i class="fa fa-plus me-1"></i> New Voucher</a>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <select id="fCompany" class="form-select form-select-sm" aria-label="Company">
                    <option value="">All companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="fType" class="form-select form-select-sm" aria-label="Voucher type">
                    <option value="">All types</option>
                    @foreach(\App\Models\Accounting\AccVoucher::TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="fOrigin" class="form-select form-select-sm" aria-label="Origin">
                    <option value="">Auto &amp; manual</option>
                    <option value="auto">Auto</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div class="col-md-1">
                <select id="fStatus" class="form-select form-select-sm" aria-label="Status">
                    <option value="posted">Posted</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" id="fFrom" class="form-control form-control-sm" aria-label="From"></div>
            <div class="col-md-2"><input type="date" id="fTo" class="form-control form-control-sm" aria-label="To"></div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="vouchers-table-wrapper">
            <table id="vouchersTable" class="table table-hover table-striped table-bordered mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Narration</th>
                        <th class="text-end">Amount</th>
                        <th>Origin</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <th></th><th></th><th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th><th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search..."></th>
                        <th></th><th></th><th></th>
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
    const table = $('#vouchersTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        order: [[2, 'desc']],
        orderCellsTop: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('admin.accounting.vouchers.index') }}',
            data: function (d) {
                d.company_id = $('#fCompany').val();
                d.voucher_type = $('#fType').val();
                d.origin = $('#fOrigin').val();
                d.status = $('#fStatus').val();
                d.from = $('#fFrom').val();
                d.to = $('#fTo').val();
            },
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', width: '40px' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap', width: '110px' },
            { data: 'voucher_date', name: 'voucher_date', searchable: false, width: '85px' },
            { data: 'voucher_no', name: 'voucher_no' },
            { data: 'company_name', name: 'company_name', orderable: false },
            { data: 'voucher_type', name: 'acc_vouchers.voucher_type', searchable: false },
            { data: 'narration', name: 'narration', orderable: false },
            { data: 'total_amount', name: 'acc_vouchers.total_amount', searchable: false, className: 'text-end' },
            { data: 'origin', name: 'origin', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
        ],
        dom: "<'row mb-0'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-12'tr>>" + "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        initComplete: function () {
            var self = this.api();
            const firstRowH = $('#vouchersTable thead tr:first-child').outerHeight();
            $('#vouchersTable thead tr:last-child th').css('top', firstRowH + 'px');
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No vouchers.</div>' },
    });

    $('#fCompany, #fType, #fOrigin, #fStatus, #fFrom, #fTo').on('change', function () { table.ajax.reload(); });

    $(document).on('click', '.btn-cancel', function () {
        const url = $(this).data('url');
        Swal.fire({
            title: 'Cancel voucher ' + $(this).data('no') + '?',
            input: 'text',
            inputLabel: 'Reason',
            inputValidator: v => !v && 'Reason is required.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Cancel voucher',
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            $.post(url, { _token: $('meta[name="csrf-token"]').attr('content'), reason: res.value })
                .done(function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(null, false); })
                .fail(function (xhr) { Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Cancel failed.' }); });
        });
    });
});
</script>
@endpush

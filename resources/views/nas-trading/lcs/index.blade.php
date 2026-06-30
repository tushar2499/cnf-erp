@extends('nas-trading.layouts.app')
@section('title', 'LC Entry')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-contract me-2 text-info"></i> LC Entry</h4>
    <a href="{{ route('nas-trading.lcs.create') }}" class="btn btn-sm btn-info text-white">
        <i class="fa fa-plus me-1"></i> New LC
    </a>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> LC List</div>
    <div style="overflow-x:auto">
        <table id="lcTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>LC No (System)</th><th>LC No (Bank)</th><th>PFI No</th><th>Customer</th><th>Type</th><th>LC Date</th><th>PFI Value</th><th>Status</th><th>Action</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#lcTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-trading.lcs.index') }}',
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false, width: '40px' },
            { data: 'lc_no_system', name: 'lc_no_system' },
            { data: 'lc_no',        name: 'lc_no' },
            { data: 'pfi_no',       name: 'pfi_no' },
            { data: 'customer_name',name: 'customer_name' },
            { data: 'lc_type',      name: 'lc_type' },
            { data: 'lc_open_date', name: 'lc_open_date' },
            { data: 'pfi_value',    name: 'pfi_value' },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'action',       orderable: false, searchable: false, width: '100px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete LC "' + name + '"?', text: 'This will also delete all items and expenses.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
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

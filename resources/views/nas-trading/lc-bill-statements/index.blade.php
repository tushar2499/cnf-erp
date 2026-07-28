@extends('nas-trading.layouts.app')
@section('title', 'LC Bill Statements')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table thead { --bs-table-bg:#1a6b60; --bs-table-color:#fff; }
.dt-table th { font-size:.78rem; padding:.45rem .6rem; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-file-alt me-2 text-success"></i> LC Bill Statements</h4>
    <a href="{{ route('nas-trading.lc-bill-statements.create') }}" class="btn btn-sm btn-success">
        <i class="fa fa-plus me-1"></i> New Statement
    </a>
</div>

<div class="panel">
    <div class="panel-header"><i class="fa fa-list me-2"></i> Statement List</div>
    <div style="overflow-x:auto">
        <table id="stmtTable" class="table table-hover table-striped dt-table mb-0 w-100">
            <thead><tr>
                <th>#</th><th>Bill No</th><th>Customer</th><th>Bill Date</th><th>Status</th><th>Action</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#stmtTable').DataTable({
        processing: true, serverSide: true,
        autoWidth: false,
        ajax: '{{ route('nas-trading.lc-bill-statements.index') }}',
        columns: [
            { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '40px' },
            { data: 'bill_no',       name: 'bill_no' },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'bill_date',     name: 'bill_date' },
            { data: 'status_badge',  orderable: false, searchable: false },
            { data: 'action',        orderable: false, searchable: false, width: '110px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });

    $(document).on('click', '.btn-confirm', function () {
        const url = $(this).data('url');
        Swal.fire({ title: 'Confirm this statement?', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, Confirm' })
            .then(res => {
                if (res.isConfirmed) {
                    $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
                }
            });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete ' + name + '?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(xhr => Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Failed.' }));
                }
            });
    });
});
</script>
@endpush

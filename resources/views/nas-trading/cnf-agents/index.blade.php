@extends('nas-trading.layouts.app')
@section('title', 'CNF Agents')
@push('styles')
<style>
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.dt-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.dt-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-handshake me-2 text-info"></i> CNF Agents</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew"><i class="fa fa-plus me-1"></i> Add New</button>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="panel">
            <div class="panel-header" id="formTitle"><i class="fa fa-plus me-2"></i> Add CNF Agent</div>
            <div class="p-3">
                <form id="mainForm">
                    @csrf <input type="hidden" id="recId">
                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="fName" class="form-control form-control-sm">
                        <div class="invalid-feedback" id="fNameErr"></div>
                    </div>
                    <div class="mb-2"><label class="form-label">Phone</label><input type="text" id="fPhone" class="form-control form-control-sm"></div>
                    <div class="mb-2"><label class="form-label">Address</label><textarea id="fAddress" class="form-control form-control-sm" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select id="fStatus" class="form-select form-select-sm"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm px-4" id="btnSave"><i class="fa fa-save me-1"></i> Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCancel"><i class="fa fa-times me-1"></i> Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel">
            <div class="panel-header"><i class="fa fa-list me-2"></i> CNF Agent List</div>
            <div style="overflow-x:auto">
                <table id="mainTable" class="table table-hover table-striped dt-table mb-0 w-100">
                    <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Address</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;
$(function () {
    table = $('#mainTable').DataTable({
        processing: true, serverSide: true, ajax: '{{ route('nas-trading.cnf-agents.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'name', name: 'name' }, { data: 'phone', name: 'phone' }, { data: 'address', name: 'address' },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 15,
    });
    function resetForm() {
        $('#recId').val(''); $('#fName,#fPhone,#fAddress').val('');
        $('#fName').removeClass('is-invalid'); $('#fNameErr').text('');
        $('#fStatus').val('Active');
        $('#formTitle').html('<i class="fa fa-plus me-2"></i> Add CNF Agent');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }
    $('#btnAddNew,#btnCancel').on('click', resetForm);
    $(document).on('click', '.btn-edit', function () {
        $.getJSON('{{ url('nas-trading/cnf-agents') }}/' + $(this).data('id'), function (r) {
            resetForm(); $('#recId').val(r.id); $('#fName').val(r.name); $('#fPhone').val(r.phone||''); $('#fAddress').val(r.address||''); $('#fStatus').val(r.status);
            $('#formTitle').html('<i class="fa fa-edit me-2"></i> Edit CNF Agent'); $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html,body').animate({ scrollTop: 0 }, 200);
        });
    });
    $(document).on('click', '.btn-delete', function () {
        Swal.fire({ title: 'Delete "' + $(this).data('name') + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes' })
            .then(res => { if (res.isConfirmed) $.ajax({ url: $(this).data('url'), method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } }).done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); }); });
    });
    $('#mainForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#fName').val().trim()) { $('#fName').addClass('is-invalid'); $('#fNameErr').text('Name is required.'); return; }
        const id = $('#recId').val(), url = id ? '{{ url('nas-trading/cnf-agents') }}/' + id : '{{ route('nas-trading.cnf-agents.store') }}';
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: { _token: $('meta[name="csrf-token"]').attr('content'), name: $('#fName').val(), phone: $('#fPhone').val(), address: $('#fAddress').val(), status: $('#fStatus').val() }})
        .done(r => { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true }); resetForm(); table.ajax.reload(); })
        .fail(xhr => Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error' }))
        .always(() => { const e = $('#recId').val(); $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (e ? 'Update' : 'Save')); });
    });
});
</script>
@endpush

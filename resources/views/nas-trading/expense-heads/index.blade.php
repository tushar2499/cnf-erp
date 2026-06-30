@extends('nas-trading.layouts.app')
@section('title', 'Expense Heads')
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
    <h4><i class="fa fa-tags me-2 text-info"></i> Expense Heads</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew"><i class="fa fa-plus me-1"></i> Add New</button>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="panel">
            <div class="panel-header" id="formTitle"><i class="fa fa-plus me-2"></i> Add Expense Head</div>
            <div class="p-3">
                <form id="mainForm">
                    @csrf
                    <input type="hidden" id="recId">
                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="fName" class="form-control form-control-sm" placeholder="e.g. LC Commission, Customs Duty">
                        <div class="invalid-feedback" id="fNameErr"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="fCategory" class="form-select form-select-sm">
                            <option value="LC Cost">LC Cost</option>
                            <option value="Duty">Duty</option>
                            <option value="Shipping">Shipping</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="fStatus" class="form-select form-select-sm">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
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
            <div class="panel-header"><i class="fa fa-list me-2"></i> Expense Head List</div>
            <div style="overflow-x:auto">
                <table id="mainTable" class="table table-hover table-striped dt-table mb-0 w-100">
                    <thead><tr>
                        <th>#</th><th>Name</th><th>Category</th><th>Status</th><th>Action</th>
                    </tr></thead>
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
        processing: true, serverSide: true,
        ajax: '{{ route('nas-trading.expense-heads.index') }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'name',        name: 'name' },
            { data: 'category',    name: 'category' },
            { data: 'status_badge',orderable: false, searchable: false },
            { data: 'action',      orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 20,
    });

    function resetForm() {
        $('#recId').val(''); $('#fName').val('').removeClass('is-invalid'); $('#fNameErr').text('');
        $('#fCategory').val('LC Cost'); $('#fStatus').val('Active');
        $('#formTitle').html('<i class="fa fa-plus me-2"></i> Add Expense Head');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }
    $('#btnAddNew,#btnCancel').on('click', resetForm);

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('nas-trading/expense-heads') }}/' + id, function (r) {
            resetForm();
            $('#recId').val(r.id); $('#fName').val(r.name);
            $('#fCategory').val(r.category); $('#fStatus').val(r.status);
            $('#formTitle').html('<i class="fa fa-edit me-2"></i> Edit Expense Head');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html,body').animate({ scrollTop: 0 }, 200);
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => { if (res.isConfirmed) $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } }).done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); }); });
    });

    $('#mainForm').on('submit', function (e) {
        e.preventDefault();
        $('#fName').removeClass('is-invalid'); $('#fNameErr').text('');
        if (!$('#fName').val().trim()) { $('#fName').addClass('is-invalid'); $('#fNameErr').text('Name is required.'); return; }
        const id = $('#recId').val();
        const url = id ? '{{ url('nas-trading/expense-heads') }}/' + id : '{{ route('nas-trading.expense-heads.store') }}';
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#fName').val(), category: $('#fCategory').val(), status: $('#fStatus').val(),
        }})
        .done(r => { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true }); resetForm(); table.ajax.reload(); })
        .fail(xhr => { if (xhr.status === 422 && xhr.responseJSON.errors.name) { $('#fName').addClass('is-invalid'); $('#fNameErr').text(xhr.responseJSON.errors.name[0]); } else Swal.fire({ icon: 'error', title: 'Something went wrong.' }); })
        .always(() => { const isEdit = $('#recId').val(); $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save')); });
    });
});
</script>
@endpush

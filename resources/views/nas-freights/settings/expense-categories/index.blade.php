@extends('nas-freights.layouts.app')
@section('title', 'Expense Categories')

@push('styles')
    <style>
        #catTable th, #catTable td { white-space: nowrap; font-size: .73rem; padding: .3rem .5rem; }
        #catTable thead tr:first-child th { background: #e9ecef; font-weight: 600; position: sticky; top: 0; z-index: 2; }
        #catTable thead tr:last-child th { background: #f8f9fa; font-weight: normal; position: sticky; z-index: 2; }
        #catTable thead tr:last-child th input.form-control { min-width: 120px; width: 100%; font-size: .78rem; padding: .3rem .5rem; }
        .cat-table-wrapper { max-height: 65vh; overflow: auto; }
        .cat-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
        .cat-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
        .cat-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #catTable_wrapper > .row:last-child { position: sticky; bottom: 0; background: #fff; z-index: 3; border-top: 1px solid #dee2e6; margin: 0; padding: 6px 12px; }
    </style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-tags me-2 text-info"></i> Expense Categories</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#catModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Category
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Expense Categories</span>
    </div>
    <div class="card-body p-0">
        <div class="cat-table-wrapper">
            <table id="catTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Description"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Expense Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="catForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control form-control-sm" placeholder="e.g. Transport">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea id="fieldDescription" class="form-control form-control-sm" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fieldActive" checked>
                                <label class="form-check-label" for="fieldActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white" id="btnSave">
                        <i class="fa fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;

function clearForm() {
    $('#recordId').val('');
    $('#fieldName').val('').removeClass('is-invalid');
    $('#fieldDescription').val('');
    $('#fieldActive').prop('checked', true);
}

$(function () {
    table = $('#catTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        ajax: '{{ route('nas-freights.settings.expense-categories.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'name',         name: 'name' },
            { data: 'description',  name: 'description' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '80px', className: 'text-center' },
            { data: 'action',       name: 'action',       orderable: false, searchable: false, width: '90px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'>>" +
             "<'row'<'col-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No expense categories yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#catTable thead tr:first-child').outerHeight();
            $('#catTable thead tr:last-child th').css('top', firstRowH + 'px');
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

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Expense Category');
        clearForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Expense Category');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldName').val(d.name);
        $('#fieldDescription').val(d.description);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#catModal').modal('show');
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

    $('#catForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#fieldName').val().trim()) {
            $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.'); return;
        }
        $('#fieldName').removeClass('is-invalid');

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/expense-categories') }}/' + id
            : '{{ route('nas-freights.settings.expense-categories.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:      $('meta[name="csrf-token"]').attr('content'),
            name:        $('#fieldName').val(),
            description: $('#fieldDescription').val(),
            is_active:   $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#catModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(xhr => {
            const errors = xhr.responseJSON?.errors;
            if (errors?.name) { $('#fieldName').addClass('is-invalid'); $('#nameError').text(errors.name[0]); }
            else Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });
});
</script>
@endpush

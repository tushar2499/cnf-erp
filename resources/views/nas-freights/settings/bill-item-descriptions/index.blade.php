@extends('nas-freights.layouts.app')
@section('title', 'Bill Item Descriptions')

@push('styles')
    <style>
        #billItemDescTable th,
        #billItemDescTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #billItemDescTable thead tr:first-child th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #billItemDescTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #billItemDescTable thead tr:last-child th input.form-control {
            min-width: 120px;
            width: 100%;
            box-sizing: border-box;
            font-size: .78rem;
            padding: .3rem .5rem;
        }

        .bill-item-desc-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .bill-item-desc-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .bill-item-desc-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bill-item-desc-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #billItemDescTable_wrapper>.row:last-child {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 3;
            border-top: 1px solid #dee2e6;
            margin: 0;
            padding: 6px 12px;
        }
    </style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-list-ul me-2 text-warning"></i> Bill Item Descriptions</h4>
    @if (auth()->user()->hasPermission('freight.bill-item-description.create'))
    <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#billItemDescModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Description
    </button>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Bill Item Descriptions</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item"
                        onclick="$('#billItemDescTable').DataTable().button('.buttons-csv').trigger()"><i
                            class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#billItemDescTable').DataTable().button('.buttons-excel').trigger()"><i
                            class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#billItemDescTable').DataTable().button('.buttons-pdf').trigger()"><i
                            class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item"
                        onclick="$('#billItemDescTable').DataTable().button('.buttons-print').trigger()"><i
                            class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="bill-item-desc-table-wrapper">
            <table id="billItemDescTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Sort"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="billItemDescModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Bill Item Description</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="billItemDescForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control" placeholder="e.g. Air Freight Charges" maxlength="255">
                            <div class="invalid-feedback" id="nameError"></div>
                            <div class="form-text">This exact text is saved on the bill item.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" id="fieldSortOrder" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-8 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fieldActive" checked>
                                <label class="form-check-label" for="fieldActive">Active (show in dropdown)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark" id="btnSave">
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
$(function () {
    table = $('#billItemDescTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        orderCellsTop: true,
        pageLength: 15,
        order: [],
        lengthMenu: [
            [10, 15, 25, 50, 100, 200],
            [10, 15, 25, 50, 100, 200]
        ],
        ajax: '{{ route('nas-freights.settings.bill-item-descriptions.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'sort_order', name: 'sort_order', width: '90px' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '80px', className: 'text-center' },
        ],
        dom: "<'row mb-1'<'col-sm-6'l><'col-sm-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            { extend: 'csv' },
            { extend: 'excel' },
            { extend: 'pdf' },
            { extend: 'print' }
        ],
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No bill item descriptions yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#billItemDescTable thead tr:first-child').outerHeight();
            $('#billItemDescTable thead tr:last-child th').css('top', firstRowH + 'px');

            var self = this.api();
            self.columns().every(function (i) {
                var col = this;
                var $in = $('thead tr:eq(1) th:eq(' + i + ') input', self.table().container());
                if ($in.length) {
                    $in.on('click mousedown keydown', function (e) {
                        e.stopPropagation();
                    });
                    var timer;
                    $in.on('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            col.search($in.val()).draw();
                        }, 400);
                    });
                }
            });
        },
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Bill Item Description');
        $('#recordId').val('');
        $('#fieldName').val('');
        $('#fieldSortOrder').val(0);
        $('#fieldActive').prop('checked', true);
        $('#fieldName').removeClass('is-invalid');
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Bill Item Description');
        $('#recordId').val(d.id);
        $('#fieldName').val(d.name).removeClass('is-invalid');
        $('#fieldSortOrder').val(d.sort_order);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#billItemDescModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', text: 'Existing bills keep their saved text.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
    });

    $('#billItemDescForm').on('submit', function (e) {
        e.preventDefault();
        const name = $('#fieldName').val().trim();
        if (!name) { $('#fieldName').addClass('is-invalid'); $('#nameError').text('Description is required.'); return; }
        $('#fieldName').removeClass('is-invalid');

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/bill-item-descriptions') }}/' + id
            : '{{ route('nas-freights.settings.bill-item-descriptions.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:      $('meta[name="csrf-token"]').attr('content'),
            name:        name,
            sort_order:  $('#fieldSortOrder').val(),
            is_active:   $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#billItemDescModal').modal('hide');
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

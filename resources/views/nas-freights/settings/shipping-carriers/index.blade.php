@extends('nas-freights.layouts.app')
@section('title', 'Shipping Carriers')

@push('styles')
    <style>
        #carriersTable th,
        #carriersTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #carriersTable thead tr:first-child th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #carriersTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #carriersTable thead tr:last-child th input.form-control {
            min-width: 120px;
            width: 100%;
            box-sizing: border-box;
            font-size: .78rem;
            padding: .3rem .5rem;
        }

        .carriers-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .carriers-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .carriers-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .carriers-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #carriersTable_wrapper>.row:last-child {
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
    <h4><i class="fa fa-ship me-2 text-info"></i> Shipping Carriers</h4>
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#carrierModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Carrier
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Shipping Carriers</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item"
                        onclick="$('#carriersTable').DataTable().button('.buttons-csv').trigger()"><i
                            class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#carriersTable').DataTable().button('.buttons-excel').trigger()"><i
                            class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#carriersTable').DataTable().button('.buttons-pdf').trigger()"><i
                            class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item"
                        onclick="$('#carriersTable').DataTable().button('.buttons-print').trigger()"><i
                            class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="carriers-table-wrapper">
            <table id="carriersTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>SCAC Code</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Code"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search SCAC"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="carrierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Shipping Carrier</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="carrierForm">
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Carrier Code</label>
                            <input type="text" id="fieldCode" class="form-control form-control-sm bg-light" readonly placeholder="Auto Generated">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Carrier Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldName" class="form-control form-control-sm" placeholder="e.g. Maersk Line">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SCAC Code</label>
                            <input type="text" id="fieldScacCode" class="form-control form-control-sm text-uppercase" placeholder="e.g. MAEU" maxlength="20">
                        </div>
                        <div class="col-md-8 d-flex align-items-end pb-1">
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
    $('#recordId, #fieldCode').val('');
    $('#fieldName, #fieldScacCode').val('');
    $('#fieldActive').prop('checked', true);
    $('#fieldName').removeClass('is-invalid');
}

$(function () {
    table = $('#carriersTable').DataTable({
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
        ajax: '{{ route('nas-freights.settings.shipping-carriers.index') }}',
        columns: [
            { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'carrier_code', name: 'carrier_code', width: '110px' },
            { data: 'name',         name: 'name' },
            { data: 'scac_code',    name: 'scac_code',    width: '110px' },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '80px', className: 'text-center' },
            { data: 'action',       name: 'action',       orderable: false, searchable: false, width: '90px', className: 'text-center' },
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No shipping carriers yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#carriersTable thead tr:first-child').outerHeight();
            $('#carriersTable thead tr:last-child th').css('top', firstRowH + 'px');

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
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Shipping Carrier');
        clearForm();
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Shipping Carrier');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldCode').val(d.carrier_code);
        $('#fieldName').val(d.name);
        $('#fieldScacCode').val(d.scac_code);
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#carrierModal').modal('show');
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

    $('#carrierForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#fieldName').val().trim()) {
            $('#fieldName').addClass('is-invalid'); $('#nameError').text('Name is required.');
            return;
        }
        $('#fieldName').removeClass('is-invalid');

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/shipping-carriers') }}/' + id
            : '{{ route('nas-freights.settings.shipping-carriers.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:    $('meta[name="csrf-token"]').attr('content'),
            name:      $('#fieldName').val(),
            scac_code: $('#fieldScacCode').val(),
            is_active: $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#carrierModal').modal('hide');
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

    $('#fieldScacCode').on('input', function () { this.value = this.value.toUpperCase(); });
});
</script>
@endpush

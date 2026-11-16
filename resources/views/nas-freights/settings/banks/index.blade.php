@extends('nas-freights.layouts.app')
@section('title', 'Banks')

@push('styles')
    <style>
        #banksTable th,
        #banksTable td {
            white-space: nowrap;
            font-size: .73rem;
            padding: .3rem .5rem;
        }

        #banksTable thead tr:first-child th {
            background: #e9ecef;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #banksTable thead tr:last-child th {
            background: #f8f9fa;
            font-weight: normal;
            position: sticky;
            z-index: 2;
        }

        #banksTable thead tr:last-child th input.form-control {
            min-width: 120px;
            width: 100%;
            box-sizing: border-box;
            font-size: .78rem;
            padding: .3rem .5rem;
        }

        .banks-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .banks-table-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .banks-table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .banks-table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #banksTable_wrapper>.row:last-child {
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
    <h4><i class="fa fa-university me-2 text-info"></i> Banks</h4>
    @if (auth()->user()->hasPermission('freight.bank.create'))
    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#bankModal" id="btnAdd">
        <i class="fa fa-plus me-1"></i> Add Bank
    </button>
    @endif
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa fa-list me-2"></i> All Banks</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item"
                        onclick="$('#banksTable').DataTable().button('.buttons-csv').trigger()"><i
                            class="fa fa-file-csv me-2 text-secondary"></i>CSV</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#banksTable').DataTable().button('.buttons-excel').trigger()"><i
                            class="fa fa-file-excel me-2 text-success"></i>Excel</button></li>
                <li><button class="dropdown-item"
                        onclick="$('#banksTable').DataTable().button('.buttons-pdf').trigger()"><i
                            class="fa fa-file-pdf me-2 text-danger"></i>PDF</button></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><button class="dropdown-item"
                        onclick="$('#banksTable').DataTable().button('.buttons-print').trigger()"><i
                            class="fa fa-print me-2"></i>Print</button></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="banks-table-wrapper">
            <table id="banksTable" class="table table-hover table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Account Name</th>
                        <th>Account Number</th>
                        <th>Account Type</th>
                        <th>Bank Name</th>
                        <th>Bank Branch</th>
                        <th>Routing Number</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Account Name"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Account No"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Type"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Bank"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Branch"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Search Routing"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="bankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-600" id="modalTitle"><i class="fa fa-plus me-2"></i> Add Bank</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bankForm" novalidate>
                @csrf
                <input type="hidden" id="recordId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="fieldAccountName">Account Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldAccountName" class="form-control form-control-sm" placeholder="e.g. NAS Freights Ltd." maxlength="255">
                            <div class="invalid-feedback" id="accountNameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fieldAccountNumber">Account Number <span class="text-danger">*</span></label>
                            <input type="text" id="fieldAccountNumber" class="form-control form-control-sm" placeholder="e.g. 1234567890123" maxlength="255" inputmode="numeric">
                            <div class="invalid-feedback" id="accountNumberError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fieldAccountType">Account Type <span class="text-danger">*</span></label>
                            <input type="text" id="fieldAccountType" class="form-control form-control-sm" placeholder="e.g. Current" maxlength="50">
                            <div class="invalid-feedback" id="accountTypeError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fieldBankName">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" id="fieldBankName" class="form-control form-control-sm" placeholder="e.g. Islami Bank Bangladesh Ltd." maxlength="255">
                            <div class="invalid-feedback" id="bankNameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fieldBankBranch">Bank Branch</label>
                            <input type="text" id="fieldBankBranch" class="form-control form-control-sm" placeholder="e.g. Motijheel" maxlength="255">
                            <div class="invalid-feedback" id="bankBranchError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fieldRoutingNumber">Routing Number</label>
                            <input type="text" id="fieldRoutingNumber" class="form-control form-control-sm" placeholder="e.g. 125261234" maxlength="50" inputmode="numeric">
                            <div class="invalid-feedback" id="routingNumberError"></div>
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

// field key (server error key) => [input selector, error selector, required message]
const bankFields = {
    account_name:   ['#fieldAccountName',   '#accountNameError',   'Account name is required.'],
    account_number: ['#fieldAccountNumber', '#accountNumberError', 'Account number is required.'],
    account_type:   ['#fieldAccountType',   '#accountTypeError',   'Account type is required.'],
    bank_name:      ['#fieldBankName',      '#bankNameError',      'Bank name is required.'],
    bank_branch:    ['#fieldBankBranch',    '#bankBranchError',    null],
    routing_number: ['#fieldRoutingNumber', '#routingNumberError', null],
};

function clearErrors() {
    $.each(bankFields, (key, f) => { $(f[0]).removeClass('is-invalid'); $(f[1]).text(''); });
}

function clearForm() {
    $('#recordId').val('');
    $('#fieldAccountName, #fieldAccountNumber, #fieldAccountType, #fieldBankName, #fieldBankBranch, #fieldRoutingNumber').val('');
    $('#fieldActive').prop('checked', true);
    clearErrors();
}

$(function () {
    table = $('#banksTable').DataTable({
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
        ajax: '{{ route('nas-freights.settings.banks.index') }}',
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false, width: '40px', className: 'text-center' },
            { data: 'action',         name: 'action',         orderable: false, searchable: false, width: '90px', className: 'text-center' },
            { data: 'account_name',   name: 'account_name' },
            { data: 'account_number', name: 'account_number', width: '140px' },
            { data: 'account_type',   name: 'account_type',   width: '100px' },
            { data: 'bank_name',      name: 'bank_name' },
            { data: 'bank_branch',    name: 'bank_branch',    width: '130px' },
            { data: 'routing_number', name: 'routing_number', width: '120px' },
            { data: 'status_badge',   name: 'status_badge',   orderable: false, searchable: false, width: '80px', className: 'text-center' },
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
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No banks yet.</div>' },
        initComplete: function () {
            const firstRowH = $('#banksTable thead tr:first-child').outerHeight();
            $('#banksTable thead tr:last-child th').css('top', firstRowH + 'px');

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
        $('#modalTitle').html('<i class="fa fa-plus me-2"></i> Add Bank');
        clearForm();
    });

    $('#bankModal').on('shown.bs.modal', function () {
        $('#fieldAccountName').trigger('focus');
    });

    $(document).on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').html('<i class="fa fa-edit me-2"></i> Edit Bank');
        clearForm();
        $('#recordId').val(d.id);
        $('#fieldAccountName').val(d.account_name);
        $('#fieldAccountNumber').val(String(d.account_number));
        $('#fieldAccountType').val(d.account_type);
        $('#fieldBankName').val(d.bank_name);
        $('#fieldBankBranch').val(d.bank_branch);
        $('#fieldRoutingNumber').val(String(d.routing_number ?? ''));
        $('#fieldActive').prop('checked', d.is_active == 1);
        $('#bankModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
    });

    $('#bankForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;
        $.each(bankFields, (key, f) => {
            if (f[2] && !String($(f[0]).val() ?? '').trim()) {
                $(f[0]).addClass('is-invalid'); $(f[1]).text(f[2]);
                valid = false;
            }
        });
        if (!valid) { $('#bankForm .is-invalid').first().trigger('focus'); return; }

        const id  = $('#recordId').val();
        const url = id
            ? '{{ url('nas-freights/settings/banks') }}/' + id
            : '{{ route('nas-freights.settings.banks.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({ url, method: id ? 'PUT' : 'POST', data: {
            _token:         $('meta[name="csrf-token"]').attr('content'),
            account_name:   $('#fieldAccountName').val(),
            account_number: $('#fieldAccountNumber').val(),
            account_type:   $('#fieldAccountType').val(),
            bank_name:      $('#fieldBankName').val(),
            bank_branch:    $('#fieldBankBranch').val(),
            routing_number: $('#fieldRoutingNumber').val(),
            is_active:      $('#fieldActive').is(':checked') ? 1 : 0,
        }})
        .done(r => {
            $('#bankModal').modal('hide');
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false });
            table.ajax.reload();
        })
        .fail(xhr => {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                $.each(bankFields, (key, f) => {
                    if (errors[key]) { $(f[0]).addClass('is-invalid'); $(f[1]).text(errors[key][0]); }
                });
                $('#bankForm .is-invalid').first().trigger('focus');
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error.' });
            }
        })
        .always(() => $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save'));
    });
});
</script>
@endpush

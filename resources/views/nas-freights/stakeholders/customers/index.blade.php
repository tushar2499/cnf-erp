@extends('nas-freights.layouts.app')

@section('title', 'Customers')

@push('styles')
<style>
.cus-panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; height:100%; }
.cus-panel-header { background:#0c2340; color:#fff; padding:.6rem 1rem; border-radius:.5rem .5rem 0 0; font-weight:600; font-size:.85rem; }
.cus-list-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.45rem .6rem; }
.cus-list-table td { font-size:.8rem; padding:.4rem .6rem; vertical-align:middle; }
.cus-list-table tbody tr:hover { background:#e8f8f5; cursor:pointer; }
.form-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-users me-2 text-info"></i> Customers</h4>
    <button class="btn btn-sm btn-info text-white" id="btnAddNew">
        <i class="fa fa-plus me-1"></i> Add New Customer
    </button>
</div>

<div class="row g-3">
    {{-- ── Left: Form ── --}}
    <div class="col-md-5">
        <div class="cus-panel">
            <div class="cus-panel-header" id="formPanelTitle">
                <i class="fa fa-plus me-2"></i> Add New Customer
            </div>
            <div class="p-3">
                <form id="customerForm">
                    @csrf
                    <input type="hidden" id="cusId">

                    <div class="mb-2">
                        <label class="form-label">Customer Prefix <span class="text-danger">*</span></label>
                        <select id="cusPrefix" class="form-select form-select-sm">
                            <option value="CUS-">CUS-</option>
                            <option value="CLI-">CLI-</option>
                            <option value="CT-">CT-</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Customer group</label>
                        <select id="cusGroup" class="form-select form-select-sm">
                            <option value="">-- Select Group --</option>
                            @foreach($customerGroups as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="cusName" class="form-control form-control-sm" placeholder="Customer / Company name">
                        <div class="invalid-feedback" id="cusNameErr"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Address</label>
                        <textarea id="cusAddress" class="form-control form-control-sm" rows="3" placeholder="Full address..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Mobile</label>
                        <input type="text" id="cusMobile" class="form-control form-control-sm" placeholder="+880...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="cusEmail" class="form-control form-control-sm" placeholder="email@example.com">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm px-4" id="btnSave">
                            <i class="fa fa-save me-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnCancel">
                            <i class="fa fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Right: Customer List ── --}}
    <div class="col-md-7">
        <div class="cus-panel">
            <div class="cus-panel-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-list me-2"></i> Customer List</span>
                <div class="d-flex gap-1">
                    <button onclick="$('#customersTable').DataTable().button('.buttons-csv').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-csv me-1"></i>CSV</button>
                    <button onclick="$('#customersTable').DataTable().button('.buttons-excel').trigger()" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-excel me-1"></i>Excel</button>
                    <button onclick="$('#customersTable').DataTable().button('.buttons-pdf').trigger()"   class="btn btn-sm btn-outline-light py-0 px-2" style="font-size:.72rem"><i class="fa fa-file-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div class="p-0">
                <div style="overflow-x:auto">
                    <table id="customersTable" class="table table-hover table-striped cus-list-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Mobile</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;

$(function () {
    table = $('#customersTable').DataTable({
        processing: true, serverSide: true,
        ajax: '{{ route('nas-freights.stakeholders.customers.index') }}',
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false, width: '40px' },
            { data: 'customer_id',    name: 'customer_id' },
            { data: 'name',           name: 'name' },
            { data: 'customer_group', name: 'customer_group' },
            { data: 'mobile',         name: 'mobile' },
            { data: 'status_badge',   name: 'status', orderable: false, searchable: false },
            { data: 'action',         name: 'action', orderable: false, searchable: false, width: '80px' },
        ],
        dom: "<'row px-2 pt-2 mb-0'<'col-sm-6'><'col-sm-6'f>><'row'<'col-12'tr>><'row px-2 pt-1 pb-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{ extend: 'csv' }, { extend: 'excel' }, { extend: 'pdf' }, { extend: 'print' }],
        pageLength: 15,
        language: { emptyTable: '<div class="text-center py-3 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>No customers yet.</div>' },
    });

    function resetForm() {
        $('#cusId').val('');
        $('#cusPrefix').val('CUS-');
        $('#cusGroup').val('');
        $('#cusName').val('').removeClass('is-invalid');
        $('#cusAddress').val('');
        $('#cusMobile').val('');
        $('#cusEmail').val('');
        $('#cusNameErr').text('');
        $('#formPanelTitle').html('<i class="fa fa-plus me-2"></i> Add New Customer');
        $('#btnSave').html('<i class="fa fa-save me-1"></i> Save');
    }

    $('#btnAddNew, #btnCancel').on('click', resetForm);

    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.getJSON('{{ url('nas-freights/stakeholders/customers') }}/' + id, function (r) {
            resetForm();
            $('#cusId').val(r.id);
            $('#cusPrefix').val(r.id_prefix);
            $('#cusGroup').val(r.customer_group || '');
            $('#cusName').val(r.name);
            $('#cusAddress').val(r.address || '');
            $('#cusMobile').val(r.mobile || '');
            $('#cusEmail').val(r.email || '');
            $('#formPanelTitle').html('<i class="fa fa-edit me-2"></i> Edit Customer');
            $('#btnSave').html('<i class="fa fa-save me-1"></i> Update');
            $('html, body').animate({ scrollTop: 0 }, 200);
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const url = $(this).data('url'), name = $(this).data('name');
        Swal.fire({ title: 'Delete "' + name + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete' })
            .then(res => {
                if (res.isConfirmed) {
                    $.ajax({ url, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                        .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }); table.ajax.reload(); })
                        .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
                }
            });
    });

    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        $('#cusName').removeClass('is-invalid');
        $('#cusNameErr').text('');

        if (!$('#cusName').val().trim()) {
            $('#cusName').addClass('is-invalid');
            $('#cusNameErr').text('Please enter customer name.');
            return;
        }

        const id  = $('#cusId').val();
        const url = id
            ? '{{ url('nas-freights/stakeholders/customers') }}/' + id
            : '{{ route('nas-freights.stakeholders.customers.store') }}';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url, method: id ? 'PUT' : 'POST',
            data: {
                _token:         $('meta[name="csrf-token"]').attr('content'),
                id_prefix:      $('#cusPrefix').val(),
                customer_group: $('#cusGroup').val(),
                name:           $('#cusName').val(),
                address:        $('#cusAddress').val(),
                mobile:         $('#cusMobile').val(),
                email:          $('#cusEmail').val(),
            },
        })
        .done(function (r) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2500, timerProgressBar: true });
            resetForm();
            table.ajax.reload();
        })
        .fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.name) {
                    $('#cusName').addClass('is-invalid');
                    $('#cusNameErr').text(errors.name[0]);
                }
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        })
        .always(function () {
            const isEdit = $('#cusId').val();
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + (isEdit ? 'Update' : 'Save'));
        });
    });
});
</script>
@endpush

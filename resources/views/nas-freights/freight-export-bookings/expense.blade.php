@extends('nas-freights.layouts.app')

@section('title', 'Expense — ' . $exportBooking->export_booking_no)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .2rem;
        }

        .section-bar {
            background: #0c2340;
            color: #fff;
            padding: .4rem .85rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            border-radius: .3rem .3rem 0 0;
        }

        .booking-card {
            border: 1px solid #dee2e6;
            border-radius: .4rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .booking-card .card-body {
            padding: .75rem;
        }

        .info-label {
            font-size: .68rem;
            font-weight: 700;
            color: #6b7a99;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .1rem;
        }

        .info-value {
            font-size: .82rem;
            color: #1e293b;
        }

        #rowsTable {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        #rowsTable th {
            background: #1a6b60;
            color: #fff;
            padding: .4rem .5rem;
            white-space: nowrap;
        }

        #rowsTable td {
            padding: .3rem .4rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        #rowsTable input[type=number],
        #rowsTable input[type=text],
        #rowsTable input[type=date],
        #rowsTable select {
            font-size: .78rem;
            padding: .2rem .4rem;
        }

        .row-del-btn {
            background: none;
            border: none;
            color: #dc3545;
            font-size: .9rem;
            cursor: pointer;
            padding: .1rem .3rem;
        }

        .total-bar {
            background: #f0f8ff;
            border-top: 2px solid #0c2340;
            padding: .4rem .75rem;
            font-size: .82rem;
            font-weight: 600;
        }

        .select2-container .select2-selection--single {
            height: 31px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 29px;
            font-size: .875rem;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }

        .dup-warn { background: #fff3cd !important; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h4><i class="fa fa-receipt me-2 text-success"></i> Expense —
            <span class="text-muted fw-normal">{{ $exportBooking->export_booking_no }}</span>
        </h4>
        <a href="{{ route('nas-freights.freight-export-bookings.show', $exportBooking->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Booking/Job
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Booking Summary --}}
    <div class="booking-card">
        <div class="section-bar" style="background:#0a4f3c"><i class="fa fa-ship me-2"></i>Export Booking/Job Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-2">
                    <div class="info-label">Booking/Job No</div>
                    <div class="info-value fw-bold">{{ $exportBooking->export_booking_no }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ $exportBooking->booking_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="badge
                            @if($exportBooking->status === 'Confirmed') bg-success
                            @elseif($exportBooking->status === 'In-Transit') bg-info text-dark
                            @elseif($exportBooking->status === 'Delivered') bg-primary
                            @elseif($exportBooking->status === 'Cancelled') bg-danger
                            @else bg-secondary
                            @endif">{{ $exportBooking->status }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Customer</div>
                    <div class="info-value fw-semibold">{{ $exportBooking->customer?->name ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-label">Service / Route</div>
                    <div class="info-value">{{ $exportBooking->service_type ?? '—' }}
                        @if($exportBooking->pol || $exportBooking->pod)
                            &nbsp;<span class="text-muted">·</span>&nbsp;{{ $exportBooking->pol ?? '—' }} → {{ $exportBooking->pod ?? '—' }}
                        @endif
                    </div>
                </div>
                @if($exportBooking->export_bl_no)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Export B/L No</div>
                        <div class="info-value">{{ $exportBooking->export_bl_no }}</div>
                    </div>
                @endif
                @if($exportBooking->invoice_no)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Invoice No</div>
                        <div class="info-value">{{ $exportBooking->invoice_no }}</div>
                    </div>
                @endif
                @if($exportBooking->salesperson)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $exportBooking->salesperson->name }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <form id="expenseForm" method="POST"
        action="{{ route('nas-freights.freight-export-bookings.expense.update', $exportBooking->id) }}">
        @csrf
        @method('PUT')

        {{-- Header fields --}}
        <div class="booking-card">
            <div class="section-bar d-flex justify-content-between align-items-center">
                <span><i class="fa fa-receipt me-2"></i>Expense Details</span>
                @if($expense)
                    <span class="badge bg-light text-dark border" style="font-size:.75rem;">{{ $expense->expense_no }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <input type="hidden" name="employee_id" id="employeeId"
                               value="{{ old('employee_id', $expense?->employee_id) }}">
                        <select id="employeeSelect" class="form-select form-select-sm w-100">
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ old('employee_id', $expense?->employee_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->code }} — {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date <span style="color:#dc2626">*</span></label>
                        <input type="date" name="date" class="form-control form-control-sm @error('date') is-invalid @enderror"
                               value="{{ old('date', $expense?->date?->format('Y-m-d') ?? $today) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Expense</label>
                        <input type="text" id="totalExpDisplay" class="form-control form-control-sm bg-light" readonly
                               value="{{ number_format($expense?->total_expense_amount ?? 0, 2) }}">
                        <input type="hidden" name="total_expense_amount" id="hidTotalExp"
                               value="{{ old('total_expense_amount', $expense?->total_expense_amount ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Approved</label>
                        <input type="text" id="totalAppDisplay" class="form-control form-control-sm bg-light" readonly
                               value="{{ number_format($expense?->total_approved_amount ?? 0, 2) }}">
                        <input type="hidden" name="total_approved_amount" id="hidTotalApp"
                               value="{{ old('total_approved_amount', $expense?->total_approved_amount ?? 0) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2"
                                  placeholder="Remarks">{{ old('remarks', $expense?->remarks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expense Lines --}}
        <div class="booking-card">
            <div class="section-bar d-flex justify-content-between align-items-center">
                <span><i class="fa fa-list-ul me-2"></i>Expense Lines</span>
                <button type="button" id="btnAddRow" class="btn btn-sm btn-light py-0">
                    <i class="fa fa-plus me-1"></i> Add Row
                </button>
            </div>
            <div style="overflow-x:auto">
                <table id="rowsTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th style="width:30px;">SL</th>
                            <th style="min-width:220px;">Expense Head</th>
                            <th style="min-width:100px;">Receiptable</th>
                            <th style="min-width:130px;">Expense Amount</th>
                            <th style="min-width:130px;">Approved Amount</th>
                            <th style="min-width:130px;">Expense Date</th>
                            <th style="min-width:160px;">Note</th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody"></tbody>
                </table>
                <div class="total-bar d-flex gap-4">
                    <span>Rows: <span id="totalRows">0</span></span>
                    <span>Total Expense: <strong id="footerTotalExp">0.00</strong></span>
                    <span>Total Approved: <strong id="footerTotalApp">0.00</strong></span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4 justify-content-end">
            <button type="submit" class="btn btn-success px-5" id="btnSave">
                <i class="fa fa-save me-1"></i>
                {{ $expense ? 'Update Expense' : 'Save Expense' }}
            </button>
            <a href="{{ route('nas-freights.freight-export-bookings.show', $exportBooking->id) }}"
               class="btn btn-outline-secondary px-4">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var expenseHeads  = @json($expenseHeads);
        var existingRows  = @json($existingRows);
        var TODAY = '{{ $today }}';
        var rowCount = 0;

        function headOpts(selectedId) {
            return '<option value="">-- Select --</option>' +
                expenseHeads.map(h =>
                    `<option value="${h.id}" data-amount="${h.amount ?? ''}"
                        ${parseInt(h.id) === parseInt(selectedId) ? 'selected' : ''}>${escHtml(h.name)}</option>`
                ).join('');
        }

        function addRow(prefill) {
            rowCount++;
            const p   = prefill || {};
            const idx = $('#rowsBody tr').length;
            const sl  = idx + 1;

            const row = `
<tr data-row="${rowCount}">
    <td><button type="button" class="row-del-btn" onclick="delRow(this)" title="Remove"><i class="fa fa-times"></i></button></td>
    <td class="sl-no text-center fw-bold">${sl}</td>
    <td>
        <select name="rows[${idx}][expense_head_id]" class="form-select form-select-sm expense-head-sel" style="min-width:200px;">
            ${headOpts(p.expense_head_id || '')}
        </select>
    </td>
    <td>
        <select name="rows[${idx}][receiptable]" class="form-select form-select-sm">
            <option value="No"  ${(p.receiptable || 'No') === 'No'  ? 'selected' : ''}>No</option>
            <option value="Yes" ${(p.receiptable) === 'Yes' ? 'selected' : ''}>Yes</option>
        </select>
    </td>
    <td><input type="number" name="rows[${idx}][expense_amount]"  class="form-control form-control-sm expense-amt text-end"    step="0.01" min="0" value="${p.expense_amount  ?? 0}"></td>
    <td><input type="number" name="rows[${idx}][approved_amount]" class="form-control form-control-sm approved-amt text-end"   step="0.01" min="0" value="${p.approved_amount ?? 0}"></td>
    <td><input type="date"   name="rows[${idx}][expense_date]"    class="form-control form-control-sm" value="${p.expense_date ?? TODAY}"></td>
    <td><input type="text"   name="rows[${idx}][note]"             class="form-control form-control-sm" value="${escHtml(p.note || '')}" placeholder="Note"></td>
</tr>`;
            $('#rowsBody').append(row);
            $(`[data-row="${rowCount}"] .expense-head-sel`).select2({
                placeholder: '-- Select --',
                width: '100%',
                dropdownParent: $('body'),
            });
            reindex();
            recalc();
        }

        window.delRow = function (btn) {
            if ($('#rowsBody tr').length <= 1) {
                Swal.fire({ icon: 'warning', title: 'At least one row required.', timer: 1500, showConfirmButton: false });
                return;
            }
            $(btn).closest('tr').remove();
            reindex();
            recalc();
            highlightDuplicates();
        };

        function reindex() {
            $('#rowsBody tr').each(function (i) {
                $(this).find('.sl-no').text(i + 1);
                $(this).find('[name]').each(function () {
                    $(this).attr('name', $(this).attr('name').replace(/rows\[\d+\]/, `rows[${i}]`));
                });
            });
            $('#totalRows').text($('#rowsBody tr').length);
        }

        function recalc() {
            let exp = 0, app = 0;
            $('#rowsBody tr').each(function () {
                exp += parseFloat($(this).find('.expense-amt').val())  || 0;
                app += parseFloat($(this).find('.approved-amt').val()) || 0;
            });
            $('#totalExpDisplay, #footerTotalExp').val ? $('#totalExpDisplay').val(exp.toFixed(2)) : null;
            $('#totalExpDisplay').val(exp.toFixed(2));
            $('#totalAppDisplay').val(app.toFixed(2));
            $('#hidTotalExp').val(exp.toFixed(2));
            $('#hidTotalApp').val(app.toFixed(2));
            $('#footerTotalExp').text(exp.toFixed(2));
            $('#footerTotalApp').text(app.toFixed(2));
        }

        function getDuplicates() {
            const seen = {}, dupes = {};
            $('#rowsBody .expense-head-sel').each(function () {
                const v = $(this).val();
                if (!v) { return; }
                seen[v] ? (dupes[v] = true) : (seen[v] = true);
            });
            return dupes;
        }

        function highlightDuplicates() {
            const dupes = getDuplicates();
            $('#rowsBody .expense-head-sel').each(function () {
                const v = $(this).val();
                $(this).closest('td').toggleClass('dup-warn', !!(v && dupes[v]));
            });
            return Object.keys(dupes).length > 0;
        }

        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        $(function () {
            // Employee select2
            $('#employeeSelect').select2({
                placeholder: '-- Select Employee --',
                allowClear: true,
                width: '100%',
            }).on('select2:select', function (e) {
                $('#employeeId').val(e.params.data.id);
            }).on('select2:clear', function () {
                $('#employeeId').val('');
            });

            if ($('#employeeSelect').val()) {
                $('#employeeId').val($('#employeeSelect').val());
            }

            // Load existing rows or start with one blank
            if (existingRows && existingRows.length > 0) {
                existingRows.forEach(r => addRow(r));
            } else {
                addRow({});
            }

            // Add row button
            $('#btnAddRow').on('click', function () {
                addRow({});
            });

            // Recalc on change
            $(document).on('input', '.expense-amt, .approved-amt', recalc);

            // Expense head change — auto-fill amount + dup check
            $(document).on('change', '.expense-head-sel', function () {
                const amount = $(this).find(':selected').data('amount');
                if (amount !== '' && amount != null) {
                    $(this).closest('tr').find('.expense-amt').val(parseFloat(amount));
                    recalc();
                }
                if (highlightDuplicates()) {
                    Swal.fire({
                        icon: 'warning', title: 'Duplicate expense head.',
                        text: 'Each head can appear only once.', timer: 2000, showConfirmButton: false,
                    });
                }
            });

            // Submit via AJAX (same pattern as transport)
            $('#expenseForm').on('submit', function (e) {
                e.preventDefault();

                if (highlightDuplicates()) {
                    Swal.fire({ icon: 'error', title: 'Duplicate Expense Heads', text: 'Remove duplicates before saving.' });
                    return;
                }

                const btnLabel = $('#btnSave').text().trim();
                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                    url:    $(this).attr('action'),
                    method: 'POST',
                    data:   $(this).serializeArray(),
                })
                .done(function (r) {
                    Swal.fire({ icon: 'success', title: r.message || 'Saved.', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location = r.redirect; });
                })
                .fail(function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        const first  = Object.values(errors)[0];
                        Swal.fire({ icon: 'error', title: first ? first[0] : 'Validation error.' });
                    } else {
                        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                    $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> ' + btnLabel);
                });
            });
        });
    </script>
@endpush

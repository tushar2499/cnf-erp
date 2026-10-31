@extends('nas-freights.layouts.app')

@section('title', 'Transport — ' . $exportBooking->export_booking_no)

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
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h4><i class="fa fa-truck me-2 text-warning"></i> Transport —
            <span class="text-muted fw-normal">{{ $exportBooking->export_booking_no }}</span>
        </h4>
        <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Booking Summary (read-only) ── --}}
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
                @if($exportBooking->salesperson)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Salesperson</div>
                        <div class="info-value">{{ $exportBooking->salesperson->name }}</div>
                    </div>
                @endif
                @if($exportBooking->export_bl_no)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Export B/L No</div>
                        <div class="info-value">{{ $exportBooking->export_bl_no }}</div>
                    </div>
                @endif
                @if($exportBooking->vessel_name)
                    <div class="col-6 col-md-2">
                        <div class="info-label">Vessel</div>
                        <div class="info-value">{{ $exportBooking->vessel_name }}
                            @if($exportBooking->voyage_no)
                                / {{ $exportBooking->voyage_no }}
                            @endif
                        </div>
                    </div>
                @endif
                @if($exportBooking->etd)
                    <div class="col-6 col-md-2">
                        <div class="info-label">ETD</div>
                        <div class="info-value">{{ $exportBooking->etd->format('d M Y') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <form id="transportForm" method="POST"
        action="{{ route('nas-freights.freight-export-bookings.transport.update', $exportBooking->id) }}">
        @csrf
        @method('PUT')

        {{-- ── Vehicle Search / Add Row ── --}}
        <div class="booking-card">
            <div class="section-bar d-flex justify-content-between align-items-center">
                <span><i class="fa fa-truck me-2"></i>Cover Van Details</span>
                <button type="button" class="btn btn-sm btn-light py-0" id="btnAddManual">
                    <i class="fa fa-plus me-1"></i> Add Row Manually
                </button>
            </div>
            <div class="card-body pb-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label" style="color:#1a6b60; font-weight:700;">
                            <i class="fa fa-search me-1"></i> Search Vehicle to Add Row
                        </label>
                        <select id="fldVehicleSearch" style="width:100%">
                            <option value="">Enter vehicle no / name (min 3 chars)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="overflow-x:auto">
                <table id="rowsTable">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th style="width:30px">SL</th>
                            <th style="min-width:160px">Cover Van No</th>
                            <th style="min-width:140px">Challan No</th>
                            <th style="min-width:90px">Capacity</th>
                            <th style="min-width:180px">Supplier</th>
                            <th style="min-width:80px">Qty</th>
                            <th style="min-width:110px">Supplier Rate</th>
                            <th style="min-width:110px">Customer Rate</th>
                            <th style="min-width:95px">Demrr. Days</th>
                            <th style="min-width:130px">Cus. Demurrage</th>
                            <th style="min-width:130px">Sup. Demurrage</th>
                            <th style="min-width:100px">Amount</th>
                            <th style="min-width:130px">Location From</th>
                            <th style="min-width:130px">Location To</th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody"></tbody>
                </table>
                <div class="total-bar">Total Items: <span id="totalItems">0</span></div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4 justify-content-end">
            <button type="submit" class="btn btn-success px-5" id="btnSave">
                <i class="fa fa-save me-1"></i>
                {{ $existingItems->count() > 0 ? 'Update' : 'Save' }}
            </button>
            <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var suppliers = @json($suppliers);
        var rowCount = 0;
        var existingItems = @json($existingItems);

        function addRow(vanNo, capacity, prefill) {
            rowCount++;
            const sl = $('#rowsBody tr').length + 1;
            const p = prefill || {};
            const supplierId = p.supplier_id || '';
            const supplierOpts = '<option value="">--Select Supplier--</option>' +
                suppliers.map(s =>
                    `<option value="${s.id}" data-name="${s.company_name}" ${parseInt(s.id) === parseInt(supplierId) ? 'selected' : ''}>${s.code} — ${s.company_name}</option>`
                ).join('');

            const row = `
<tr data-row="${rowCount}">
    <td><button type="button" class="row-del-btn" onclick="delRow(this)" title="Remove"><i class="fa fa-times"></i></button></td>
    <td class="sl-no text-center fw-bold">${sl}</td>
    <td><input type="text" name="items[${rowCount}][cover_van_no]" class="form-control form-control-sm" value="${escHtml(vanNo || p.cover_van_no || '')}" placeholder="Van No"></td>
    <td><input type="text" name="items[${rowCount}][challan_no]" class="form-control form-control-sm" value="${escHtml(p.challan_no || '')}" placeholder="Challan No"></td>
    <td><input type="text" name="items[${rowCount}][capacity]" class="form-control form-control-sm" value="${escHtml(capacity || p.capacity || '')}" placeholder="Capacity"></td>
    <td>
        <select name="items[${rowCount}][supplier_id]" class="form-select form-select-sm row-supplier" data-row="${rowCount}">${supplierOpts}</select>
        <input type="hidden" name="items[${rowCount}][supplier_name]" class="row-supplier-name" value="${escHtml(p.supplier_name || '')}">
    </td>
    <td><input type="number" name="items[${rowCount}][qty]" class="form-control form-control-sm row-qty" value="${p.qty || 1}" min="0" step="0.01"></td>
    <td><input type="number" name="items[${rowCount}][supplier_rate]" class="form-control form-control-sm row-sup-rate" value="${p.supplier_rate || 0}" min="0" step="0.01"></td>
    <td><input type="number" name="items[${rowCount}][customer_rate]" class="form-control form-control-sm row-cus-rate" value="${p.customer_rate || 0}" min="0" step="0.01"></td>
    <td><input type="number" name="items[${rowCount}][demurrage_days]" class="form-control form-control-sm" value="${p.demurrage_days || 0}" min="0"></td>
    <td><input type="number" name="items[${rowCount}][cus_demurrage_charge]" class="form-control form-control-sm" value="${p.cus_demurrage_charge || 0}" min="0" step="0.01"></td>
    <td><input type="number" name="items[${rowCount}][sup_demurrage_charge]" class="form-control form-control-sm" value="${p.sup_demurrage_charge || 0}" min="0" step="0.01"></td>
    <td><input type="number" name="items[${rowCount}][amount]" class="form-control form-control-sm row-amount bg-light" readonly value="${p.amount || 0}"></td>
    <td><input type="text" name="items[${rowCount}][location_from]" class="form-control form-control-sm" value="${escHtml(p.location_from || '')}" placeholder="From"></td>
    <td><input type="text" name="items[${rowCount}][location_to]" class="form-control form-control-sm" value="${escHtml(p.location_to || '')}" placeholder="To"></td>
</tr>`;
            $('#rowsBody').append(row);

            $(`#rowsBody tr[data-row="${rowCount}"] .row-supplier`).select2({
                placeholder: '--Select Supplier--',
                allowClear: true,
                width: '100%',
                dropdownParent: $('body')
            });

            reindexSL();
            recalc();
        }

        window.delRow = function(btn) {
            $(btn).closest('tr').remove();
            reindexSL();
            recalc();
        };

        function reindexSL() {
            $('#rowsBody tr').each(function(i) {
                $(this).find('.sl-no').text(i + 1);
            });
            $('#totalItems').text($('#rowsBody tr').length);
        }

        function recalc() {
            $('#rowsBody tr').each(function() {
                const qty = parseFloat($(this).find('.row-qty').val()) || 0;
                const rate = parseFloat($(this).find('.row-cus-rate').val()) || 0;
                $(this).find('.row-amount').val((qty * rate).toFixed(2));
            });
        }

        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        $(function() {

            // Load existing transport items
            existingItems.forEach(function(item) {
                addRow('', '', item);
            });

            // Vehicle search select2
            $('#fldVehicleSearch').select2({
                placeholder: 'Enter vehicle no / name (min 3 chars)',
                minimumInputLength: 3,
                allowClear: true,
                ajax: {
                    url: '{{ route('nas-freights.freight-export-bookings.transport.search-vehicles') }}',
                    dataType: 'json',
                    delay: 300,
                    data: p => ({ q: p.term }),
                    processResults: d => ({ results: d })
                },
            }).on('select2:select', function(e) {
                const d = e.params.data;
                addRow(d.id, d.vehicle_type || '', {});
                setTimeout(() => { $(this).val(null).trigger('change'); }, 50);
            });

            // Manual add row
            $('#btnAddManual').on('click', function() {
                addRow('', '', {});
            });

            // Recalc on qty / rate change
            $(document).on('input change', '.row-qty, .row-cus-rate', recalc);

            // Sync supplier name hidden field
            $(document).on('change', '.row-supplier', function() {
                const name = $(this).find('option:selected').data('name') || '';
                $(this).closest('tr').find('.row-supplier-name').val(name);
            });

            // Form submit
            $('#transportForm').on('submit', function(e) {
                e.preventDefault();

                var btnLabel = $('#btnSave').text().trim();
                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serializeArray(),
                    })
                    .done(function(r) {
                        if (r && r.redirect) {
                            Swal.fire({ icon: 'success', title: r.message || 'Saved.', timer: 1200, showConfirmButton: false })
                                .then(() => { window.location = r.redirect; });
                        } else {
                            window.location.reload();
                        }
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            const first = Object.values(errors)[0];
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

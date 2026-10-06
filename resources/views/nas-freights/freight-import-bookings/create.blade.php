@extends('nas-freights.layouts.app')

@section('title', $freightBooking ? 'Edit Freight Import Booking/Job' : 'New Freight Import Booking/Job')

@push('styles')
    <style>
        .form-header {
            background: linear-gradient(135deg, #0a4f3c, #14b8a6);
            color: #fff;
            padding: .5rem 1rem;
            border-radius: .35rem .35rem 0 0;
            font-weight: 600;
            font-size: .78rem;
        }

        .section-card {
            border: 1px solid #dee2e6;
            border-radius: .35rem;
            margin-bottom: .75rem;
        }

        .section-card .section-body {
            padding: .6rem .75rem;
        }

        .fb-label {
            display: block;
            font-size: .7rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: .1rem;
        }

        .fb-input {
            font-size: .75rem;
            height: 28px;
            padding: .18rem .4rem;
        }

        /* .fb-input padding would collapse the select's right padding and run the text under the chevron. */
        .form-select.fb-input {
            padding-right: 1.6rem;
            background-position: right .45rem center;
            background-size: 12px 9px;
        }

        /*
         * Booking/Job sub-sections. Every group shares one 4-track grid (col-lg-3, wide fields col-lg-6)
         * so field edges line up vertically from group to group.
         */
        .fb-group {
            min-width: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .fb-group+.fb-group {
            margin-top: .75rem;
        }

        /* Select2 defaults to 38px / 1rem; size it like the 28px .fb-input fields beside it. */
        .booking-info .select2-container--bootstrap-5 .select2-selection--single {
            min-height: 28px;
            height: 28px;
            padding: .18rem 1.6rem .18rem .4rem;
            font-size: .75rem;
            background-position: right .45rem center;
            background-size: 12px 9px;
        }

        .booking-info .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-right: 1rem;
        }

        .booking-info .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            right: 1.4rem;
        }

        .hs-code-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        @media (max-width: 575.98px) {
            .hs-code-list {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .hs-code-item .hs-code-action {
            font-size: .75rem;
        }

        #itemsTable th {
            background: #f1f3f5;
            font-size: .68rem;
            font-weight: 700;
            padding: .25rem .4rem;
            white-space: nowrap;
        }

        #itemsTable td {
            padding: .2rem .3rem;
            vertical-align: middle;
        }

        #itemsTable .form-control,
        #itemsTable .form-select,
        #itemsTable .sl-no {
            font-size: .72rem;
        }

        /* Slimmer select padding so short values (Container, No DG) are not clipped in narrow columns. */
        #itemsTable .form-select {
            padding-left: .4rem;
            padding-right: 1.5rem;
            background-position: right .4rem center;
            background-size: 12px 9px;
        }

        /*
         * Cargo table responsiveness: the wrapper is a size container, so the layout follows the
         * space the table actually gets (sidebar included), not the viewport.
         * Wide: classic one-line-per-item table. Narrower: each item becomes a labelled card grid,
         * so there is never a horizontal scrollbar.
         */
        .cargo-scroll {
            container: cargo / inline-size;
            overflow-x: auto;
        }

        #itemsTable {
            min-width: 1480px;
        }

        @container cargo (max-width: 1479.98px) {
            #itemsTable {
                min-width: 0;
                width: 100%;
            }

            #itemsTable thead {
                display: none;
            }

            #itemsTable,
            #itemsTable tbody {
                display: block;
            }

            #itemsTable tbody tr {
                position: relative;
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(min(100%, 130px), 1fr));
                gap: .55rem .75rem;
                padding: .6rem .75rem .75rem;
                border: 0;
                border-bottom: 1px solid #dee2e6;
            }

            #itemsTable tbody tr:nth-child(even) {
                background: #f8f9fa;
            }

            #itemsTable tbody td {
                display: block;
                min-width: 0;
                padding: 0;
                border: 0;
                background: transparent;
                box-shadow: none;
            }

            #itemsTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: .1rem;
                font-size: .7rem;
                font-weight: 600;
                color: #495057;
            }

            #itemsTable tbody td.sl-no {
                grid-column: 1 / -1;
                padding-right: 2.75rem;
                text-align: left !important;
                font-size: .78rem;
                color: #0a4f3c;
            }

            #itemsTable tbody td.sl-no::before {
                content: 'Cargo Item #';
            }

            #itemsTable tbody td.remove-cell {
                position: absolute;
                top: .4rem;
                right: .6rem;
            }

            #itemsTable tbody td.remove-cell .remove-item-row {
                min-width: 32px;
                min-height: 32px;
            }

            #itemsTable tbody td.special-cell {
                grid-column: 1 / -1;
            }

            /* Package rows have no container fields; drop them from the card instead of showing disabled inputs. */
            #itemsTable tbody td.container-only.is-na {
                display: none;
            }
        }

        @container cargo (min-width: 576px) and (max-width: 1479.98px) {
            #itemsTable tbody td.special-cell {
                grid-column: span 2;
            }
        }

        /* Touch devices: 44px targets; phones: 16px text so iOS does not zoom on focus. */
        @media (pointer: coarse) {

            .booking-info .fb-input,
            .booking-info .select2-container--bootstrap-5 .select2-selection--single {
                height: auto;
                min-height: 44px;
            }

            #itemsTable .form-control,
            #itemsTable .form-select {
                min-height: 44px;
            }

            #itemsTable .remove-item-row {
                min-width: 44px;
                min-height: 44px;
            }

            #addItemRow {
                min-height: 44px;
                padding-inline: 1rem;
            }
        }

        @media (max-width: 767.98px) {

            .booking-info .fb-input,
            .booking-info .select2-container--bootstrap-5 .select2-selection--single {
                height: auto;
                font-size: 16px;
            }

            #itemsTable .form-control,
            #itemsTable .form-select {
                font-size: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .cargo-scroll {
                scroll-behavior: auto;
            }
        }

        /* ── Financial Summary (compact right-aligned card) ── */
        .fin-summary-card {
            width: 100%;
            max-width: 430px;
            border: 1px solid #dee2e6;
            border-radius: .35rem;
        }

        .fin-summary-body {
            padding: .5rem .65rem .55rem;
        }

        .fin-rate-row {
            display: flex;
            gap: .5rem;
            align-items: flex-end;
            margin-bottom: .45rem;
        }

        .fin-rate-field { flex: 0 0 auto; }
        .fin-rate-field--grow { flex: 1 1 auto; }

        .fin-cur-input { width: 64px; text-align: center !important; font-weight: 700; }

        .fin-table { border-collapse: collapse; }

        .fin-table thead th {
            font-size: .65rem;
            font-weight: 700;
            color: #6c757d;
            padding: 0 .3rem .2rem;
            border-bottom: 1px solid #dee2e6;
            white-space: nowrap;
        }

        .fin-table tbody td { padding: .2rem .3rem; vertical-align: middle; }

        .fin-table tbody tr:not(:last-child) td { border-bottom: 1px solid #f0f0f0; }

        .fin-cur-badge {
            font-size: .6rem;
            font-weight: 700;
            color: #0a4f3c;
            background: #e8f5f1;
            border-radius: .2rem;
            padding: .05rem .25rem;
            margin-left: .2rem;
        }

        .fin-amount-input,
        .fin-bdt-display {
            font-size: .72rem;
            height: 26px;
            padding: .15rem .35rem;
        }

        .fin-bdt-display { font-weight: 600; color: #0a4f3c; }
    </style>
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div></div>
        <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
            Freight Import Booking/Job Entry
            @if ($freightBooking)
                <span class="ms-2 badge bg-light text-dark border">{{ $freightBooking->freight_booking_no }}</span>
            @endif
        </div>
        <div>
            <a href="{{ route('nas-freights.freight-import-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back To List
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST"
        action="{{ $freightBooking ? route('nas-freights.freight-import-bookings.update', $freightBooking->id) : route('nas-freights.freight-import-bookings.store') }}">
        @csrf
        @if ($freightBooking)
            @method('PUT')
        @endif

        {{-- ═══ BOOKING/JOB INFORMATION ═══ --}}
        <div class="section-card booking-info">
            <div class="form-header"><i class="fa fa-ship me-1"></i> Booking/Job Information</div>
            <div class="section-body">

                {{-- Job & Parties --}}
                <fieldset class="fb-group" aria-label="Job &amp; Parties">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbBookingNo">Booking/Job No</label>
                            <input type="text" id="fbBookingNo" class="form-control fb-input bg-light"
                                value="{{ $freightBooking?->freight_booking_no ?? 'Auto Generated' }}" readonly>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbBookingDate">Booking/Job Date <span class="text-danger">*</span></label>
                            <input type="date" id="fbBookingDate" name="booking_date" class="form-control fb-input"
                                value="{{ old('booking_date', $freightBooking?->booking_date?->format('Y-m-d') ?? $today) }}"
                                required>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbServiceType">Shipment Mode/Service Type <span class="text-danger">*</span></label>
                            <select name="service_type" id="fbServiceType" class="form-select fb-input" required>
                                @foreach ($serviceTypes as $st)
                                    <option value="{{ $st }}"
                                        {{ old('service_type', $freightBooking?->service_type ?? 'FCL') === $st ? 'selected' : '' }}>
                                        {{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbStatus">Status</label>
                            <select name="status" id="fbStatus" class="form-select fb-input">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}"
                                        {{ old('status', $freightBooking?->status ?? 'Draft') === $st ? 'selected' : '' }}>
                                        {{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="customerSelect">Customer (Importer) <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select fb-input" style="width:100%" required>
                                @if ($freightBooking?->customer_id)
                                    <option value="{{ $freightBooking->customer_id }}" selected>
                                        {{ $freightBooking->customer?->customer_id }} — {{ $freightBooking->customer?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="salespersonSelect">Salesperson</label>
                            <select name="salesperson_id" id="salespersonSelect" class="form-select fb-input"
                                style="width:100%">
                                @if ($freightBooking?->salesperson_id)
                                    <option value="{{ $freightBooking->salesperson_id }}" selected>
                                        {{ $freightBooking->salesperson?->name }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="overseasAgentSelect">Overseas Agent</label>
                            <select name="overseas_agent_id" id="overseasAgentSelect" class="form-select fb-input"
                                style="width:100%">
                                @if ($freightBooking?->overseas_agent_id)
                                    <option value="{{ $freightBooking->overseas_agent_id }}" selected>
                                        {{ $freightBooking->overseasAgent?->agent_code }} —
                                        {{ $freightBooking->overseasAgent?->name }}
                                        ({{ $freightBooking->overseasAgent?->country }})
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="shippingCarrierSelect">Shipping Carrier/Airline</label>
                            <select name="shipping_carrier_id" id="shippingCarrierSelect" class="form-select fb-input"
                                style="width:100%">
                                @if ($freightBooking?->shipping_carrier_id)
                                    <option value="{{ $freightBooking->shipping_carrier_id }}" selected>
                                        {{ $freightBooking->shippingCarrier?->carrier_code }} —
                                        {{ $freightBooking->shippingCarrier?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- Routing & Schedule --}}
                <fieldset class="fb-group" aria-label="Routing &amp; Schedule">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbPol">Port of Loading (POL)</label>
                            <input type="text" id="fbPol" name="pol" class="form-control fb-input"
                                value="{{ old('pol', $freightBooking?->pol) }}" placeholder="e.g. Singapore">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbPod">Port of Discharge (POD)</label>
                            <input type="text" id="fbPod" name="pod" class="form-control fb-input"
                                value="{{ old('pod', $freightBooking?->pod) }}" placeholder="e.g. Chittagong">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbEtd">ETD</label>
                            <input type="date" id="fbEtd" name="etd" class="form-control fb-input"
                                value="{{ old('etd', $freightBooking?->etd?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbEta">ETA</label>
                            <input type="date" id="fbEta" name="eta" class="form-control fb-input"
                                value="{{ old('eta', $freightBooking?->eta?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbVesselName">Vessel Name</label>
                            <input type="text" id="fbVesselName" name="vessel_name"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('vessel_name', $freightBooking?->vessel_name) }}"
                                placeholder="e.g. MSC ANNA">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbVoyageNo">Voyage No</label>
                            <input type="text" id="fbVoyageNo" name="voyage_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('voyage_no', $freightBooking?->voyage_no) }}"
                                placeholder="e.g. 024W">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbFlightNo">Flight No</label>
                            <input type="text" id="fbFlightNo" name="flight_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('flight_no', $freightBooking?->flight_no) }}"
                                placeholder="e.g. BG-147">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbFlightDate">Flight Date</label>
                            <input type="date" id="fbFlightDate" name="flight_date"
                                class="form-control fb-input"
                                value="{{ old('flight_date', $freightBooking?->flight_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </fieldset>

                {{-- Documents & References --}}
                <fieldset class="fb-group" aria-label="Documents &amp; References">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbMblMawbNo">MB/L / MAWB No</label>
                            <input type="text" id="fbMblMawbNo" name="mbl_mawb_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('mbl_mawb_no', $freightBooking?->mbl_mawb_no) }}"
                                placeholder="e.g. 176-12345675">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbMblMawbDate">MB/L / MAWB Date</label>
                            <input type="date" id="fbMblMawbDate" name="mbl_mawb_date"
                                class="form-control fb-input"
                                value="{{ old('mbl_mawb_date', $freightBooking?->mbl_mawb_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbHblHawbNo">HBL/HAWB No</label>
                            <input type="text" id="fbHblHawbNo" name="hbl_hawb_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('hbl_hawb_no', $freightBooking?->hbl_hawb_no) }}"
                                placeholder="e.g. HBL-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbHblHawbDate">HBL/HAWB Date</label>
                            <input type="date" id="fbHblHawbDate" name="hbl_hawb_date"
                                class="form-control fb-input"
                                value="{{ old('hbl_hawb_date', $freightBooking?->hbl_hawb_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbBlNo">B/L No</label>
                            <input type="text" id="fbBlNo" name="bl_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('bl_no', $freightBooking?->bl_no) }}"
                                placeholder="e.g. MSLU-024W-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbRfqTenderNo">RFQ/Tender No</label>
                            <input type="text" id="fbRfqTenderNo" name="rfq_tender_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('rfq_tender_no', $freightBooking?->rfq_tender_no) }}"
                                placeholder="e.g. TND-2026-0001">
                        </div>
                        @if ($freightBooking?->rfq_no)
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="fb-label" for="fbFromRfq">From RFQ</label>
                                <input type="text" id="fbFromRfq" class="form-control fb-input bg-light"
                                    value="{{ $freightBooking->rfq_no }}" readonly>
                            </div>
                        @endif
                    </div>
                </fieldset>

                {{-- Commercial & Payment --}}
                <fieldset class="fb-group" aria-label="Commercial &amp; Payment">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbCustomerInvoiceNo">Customer Invoice No</label>
                            <input type="text" id="fbCustomerInvoiceNo" name="customer_invoice_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('customer_invoice_no', $freightBooking?->customer_invoice_no) }}"
                                placeholder="e.g. INV-2026-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbCustomerInvoiceDate">Customer Invoice Date</label>
                            <input type="date" id="fbCustomerInvoiceDate" name="customer_invoice_date"
                                class="form-control fb-input"
                                value="{{ old('customer_invoice_date', $freightBooking?->customer_invoice_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbAgentInvoiceNo">Agent Invoice No</label>
                            <input type="text" id="fbAgentInvoiceNo" name="agent_invoice_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('agent_invoice_no', $freightBooking?->agent_invoice_no) }}"
                                placeholder="e.g. AGT-2026-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbCurrency">Currency</label>
                            <select name="currency" id="fbCurrency" class="form-select fb-input">
                                @foreach ($currencies as $cur)
                                    <option value="{{ $cur }}"
                                        {{ old('currency', $freightBooking?->currency ?? 'BDT') === $cur ? 'selected' : '' }}>
                                        {{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbIncoterms">Incoterms</label>
                            <select name="incoterms" id="fbIncoterms" class="form-select fb-input">
                                <option value="">-- Select --</option>
                                @foreach ($incoterms as $inc)
                                    <option value="{{ $inc }}"
                                        {{ old('incoterms', $freightBooking?->incoterms) === $inc ? 'selected' : '' }}>
                                        {{ $inc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbLcNo">LC No</label>
                            <input type="text" id="fbLcNo" name="lc_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('lc_no', $freightBooking?->lc_no) }}"
                                placeholder="e.g. LC-2026-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbCadNo">CAD No</label>
                            <input type="text" id="fbCadNo" name="cad_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('cad_no', $freightBooking?->cad_no) }}"
                                placeholder="e.g. CAD-2026-0001">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="fb-label" for="fbTtNo">TT No</label>
                            <input type="text" id="fbTtNo" name="tt_no"
                                class="form-control fb-input" maxlength="255"
                                value="{{ old('tt_no', $freightBooking?->tt_no) }}"
                                placeholder="e.g. TT-2026-0001">
                        </div>
                    </div>
                </fieldset>

                {{-- Cargo Description: top-aligned because the HS code list grows downward. --}}
                <fieldset class="fb-group" aria-label="Cargo Description">
                    <div class="row g-2">
                        <div class="col-12 col-lg-6">
                            <label class="fb-label" for="fbCommodityDescription">Commodity Description</label>
                            <input type="text" id="fbCommodityDescription" name="commodity_description"
                                class="form-control fb-input"
                                value="{{ old('commodity_description', $freightBooking?->commodity_description) }}"
                                placeholder="e.g. Raw materials, machinery">
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="fb-label" id="hsCodeLabel">HS Code</div>
                            <div id="hsCodeList" class="hs-code-list" role="group" aria-labelledby="hsCodeLabel"></div>
                        </div>
                        <div class="col-12">
                            <label class="fb-label" for="fbRemarks">Remarks</label>
                            <input type="text" id="fbRemarks" name="remarks" class="form-control fb-input"
                                value="{{ old('remarks', $freightBooking?->remarks) }}"
                                placeholder="e.g. Bonded warehouse">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        {{-- ═══ CARGO ITEMS ═══ --}}
        <div class="section-card">
            <div class="form-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-boxes me-1"></i> Cargo / Shipment Details</span>
                <button type="button" class="btn btn-sm btn-light py-0 px-2" id="addItemRow">
                    <i class="fa fa-plus me-1"></i> Add Row
                </button>
            </div>
            <div class="section-body p-0">
                <div class="cargo-scroll">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:35px" class="text-center">#</th>
                                <th style="width:35px" class="text-center"><span class="visually-hidden">Remove</span></th>
                                <th style="width:110px">Item Type</th>
                                <th style="width:60px">Qty</th>
                                <th style="width:130px">Container Size / Pkg</th>
                                <th style="width:90px">Pkg Qty</th>
                                <th style="width:105px">Pkg Unit</th>
                                <th style="width:125px">Container No</th>
                                <th style="width:105px">Seal No</th>
                                <th style="width:95px" data-weight-col="net">Net Weight</th>
                                <th style="width:95px">Gross Weight</th>
                                <th style="width:110px" data-weight-col="chargeable">Chargeable Weight</th>
                                <th style="width:72px">Wt Unit</th>
                                <th style="width:80px">CBM</th>
                                <th style="width:110px">Country of Origin</th>
                                <th style="width:85px">DG</th>
                                <th style="min-width:120px">Special Handling</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══ CURRENCY & FINANCIAL SUMMARY ═══ --}}
        <div class="d-flex justify-content-end mb-2">
            <div class="fin-summary-card">
                <div class="form-header py-1"><i class="fa fa-exchange-alt me-1"></i> Currency &amp; Financial Summary</div>
                <div class="fin-summary-body">

                    {{-- Currency + Rate row --}}
                    <div class="fin-rate-row">
                        <div class="fin-rate-field">
                            <label class="fb-label" for="fbCurrencyDisplay">Currency</label>
                            <input type="text" id="fbCurrencyDisplay" class="form-control fb-input bg-light text-center fw-bold fin-cur-input" readonly>
                        </div>
                        <div class="fin-rate-field fin-rate-field--grow">
                            <label class="fb-label" for="fbExchangeRate">Rate <small class="text-muted fw-normal">(1 unit = ? BDT)</small></label>
                            <input type="number" id="fbExchangeRate" name="exchange_rate"
                                class="form-control fb-input text-end"
                                step="0.000001" min="0" placeholder="e.g. 110.50"
                                value="{{ old('exchange_rate', $freightBooking?->exchange_rate ? (float) $freightBooking->exchange_rate : '') }}">
                        </div>
                    </div>

                    {{-- Amount rows --}}
                    <table class="fin-table w-100">
                        <colgroup>
                            <col style="width:130px">
                            <col style="width:110px">
                            <col style="width:36px">
                            <col style="width:120px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount <span id="fbFinCurHead" class="fin-cur-badge"></span></th>
                                <th></th>
                                <th class="text-end">BDT Equivalent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fb-label align-middle">Buy Amount</td>
                                <td>
                                    <input type="number" id="fbBuyAmount" name="buy_amount"
                                        class="form-control form-control-sm text-end fin-amount-input"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('buy_amount', $freightBooking?->buy_amount ? (float) $freightBooking->buy_amount : '') }}">
                                </td>
                                <td class="text-center" style="font-size:.65rem;color:#6c757d;">→</td>
                                <td>
                                    <input type="text" id="fbBuyBdtAmount" name="buy_bdt_amount"
                                        class="form-control form-control-sm text-end bg-light fin-bdt-display"
                                        readonly placeholder="—"
                                        value="{{ old('buy_bdt_amount', $freightBooking?->buy_bdt_amount ? number_format($freightBooking->buy_bdt_amount, 2) : '') }}">
                                </td>
                            </tr>
                            <tr>
                                <td class="fb-label align-middle">Sell Amount</td>
                                <td>
                                    <input type="number" id="fbSellAmount" name="sell_amount"
                                        class="form-control form-control-sm text-end fin-amount-input"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('sell_amount', $freightBooking?->sell_amount ? (float) $freightBooking->sell_amount : '') }}">
                                </td>
                                <td class="text-center" style="font-size:.65rem;color:#6c757d;">→</td>
                                <td>
                                    <input type="text" id="fbSellBdtAmount" name="sell_bdt_amount"
                                        class="form-control form-control-sm text-end bg-light fin-bdt-display"
                                        readonly placeholder="—"
                                        value="{{ old('sell_bdt_amount', $freightBooking?->sell_bdt_amount ? number_format($freightBooking->sell_bdt_amount, 2) : '') }}">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
            <a href="{{ route('nas-freights.freight-import-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-sm btn-success px-4">
                <i class="fa fa-save me-1"></i> {{ $freightBooking ? 'Update' : 'Save' }}
            </button>
        </div>

    </form>

    <template id="hsCodeTemplate">
        <div class="input-group hs-code-item">
            <input type="text" name="hs_codes[]" class="form-control fb-input hs-code-input" maxlength="50"
                placeholder="e.g. 6109.10">
            <button type="button" class="btn py-0 px-2 hs-code-action"></button>
        </div>
    </template>

    <template id="itemTemplate">
        <tr>
            <td class="sl-no text-center fw-bold"></td>
            <td class="remove-cell text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-item-row"
                    title="Remove row" aria-label="Remove cargo row"><i class="fa fa-times"></i></button>
            </td>
            <td data-label="Item Type">
                <select name="items[0][item_type]" class="form-select form-select-sm item-type-sel"
                    aria-label="Item type">
                    <option value="container">Container</option>
                    <option value="package">Package</option>
                </select>
            </td>
            <td data-label="Qty">
                <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-center"
                    value="1" min="1" placeholder="1" aria-label="Quantity">
            </td>
            <td class="size-pkg-cell" data-label="Container Size">
                <select name="items[0][container_size]" class="form-select form-select-sm container-size-sel"
                    aria-label="Container size">
                    <option value="">-- Size --</option>
                    @foreach ($containerSizes as $cs)
                        <option value="{{ $cs }}">{{ $cs }}</option>
                    @endforeach
                </select>
                <select name="items[0][package_type]" class="form-select form-select-sm package-type-sel d-none"
                    aria-label="Package type">
                    <option value="">-- Type --</option>
                    @foreach ($packageTypes as $pt)
                        <option value="{{ $pt }}">{{ $pt }}</option>
                    @endforeach
                </select>
            </td>
            <td class="container-only" data-label="Pkg Qty">
                <input type="number" name="items[0][package_qty]"
                    class="form-control form-control-sm text-center pkg-qty-input"
                    min="1" step="1" placeholder="e.g. 500" aria-label="Package quantity inside container">
            </td>
            <td class="container-only" data-label="Pkg Unit">
                <select name="items[0][package_unit]" class="form-select form-select-sm pkg-unit-sel"
                    aria-label="Package unit inside container">
                    <option value="">-- Unit --</option>
                    @foreach ($packageTypes as $pt)
                        <option value="{{ $pt }}">{{ $pt }}</option>
                    @endforeach
                </select>
            </td>
            <td class="container-only" data-label="Container No">
                <input type="text" name="items[0][container_no]"
                    class="form-control form-control-sm container-no-input"
                    placeholder="e.g. MSCU1234567" aria-label="Container number">
            </td>
            <td class="container-only" data-label="Seal No">
                <input type="text" name="items[0][seal_no]" class="form-control form-control-sm seal-no-input"
                    placeholder="e.g. SL1234567" aria-label="Seal number">
            </td>
            <td data-label="Net Weight" data-weight-col="net"><input type="number" name="items[0][net_weight]"
                    class="form-control form-control-sm text-end net-weight-input"
                    step="0.01" min="0" placeholder="0.00" aria-label="Net weight"></td>
            <td data-label="Gross Weight"><input type="number" name="items[0][gross_weight]"
                    class="form-control form-control-sm text-end gross-weight-input"
                    step="0.01" min="0" placeholder="0.00" aria-label="Gross weight"></td>
            <td data-label="Chargeable Weight" data-weight-col="chargeable"><input type="number"
                    name="items[0][chargeable_weight]"
                    class="form-control form-control-sm text-end chargeable-weight-input"
                    step="0.01" min="0" placeholder="0.00" aria-label="Chargeable weight"></td>
            <td data-label="Wt Unit">
                <select name="items[0][weight_unit]" class="form-select form-select-sm" aria-label="Weight unit">
                    @foreach ($weightUnits as $wu)
                        <option value="{{ $wu }}">{{ $wu }}</option>
                    @endforeach
                </select>
            </td>
            <td data-label="CBM"><input type="number" name="items[0][volume_cbm]"
                    class="form-control form-control-sm text-end" step="0.001" min="0" placeholder="0.000"
                    aria-label="Volume in CBM"></td>
            <td data-label="Country of Origin"><input type="text" name="items[0][country_of_origin]"
                    class="form-control form-control-sm" placeholder="e.g. China" aria-label="Country of origin"></td>
            <td data-label="DG">
                <select name="items[0][is_dangerous_goods]" class="form-select form-select-sm"
                    aria-label="Dangerous goods">
                    <option value="0">No DG</option>
                    <option value="1">DG</option>
                </select>
            </td>
            <td class="special-cell" data-label="Special Handling"><input type="text"
                    name="items[0][special_handling]" class="form-control form-control-sm"
                    placeholder="e.g. Fragile" aria-label="Special handling"></td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        var existingItems = @json($existingItems);
        var existingHsCodes = @json(old('hs_codes', $freightBooking?->hs_codes ?? []));

        $(function() {
            $('#customerSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search customer...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-import-bookings.search-customers') }}',
                    dataType: 'json',
                    delay: 300,
                    data: d => ({
                        q: d.term
                    }),
                    processResults: d => ({
                        results: d
                    })
                },
            });

            $('#salespersonSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search salesperson...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-import-bookings.search-employees') }}',
                    dataType: 'json',
                    delay: 300,
                    data: d => ({
                        q: d.term
                    }),
                    processResults: d => ({
                        results: d
                    })
                },
            });

            $('#overseasAgentSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search overseas agent...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-import-bookings.search-overseas-agents') }}',
                    dataType: 'json',
                    delay: 300,
                    data: d => ({
                        q: d.term
                    }),
                    processResults: d => ({
                        results: d
                    })
                },
            });

            $('#shippingCarrierSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search shipping carrier/airline...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-import-bookings.search-shipping-carriers') }}',
                    dataType: 'json',
                    delay: 300,
                    data: d => ({
                        q: d.term
                    }),
                    processResults: d => ({
                        results: d
                    })
                },
            });

            if (existingHsCodes.length > 0) {
                existingHsCodes.forEach(function(code) {
                    addHsCodeRow(code);
                });
            } else {
                addHsCodeRow();
            }

            $(document).on('click', '.add-hs-code', function() {
                addHsCodeRow().find('.hs-code-input').trigger('focus');
            });

            $(document).on('click', '.remove-hs-code', function() {
                $(this).closest('.hs-code-item').remove();
                reindexHsCodes();
            });

            if (existingItems.length > 0) {
                existingItems.forEach(function(item) {
                    addItemRow(item);
                });
            } else {
                addItemRow();
            }

            $('#addItemRow').on('click', function() {
                var $row = addItemRow();
                var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                $row[0].scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
                $row.find('.item-type-sel').trigger('focus');
            });

            $(document).on('click', '.remove-item-row', function() {
                if ($('#itemsBody tr').length <= 1) return;
                $(this).closest('tr').remove();
                reindexItems();
            });

            $(document).on('change', '.item-type-sel', function() {
                toggleItemTypeFields($(this).closest('tr'));
            });

            $('#fbServiceType').on('change', applyWeightMode);
            applyWeightMode();

            // Currency display + exchange rate auto-calc
            function syncCurrencyDisplay() {
                var cur = $('#fbCurrency').val() || 'BDT';
                $('#fbCurrencyDisplay').val(cur);
                $('#fbFinCurHead').text(cur);
            }

            function recalcBdt() {
                var rate = parseFloat($('#fbExchangeRate').val()) || 0;

                var buy = parseFloat($('#fbBuyAmount').val()) || 0;
                $('#fbBuyBdtAmount').val(buy && rate ? numberFormat(buy * rate) : '');

                var sell = parseFloat($('#fbSellAmount').val()) || 0;
                $('#fbSellBdtAmount').val(sell && rate ? numberFormat(sell * rate) : '');
            }

            function numberFormat(n) {
                return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            $('#fbCurrency').on('change', function () {
                syncCurrencyDisplay();
                recalcBdt();
            });
            $('#fbExchangeRate, #fbBuyAmount, #fbSellAmount').on('input change', recalcBdt);

            syncCurrencyDisplay();
            recalcBdt();
        });

        function addItemRow(data) {
            var tmpl = document.getElementById('itemTemplate').content.cloneNode(true);
            var $tr = $(tmpl.querySelector('tr'));
            $('#itemsBody').append($tr);
            var $row = $('#itemsBody tr:last');

            if (data) {
                $row.find('.item-type-sel').val(data.item_type || 'container');
                toggleItemTypeFields($row);
                $row.find('.container-size-sel').val(data.container_size || '');
                $row.find('.package-type-sel').val(data.package_type || '');
                $row.find('.pkg-qty-input').val(data.package_qty || '');
                $row.find('.pkg-unit-sel').val(data.package_unit || '');
                $row.find('[name$="[container_no]"]').val(data.container_no || '');
                $row.find('[name$="[seal_no]"]').val(data.seal_no || '');
                $row.find('[name$="[quantity]"]').val(data.quantity || 1);
                $row.find('[name$="[net_weight]"]').val(data.net_weight || '');
                $row.find('[name$="[gross_weight]"]').val(data.gross_weight || '');
                $row.find('[name$="[chargeable_weight]"]').val(data.chargeable_weight || '');
                $row.find('[name$="[weight_unit]"]').val(data.weight_unit || 'KG');
                $row.find('[name$="[volume_cbm]"]').val(data.volume_cbm || '');
                $row.find('[name$="[country_of_origin]"]').val(data.country_of_origin || '');
                $row.find('[name$="[is_dangerous_goods]"]').val(data.is_dangerous_goods == '1' ? '1' : '0');
                $row.find('[name$="[special_handling]"]').val(data.special_handling || '');
            }
            reindexItems();
            applyWeightMode();
            return $row;
        }

        // Air: Gross + Chargeable weight. Every other mode: Net + Gross weight.
        // Hidden columns are disabled so they are never submitted.
        function applyWeightMode() {
            var isAir = $('#fbServiceType').val() === 'Air';

            $('#itemsTable [data-weight-col="net"]').toggleClass('d-none', isAir)
                .find('input').prop('disabled', isAir);
            $('#itemsTable [data-weight-col="chargeable"]').toggleClass('d-none', !isAir)
                .find('input').prop('disabled', !isAir);
        }

        function addHsCodeRow(value) {
            var $item = $(document.getElementById('hsCodeTemplate').content.cloneNode(true).querySelector('.hs-code-item'));
            $item.find('.hs-code-input').val(value || '');
            $('#hsCodeList').append($item);
            reindexHsCodes();
            return $item;
        }

        function reindexHsCodes() {
            $('#hsCodeList .hs-code-item').each(function(i) {
                var $action = $(this).find('.hs-code-action');
                $(this).find('.hs-code-input').attr('aria-label', 'HS Code ' + (i + 1));

                if (i === 0) {
                    $action.removeClass('btn-outline-danger remove-hs-code').addClass('btn-success add-hs-code')
                        .attr({ title: 'Add another HS code', 'aria-label': 'Add another HS code' })
                        .html('<i class="fa fa-plus"></i>');
                } else {
                    $action.removeClass('btn-success add-hs-code').addClass('btn-outline-danger remove-hs-code')
                        .attr({ title: 'Remove HS code', 'aria-label': 'Remove HS Code ' + (i + 1) })
                        .html('<i class="fa fa-times"></i>');
                }
            });
        }

        function reindexItems() {
            $('#itemsBody tr').each(function(i) {
                var $tr = $(this);
                $tr.find('.sl-no').text(i + 1);
                $tr.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/items\[\d+\]/, 'items[' + i + ']'));
                });
            });
        }

        function toggleItemTypeFields($row) {
            var type = $row.find('.item-type-sel').val();
            var isContainer = type === 'container';
            $row.find('.size-pkg-cell').attr('data-label', isContainer ? 'Container Size' : 'Package Type');
            $row.find('.container-size-sel').toggleClass('d-none', !isContainer);
            $row.find('.package-type-sel').toggleClass('d-none', isContainer);
            $row.find('.container-only').toggleClass('is-na', !isContainer);
            $row.find('.pkg-qty-input, .pkg-unit-sel').prop('disabled', !isContainer);
            $row.find('.container-no-input').prop('disabled', !isContainer);
            $row.find('.seal-no-input').prop('disabled', !isContainer);
        }
    </script>
@endpush

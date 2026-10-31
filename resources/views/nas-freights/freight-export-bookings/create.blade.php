@extends('nas-freights.layouts.app')

@section('title', $exportBooking ? 'Edit Freight Export Booking/Job' : 'New Freight Export Booking/Job')

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
            display: flex;
            align-items: center;
            gap: .4rem;
            min-height: 22px;
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

        .fb-mini-btn {
            padding: 0 .5rem;
            font-size: .66rem;
            line-height: 1.6;
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

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-right: 2rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
    </style>
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div></div>
        <div class="fw-bold" style="font-size:.9rem; color:#0a4f3c;">
            Freight Export Booking/Job Entry
            @if ($exportBooking)
                <span class="ms-2 badge bg-light text-dark border">{{ $exportBooking->export_booking_no }}</span>
            @endif
        </div>
        <div>
            <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
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
        action="{{ $exportBooking ? route('nas-freights.freight-export-bookings.update', $exportBooking->id) : route('nas-freights.freight-export-bookings.store') }}">
        @csrf
        @if ($exportBooking)
            @method('PUT')
        @endif

        {{-- ═══ BOOKING/JOB INFORMATION ═══ --}}
        <div class="section-card">
            <div class="form-header"><i class="fa fa-ship me-1"></i> Booking/Job Information</div>
            <div class="section-body">

                {{-- Group 1: Booking/Job details --}}
                <div class="row g-2 mb-2">
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbBookingNo">Booking/Job No</label>
                        <input type="text" id="fbBookingNo" class="form-control fb-input bg-light"
                            value="{{ $exportBooking?->export_booking_no ?? 'Auto Generated' }}" readonly>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbBookingDate">Booking/Job Date <span class="text-danger">*</span></label>
                        <input type="date" id="fbBookingDate" name="booking_date" class="form-control fb-input"
                            value="{{ old('booking_date', $exportBooking?->booking_date?->format('Y-m-d') ?? $today) }}"
                            required>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbServiceType">Service Type/Mode <span class="text-danger">*</span></label>
                        <select id="fbServiceType" name="service_type" class="form-select fb-input" required>
                            @foreach ($serviceTypes as $st)
                                <option value="{{ $st }}"
                                    {{ old('service_type', $exportBooking?->service_type ?? 'FCL') === $st ? 'selected' : '' }}>
                                    {{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbCurrency">Currency</label>
                        <select id="fbCurrency" name="currency" class="form-select fb-input">
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur }}"
                                    {{ old('currency', $exportBooking?->currency ?? 'BDT') === $cur ? 'selected' : '' }}>
                                    {{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbIncoterms">Incoterms</label>
                        <select id="fbIncoterms" name="incoterms" class="form-select fb-input">
                            <option value="">-- Select --</option>
                            @foreach ($incoterms as $inc)
                                <option value="{{ $inc }}"
                                    {{ old('incoterms', $exportBooking?->incoterms) === $inc ? 'selected' : '' }}>
                                    {{ $inc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="fb-label" for="fbStatus">Status</label>
                        <select id="fbStatus" name="status" class="form-select fb-input">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}"
                                    {{ old('status', $exportBooking?->status ?? 'Draft') === $st ? 'selected' : '' }}>
                                    {{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Group 2: Parties --}}
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-4">
                        <label class="fb-label" for="customerSelect">
                            <span>Customer (Exporter) <span class="text-danger">*</span></span>
                            <button type="button" class="btn btn-success fb-mini-btn ms-auto" id="btnQuickCustomer"
                                title="Create new customer" aria-label="Create new customer">
                                <i class="fa fa-plus"></i> New
                            </button>
                        </label>
                        <select name="customer_id" id="customerSelect" class="form-select fb-input" style="width:100%" required>
                            @if ($exportBooking?->customer_id)
                                <option value="{{ $exportBooking->customer_id }}" selected>
                                    {{ $exportBooking->customer?->customer_id }} — {{ $exportBooking->customer?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="fb-label" for="overseasAgentSelect">Overseas Agent / Consignee</label>
                        <select name="overseas_agent_id" id="overseasAgentSelect" class="form-select fb-input"
                            style="width:100%">
                            @if ($exportBooking?->overseas_agent_id)
                                <option value="{{ $exportBooking->overseas_agent_id }}" selected>
                                    {{ $exportBooking->overseasAgent?->agent_code }} —
                                    {{ $exportBooking->overseasAgent?->name }}
                                    ({{ $exportBooking->overseasAgent?->country }})
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="fb-label" for="salespersonSelect">Salesperson</label>
                        <select name="salesperson_id" id="salespersonSelect" class="form-select fb-input"
                            style="width:100%">
                            @if ($exportBooking?->salesperson_id)
                                <option value="{{ $exportBooking->salesperson_id }}" selected>
                                    {{ $exportBooking->salesperson?->name }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Group 3: Party bill & invoice --}}
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPartyBillRefNo">Party Bill Ref No</label>
                        <input type="text" id="fbPartyBillRefNo" name="party_bill_ref_no" class="form-control fb-input"
                            value="{{ old('party_bill_ref_no', $exportBooking?->party_bill_ref_no) }}"
                            placeholder="e.g. PB-2024-0001">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPartyBillRefDate">Party Bill Ref Date</label>
                        <input type="date" id="fbPartyBillRefDate" name="party_bill_date" class="form-control fb-input"
                            value="{{ old('party_bill_date', $exportBooking?->party_bill_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPartyInvoiceNo">Party Invoice No</label>
                        <input type="text" id="fbPartyInvoiceNo" name="party_invoice_no" class="form-control fb-input"
                            value="{{ old('party_invoice_no', $exportBooking?->party_invoice_no) }}"
                            placeholder="e.g. PI-2024-0001">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPartyInvoiceDate">Party Invoice Date</label>
                        <input type="date" id="fbPartyInvoiceDate" name="party_invoice_date" class="form-control fb-input"
                            value="{{ old('party_invoice_date', $exportBooking?->party_invoice_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- Group 4: Shipment & route --}}
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="shippingCarrierSelect">Shipping Carrier</label>
                        <select name="shipping_carrier_id" id="shippingCarrierSelect" class="form-select fb-input"
                            style="width:100%">
                            @if ($exportBooking?->shipping_carrier_id)
                                <option value="{{ $exportBooking->shipping_carrier_id }}" selected>
                                    {{ $exportBooking->shippingCarrier?->carrier_code }} —
                                    {{ $exportBooking->shippingCarrier?->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPol">Port of Loading (POL)</label>
                        <input type="text" id="fbPol" name="pol" class="form-control fb-input"
                            value="{{ old('pol', $exportBooking?->pol) }}" placeholder="e.g. Chittagong">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPod">Port of Discharge (POD)</label>
                        <input type="text" id="fbPod" name="pod" class="form-control fb-input"
                            value="{{ old('pod', $exportBooking?->pod) }}" placeholder="e.g. Singapore">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbPlaceOfReceipt">Place of Receipt</label>
                        <input type="text" id="fbPlaceOfReceipt" name="place_of_receipt" class="form-control fb-input"
                            value="{{ old('place_of_receipt', $exportBooking?->place_of_receipt) }}"
                            placeholder="e.g. Dhaka ICD">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbVesselName">Vessel Name</label>
                        <input type="text" id="fbVesselName" name="vessel_name" class="form-control fb-input"
                            value="{{ old('vessel_name', $exportBooking?->vessel_name) }}" placeholder="e.g. MSC ANNA">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbVoyageNo">Voyage No</label>
                        <input type="text" id="fbVoyageNo" name="voyage_no" class="form-control fb-input"
                            value="{{ old('voyage_no', $exportBooking?->voyage_no) }}" placeholder="e.g. 024W">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbEtd">ETD / Flight Date</label>
                        <input type="date" id="fbEtd" name="etd" class="form-control fb-input"
                            value="{{ old('etd', $exportBooking?->etd?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbEta">ETA</label>
                        <input type="date" id="fbEta" name="eta" class="form-control fb-input"
                            value="{{ old('eta', $exportBooking?->eta?->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- Group 5: Cargo description --}}
                <div class="row g-2 mb-2">
                    <div class="col-12 col-lg-6">
                        <label class="fb-label" for="fbCommodityDescription">Commodity Description</label>
                        <input type="text" id="fbCommodityDescription" name="commodity_description" class="form-control fb-input"
                            value="{{ old('commodity_description', $exportBooking?->commodity_description) }}"
                            placeholder="e.g. Garments, ceramics">
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="fb-label"><span id="hsCodeLabel">HS Code</span></div>
                        <div id="hsCodeList" class="hs-code-list" role="group" aria-labelledby="hsCodeLabel"></div>
                    </div>
                </div>

                {{-- Group 6: Shipping & trade documents --}}
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbExportBlNo">Export B/L No</label>
                        <input type="text" id="fbExportBlNo" name="export_bl_no" class="form-control fb-input"
                            value="{{ old('export_bl_no', $exportBooking?->export_bl_no) }}"
                            placeholder="e.g. EXP024W-0001">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbBlDate">B/L Date</label>
                        <input type="date" id="fbBlDate" name="bl_date" class="form-control fb-input"
                            value="{{ old('bl_date', $exportBooking?->bl_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbBookingNoteNo">Booking Note No</label>
                        <input type="text" id="fbBookingNoteNo" name="booking_note_no" class="form-control fb-input"
                            value="{{ old('booking_note_no', $exportBooking?->booking_note_no) }}"
                            placeholder="e.g. MSL024W-8899">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbLcNo">LC No</label>
                        <input type="text" id="fbLcNo" name="lc_no" class="form-control fb-input"
                            value="{{ old('lc_no', $exportBooking?->lc_no) }}" placeholder="e.g. LC-2024-0001">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbExpNo">EXP No</label>
                        <input type="text" id="fbExpNo" name="exp_no" class="form-control fb-input"
                            value="{{ old('exp_no', $exportBooking?->exp_no) }}" placeholder="e.g. EXP-2024-0001">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbExpDate">EXP Date</label>
                        <input type="date" id="fbExpDate" name="exp_date" class="form-control fb-input"
                            value="{{ old('exp_date', $exportBooking?->exp_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbInvoiceNo">Invoice No</label>
                        <input type="text" id="fbInvoiceNo" name="invoice_no" class="form-control fb-input"
                            value="{{ old('invoice_no', $exportBooking?->invoice_no) }}"
                            placeholder="e.g. INV-2024-0001">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="fb-label" for="fbInvoiceDate">Invoice Date</label>
                        <input type="date" id="fbInvoiceDate" name="invoice_date" class="form-control fb-input"
                            value="{{ old('invoice_date', $exportBooking?->invoice_date?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="fb-label" for="fbRemarks">Remarks</label>
                        <input type="text" id="fbRemarks" name="remarks" class="form-control fb-input"
                            value="{{ old('remarks', $exportBooking?->remarks) }}"
                            placeholder="e.g. Customs clearance required">
                    </div>
                </div>

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
                <div style="overflow-x:auto;">
                    <table class="table table-bordered mb-0" id="itemsTable" style="min-width:1500px;">
                        <thead>
                            <tr>
                                <th style="width:35px" class="text-center">#</th>
                                <th style="width:35px" class="text-center"></th>
                                <th style="width:115px">Item Type</th>
                                <th style="width:75px">Qty</th>
                                <th style="width:160px">Container Size / Pkg</th>
                                <th style="width:90px">Pkg Qty</th>
                                <th style="width:130px">Pkg Unit</th>
                                <th style="width:145px">Container No</th>
                                <th style="width:130px">Seal No</th>
                                <th style="width:110px">Weight</th>
                                <th style="width:80px">Unit</th>
                                <th style="width:90px">CBM</th>
                                <th style="width:140px">Country of Origin</th>
                                <th style="width:90px">DG</th>
                                <th style="min-width:140px">Special Handling</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3 mb-3">
            <a href="{{ route('nas-freights.freight-export-bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-sm btn-success px-4">
                <i class="fa fa-save me-1"></i> {{ $exportBooking ? 'Update' : 'Save' }}
            </button>
        </div>

    </form>

    {{-- ═══ BILLS, EXPENSE & TRANSPORT (edit mode only, read-only; below the action buttons) ═══ --}}
    @if($exportBooking)

    @include('nas-freights.freight-export-bookings._bills', ['exportBooking' => $exportBooking])

    @include('nas-freights.freight-export-bookings._expense', ['exportBooking' => $exportBooking])

    @include('nas-freights.freight-export-bookings._transport', ['exportBooking' => $exportBooking])

    @endif
    {{-- /edit-mode sections --}}

    <template id="hsCodeTemplate">
        <div class="input-group hs-code-item">
            <input type="text" name="hs_codes[]" class="form-control fb-input hs-code-input" maxlength="50"
                placeholder="e.g. 6109.10">
            <button type="button" class="btn py-0 px-2 hs-code-action"></button>
        </div>
    </template>

    <template id="itemTemplate">
        <tr>
            <td class="sl-no text-center fw-bold" style="font-size:.72rem;"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-item-row"><i
                        class="fa fa-times"></i></button>
            </td>
            <td>
                <select name="items[0][item_type]" class="form-select form-select-sm item-type-sel"
                    style="font-size:.72rem;">
                    <option value="container">Container</option>
                    <option value="package">Package</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-center"
                    style="font-size:.72rem;" value="1" min="1" placeholder="1">
            </td>
            <td>
                <select name="items[0][container_size]" class="form-select form-select-sm container-size-sel"
                    style="font-size:.72rem;">
                    <option value="">-- Size --</option>
                    @foreach ($containerSizes as $cs)
                        <option value="{{ $cs }}">{{ $cs }}</option>
                    @endforeach
                </select>
                <select name="items[0][package_type]" class="form-select form-select-sm package-type-sel d-none"
                    style="font-size:.72rem;">
                    <option value="">-- Type --</option>
                    @foreach ($packageTypes as $pt)
                        <option value="{{ $pt }}">{{ $pt }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[0][package_qty]"
                    class="form-control form-control-sm text-center pkg-qty-input" style="font-size:.72rem;"
                    min="1" step="1" placeholder="e.g. 500" aria-label="Package quantity inside container">
            </td>
            <td>
                <select name="items[0][package_unit]" class="form-select form-select-sm pkg-unit-sel"
                    style="font-size:.72rem;" aria-label="Package unit inside container">
                    <option value="">-- Unit --</option>
                    @foreach ($packageTypes as $pt)
                        <option value="{{ $pt }}">{{ $pt }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[0][container_no]"
                    class="form-control form-control-sm container-no-input" style="font-size:.72rem;"
                    placeholder="e.g. MSCU1234567">
            </td>
            <td>
                <input type="text" name="items[0][seal_no]" class="form-control form-control-sm seal-no-input"
                    style="font-size:.72rem;" placeholder="e.g. SL1234567">
            </td>
            <td><input type="number" name="items[0][gross_weight]" class="form-control form-control-sm text-end"
                    style="font-size:.72rem;" step="0.01" placeholder="0.00"></td>
            <td>
                <select name="items[0][weight_unit]" class="form-select form-select-sm" style="font-size:.72rem;">
                    @foreach ($weightUnits as $wu)
                        <option value="{{ $wu }}">{{ $wu }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[0][volume_cbm]" class="form-control form-control-sm text-end"
                    style="font-size:.72rem;" step="0.001" placeholder="0.000"></td>
            <td><input type="text" name="items[0][country_of_origin]" class="form-control form-control-sm"
                    style="font-size:.72rem;" placeholder="e.g. Bangladesh"></td>
            <td>
                <select name="items[0][is_dangerous_goods]" class="form-select form-select-sm" style="font-size:.72rem;">
                    <option value="0">No DG</option>
                    <option value="1">DG</option>
                </select>
            </td>
            <td><input type="text" name="items[0][special_handling]" class="form-control form-control-sm"
                    style="font-size:.72rem;" placeholder="e.g. Fragile"></td>
        </tr>
    </template>

    {{-- Quick Create Customer Modal --}}
    <div class="modal fade" id="quickCustomerModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2" style="background:#0a4f3c;">
                    <h6 class="modal-title text-white mb-0"><i class="fa fa-user-plus me-1"></i> New Customer</h6>
                    <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.75rem; font-weight:600;">Name <span class="text-danger">*</span></label>
                        <input type="text" id="qc_name" class="form-control form-control-sm" placeholder="e.g. ABC Exports Ltd">
                        <div class="invalid-feedback" id="qc_name_err"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.75rem; font-weight:600;">Phone</label>
                        <input type="text" id="qc_phone" class="form-control form-control-sm" placeholder="e.g. 01700000000">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success" id="btnSaveQuickCustomer">
                        <i class="fa fa-save me-1"></i> Save &amp; Select
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var existingItems = @json($existingItems);
        var existingHsCodes = @json(old('hs_codes', $exportBooking?->hs_codes ?? []));
        var QUICK_STORE_CUSTOMER = '{{ route('nas-freights.freight-export-bookings.quick-store-customer') }}';

        $(function() {
            $('#customerSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search customer...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-export-bookings.search-customers') }}',
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
                    url: '{{ route('nas-freights.freight-export-bookings.search-employees') }}',
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
                    url: '{{ route('nas-freights.freight-export-bookings.search-overseas-agents') }}',
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
                placeholder: 'Search shipping carrier...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-freights.freight-export-bookings.search-shipping-carriers') }}',
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
                addItemRow();
            });

            $(document).on('click', '.remove-item-row', function() {
                if ($('#itemsBody tr').length <= 1) return;
                $(this).closest('tr').remove();
                reindexItems();
            });

            $(document).on('change', '.item-type-sel', function() {
                toggleItemTypeFields($(this).closest('tr'));
            });

            // ── Quick Create Customer ──
            $('#btnQuickCustomer').on('click', function() {
                $('#qc_name').val('').removeClass('is-invalid');
                $('#qc_phone').val('');
                $('#quickCustomerModal').modal('show');
            });

            $('#btnSaveQuickCustomer').on('click', function() {
                var name = $('#qc_name').val().trim();
                if (!name) {
                    $('#qc_name').addClass('is-invalid');
                    $('#qc_name_err').text('Required.');
                    return;
                }
                $('#qc_name').removeClass('is-invalid');

                $('#btnSaveQuickCustomer').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                    url: QUICK_STORE_CUSTOMER,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: name,
                        phone: $('#qc_phone').val().trim()
                    },
                }).done(function(r) {
                    var opt = new Option(r.text, r.id, true, true);
                    $('#customerSelect').append(opt).trigger('change');
                    $('#quickCustomerModal').modal('hide');
                    Swal.fire({ icon: 'success', title: r.message, timer: 1800, showConfirmButton: false });
                }).fail(function(xhr) {
                    var errs = xhr.responseJSON?.errors;
                    if (errs?.name) {
                        $('#qc_name').addClass('is-invalid');
                        $('#qc_name_err').text(errs.name[0]);
                    } else {
                        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Save failed.' });
                    }
                }).always(function() {
                    $('#btnSaveQuickCustomer').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save &amp; Select');
                });
            });
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
                $row.find('[name$="[gross_weight]"]').val(data.gross_weight || '');
                $row.find('[name$="[weight_unit]"]').val(data.weight_unit || 'KG');
                $row.find('[name$="[volume_cbm]"]').val(data.volume_cbm || '');
                $row.find('[name$="[country_of_origin]"]').val(data.country_of_origin || '');
                $row.find('[name$="[is_dangerous_goods]"]').val(data.is_dangerous_goods == '1' ? '1' : '0');
                $row.find('[name$="[special_handling]"]').val(data.special_handling || '');
            }
            reindexItems();
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
            $row.find('.container-size-sel').toggleClass('d-none', !isContainer);
            $row.find('.package-type-sel').toggleClass('d-none', isContainer);
            $row.find('.pkg-qty-input, .pkg-unit-sel').prop('disabled', !isContainer);
            $row.find('.container-no-input').prop('disabled', !isContainer);
            $row.find('.seal-no-input').prop('disabled', !isContainer);
        }
    </script>
@endpush

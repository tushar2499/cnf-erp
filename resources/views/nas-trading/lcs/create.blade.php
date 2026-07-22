@extends('nas-trading.layouts.app')
@section('title', 'New LC Entry')
@push('styles')
    <style>
        .lc-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .lc-section-header {
            background: #1a6b60;
            color: #fff;
            padding: .5rem 1rem;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .03em;
            display: flex;
            align-items: center;
        }

        .lc-section-body {
            padding: 1rem;
        }

        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .2rem;
        }

        .form-control,
        .form-select {
            font-size: .82rem;
        }

        .items-table th {
            background: #e9ecef;
            font-size: .77rem;
            padding: .4rem .5rem;
        }

        .items-table td {
            padding: .3rem .5rem;
            vertical-align: middle;
        }

        .items-table .form-control-sm,
        .items-table .form-select-sm {
            font-size: .78rem;
        }

        .badge-auto {
            background: #0c2340;
            color: #fff;
            font-size: .72rem;
            padding: .2rem .5rem;
            border-radius: .25rem;
        }

        /* Optional tabs */
        .lc-tabs .nav-link {
            font-size: .78rem;
            padding: .35rem .75rem;
            color: #495057;
        }

        .lc-tabs .nav-link.active {
            color: #1a6b60;
            font-weight: 700;
            border-bottom: 2px solid #1a6b60;
        }

        /* Hide number spinner arrows so compact inputs don't lose space to them */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fa fa-file-contract me-2 text-info"></i> New LC Entry</h4>
        <a href="{{ route('nas-trading.lcs.index') }}" class="btn btn-sm btn-outline-secondary"><i
                class="fa fa-arrow-left me-1"></i> Back</a>
    </div>

    <form id="lcForm">
        @csrf

        {{-- Identification (always visible, required) --}}
        <div class="lc-card">
            <div class="lc-section-header">
                <span><i class="fa fa-id-card me-2"></i> Section 1 — Identification</span>
            </div>
            <div class="lc-section-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">LC Entry No</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold" value="Auto-generated"
                            readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select id="customerSelect" class="form-select form-select-sm" name="customer_id" required>
                            <option value="">Select Customer...</option>
                        </select>
                        <input type="hidden" name="customer_name" id="customerName">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PFI No <span class="text-danger">*</span></label>
                        <input type="text" name="pfi_no" class="form-control form-control-sm"
                            placeholder="e.g. PFI-2025-001" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PFI Date</label>
                        <input type="date" name="pfi_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LC No (Bank)</label>
                        <input type="text" name="lc_no" class="form-control form-control-sm"
                            placeholder="e.g. LC-2025-001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LC Open Date</label>
                        <input type="date" name="lc_open_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LC Expiry Date</label>
                        <input type="date" name="lc_expiry_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LC Type</label>
                        <select name="lc_type" class="form-select form-select-sm">
                            <option value="">Select...</option>
                            <option value="TT/LCA">TT/LCA</option>
                            <option value="Sight">Sight</option>
                            <option value="DF">DF (Deferred)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LC Status</label>
                        <select name="lc_status" class="form-select form-select-sm">
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Amended">Amended</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input type="text" name="month" class="form-control form-control-sm"
                            placeholder="e.g. January 2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Shipment From</label>
                        <input type="text" name="shipment_from" class="form-control form-control-sm"
                            placeholder="e.g. China">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Shipment Date</label>
                        <input type="date" name="last_shipment_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Shipping Docs Received</label>
                        <input type="date" name="shipping_docs_received_date" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>

        {{-- Optional tabs (steps 2–9) --}}
        <div class="lc-card mt-1">
            <div class="lc-section-header"><i class="fa fa-list-alt me-2"></i> Additional Details <span
                    class="fw-normal ms-2" style="font-size:.72rem;opacity:.8">(optional — can be filled later)</span></div>
            <div class="px-3 pt-2">
                <ul class="nav nav-tabs lc-tabs border-bottom mb-0" id="lcOptTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-supplier"><i
                                class="fa fa-industry me-1"></i>Supplier &amp; Goods</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-bank"><i
                                class="fa fa-university me-1"></i>Bank &amp; Documents</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-lcdetails"><i
                                class="fa fa-dollar-sign me-1"></i>LC Details</a></li>

                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payment"><i
                                class="fa fa-money-check-alt me-1"></i>Payment Tracking</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-duty"><i
                                class="fa fa-clipboard-check me-1"></i>Duty &amp; Clearance</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-vat"><i
                                class="fa fa-percent me-1"></i>VAT / Tax / Sales</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-items"><i
                                class="fa fa-boxes me-1"></i>Product Items</a></li>
                </ul>
            </div>
            <div class="tab-content px-3 pb-3 pt-2">

                {{-- Tab: Supplier & Goods --}}
                <div class="tab-pane fade show active" id="tab-supplier">
                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Supplier</label>
                            <select id="supplierSelect" class="form-select form-select-sm" name="supplier_id">
                                <option value="">Select Supplier...</option>
                            </select>
                            <input type="hidden" name="supplier_name" id="supplierName">
                            <input type="hidden" name="supplier_country" id="supplierCountry">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Country</label>
                            <input type="text" name="supplier_country" id="supplierCountryDisplay"
                                class="form-control form-control-sm bg-light" readonly placeholder="Auto-filled">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Importer</label>
                            <div class="input-group input-group-sm">
                                <select name="importer_id" id="importerSelect" class="form-select form-select-sm">
                                    <option value="">Select...</option>
                                    @foreach ($importers as $imp)
                                        <option value="{{ $imp->id }}">{{ $imp->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2" id="btnAddImporter"
                                    title="Add new importer"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer PO Date</label>
                            <input type="date" name="customer_po_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Item Description</label>
                            <textarea name="item_description" class="form-control form-control-sm" rows="2"
                                placeholder="Brief description of goods"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Tab: Bank & Documents --}}
                <div class="tab-pane fade" id="tab-bank">
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Opening Bank</label>
                            <select name="opening_bank_id" class="form-select form-select-sm">
                                <option value="">Select Bank...</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">
                                        {{ $bank->name }}{{ $bank->branch ? ' - ' . $bank->branch : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Port of Destination</label>
                            <select name="port_of_dest_id" class="form-select form-select-sm">
                                <option value="">Select Port...</option>
                                @foreach ($ports as $port)
                                    <option value="{{ $port->id }}">{{ $port->name }} ({{ $port->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country of Origin</label>
                            <input type="text" name="country_of_origin" class="form-control form-control-sm"
                                placeholder="e.g. China">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Mode</label>
                            <input type="text" name="payment_mode" class="form-control form-control-sm"
                                placeholder="e.g. SWIFT, TT">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cover Note</label>
                            <input type="text" name="cover_note" class="form-control form-control-sm"
                                placeholder="e.g. CN-2025-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PSI No</label>
                            <input type="text" name="psi_no" class="form-control form-control-sm"
                                placeholder="e.g. PSI-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PSI Company</label>
                            <select name="psi_company_id" class="form-select form-select-sm">
                                <option value="">Select...</option>
                                @foreach ($psiCompanies as $psi)
                                    <option value="{{ $psi->id }}">{{ $psi->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Doc Status</label>
                            <select name="doc_status" class="form-select form-select-sm">
                                <option value="Pending">Pending</option>
                                <option value="Received">Received</option>
                                <option value="Complete">Complete</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sanction Types</label>
                            <input type="text" name="sanction_types" class="form-control form-control-sm"
                                placeholder="e.g. OFAC, UN">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Third Party</label>
                            <input type="text" name="third_party" class="form-control form-control-sm"
                                placeholder="e.g. Agent / Broker Name">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Tab: LC Details --}}
                <div class="tab-pane fade" id="tab-lcdetails">
                    <div class="row g-2 mt-1">
                        <div class="col-6 col-md-3">
                            <label class="form-label">PFI Value</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="pfi_value" id="pfiValue"
                                    class="form-control form-control-sm" step="0.0001" placeholder="0.00">
                                <span class="input-group-text fcy-label">USD</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Currency</label>
                            <select name="currency" id="currency" class="form-select form-select-sm">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="CNY">CNY</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC OP Rate</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_open_rate" id="lcOpRate"
                                    class="form-control form-control-sm" step="0.0001" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Margin %</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="margin_percent" id="marginPct"
                                    class="form-control form-control-sm" step="0.0001" placeholder="0.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Margin Amt</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_margin_amt" id="lcMarginAmt"
                                    class="form-control form-control-sm bg-light" readonly step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Opening Bank Cost</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_open_cost_bdt" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Freight Value</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="freight_value" id="freightValue"
                                    class="form-control form-control-sm" step="0.0001" placeholder="0.00">
                                <span class="input-group-text fcy-label">USD</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Value (calc)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_value" id="lcValue"
                                    class="form-control form-control-sm bg-light" readonly step="0.0001" placeholder="0.00">
                                <span class="input-group-text fcy-label">USD</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Amount (calc)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="amount_bdt" id="amountBdt"
                                    class="form-control form-control-sm bg-light" readonly step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Total LC Cost</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="total_lc_cost" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Landed Cost</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="landed_cost" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Rate Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_rate_amount" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Doc RT Rate</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="doc_rt_rate" class="form-control form-control-sm"
                                    step="0.0001" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC RT Value</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_rt_value" id="lcRtValue" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Commission</label>
                            <div class="input-group input-group-sm mb-1">
                                <input type="number" name="lc_commission_percent" id="lcCommissionPct"
                                    class="form-control form-control-sm" step="0.0001" min="0" placeholder="0"
                                    title="Percentage of LC RT Value">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_commission_flat" id="lcCommission"
                                    class="form-control form-control-sm" step="0.01" placeholder="0.00"
                                    title="Auto-filled from % or enter flat amount directly">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Charge Posting</label>
                            <input type="text" name="lc_charge_posting" class="form-control form-control-sm"
                                placeholder="e.g. DR-001">
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Insurance Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="insurance_amt" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Insurance Validity</label>
                            <input type="date" name="insurance_validity" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Comm. Currency</label>
                            <select name="comm_currency" class="form-select form-select-sm">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="CNY">CNY</option>
                                <option value="BDT">BDT</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Comm. Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="comm_amount" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">LC Amendment Charge</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_amendment_charge" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Credit Report Charge</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="credit_report_charge" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Other Charges</label>
                                <button type="button" class="btn btn-secondary btn-sm py-0 px-2"
                                    id="btnAddOtherCharge" style="font-size:.75rem">
                                    <i class="fa fa-plus me-1"></i>Add Charge
                                </button>
                            </div>
                            <div style="overflow-x:auto">
                                <table class="table table-sm table-bordered items-table mb-1 w-100"
                                    id="otherChargesTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:32px">#</th>
                                            <th>Charge Name</th>
                                            <th style="width:180px">Amount</th>
                                            <th style="width:32px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="otherChargesBody"></tbody>
                                </table>
                            </div>
                            <div id="otherChargesEmpty" class="text-center py-1"
                                style="display:none;font-size:.78rem;color:#adb5bd">No other charges added yet.</div>
                            <div class="d-flex justify-content-end align-items-center mt-1">
                                <label class="form-label me-2 mb-0 fw-semibold"
                                    style="font-size:.78rem">Total Other Charges:</label>
                                <div class="input-group input-group-sm" style="width:160px">
                                    <input type="number" name="other_charges" id="otherChargesTotal"
                                        class="form-control form-control-sm bg-light fw-bold" readonly step="0.01"
                                        placeholder="0.00">
                                    <span class="input-group-text">BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Payment Tracking --}}
                <div class="tab-pane fade" id="tab-payment">
                    {{-- LC Closing Bill (flat summary fields) --}}
                    <div class="row g-2 mt-1 mb-2">
                        <div class="col-md-3">
                            <label class="form-label">LC Closing Bill</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_closing_bill" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">LC Closing Bill Date</label>
                            <input type="date" name="lc_closing_bill_date" class="form-control form-control-sm">
                        </div>
                    </div>

                    {{-- Multi-row payment table --}}
                    <div class="lc-card" style="margin-bottom:0">
                        <div class="lc-section-header" style="justify-content:space-between">
                            <span><i class="fa fa-money-bill-wave me-2"></i>Payment Receipts</span>
                            <button type="button" class="btn btn-sm py-0 px-2" id="btnAddPayment"
                                style="font-size:.77rem;color:#fff;border:1px solid rgba(255,255,255,.5)"><i
                                    class="fa fa-plus me-1"></i>Add Payment</button>
                        </div>
                        <div class="p-0" style="overflow-x:auto">
                            <table class="table table-sm table-bordered items-table mb-0 w-100" id="paymentTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:32px">#</th>
                                        <th style="width:110px">Type</th>
                                        <th style="min-width:140px">Receipt No</th>
                                        <th style="width:140px">Date</th>
                                        <th style="width:150px">Amount</th>
                                        <th style="width:32px"></th>
                                    </tr>
                                </thead>
                                <tbody id="paymentBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Total Received (auto-sum) --}}
                    <div class="d-flex justify-content-end align-items-center mt-2">
                        <label class="form-label me-2 mb-0 fw-semibold" style="font-size:.82rem">Total Received:</label>
                        <div class="input-group input-group-sm" style="width:180px">
                            <input type="number" name="total_received_bdt" id="totalReceived"
                                class="form-control form-control-sm bg-light fw-bold" readonly step="0.01"
                                placeholder="0.00">
                            <span class="input-group-text">BDT</span>
                        </div>
                    </div>
                </div>

                {{-- Tab: Duty & Clearance --}}
                <div class="tab-pane fade" id="tab-duty">
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Duty Advance</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="duty_advance" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Duty Advance Date</label>
                            <input type="date" name="duty_advance_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Duty Advance Posting</label>
                            <input type="text" name="duty_advance_posting" class="form-control form-control-sm"
                                placeholder="e.g. DA-001">
                        </div>
                        <div class="col-md-3"></div>
                        <div class="col-md-3">
                            <label class="form-label">Bill of Entry No</label>
                            <input type="text" name="bill_of_entry_no" class="form-control form-control-sm"
                                placeholder="e.g. BE-2025-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bill of Entry Date</label>
                            <input type="date" name="bill_of_entry_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customs Duty</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="customs_duty" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customs Duty Posting</label>
                            <input type="text" name="customs_duty_posting" class="form-control form-control-sm"
                                placeholder="e.g. CD-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CNF Party</label>
                            <input type="text" name="cnf_party" class="form-control form-control-sm"
                                placeholder="e.g. Party Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CNF Total Cost</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="cnf_total_cost" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CNF Cost Posting</label>
                            <input type="text" name="cnf_cost_posting" class="form-control form-control-sm"
                                placeholder="e.g. CC-001">
                        </div>
                    </div>
                </div>

                {{-- Tab: VAT / Tax / Sales --}}
                <div class="tab-pane fade" id="tab-vat">
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Payable / Receivable</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="payable_receivable" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Received Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="received_amount" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Received Date</label>
                            <input type="date" name="received_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3"></div>
                        <div class="col-md-3">
                            <label class="form-label">VAT Return</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="vat_return" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">VAT Return Date</label>
                            <input type="date" name="vat_return_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">VAT Return Posting</label>
                            <input type="text" name="vat_return_posting" class="form-control form-control-sm"
                                placeholder="e.g. VR-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Income Tax</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="income_tax" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bank Statement Amt</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="bank_statement_amt" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">LC Commission</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="lc_commission" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">LC Commission Date</label>
                            <input type="date" name="lc_commission_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sales Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="sales_amount" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sales Posting</label>
                            <input type="text" name="sales_posting" class="form-control form-control-sm"
                                placeholder="e.g. SP-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">COSS Amount</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="coss_amount" class="form-control form-control-sm"
                                    step="0.01" placeholder="0.00">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">COSS Posting</label>
                            <input type="text" name="coss_posting" class="form-control form-control-sm"
                                placeholder="e.g. CP-001">
                        </div>
                    </div>
                </div>

                {{-- Tab: Product Line Items --}}
                <div class="tab-pane fade" id="tab-items">
                    <div class="lc-card mt-2" style="margin-bottom:0">
                        <div class="d-flex justify-content-end mt-2 mb-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnAddItem"
                                style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Row</button>
                        </div>
                        <div class="p-0" style="overflow-x:auto">
                            <table class="table table-sm table-bordered items-table mb-0 w-100" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:32px">#</th>
                                        <th style="min-width:185px">Product</th>
                                        <th style="width:78px">Code</th>
                                        <th style="width:88px">HS Code</th>
                                        <th style="width:68px">Qty</th>
                                        <th style="width:60px">Unit</th>
                                        <th style="width:72px">Weight</th>
                                        <th style="width:60px">W.Unit</th>
                                        <th style="width:90px">Unit Price</th>
                                        <th style="width:90px">Amount</th>
                                        <th style="width:55px">Curr.</th>
                                        <th style="width:32px"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center py-2" id="itemsEmptyMsg" style="display:none;font-size:.78rem;color:#adb5bd">
                        No product rows added yet.</div>
                </div>

            </div>{{-- /tab-content --}}
        </div>

        {{-- Always-visible Save button --}}
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-success px-5" id="btnSave">
                <i class="fa fa-save me-1"></i> Save LC
            </button>
        </div>
    </form>

    {{-- Add Importer Modal --}}
    <div class="modal fade" id="addImporterModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2" style="background:#0c2340;color:#fff">
                    <h6 class="modal-title mb-0"><i class="fa fa-plus me-2"></i>Add Importer</h6>
                    <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addImporterForm">
                        <div class="mb-2">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="newImporterName" class="form-control form-control-sm"
                                placeholder="Importer name">
                            <div class="invalid-feedback">Name is required.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">BIN No</label>
                            <input type="text" id="newImporterBin" class="form-control form-control-sm"
                                placeholder="e.g. BIN-001">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Address</label>
                            <input type="text" id="newImporterAddress" class="form-control form-control-sm"
                                placeholder="Address">
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" id="btnSaveImporter">
                        <i class="fa fa-save me-1"></i>Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var itemRowIdx = 0;
        var paymentRowIdx = 0;
        var otherChargeRowIdx = 0;

        function validateIdentification() {
            var customerId = $('#customerSelect').val();
            var pfiNo = $('[name=pfi_no]').val().trim();
            if (!customerId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please select a customer.'
                });
                return false;
            }
            if (!pfiNo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'PFI No is required.'
                });
                return false;
            }
            return true;
        }

        // ── Other Charges ────────────────────────────────────────────────────────
        function addOtherChargeRow(data) {
            data = data || {};
            var idx = otherChargeRowIdx++;
            var rowNum = $('#otherChargesBody tr').length + 1;
            var html = `
            <tr>
                <td class="text-center other-charge-row-num" style="font-size:.75rem;vertical-align:middle">${rowNum}</td>
                <td><input type="text" class="form-control form-control-sm" name="other_charge_items[${idx}][name]" value="${data.name || ''}" placeholder="e.g. Port Charges"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm other-charge-amount" name="other_charge_items[${idx}][amount]" value="${data.amount || ''}" step="0.01" min="0" placeholder="0.00">
                        <span class="input-group-text">BDT</span>
                    </div>
                </td>
                <td class="text-center" style="vertical-align:middle">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-other-charge p-0" style="width:24px;height:24px" title="Remove">
                        <i class="fa fa-times" style="font-size:.65rem"></i>
                    </button>
                </td>
            </tr>`;
            $('#otherChargesBody').append(html);
            syncOtherChargesEmpty();
        }

        function syncOtherChargesTotal() {
            var total = 0;
            $('.other-charge-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#otherChargesTotal').val(total > 0 ? total.toFixed(2) : '');
        }

        function syncOtherChargesEmpty() {
            var empty = $('#otherChargesBody tr').length === 0;
            $('#otherChargesEmpty').toggle(empty);
        }

        // ── Payment Receipts ─────────────────────────────────────────────────────
        function addPaymentRow(data) {
            data = data || {};
            var idx = paymentRowIdx++;
            var rowNum = $('#paymentBody tr').length + 1;
            var html = `
            <tr>
                <td class="text-center payment-row-num" style="font-size:.75rem;vertical-align:middle">${rowNum}</td>
                <td>
                    <select class="form-select form-select-sm" name="payments[${idx}][payment_type]">
                        <option value="advance" ${(data.payment_type || 'advance') === 'advance' ? 'selected' : ''}>Advance</option>
                        <option value="regular" ${(data.payment_type || '') === 'regular' ? 'selected' : ''}>Regular</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="payments[${idx}][receipt_no]" value="${data.receipt_no || ''}" placeholder="e.g. MR-001"></td>
                <td><input type="date" class="form-control form-control-sm" name="payments[${idx}][date]" value="${data.date || ''}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm payment-amount" name="payments[${idx}][amount]" value="${data.amount || ''}" step="0.01" min="0" placeholder="0.00">
                        <span class="input-group-text">BDT</span>
                    </div>
                </td>
                <td class="text-center" style="vertical-align:middle">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-payment-row p-0" style="width:24px;height:24px" title="Remove">
                        <i class="fa fa-times" style="font-size:.65rem"></i>
                    </button>
                </td>
            </tr>`;
            $('#paymentBody').append(html);
        }

        function syncPaymentTotal() {
            var total = 0;
            $('.payment-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalReceived').val(total > 0 ? total.toFixed(2) : '');
        }

        // ── Line Items ──────────────────────────────────────────────────────────
        function syncItemsEmpty() {
            var empty = $('#itemsBody tr').length === 0;
            $('#itemsEmptyMsg').toggle(empty);
        }

        function addItemRow(data) {
            data = data || {};
            var idx = itemRowIdx++;
            var html = `
    <tr>
        <td class="text-center row-num" style="font-size:.75rem;vertical-align:middle">${idx + 1}</td>
        <td>
            <select class="form-select form-select-sm item-select" name="items[${idx}][item_id]" data-row="${idx}">
                <option value="">Search product...</option>
                ${data.item_id ? `<option value="${data.item_id}" selected>${data.product_name || ''}</option>` : ''}
            </select>
            <input type="hidden" name="items[${idx}][product_name]" class="item-name" value="${data.product_name || ''}">
        </td>
        <td><input type="text" class="form-control form-control-sm item-code" name="items[${idx}][product_code]" value="${data.product_code || ''}" placeholder="Auto" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control form-control-sm item-hscode" name="items[${idx}][hs_code]" value="${data.hs_code || ''}" placeholder="e.g. 8471.30"></td>
        <td><input type="number" class="form-control form-control-sm item-qty" name="items[${idx}][qty_count]" value="${data.qty_count || ''}" step="0.0001" min="0" placeholder="0.00"></td>
        <td><input type="text" class="form-control form-control-sm item-unit" name="items[${idx}][qty_unit]" value="${data.qty_unit || ''}" placeholder="PCS"></td>
        <td><input type="number" class="form-control form-control-sm" name="items[${idx}][weight]" value="${data.weight || ''}" step="0.0001" min="0" placeholder="0.00"></td>
        <td><input type="text" class="form-control form-control-sm" name="items[${idx}][weight_unit]" value="${data.weight_unit || ''}" placeholder="KG"></td>
        <td><input type="number" class="form-control form-control-sm item-uprice" name="items[${idx}][unit_price]" value="${data.unit_price || ''}" step="0.0001" min="0" placeholder="0.00"></td>
        <td><input type="number" class="form-control form-control-sm item-amount bg-light" name="items[${idx}][line_amount]" value="${data.line_amount || ''}" step="0.0001" readonly tabindex="-1" placeholder="0.00"></td>
        <td>
            <select class="form-select form-select-sm" name="items[${idx}][currency]">
                ${['USD','EUR','GBP','CNY','BDT'].map(c => `<option value="${c}" ${(data.currency||'USD')===c?'selected':''}>${c}</option>`).join('')}
            </select>
        </td>
        <td class="text-center" style="vertical-align:middle"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-0" style="width:24px;height:24px" title="Remove"><i class="fa fa-times" style="font-size:.65rem"></i></button></td>
    </tr>`;
            $('#itemsBody').append(html);
            initItemSelect(idx);
            syncItemsEmpty();
        }

        function initItemSelect(idx) {
            $(`[name="items[${idx}][item_id]"]`).select2({
                width: '100%',
                placeholder: 'Search item...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-trading.items.search') }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    }),
                }
            }).on('select2:select', function(e) {
                var d = e.params.data;
                var row = $(this).closest('tr');
                row.find('.item-name').val(d.text.split(' | ')[1] || d.text);
                row.find('.item-code').val(d.code || '');
                row.find('.item-hscode').val(d.hs_code || '');
                row.find('.item-unit').val(d.unit || '');
            }).on('select2:clear', function() {
                var row = $(this).closest('tr');
                row.find('.item-name,.item-code,.item-hscode,.item-unit').val('');
            });
        }

        $(function() {
            // Customer Select2
            $('#customerSelect').select2({
                width: '100%',
                placeholder: 'Search customer...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-trading.lcs.search-customers') }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    }),
                }
            }).on('select2:select', function(e) {
                $('#customerName').val(e.params.data.text.split(' | ')[1] || e.params.data.text);
            });

            // Supplier Select2
            $('#supplierSelect').select2({
                width: '100%',
                placeholder: 'Search supplier...',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('nas-trading.lcs.search-suppliers') }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    }),
                }
            }).on('select2:select', function(e) {
                $('#supplierName').val(e.params.data.text.split(' | ')[1] || e.params.data.text);
                $('#supplierCountry, #supplierCountryDisplay').val(e.params.data.country || '');
            });

            // Calculations
            function calcFinancials() {
                var pfi = parseFloat($('[name=pfi_value]').val()) || 0;
                var rate = parseFloat($('[name=lc_open_rate]').val()) || 0;
                var margin = parseFloat($('[name=margin_percent]').val()) || 0;
                var freight = parseFloat($('[name=freight_value]').val()) || 0;
                var lcVal = pfi + freight;
                var amtBdt = lcVal * rate;
                var marginAmt = amtBdt * margin / 100;
                $('#lcValue').val(lcVal.toFixed(4));
                $('#amountBdt').val(amtBdt.toFixed(2));
                $('#lcMarginAmt').val(marginAmt.toFixed(2));
            }
            $('[name=pfi_value],[name=lc_open_rate],[name=margin_percent],[name=freight_value]').on('input',
                calcFinancials);

            $('#lcRtValue,#lcCommissionPct').on('input', function () {
                var rtVal = parseFloat($('#lcRtValue').val()) || 0;
                var pct   = parseFloat($('#lcCommissionPct').val()) || 0;
                $('#lcCommission').val(rtVal && pct ? (rtVal * pct / 100).toFixed(2) : '');
            });
            $('#lcCommission').on('input', function () {
                var rtVal = parseFloat($('#lcRtValue').val()) || 0;
                var amt   = parseFloat($(this).val()) || 0;
                $('#lcCommissionPct').val(rtVal && amt ? (amt / rtVal * 100).toFixed(4) : '');
            });
            $('#lcCommissionPct').on('blur', function () {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) $(this).val(v);
            });

            $('#currency').on('change', function() {
                $('.fcy-label').text($(this).val());
            }).trigger('change');

            // Item qty × price = amount
            $(document).on('input', '.item-qty, .item-uprice', function() {
                var row = $(this).closest('tr');
                var qty = parseFloat(row.find('.item-qty').val()) || 0;
                var up = parseFloat(row.find('.item-uprice').val()) || 0;
                row.find('.item-amount').val((qty * up).toFixed(4));
            });

            // Other charge rows
            syncOtherChargesEmpty();
            $('#btnAddOtherCharge').on('click', () => addOtherChargeRow());
            $(document).on('input', '.other-charge-amount', syncOtherChargesTotal);
            $(document).on('click', '.btn-remove-other-charge', function() {
                $(this).closest('tr').remove();
                $('#otherChargesBody tr').each((i, tr) => $(tr).find('.other-charge-row-num').text(i + 1));
                syncOtherChargesTotal();
                syncOtherChargesEmpty();
            });

            // Payment rows
            $('#btnAddPayment').on('click', () => addPaymentRow());
            $(document).on('input', '.payment-amount', syncPaymentTotal);
            $(document).on('click', '.btn-remove-payment-row', function() {
                $(this).closest('tr').remove();
                $('#paymentBody tr').each((i, tr) => $(tr).find('.payment-row-num').text(i + 1));
                syncPaymentTotal();
            });

            // Item rows
            $('#btnAddItem').on('click', () => addItemRow());
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                $('#itemsBody tr').each((i, tr) => $(tr).find('.row-num').text(i + 1));
                syncItemsEmpty();
            });

            addItemRow();

            // Form submit
            $('#lcForm').on('submit', function(e) {
                e.preventDefault();
                if (!validateIdentification()) {
                    return;
                }

                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

                $.ajax({
                        url: '{{ route('nas-trading.lcs.store') }}',
                        method: 'POST',
                        data: $('#lcForm').serialize(),
                    })
                    .done(function(r) {
                        Swal.fire({
                                icon: 'success',
                                title: r.message,
                                timer: 1500,
                                showConfirmButton: false
                            })
                            .then(() => {
                                if (r.redirect) window.location.href = r.redirect;
                            });
                    })
                    .fail(function(xhr) {
                        $('#btnSave').prop('disabled', false).html(
                            '<i class="fa fa-save me-1"></i> Save LC');
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var msg = Object.values(errors).flat().join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: msg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: xhr.responseJSON?.message || 'Something went wrong.'
                            });
                        }
                    });
            });
        });
        // Add Importer modal
        $('#btnAddImporter').on('click', function () {
            $('#addImporterForm')[0].reset();
            $('#addImporterModal').modal('show');
        });

        $('#btnSaveImporter').on('click', function () {
            var name = $('#newImporterName').val().trim();
            if (!name) {
                $('#newImporterName').addClass('is-invalid').focus();
                return;
            }
            $('#newImporterName').removeClass('is-invalid');
            $('#btnSaveImporter').prop('disabled', true);

            $.ajax({
                url: '{{ route('nas-trading.importers.store') }}',
                method: 'POST',
                data: {
                    _token: $('[name=_token]').val(),
                    name: name,
                    bin_no: $('#newImporterBin').val().trim(),
                    address: $('#newImporterAddress').val().trim(),
                    status: 'Active',
                },
            }).done(function (res) {
                var opt = new Option(res.name, res.id, true, true);
                $('#importerSelect').append(opt).trigger('change');
                $('#addImporterModal').modal('hide');
            }).fail(function (xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to save importer.';
                Swal.fire({ icon: 'error', title: msg });
            }).always(function () {
                $('#btnSaveImporter').prop('disabled', false);
            });
        });
    </script>
@endpush

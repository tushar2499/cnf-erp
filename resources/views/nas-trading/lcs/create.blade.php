@extends('nas-trading.layouts.app')
@section('title', 'New LC Entry')
@push('styles')
<style>
.lc-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.lc-section-header { background:#1a6b60; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; letter-spacing:.03em; cursor:pointer; display:flex; justify-content:between; align-items:center; }
.lc-section-header:hover { background:#155a50; }
.lc-section-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.items-table th { background:#e9ecef; font-size:.77rem; padding:.4rem .5rem; }
.items-table td { padding:.3rem .5rem; vertical-align:middle; }
.items-table .form-control-sm, .items-table .form-select-sm { font-size:.78rem; }
.section-title { flex:1; }
.badge-auto { background:#0c2340; color:#fff; font-size:.72rem; padding:.2rem .5rem; border-radius:.25rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-file-contract me-2 text-info"></i> New LC Entry</h4>
    <a href="{{ route('nas-trading.lcs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<form id="lcForm">
    @csrf
    {{-- Section 1: Identification --}}
    <div class="lc-card">
        <div class="lc-section-header">
            <span class="section-title"><i class="fa fa-id-card me-2"></i> Section 1 — Identification</span>
        </div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">LC No (System)</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="Auto-generated" readonly>
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
                    <input type="text" name="pfi_no" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">PFI Date</label>
                    <input type="date" name="pfi_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC No (Bank)</label>
                    <input type="text" name="lc_no" class="form-control form-control-sm">
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
                    <input type="text" name="month" class="form-control form-control-sm" placeholder="e.g. January 2025">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipment From</label>
                    <input type="text" name="shipment_from" class="form-control form-control-sm">
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

    {{-- Section 2: Supplier & Goods --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-industry me-2"></i> Section 2 — Supplier & Goods</div>
        <div class="lc-section-body">
            <div class="row g-2">
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
                    <input type="text" name="supplier_country" id="supplierCountryDisplay" class="form-control form-control-sm bg-light" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Importer</label>
                    <select name="importer_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($importers as $imp)
                        <option value="{{ $imp->id }}">{{ $imp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customer PO Date</label>
                    <input type="date" name="customer_po_date" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label class="form-label">Item Description</label>
                    <textarea name="item_description" class="form-control form-control-sm" rows="2" placeholder="Brief description of goods"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: LC Financials --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-dollar-sign me-2"></i> Section 3 — LC Financials</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">PFI Value</label>
                    <input type="number" name="pfi_value" id="pfiValue" class="form-control form-control-sm" step="0.0001" placeholder="0.0000">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select form-select-sm">
                        <option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP">GBP</option><option value="CNY">CNY</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC OP Rate</label>
                    <input type="number" name="lc_open_rate" id="lcOpRate" class="form-control form-control-sm" step="0.0001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Margin %</label>
                    <input type="number" name="margin_percent" id="marginPct" class="form-control form-control-sm" step="0.0001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC Margin Amt</label>
                    <input type="number" name="lc_margin_amt" id="lcMarginAmt" class="form-control form-control-sm bg-light" readonly step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC Opening Cost BDT</label>
                    <input type="number" name="lc_open_cost_bdt" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Freight Value</label>
                    <input type="number" name="freight_value" id="freightValue" class="form-control form-control-sm" step="0.0001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">LC Value (calc)</label>
                    <input type="number" name="lc_value" id="lcValue" class="form-control form-control-sm bg-light" readonly step="0.0001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount BDT (calc)</label>
                    <input type="number" name="amount_bdt" id="amountBdt" class="form-control form-control-sm bg-light" readonly step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total LC Cost</label>
                    <input type="number" name="total_lc_cost" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Landed Cost</label>
                    <input type="number" name="landed_cost" class="form-control form-control-sm" step="0.01">
                </div>
            </div>
        </div>
    </div>

    {{-- Section 4: Document Retirement --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-file-alt me-2"></i> Section 4 — Document Retirement</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Doc RT Rate</label>
                    <input type="number" name="doc_rt_rate" class="form-control form-control-sm" step="0.0001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC RT Value</label>
                    <input type="number" name="lc_rt_value" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Charge Posting</label>
                    <input type="text" name="lc_charge_posting" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Section 5: Payment Tracking --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-money-check-alt me-2"></i> Section 5 — Payment Tracking</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Advance Received BDT</label>
                    <input type="number" name="advance_received_bdt" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Advance Date</label>
                    <input type="date" name="advance_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Advance Posting</label>
                    <input type="text" name="advance_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-3">
                    <label class="form-label">Rest Amount BDT</label>
                    <input type="number" name="rest_amount_bdt" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rest Amount Date</label>
                    <input type="date" name="rest_amount_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rest Amount Posting</label>
                    <input type="text" name="rest_amount_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total Received BDT</label>
                    <input type="number" name="total_received_bdt" class="form-control form-control-sm bg-light" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Closing Bill</label>
                    <input type="number" name="lc_closing_bill" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Closing Bill Date</label>
                    <input type="date" name="lc_closing_bill_date" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Section 6: Duty & Clearance --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-clipboard-check me-2"></i> Section 6 — Duty & Clearance</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Duty Advance</label>
                    <input type="number" name="duty_advance" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duty Advance Date</label>
                    <input type="date" name="duty_advance_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duty Advance Posting</label>
                    <input type="text" name="duty_advance_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-3">
                    <label class="form-label">Bill of Entry No</label>
                    <input type="text" name="bill_of_entry_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill of Entry Date</label>
                    <input type="date" name="bill_of_entry_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customs Duty</label>
                    <input type="number" name="customs_duty" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customs Duty Posting</label>
                    <input type="text" name="customs_duty_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CNF Party</label>
                    <input type="text" name="cnf_party" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CNF Total Cost</label>
                    <input type="number" name="cnf_total_cost" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CNF Cost Posting</label>
                    <input type="text" name="cnf_cost_posting" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Section 7: VAT / Tax / Sales --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-percent me-2"></i> Section 7 — VAT / Tax / Sales</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Payable / Receivable</label>
                    <input type="number" name="payable_receivable" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Received Amount</label>
                    <input type="number" name="received_amount" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Received Date</label>
                    <input type="date" name="received_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-3">
                    <label class="form-label">VAT Return</label>
                    <input type="number" name="vat_return" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">VAT Return Date</label>
                    <input type="date" name="vat_return_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">VAT Return Posting</label>
                    <input type="text" name="vat_return_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Income Tax</label>
                    <input type="number" name="income_tax" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bank Statement Amt</label>
                    <input type="number" name="bank_statement_amt" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Commission</label>
                    <input type="number" name="lc_commission" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Commission Date</label>
                    <input type="date" name="lc_commission_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales Amount</label>
                    <input type="number" name="sales_amount" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales Posting</label>
                    <input type="text" name="sales_posting" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">COSS Amount</label>
                    <input type="number" name="coss_amount" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">COSS Posting</label>
                    <input type="text" name="coss_posting" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Section 8: Bank & Docs --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-university me-2"></i> Section 8 — Bank & Documents</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Opening Bank</label>
                    <select name="opening_bank_id" class="form-select form-select-sm">
                        <option value="">Select Bank...</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}{{ $bank->branch ? ' - '.$bank->branch : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Port of Destination</label>
                    <select name="port_of_dest_id" class="form-select form-select-sm">
                        <option value="">Select Port...</option>
                        @foreach($ports as $port)
                        <option value="{{ $port->id }}">{{ $port->name }} ({{ $port->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Origin</label>
                    <input type="text" name="country_of_origin" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Mode</label>
                    <input type="text" name="payment_mode" class="form-control form-control-sm" placeholder="e.g. SWIFT, TT">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Insurance Amount</label>
                    <input type="number" name="insurance_amt" class="form-control form-control-sm" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cover Note</label>
                    <input type="text" name="cover_note" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Insurance Validity</label>
                    <input type="date" name="insurance_validity" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI No</label>
                    <input type="text" name="psi_no" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI Company</label>
                    <select name="psi_company_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($psiCompanies as $psi)
                        <option value="{{ $psi->id }}">{{ $psi->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comm. Currency</label>
                    <input type="text" name="comm_currency" class="form-control form-control-sm" placeholder="USD">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comm. Amount</label>
                    <input type="number" name="comm_amount" class="form-control form-control-sm" step="0.01">
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
                    <input type="text" name="sanction_types" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Third Party</label>
                    <input type="text" name="third_party" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- LC Line Items --}}
    <div class="lc-card">
        <div class="lc-section-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-boxes me-2"></i> Product Line Items</span>
            <button type="button" class="btn btn-sm btn-light py-0 px-2" id="btnAddItem" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Row</button>
        </div>
        <div class="lc-section-body p-0">
            <div style="overflow-x:auto">
                <table class="table table-bordered items-table mb-0 w-100" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="min-width:180px">Product</th>
                            <th style="width:90px">Code</th>
                            <th style="width:100px">HS Code</th>
                            <th style="width:80px">Qty</th>
                            <th style="width:70px">Unit</th>
                            <th style="width:80px">Weight</th>
                            <th style="width:70px">W.Unit</th>
                            <th style="width:100px">Unit Price</th>
                            <th style="width:100px">Amount</th>
                            <th style="width:60px">Curr.</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Save LC</button>
        <a href="{{ route('nas-trading.lcs.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var itemRowIdx = 0;

function addItemRow(data) {
    data = data || {};
    var idx = itemRowIdx++;
    var html = `
    <tr>
        <td class="text-center row-num">${idx + 1}</td>
        <td>
            <select class="form-select form-select-sm item-select" name="items[${idx}][item_id]" data-row="${idx}">
                <option value="">Type to search...</option>
                ${data.item_id ? `<option value="${data.item_id}" selected>${data.product_name || ''}</option>` : ''}
            </select>
            <input type="hidden" name="items[${idx}][product_name]" class="item-name" value="${data.product_name || ''}">
        </td>
        <td><input type="text" class="form-control form-control-sm item-code" name="items[${idx}][product_code]" value="${data.product_code || ''}" readonly></td>
        <td><input type="text" class="form-control form-control-sm item-hscode" name="items[${idx}][hs_code]" value="${data.hs_code || ''}"></td>
        <td><input type="number" class="form-control form-control-sm item-qty" name="items[${idx}][qty_count]" value="${data.qty_count || ''}" step="0.0001" min="0"></td>
        <td><input type="text" class="form-control form-control-sm item-unit" name="items[${idx}][qty_unit]" value="${data.qty_unit || ''}"></td>
        <td><input type="number" class="form-control form-control-sm" name="items[${idx}][weight]" value="${data.weight || ''}" step="0.0001" min="0"></td>
        <td><input type="text" class="form-control form-control-sm" name="items[${idx}][weight_unit]" value="${data.weight_unit || ''}"></td>
        <td><input type="number" class="form-control form-control-sm item-uprice" name="items[${idx}][unit_price]" value="${data.unit_price || ''}" step="0.0001" min="0"></td>
        <td><input type="number" class="form-control form-control-sm item-amount" name="items[${idx}][line_amount]" value="${data.line_amount || ''}" step="0.0001" readonly></td>
        <td>
            <select class="form-select form-select-sm" name="items[${idx}][currency]">
                ${['USD','EUR','GBP','CNY','BDT'].map(c => `<option value="${c}" ${(data.currency||'USD')===c?'selected':''}>${c}</option>`).join('')}
            </select>
        </td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-0" style="width:24px;height:24px"><i class="fa fa-times" style="font-size:.65rem"></i></button></td>
    </tr>`;
    $('#itemsBody').append(html);
    initItemSelect(idx);
}

function initItemSelect(idx) {
    $(`[name="items[${idx}][item_id]"]`).select2({
        width: '100%', placeholder: 'Search item...', allowClear: true, minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-trading.items.search') }}',
            dataType: 'json', delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
        }
    }).on('select2:select', function (e) {
        var d = e.params.data;
        var row = $(this).closest('tr');
        row.find('.item-name').val(d.text.split(' | ')[1] || d.text);
        row.find('.item-code').val(d.code || '');
        row.find('.item-hscode').val(d.hs_code || '');
        row.find('.item-unit').val(d.unit || '');
    }).on('select2:clear', function () {
        var row = $(this).closest('tr');
        row.find('.item-name,.item-code,.item-hscode,.item-unit').val('');
    });
}

$(function () {
    // Customer Select2
    $('#customerSelect').select2({
        width: '100%', placeholder: 'Search customer...', allowClear: true, minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-trading.lcs.search-customers') }}',
            dataType: 'json', delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
        }
    }).on('select2:select', function (e) {
        $('#customerName').val(e.params.data.text.split(' | ')[1] || e.params.data.text);
    });

    // Supplier Select2
    $('#supplierSelect').select2({
        width: '100%', placeholder: 'Search supplier...', allowClear: true, minimumInputLength: 1,
        ajax: {
            url: '{{ route('nas-trading.lcs.search-suppliers') }}',
            dataType: 'json', delay: 300,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
        }
    }).on('select2:select', function (e) {
        $('#supplierName').val(e.params.data.text.split(' | ')[1] || e.params.data.text);
        $('#supplierCountry, #supplierCountryDisplay').val(e.params.data.country || '');
    });

    // Calculations
    function calcFinancials() {
        var pfi    = parseFloat($('[name=pfi_value]').val()) || 0;
        var rate   = parseFloat($('[name=lc_open_rate]').val()) || 0;
        var margin = parseFloat($('[name=margin_percent]').val()) || 0;
        var freight= parseFloat($('[name=freight_value]').val()) || 0;
        var lcVal  = pfi + freight;
        var amtBdt = lcVal * rate;
        var marginAmt = amtBdt * margin / 100;
        $('#lcValue').val(lcVal.toFixed(4));
        $('#amountBdt').val(amtBdt.toFixed(2));
        $('#lcMarginAmt').val(marginAmt.toFixed(2));
    }
    $('[name=pfi_value],[name=lc_open_rate],[name=margin_percent],[name=freight_value]').on('input', calcFinancials);

    // Item qty × price = amount
    $(document).on('input', '.item-qty, .item-uprice', function () {
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.item-qty').val()) || 0;
        var up  = parseFloat(row.find('.item-uprice').val()) || 0;
        row.find('.item-amount').val((qty * up).toFixed(4));
    });

    $('#btnAddItem').on('click', () => addItemRow());
    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('tr').remove();
        $('#itemsBody tr').each((i, tr) => $(tr).find('.row-num').text(i + 1));
    });

    // Add first row
    addItemRow();

    // Form submit
    $('#lcForm').on('submit', function (e) {
        e.preventDefault();
        var customerId = $('[name=customer_id]').val() || $('#customerSelect').val();
        if (!customerId) { Swal.fire({ icon: 'warning', title: 'Please select a customer.' }); return; }
        if (!$('[name=pfi_no]').val().trim()) { Swal.fire({ icon: 'warning', title: 'PFI No is required.' }); return; }

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: '{{ route('nas-trading.lcs.store') }}',
            method: 'POST',
            data: $('#lcForm').serialize(),
        })
        .done(function (r) {
            Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false })
                .then(() => { if (r.redirect) window.location.href = r.redirect; });
        })
        .fail(function (xhr) {
            $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save LC');
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                var msg = Object.values(errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Validation Error', html: msg });
            } else {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        });
    });
});
</script>
@endpush

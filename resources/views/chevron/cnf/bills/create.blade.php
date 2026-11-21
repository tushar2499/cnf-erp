@extends('chevron.layouts.app')

@section('title', $bill ? 'Edit Bill' : 'New Bill')

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

        .bill-form-label {
            font-size: .7rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: .1rem;
        }

        .bill-input {
            font-size: .75rem;
            height: 28px;
            padding: .18rem .4rem;
        }

        .input-group>.bill-input {
            height: 28px;
            font-size: .75rem;
            padding: .18rem .4rem;
        }

        .bill-textarea {
            font-size: .75rem;
            padding: .18rem .4rem;
            resize: vertical;
        }

        #rowsTable th {
            background: #f1f3f5;
            font-size: .68rem;
            font-weight: 700;
            padding: .25rem .4rem;
            white-space: nowrap;
        }

        #rowsTable td {
            padding: .2rem .3rem;
            vertical-align: middle;
        }

        .totals-row {
            display: flex;
            align-items: center;
            padding: .3rem .65rem;
            border-bottom: 1px solid #eef0f2;
            gap: .5rem;
        }

        .totals-row:last-child {
            border-bottom: none;
        }

        .totals-label {
            font-weight: 600;
            color: #374151;
            font-size: .68rem;
            letter-spacing: .03em;
            white-space: nowrap;
            width: 120px;
            min-width: 120px;
            text-transform: uppercase;
        }

        .totals-input {
            flex: 1;
            min-width: 0;
            font-size: .72rem;
            height: 26px;
            padding: .15rem .45rem;
            border-radius: .25rem;
            background: #fff;
            border: 1px solid #ced4da;
        }

        .totals-input[readonly] {
            background: #e9ecef;
            color: #6c757d;
            border-color: #dee2e6;
            cursor: not-allowed;
            font-style: italic;
        }

        .totals-row-subtotal {
            background: #f1f5f9;
        }

        .totals-row-total {
            background: #e8f5f0;
        }

        .totals-row-net {
            background: #d1ece3;
        }

        .due-row {
            background: #fff0f0;
        }

        .due-row .totals-label {
            color: #dc2626;
            font-weight: 700;
        }

        .totals-divider {
            border-top: 2px solid #dee2e6;
            margin: 0;
        }

        .job-locked[readonly],
        .job-locked[readonly]:focus {
            background-color: #eef6f2;
            border-color: #b2d8c8;
            color: #2d6a4f;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Customer advance panel ── */
        .advance-panel {
            margin: .45rem .65rem;
            border: 1px solid #b2d8c8;
            border-radius: .35rem;
            background: #f4faf7;
            min-height: 6.5rem;
        }

        .advance-panel-head {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .6rem;
            border-bottom: 1px solid #d6ebe2;
            color: #0a4f3c;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .advance-note {
            padding: .55rem .6rem;
            color: #495057;
            font-size: .72rem;
        }

        .advance-note.is-error {
            color: #b42318;
        }

        .advance-line {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .5rem;
            padding: .22rem .6rem;
            font-size: .72rem;
        }

        .advance-line dt {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .advance-line dd {
            margin: 0;
            text-align: right;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .advance-collections {
            margin: .25rem .6rem .35rem;
            border: 1px solid #d6ebe2;
            border-radius: .3rem;
            background: #fff;
        }

        .advance-collections-title {
            padding: .25rem .5rem;
            border-bottom: 1px solid #d6ebe2;
            background: #eaf5f0;
            color: #0a4f3c;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .advance-collections-scroll {
            max-height: 8.5rem;
            overflow-y: auto;
        }

        .advance-collections-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .7rem;
            font-variant-numeric: tabular-nums;
        }

        .advance-collections-table th,
        .advance-collections-table td {
            padding: .2rem .5rem;
            white-space: nowrap;
        }

        .advance-collections-table thead th {
            position: sticky;
            top: 0;
            background: #fff;
            border-bottom: 1px solid #e3efe9;
            color: #4b5563;
            font-weight: 600;
        }

        .advance-collections-table tbody tr+tr td {
            border-top: 1px solid #f0f5f2;
        }

        .advance-collections-table tfoot th,
        .advance-collections-table tfoot td {
            border-top: 1px solid #d6ebe2;
            background: #f4faf7;
            font-weight: 700;
        }

        .advance-collections-table a {
            color: #0a4f3c;
            font-weight: 600;
            text-decoration: none;
        }

        .advance-collections-table a:hover,
        .advance-collections-table a:focus-visible {
            text-decoration: underline;
        }

        .advance-line-max {
            border-top: 1px solid #d6ebe2;
            background: #e8f5f0;
        }

        .advance-line-max dt,
        .advance-line-max dd {
            color: #0a4f3c;
            font-weight: 700;
        }

        .advance-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .35rem;
            padding: .4rem .6rem .5rem;
        }

        .advance-feedback {
            min-height: 1.35rem;
            padding: .1rem .65rem .35rem;
            color: #495057;
            font-size: .68rem;
        }

        .advance-feedback.is-error {
            color: #b42318;
            font-weight: 600;
        }

        .advance-feedback.is-warning {
            color: #8a5300;
            font-weight: 600;
        }

        /* ── Responsive ── */
        @media (max-width: 575.98px) {
            .advance-actions .btn {
                min-height: 38px;
            }

            .bill-input {
                height: 38px;
                font-size: .8rem;
                padding: .35rem .5rem;
            }

            .input-group>.bill-input {
                height: 38px;
                font-size: .8rem;
                padding: .35rem .5rem;
            }

            .bill-textarea {
                font-size: .8rem;
            }

            .totals-input {
                height: 36px;
                font-size: .75rem;
            }

            .totals-label {
                width: 95px;
                min-width: 95px;
                font-size: .62rem;
            }

            .form-header {
                font-size: .75rem;
            }

            .page-header-top {
                flex-wrap: wrap;
                gap: .4rem;
            }

            .page-header-top .page-title {
                order: -1;
                width: 100%;
                text-align: center;
            }

            .comm-rate-row {
                flex-wrap: wrap;
                gap: .3rem;
            }

            .comm-rate-row>input {
                width: 100% !important;
                flex: 1 1 auto !important;
            }
        }

        @media (min-width: 576px) and (max-width: 767.98px) {
            .bill-input {
                height: 34px;
                font-size: .78rem;
            }

            .input-group>.bill-input {
                height: 34px;
                font-size: .78rem;
            }

            .totals-label {
                width: 105px;
                min-width: 105px;
            }
        }
    </style>
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-2 page-header-top">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back To List
            </a>
        </div>
        <div class="fw-bold page-title" style="font-size:.9rem; color:#0a4f3c;">
            Bill Entry @if ($bill)
                <span class="ms-2 badge bg-light text-dark border">{{ $bill->bill_no }}</span>
            @endif
        </div>
        <div></div>
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
        action="{{ $bill ? route('chevron.cnf.bills.update', $bill->id) : route('chevron.cnf.bills.store') }}">
        @csrf
        @if ($bill)
            @method('PUT')
        @endif

        {{-- ═══ HEADER SECTION ═══ --}}
        <div class="section-card">
            <div class="form-header"><i class="fa fa-file-invoice me-1"></i> Bill Information</div>
            <div class="section-body">
                {{-- Row 1: Bill Type | Bill Date | Delivery Date --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Bill Type</div>
                        <select name="bill_type" class="form-select bill-input">
                            <option value="">-- Select --</option>
                            @foreach ($billTypes as $t)
                                <option value="{{ $t }}"
                                    {{ old('bill_type', $bill?->bill_type) === $t ? 'selected' : '' }}>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Bill Date <span class="text-danger">*</span></div>
                        <input type="date" name="bill_date" class="form-control bill-input"
                            value="{{ old('bill_date', $bill?->bill_date?->format('Y-m-d') ?? $today) }}" required>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Delivery Date</div>
                        <input type="date" name="delivery_date" id="deliveryDate" class="form-control bill-input job-locked"
                            value="{{ old('delivery_date', $bill?->delivery_date?->format('Y-m-d')) }}" readonly>
                    </div>
                </div>

                {{-- Row 2: Job No | Party Name | Address --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Job No <span class="text-danger">*</span></div>
                        <select name="job_id" id="jobSelect" class="form-select bill-input" style="width:100%">
                            @php($selectedJobId = old('job_id', $bill?->job_id))
                            @if ($selectedJobId)
                                <option value="{{ $selectedJobId }}" selected>
                                    {{ old('job_no', $bill?->job_no) }} — {{ old('party_name', $bill?->party_name) }}
                                </option>
                            @endif
                        </select>
                        <input type="hidden" name="job_no" id="jobNo" value="{{ old('job_no', $bill?->job_no) }}">
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Party Name</div>
                        <input type="text" name="party_name" id="partyName" class="form-control bill-input job-locked"
                            value="{{ old('party_name', $bill?->party_name) }}" placeholder="Party name" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Address</div>
                        <textarea name="party_address" id="partyAddress" class="form-control bill-textarea job-locked" rows="2" readonly>{{ old('party_address', $bill?->party_address) }}</textarea>
                    </div>
                </div>

                {{-- Row 3: Goods Description | Mate Code | P.O. No --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Goods Description</div>
                        <input type="text" name="goods_description" id="goodsDesc" class="form-control bill-input job-locked"
                            value="{{ old('goods_description', $bill?->goods_description) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Mate Code</div>
                        <input type="text" name="mate_code" id="mateCode" class="form-control bill-input job-locked"
                            value="{{ old('mate_code', $bill?->mate_code) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">P.O. No</div>
                        <input type="text" name="po_no" id="poNo" class="form-control bill-input job-locked"
                            value="{{ old('po_no', $bill?->po_no) }}" readonly>
                    </div>
                </div>

                {{-- Row 4: Quantity | Gross Weight | L.C. No --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Quantity</div>
                        <div class="input-group">
                            <input type="number" name="quantity" id="quantity" class="form-control bill-input job-locked"
                                step="0.001" value="{{ old('quantity', $bill?->quantity) }}" placeholder="0" readonly>
                            <input type="text" name="quantity_unit" id="quantityUnit"
                                class="form-control bill-input text-center job-locked" style="max-width:50px;"
                                value="{{ old('quantity_unit', $bill?->quantity_unit ?? 'KG') }}" readonly>
                            <input type="text" name="quantity_remark" class="form-control bill-input"
                                value="{{ old('quantity_remark', $bill?->quantity_remark) }}" placeholder="Remark">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Gross Weight</div>
                        <div class="input-group">
                            <input type="number" name="gross_weight" id="grossWeight" class="form-control bill-input job-locked"
                                step="0.001" value="{{ old('gross_weight', $bill?->gross_weight) }}" placeholder="0" readonly>
                            <input type="text" name="gross_weight_unit" id="grossWeightUnit"
                                class="form-control bill-input text-center job-locked" style="max-width:55px;"
                                value="{{ old('gross_weight_unit', $bill?->gross_weight_unit) }}" placeholder="Unit" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">L.C. No</div>
                        <div class="input-group">
                            <input type="text" name="lc_no" id="lcNo" class="form-control bill-input job-locked"
                                value="{{ old('lc_no', $bill?->lc_no) }}" placeholder="LC No" readonly>
                            <input type="text" name="lc_ref" id="lcRef" class="form-control bill-input job-locked"
                                style="max-width:110px;" value="{{ old('lc_ref', $bill?->lc_ref) }}"
                                placeholder="LCA No / Ref" readonly>
                        </div>
                    </div>
                </div>

                {{-- Row 5: B/E No | B/E Date | Assessable Value --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">B/E No</div>
                        <input type="text" name="be_no" id="beNo" class="form-control bill-input job-locked"
                            value="{{ old('be_no', $bill?->be_no) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">B/E Date</div>
                        <input type="date" name="be_date" id="beDate" class="form-control bill-input job-locked"
                            value="{{ old('be_date', $bill?->be_date?->format('Y-m-d')) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Assessable Value</div>
                        <input type="number" name="assessable_value" id="assessableValue"
                            class="form-control bill-input text-end job-locked" step="0.01"
                            value="{{ old('assessable_value', $assessableValue ?? $bill?->assessable_value) }}"
                            placeholder="0.00" readonly>
                    </div>
                </div>

                {{-- Row 6: Invoice No | Invoice Date | Invoice Value (original) + BDT --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Invoice No</div>
                        <div class="input-group">
                            <input type="text" name="invoice_no" id="invoiceNo" class="form-control bill-input job-locked"
                                value="{{ old('invoice_no', $bill?->invoice_no) }}" placeholder="Invoice No" readonly>
                            <input type="text" name="invoice_ref" id="invoiceRef"
                                class="form-control bill-input text-center job-locked" style="max-width:85px;"
                                value="{{ old('invoice_ref', $bill?->invoice_ref) }}" placeholder="HBL/HAWB" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Invoice Date</div>
                        <input type="date" name="invoice_date" id="invoiceDate" class="form-control bill-input job-locked"
                            value="{{ old('invoice_date', $bill?->invoice_date?->format('Y-m-d')) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Invoice Value (Foreign)</div>
                        <input type="number" name="invoice_value" id="invoiceValue"
                            class="form-control bill-input text-end job-locked" step="0.01"
                            value="{{ old('invoice_value', $bill?->invoice_value) }}" placeholder="0.00" readonly>
                    </div>
                </div>

                {{-- Row 7: Currency | Invoice Value BDT | B/L No --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="bill-form-label">Currency &amp; Rate</div>
                        <div class="input-group">
                            <input type="text" name="currency_type" id="currencyType"
                                class="form-control bill-input text-center job-locked" style="max-width:65px;"
                                value="{{ old('currency_type', $bill?->job?->currency_type ?? $bill?->currency_type) }}" placeholder="USD" readonly>
                            <span class="input-group-text px-2" style="font-size:.72rem;">@</span>
                            <input type="number" name="currency_rate" id="currencyRate"
                                class="form-control bill-input text-end job-locked" step="0.0001"
                                value="{{ old('currency_rate', $bill?->job?->currency_rate ?? $bill?->currency_rate) }}" placeholder="Rate" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">Invoice Value (BDT)</div>
                        <input type="number" name="invoice_value_bdt" id="invoiceValueBdt"
                            class="form-control bill-input text-end job-locked" step="0.01"
                            value="{{ old('invoice_value_bdt', $bill?->invoice_value_bdt) }}" placeholder="0.00" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="bill-form-label">B/L No</div>
                        <div class="input-group">
                            <input type="text" name="bl_no" id="blNo" class="form-control bill-input job-locked"
                                value="{{ old('bl_no', $bill?->bl_no) }}" placeholder="B/L No" readonly>
                            <input type="text" name="bl_ref" id="blRef"
                                class="form-control bill-input text-center job-locked" style="max-width:90px;"
                                value="{{ old('bl_ref', $bill?->bl_ref) }}" placeholder="MBL/MAWB" readonly>
                        </div>
                    </div>
                </div>

                {{-- Row 8: Remarks --}}
                <div class="row g-2">
                    <div class="col-md-12">
                        <div class="bill-form-label">Remarks</div>
                        <input type="text" name="remarks" class="form-control bill-input"
                            value="{{ old('remarks', $bill?->remarks) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ EXPENSE ROWS + TOTALS ═══ --}}
        <div class="row g-3">
            {{-- Expense Rows --}}
            <div class="col-12 col-lg-8">
                <div class="section-card">
                    <div class="form-header d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-list me-1"></i> Expense Details</span>
                        <button type="button" class="btn btn-sm btn-light py-0 px-2" id="addRow">
                            <i class="fa fa-plus me-1"></i> Add Row
                        </button>
                    </div>
                    <div class="section-body p-0">
                        <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="rowsTable" style="min-width:820px;">
                            <thead>
                                <tr>
                                    <th style="width:35px">SL</th>
                                    <th style="width:30px"></th>
                                    <th style="min-width:160px;">Expense Category</th>
                                    <th style="min-width:150px;">Particular Info (Head)</th>
                                    <th style="width:85px">Rate</th>
                                    <th style="width:75px">Qty</th>
                                    <th style="width:110px">Amount</th>
                                    <th style="min-width:140px;">Note</th>
                                </tr>
                            </thead>
                            <tbody id="rowsBody"></tbody>
                        </table>
                        </div>{{-- /table-responsive --}}
                    </div>
                </div>
            </div>

            {{-- Totals --}}
            <div class="col-12 col-lg-4">
                <div class="section-card">
                    <div class="form-header"><i class="fa fa-calculator me-1"></i> Summary</div>
                    <div class="section-body p-0">
                        {{-- Sub Total --}}
                        <div class="totals-row totals-row-subtotal">
                            <span class="totals-label">Sub Total</span>
                            <input type="number" name="sub_total" id="subTotal"
                                class="form-control totals-input text-end" step="0.01"
                                value="{{ old('sub_total', $bill?->sub_total ?? 0) }}" readonly>
                        </div>
                        <hr class="totals-divider">
                        {{-- Commission On --}}
                        <div class="totals-row">
                            <span class="totals-label">Commission On</span>
                            <select name="commission_on" id="commissionOn" class="form-select totals-input"
                                style="font-size:.7rem;">
                                @foreach ($commissionOnOptions as $opt)
                                    <option value="{{ $opt }}"
                                        {{ old('commission_on', $bill?->commission_on ?? 'ASSESSABLE') === $opt ? 'selected' : '' }}>
                                        {{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Comm Rate + Amount --}}
                        <div class="totals-row">
                            <span class="totals-label">Comm. Rate %</span>
                            <div class="d-flex gap-1 comm-rate-row" style="flex:1; min-width:0;">
                                <input type="number" name="commission_rate" id="commissionRate"
                                    class="form-control totals-input text-end" step="0.01"
                                    value="{{ in_array(old('commission_on', $bill?->commission_on ?? 'ASSESSABLE'), ['MINIMUM', 'MAXIMUM']) ? '0' : old('commission_rate', $bill?->commission_rate) }}"
                                    placeholder="0.00" style="width:90px; flex:0 0 90px;"
                                    {{ in_array(old('commission_on', $bill?->commission_on ?? 'ASSESSABLE'), ['MINIMUM', 'MAXIMUM']) ? 'readonly' : '' }}>
                                <input type="number" name="commission_amount" id="commissionAmount"
                                    class="form-control totals-input text-end" step="0.01"
                                    value="{{ old('commission_amount', $bill?->commission_amount ?? 0) }}"
                                    placeholder="Amount" style="flex:1; min-width:0;"
                                    {{ in_array(old('commission_on', $bill?->commission_on ?? 'ASSESSABLE'), ['MINIMUM', 'MAXIMUM']) ? '' : 'readonly' }}>
                            </div>
                        </div>
                        <hr class="totals-divider">
                        {{-- Total Payable --}}
                        <div class="totals-row totals-row-total">
                            <span class="totals-label">Total Payable</span>
                            <input type="number" name="total_payable" id="totalPayable"
                                class="form-control totals-input text-end fw-semibold" step="0.01"
                                value="{{ old('total_payable', $bill?->total_payable ?? 0) }}" readonly>
                        </div>
                        {{-- Deductions --}}
                        <div class="totals-row">
                            <span class="totals-label">Less Duty &amp; Tax</span>
                            <input type="number" name="less_customs_duty_tax" id="lessDutyTax"
                                class="form-control totals-input text-end" step="0.01"
                                value="{{ old('less_customs_duty_tax', $bill?->less_customs_duty_tax ?? 0) }}"
                                placeholder="0.00">
                        </div>
                        <div class="totals-row">
                            <span class="totals-label">Income Tax C&amp;F</span>
                            <input type="number" name="income_tax_cnf_com" id="incomeTax"
                                class="form-control totals-input text-end" step="0.01"
                                value="{{ old('income_tax_cnf_com', $bill?->income_tax_cnf_com ?? 0) }}"
                                placeholder="0.00">
                        </div>
                        <hr class="totals-divider">
                        {{-- Net Payable --}}
                        <div class="totals-row totals-row-net">
                            <span class="totals-label">Net Payable</span>
                            <input type="number" name="net_payable" id="netPayable"
                                class="form-control totals-input text-end fw-bold" step="0.01"
                                value="{{ old('net_payable', $bill?->net_payable ?? 0) }}" readonly>
                        </div>
                        <hr class="totals-divider">
                        {{-- Customer advance: job-wise collection, total balance, and what this bill may use --}}
                        <div class="advance-panel" id="advancePanel" aria-live="polite" aria-busy="false">
                            <div class="advance-panel-head">
                                <i class="fa fa-wallet" aria-hidden="true"></i> Customer Advance
                                <span id="advanceSpinner" class="spinner-border spinner-border-sm ms-auto d-none"
                                    role="status"><span class="visually-hidden">Loading advance</span></span>
                            </div>
                            <div class="advance-note" id="advanceNote">Select a job to see the customer's advance.</div>
                            <div class="d-none" id="advanceStats">
                                <dl class="mb-0">
                                    <div class="advance-line">
                                        <dt>Customer</dt>
                                        <dd id="advCustomer">—</dd>
                                    </div>
                                </dl>
                                {{-- Date-wise advance collected for the selected job; hidden when there is none --}}
                                <div class="advance-collections d-none" id="advJobSection" role="region"
                                    aria-labelledby="advJobTitle">
                                    <div class="advance-collections-title" id="advJobTitle">Advance collected for this job
                                    </div>
                                    <div class="advance-collections-scroll" tabindex="0">
                                        <table class="advance-collections-table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Receipt</th>
                                                    <th scope="col" class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="advJobRows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th scope="row" colspan="2">Total collected</th>
                                                    <td class="text-end" id="advJobTotal">0.00</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <dl class="mb-0">
                                    <div class="advance-line">
                                        <dt>Total advance balance</dt>
                                        <dd id="advTotal">0.00</dd>
                                    </div>
                                    <div class="advance-line advance-line-max">
                                        <dt>Can apply to this bill</dt>
                                        <dd id="advMax">0.00</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="advance-actions d-none" id="advanceActions">
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2"
                                    id="useJobAdvance">Use job advance</button>
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2"
                                    id="useMaxAdvance">Use maximum</button>
                                <a href="#" class="btn btn-sm btn-link py-0 px-1 d-none" id="advanceStatement"
                                    target="_blank" rel="noopener">View statement</a>
                            </div>
                        </div>
                        {{-- Advance --}}
                        <div class="totals-row">
                            <label class="totals-label" for="advanceAmount">Advance Amount</label>
                            <input type="number" name="advance_amount" id="advanceAmount"
                                class="form-control totals-input text-end {{ $errors->has('advance_amount') ? 'is-invalid' : '' }}"
                                step="0.01" min="0" aria-describedby="advanceFeedback"
                                aria-invalid="{{ $errors->has('advance_amount') ? 'true' : 'false' }}"
                                value="{{ old('advance_amount', $bill?->advance_amount ?? 0) }}" placeholder="0.00">
                        </div>
                        <div class="advance-feedback {{ $errors->has('advance_amount') ? 'is-error' : '' }}"
                            id="advanceFeedback" role="status">{{ $errors->first('advance_amount') }}</div>
                        <hr class="totals-divider">
                        {{-- Due Amount --}}
                        <div class="totals-row due-row">
                            <span class="totals-label">Due Amount</span>
                            <input type="number" name="due_amount" id="dueAmount"
                                class="form-control totals-input text-end fw-bold text-danger" step="0.01"
                                value="{{ old('due_amount', $bill?->due_amount ?? 0) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-3 mb-4">
            <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-sm btn-success px-4">
                <i class="fa fa-save me-1"></i> {{ $bill ? 'Update Bill' : 'Save Bill' }}
            </button>
        </div>

    </form>

    {{-- Row template --}}
    <template id="rowTemplate">
        <tr>
            <td class="sl-no text-center fw-bold" style="font-size:.72rem;"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-row"><i
                        class="fa fa-times"></i></button>
            </td>
            <td>
                <select name="rows[0][expense_category_id]" class="form-select form-select-sm cat-select"
                    style="font-size:.72rem;">
                    <option value="">-- Category --</option>
                    @foreach ($expenseCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="rows[0][expense_head_id]" class="form-select form-select-sm head-select"
                    style="font-size:.72rem;">
                    <option value="">-- Head --</option>
                    @foreach ($expenseHeads as $h)
                        <option value="{{ $h->id }}" data-cat="{{ $h->expense_category_id }}">
                            {{ $h->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="rows[0][rate]" class="form-control form-control-sm row-rate text-end"
                    step="0.01" placeholder="—" style="font-size:.72rem;">
            </td>
            <td>
                <input type="number" name="rows[0][qty]" class="form-control form-control-sm row-qty text-end"
                    step="0.001" placeholder="—" style="font-size:.72rem;">
            </td>
            <td>
                <input type="number" name="rows[0][amount]" class="form-control form-control-sm row-amount text-end"
                    step="0.01" value="0" style="font-size:.72rem;">
            </td>
            <td>
                <input type="text" name="rows[0][note]" class="form-control form-control-sm"
                    style="font-size:.72rem;">
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        var allHeads = @json($expenseHeadsJson);
        var existingRows = @json($existingRows);
        var billId = @json($bill?->id);
        var advanceInfoUrl = @json(route('chevron.cnf.bills.advance-info'));
        var advanceSeq = 0;
        var advanceState = emptyAdvance('idle');

        function emptyAdvance(status) {
            return {
                status: status, // idle | loading | ready | error
                customerId: null,
                customerName: '',
                jobCollections: [],
                jobAdvance: 0,
                totalAdvance: 0,
                available: 0,
                statementUrl: null
            };
        }

        // Date-wise advance receipts of the selected job; the whole section stays hidden when there are none.
        function renderJobCollections(collections, total) {
            var $rows = $('#advJobRows').empty();

            collections.forEach(function(c) {
                $rows.append(
                    $('<tr>').append(
                        $('<td>').text(c.date || '—'),
                        $('<td>').append(c.receipt_url ?
                            $('<a>', {
                                href: c.receipt_url,
                                target: '_blank',
                                rel: 'noopener',
                                text: c.receipt_no
                            }) :
                            document.createTextNode(c.receipt_no)),
                        $('<td class="text-end">').text(money(c.amount))
                    )
                );
            });

            $('#advJobTotal').text(money(total));
            $('#advJobSection').toggleClass('d-none', collections.length === 0);
        }

        function money(v) {
            return (parseFloat(v) || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Most advance this bill can use: the customer's available advance, never more than the net payable.
        function advanceCap() {
            var net = parseFloat($('#netPayable').val()) || 0;
            return Math.max(0, Math.min(advanceState.available, net));
        }

        function loadAdvance(jobId) {
            var seq = ++advanceSeq;

            if (!jobId) {
                advanceState = emptyAdvance('idle');
                renderAdvance();
                return;
            }

            advanceState = emptyAdvance('loading');
            renderAdvance();

            $.getJSON(advanceInfoUrl, { job_id: jobId, bill_id: billId || '' })
                .done(function(d) {
                    if (seq !== advanceSeq) return;
                    advanceState = {
                        status: 'ready',
                        customerId: d.customer_id,
                        customerName: d.customer_name || '',
                        jobCollections: d.job_collections || [],
                        jobAdvance: parseFloat(d.job_advance) || 0,
                        totalAdvance: parseFloat(d.total_advance) || 0,
                        available: parseFloat(d.available) || 0,
                        statementUrl: d.statement_url
                    };
                })
                .fail(function() {
                    if (seq !== advanceSeq) return;
                    advanceState = emptyAdvance('error');
                })
                .always(function() {
                    if (seq !== advanceSeq) return;
                    renderAdvance();
                });
        }

        function renderAdvance() {
            var s = advanceState;
            var cap = advanceCap();
            var loading = s.status === 'loading';
            var linked = s.status === 'ready' && s.customerId;
            var note = '';
            var noteIsError = false;

            if (s.status === 'idle') {
                note = 'Select a job to see the customer\'s advance.';
            } else if (loading) {
                note = 'Checking the customer\'s advance…';
            } else if (s.status === 'error') {
                note = 'Could not load the customer\'s advance. Select the job again to retry.';
                noteIsError = true;
            } else if (!s.customerId) {
                note = 'This job has no linked customer, so no advance can be applied.';
            }

            $('#advancePanel').attr('aria-busy', loading ? 'true' : 'false');
            $('#advanceSpinner').toggleClass('d-none', !loading);
            $('#advanceNote').text(note).toggleClass('d-none', !!linked).toggleClass('is-error', noteIsError);
            $('#advanceStats').toggleClass('d-none', !linked);
            $('#advanceActions').toggleClass('d-none', !linked);

            if (linked) {
                $('#advCustomer').text(s.customerName || '—');
                renderJobCollections(s.jobCollections, s.jobAdvance);
                $('#advTotal').text(money(s.totalAdvance));
                $('#advMax').text(money(cap));
                $('#useJobAdvance')
                    .toggleClass('d-none', s.jobCollections.length === 0)
                    .prop('disabled', Math.min(s.jobAdvance, cap) <= 0);
                $('#useMaxAdvance').prop('disabled', cap <= 0);
                $('#advanceStatement').toggleClass('d-none', !s.statementUrl).attr('href', s.statementUrl || '#');
            }

            validateAdvance();
        }

        // Inline check of the advance field; returns false while it holds an amount the bill cannot use.
        function validateAdvance(warning) {
            var s = advanceState;
            var $input = $('#advanceAmount');
            var $feedback = $('#advanceFeedback');
            var value = parseFloat($input.val()) || 0;
            var net = parseFloat($('#netPayable').val()) || 0;
            var error = '';
            var hint = '';

            if (s.status === 'loading') {
                hint = 'Checking available advance…';
            } else if (s.status === 'idle') {
                if (value > 0) {
                    error = 'Select a job before applying advance.';
                }
            } else if (s.status === 'ready') {
                if (value < 0) {
                    error = 'Advance cannot be negative.';
                } else if (value - Math.max(net, 0) > 0.004) {
                    error = 'Advance cannot exceed the net payable (BDT ' + money(net) + ').';
                } else if (value - s.available > 0.004) {
                    error = s.customerId ?
                        'Only BDT ' + money(s.available) + ' of advance is available for this customer.' :
                        'No customer advance is available for this job.';
                } else {
                    hint = advanceCap() > 0 ?
                        'You can apply up to BDT ' + money(advanceCap()) + ' on this bill.' :
                        'No advance available to apply.';
                }
            }

            $input.toggleClass('is-invalid', !!error).attr('aria-invalid', error ? 'true' : 'false');
            $feedback
                .toggleClass('is-error', !!error)
                .toggleClass('is-warning', !error && !!warning)
                .text(error || warning || hint);

            return !error;
        }

        function setAdvance(amount) {
            $('#advanceAmount').val((Math.max(0, amount)).toFixed(2));
            recalcAll();
        }

        function initRowSelects($row) {
            $row.find('.cat-select').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '-- Category --' });
            $row.find('.head-select').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '-- Head --' });
        }

        $(function() {
            // Job No Select2 AJAX
            $('#jobSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Enter min 3 characters...',
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route('chevron.cnf.bills.search-jobs') }}',
                    dataType: 'json',
                    delay: 300,
                    data: d => ({
                        q: d.term
                    }),
                    processResults: d => ({
                        results: d
                    }),
                },
            }).on('select2:select', function(e) {
                var d = e.params.data;
                $('#jobNo').val(d.job_no || '');
                $('#deliveryDate').val(d.delivery_date || '');
                $('#partyName').val(d.party_name || '');
                $('#partyAddress').val(d.party_address || '');
                $('#goodsDesc').val(d.goods_name || '');
                $('#mateCode').val(d.mate_code || '');
                $('#poNo').val(d.po_no || '');
                $('#quantity').val(d.quantity || '');
                $('#quantityUnit').val(d.quantity_unit || 'KG');
                $('#grossWeight').val(d.gross_weight || '');
                $('#grossWeightUnit').val(d.gross_weight_unit || '');
                $('#lcNo').val(d.lc_no || '');
                $('#lcRef').val(d.lc_ref || '');
                $('#beNo').val(d.be_no || '');
                $('#beDate').val(d.be_date || '');
                $('#assessableValue').val(d.assessable_value || '');
                $('#invoiceNo').val(d.invoice_no || '');
                $('#invoiceRef').val(d.invoice_ref || '');
                $('#invoiceDate').val(d.invoice_date || '');
                $('#invoiceValueBdt').val(d.invoice_value_bdt || '');
                $('#blNo').val(d.bl_no || '');
                $('#blRef').val(d.bl_ref || '');
                $('#invoiceValue').val(d.invoice_value || '');
                $('#currencyType').val(d.currency_type || '');
                $('#currencyRate').val(d.currency_rate || '');
                $('#advanceAmount').val('0.00'); // advance belongs to the previous job's customer
                loadAdvance(d.id);
                recalcAll();
            });

            // Load existing rows or start with 1 blank
            if (existingRows.length > 0) {
                existingRows.forEach(function(r) {
                    addRow(r);
                });
            } else {
                addRow();
            }

            // Add row button
            $('#addRow').on('click', function() {
                addRow();
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('#rowsBody tr').length <= 1) return;
                $(this).closest('tr').remove();
                reindex();
                recalcAll();
            });

            // Rate × Qty → auto-fill Amount
            $(document).on('input', '.row-rate, .row-qty', function() {
                var $tr = $(this).closest('tr');
                var rate = parseFloat($tr.find('.row-rate').val()) || 0;
                var qty = parseFloat($tr.find('.row-qty').val()) || 0;
                if (rate > 0 && qty > 0) {
                    $tr.find('.row-amount').val((rate * qty).toFixed(2));
                }
                recalcAll();
            });

            // Amount change → recalc
            $(document).on('input', '.row-amount', recalcAll);

            // Commission rate / on change
            $('#commissionRate, #commissionOn, #assessableValue, #invoiceValueBdt').on('input change', recalcAll);
            $('#commissionOn').on('change', function() {
                var manual = ['MINIMUM', 'MAXIMUM'].includes($(this).val());
                if (manual) {
                    $('#commissionRate').attr('readonly', 'readonly').val(0);
                    $('#commissionAmount').removeAttr('readonly');
                } else {
                    $('#commissionRate').removeAttr('readonly');
                    $('#commissionAmount').attr('readonly', 'readonly').val(0);
                }
                recalcAll();
            });
            $('#commissionAmount').on('input', recalcAll);

            // Less duty, income tax, advance change
            $('#lessDutyTax, #incomeTax, #advanceAmount').on('input', recalcAll);

            // Advance: quick fills, limit to what the bill can use on blur, block submit when over the limit
            $('#useJobAdvance').on('click', function() {
                setAdvance(Math.min(advanceState.jobAdvance, advanceCap()));
            });
            $('#useMaxAdvance').on('click', function() {
                setAdvance(advanceCap());
            });
            $('#advanceAmount').on('change', function() {
                var value = parseFloat($(this).val()) || 0;
                var cap = advanceCap();

                if (advanceState.status === 'ready' && value - cap > 0.004) {
                    $(this).val(cap.toFixed(2));
                    recalcAll();
                    validateAdvance('Limited to BDT ' + money(cap) + ', the most this bill can use.');
                }
            });
            $('#advanceAmount').closest('form').on('submit', function(e) {
                if (!validateAdvance()) {
                    e.preventDefault();
                    $('#advanceAmount').trigger('focus');
                }
            });

            // Category filter heads
            $(document).on('change', '.cat-select', function() {
                filterHeads($(this).closest('tr'));
            });

            // Head select → fill default amount
            $(document).on('change', '.head-select', function() {
                var headId = parseInt($(this).val());
                var head = allHeads.find(function(h) {
                    return h.id === headId;
                });
                if (head) {
                    $(this).closest('tr').find('.row-amount').val(parseFloat(head.amount) || 0);
                    recalcAll();
                }
            });

            recalcAll();
            loadAdvance($('#jobSelect').val());
        });

        function addRow(data) {
            var tmpl = document.getElementById('rowTemplate').content.cloneNode(true);
            var $tr = $(tmpl.querySelector('tr'));
            $('#rowsBody').append($tr);
            var $row = $('#rowsBody tr:last');

            if (data) {
                $row.find('.cat-select').val(data.expense_category_id);
                filterHeads($row);
                $row.find('.head-select').val(data.expense_head_id);
                $row.find('.row-rate').val(data.rate || '');
                $row.find('.row-qty').val(data.qty || '');
                $row.find('.row-amount').val(data.amount);
                $row.find('input[type=text]').last().val(data.note);
            }
            reindex();
            initRowSelects($row);
        }

        function reindex() {
            $('#rowsBody tr').each(function(i) {
                var $tr = $(this);
                $tr.find('.sl-no').text(i + 1);
                $tr.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/rows\[\d+\]/, 'rows[' + i + ']'));
                });
            });
        }

        function filterHeads($row) {
            var catId = parseInt($row.find('.cat-select').val());
            var $head = $row.find('.head-select');
            var curVal = $head.val();
            $head.empty().append('<option value="">-- Head --</option>');
            allHeads.forEach(function(h) {
                if (!catId || h.cat == catId) {
                    $head.append('<option value="' + h.id + '">' + h.name + '</option>');
                }
            });
            $head.val(curVal).trigger('change');
        }

        function recalcAll() {
            // Sub total
            var subTotal = 0;
            $('.row-amount').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });
            $('#subTotal').val(subTotal.toFixed(2));

            // Commission
            var commOn = $('#commissionOn').val();
            var commAmt;
            if (['MINIMUM', 'MAXIMUM'].includes(commOn)) {
                commAmt = parseFloat($('#commissionAmount').val()) || 0;
            } else {
                var base = commOn === 'INVOICE VALUE' ?
                    (parseFloat($('#invoiceValueBdt').val()) || 0) :
                    (parseFloat($('#assessableValue').val()) || 0);
                var rate = parseFloat($('#commissionRate').val()) || 0;
                commAmt = base * rate / 100;
                $('#commissionAmount').val(commAmt.toFixed(2));
            }

            // Total payable
            var totalPayable = subTotal + commAmt;
            $('#totalPayable').val(totalPayable.toFixed(2));

            // Net payable
            var lessDuty = parseFloat($('#lessDutyTax').val()) || 0;
            var incomeTax = parseFloat($('#incomeTax').val()) || 0;
            var netPayable = totalPayable - lessDuty - incomeTax;
            $('#netPayable').val(netPayable.toFixed(2));

            // Due amount
            var advance = parseFloat($('#advanceAmount').val()) || 0;
            var dueAmount = netPayable - advance;
            $('#dueAmount').val(dueAmount.toFixed(2));

            // The net payable caps the advance, so keep the panel and field check in step with it
            if (advanceState.status === 'ready') {
                renderAdvance();
            } else {
                validateAdvance();
            }
        }
    </script>
@endpush

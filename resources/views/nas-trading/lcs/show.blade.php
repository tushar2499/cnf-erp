@extends('nas-trading.layouts.app')
@section('title', 'LC — ' . $lc->lc_no_system)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; }
.info-header {
    background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.82rem; font-weight:700;
    border-radius:.5rem .5rem 0 0; display:flex; align-items:center; justify-content:space-between;
    cursor:pointer; user-select:none;
}
.info-header:hover { background:#17375e; }
.info-header .chevron { transition:transform .2s ease; font-size:.75rem; opacity:.8; flex-shrink:0; }
.info-header.collapsed .chevron { transform:rotate(-90deg); }
.info-header.collapsed { border-radius:.5rem; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.75rem; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.1rem; }
.info-value { font-size:.85rem; color:#111; font-weight:500; }
.exp-table th { background:#1a6b60; color:#fff; font-size:.77rem; padding:.4rem .6rem; }
.exp-table td { font-size:.8rem; padding:.35rem .6rem; vertical-align:middle; }
.pay-table th { background:#17375e; color:#fff; font-size:.77rem; padding:.35rem .6rem; }
.pay-table td { font-size:.8rem; padding:.3rem .6rem; vertical-align:middle; }
.badge-posting { font-size:.7rem; }

/* Print button inside section headers */
.btn-print-section {
    background:transparent; border:1px solid rgba(255,255,255,.4); color:#fff;
    padding:2px 8px; font-size:.68rem; border-radius:.25rem; opacity:.75;
    cursor:pointer; line-height:1.5; transition:opacity .15s, background .15s;
}
.btn-print-section:hover { opacity:1; background:rgba(255,255,255,.18); color:#fff; }

/* Screen-hidden print elements */
.print-doc-header { display:none; }

/* ── Print letterhead styles (used only inside @media print) ── */
.pdh-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.55rem; }
.pdh-brand .pdh-company { font-size:18pt; font-weight:800; color:#0c2340; line-height:1.1; letter-spacing:-.01em; }
.pdh-brand .pdh-division { font-size:8pt; font-weight:700; color:#17375e; text-transform:uppercase; letter-spacing:.1em; margin-top:.1rem; }
.pdh-docinfo { text-align:right; }
.pdh-docinfo .pdh-doctitle { font-size:10pt; font-weight:700; color:#0c2340; text-transform:uppercase; letter-spacing:.08em; }
.pdh-docinfo .pdh-docmeta { font-size:7.5pt; color:#4b5563; margin-top:.25rem; }
.pdh-docinfo .pdh-docmeta span { margin-left:.75rem; }
.pdh-customer-bar {
    background:#f1f5f9; border-left:3px solid #0c2340;
    padding:.3rem .7rem; font-size:8pt; color:#374151;
    display:flex; gap:2rem; margin-bottom:.55rem;
    -webkit-print-color-adjust:exact; print-color-adjust:exact;
}
.pdh-rule { border-top:2px solid #0c2340; margin-bottom:.9rem; }

@media print {
    /* ── Page setup ── */
    @page { size:A4; margin:12mm 10mm; }

    /* ── Hide app chrome ── */
    nav.top-navbar,
    .mobile-context-bar,
    aside.sidebar,
    footer,
    .no-print,
    .btn-print-section,
    .btn-del-exp,
    #btnAddExpense,
    .chevron,
    .modal { display:none!important; }

    /* ── Full-width content area; reset min-height so it never forces a blank page ── */
    html, body { height:auto!important; min-height:0!important; }
    main.main-content { margin-left:0!important; padding:.5rem 0!important; width:100%!important; min-height:0!important; height:auto!important; }

    /* ── Show letterhead + footer ── */
    .print-doc-header { display:block!important; }

    /* ── Force all collapsed sections open ── */
    .collapse { display:block!important; height:auto!important; overflow:visible!important; }

    /* ── Flatten outer 2-col to single column ── */
    .col-md-8, .col-md-4 { width:100%!important; flex:none!important; max-width:100%!important; padding:0!important; }
    .row.g-3 { display:block!important; }

    /* ── Section cards ── */
    .info-card { page-break-inside:avoid; break-inside:avoid; margin-bottom:.6rem; border:1px solid #c8d3e0!important; }

    /* ── Section headers ── */
    .info-header {
        background:#0c2340!important; color:#fff!important;
        font-size:8.5pt!important; padding:.35rem .75rem!important;
        border-radius:0!important;
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }

    /* ── Field label / value typography ── */
    .info-label { font-size:6.5pt!important; color:#6b7280!important; }
    .info-value { font-size:8.5pt!important; color:#111!important; }

    /* ── Table borders (critical — tables look naked without them) ── */
    .exp-table, .pay-table { border-collapse:collapse!important; width:100%!important; font-size:8pt!important; }
    .exp-table th, .exp-table td,
    .pay-table th, .pay-table td { border:1px solid #c8d3e0!important; padding:.28rem .5rem!important; }
    .exp-table th { background:#1a6b60!important; color:#fff!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .pay-table th { background:#17375e!important; color:#fff!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

    /* ── Quick Summary — highlight as a summary document ── */
    .info-card:has(#sec-summary) .info-body {
        background:#f0f6ff!important;
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }

    /* ── Section-wise print mode ── */
    body.lc-print-section .info-card { display:none!important; }
    body.lc-print-section .info-card.lc-print-active { display:block!important; }
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4 class="mb-0">
        <i class="fa fa-file-contract me-2 text-info"></i>
        {{ $lc->lc_no_system }}
        <small class="ms-2">
            @if($lc->lc_status === 'Open') <span class="badge bg-success">Open</span>
            @elseif($lc->lc_status === 'Closed') <span class="badge bg-secondary">Closed</span>
            @elseif($lc->lc_status === 'Cancelled') <span class="badge bg-danger">Cancelled</span>
            @else <span class="badge bg-warning text-dark">{{ $lc->lc_status }}</span>
            @endif
        </small>
    </h4>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-light" id="btnExpandAll" title="Expand all sections" style="font-size:.75rem"><i class="fa fa-expand-alt me-1"></i>Expand All</button>
        <button class="btn btn-sm btn-outline-light" id="btnCollapseAll" title="Collapse all sections" style="font-size:.75rem"><i class="fa fa-compress-alt me-1"></i>Collapse All</button>
        <button class="btn btn-sm btn-outline-warning no-print" id="btnPrintAll" title="Print full LC" style="font-size:.75rem"><i class="fa fa-print me-1"></i>Print All</button>
        <a href="{{ route('nas-trading.lcs.edit', $lc->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i> Edit</a>
        <a href="{{ route('nas-trading.lcs.generate-bill', $lc->id) }}" class="btn btn-sm btn-success"><i class="fa fa-file-invoice-dollar me-1"></i> Generate Bill</a>
        <a href="{{ route('nas-trading.lcs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

@php
    $bank       = $banks->firstWhere('id', $lc->opening_bank_id);
    $port       = $ports->firstWhere('id', $lc->port_of_dest_id);
    $importer   = $importers->firstWhere('id', $lc->importer_id);
    $psiCompany = $psiCompanies->firstWhere('id', $lc->psi_company_id);
    $dash = '—';
    function fmtDate($d) { return $d?->format('d-M-Y') ?? '—'; }
    function fmtAmt($v, $prefix = 'BDT', $dec = 2) { return $v ? $prefix . ' ' . number_format((float)$v, $dec) : '—'; }

    $totalLcCost = (float)($lc->lc_rt_value ?? 0)
                 + (float)($lc->bank_charge ?? 0)
                 + (float)($lc->insurance_amt ?? 0)
                 + (float)($lc->credit_report_charge ?? 0)
                 + (float)($lc->other_charges ?? 0);
@endphp

{{-- Letterhead — visible only when printing --}}
<div class="print-doc-header">
    <div class="pdh-top">
        <div class="pdh-brand">
            <div class="pdh-company">NAS Group</div>
            <div class="pdh-division">NAS Trading</div>
        </div>
        <div class="pdh-docinfo">
            <div class="pdh-doctitle">LC Detail Report</div>
            <div class="pdh-docmeta">
                <span>LC No:&nbsp;<strong>{{ $lc->lc_no_system }}</strong></span>
                <span>PFI:&nbsp;<strong>{{ $lc->pfi_no ?? '—' }}</strong></span>
                <span>Status:&nbsp;<strong>{{ $lc->lc_status }}</strong></span>
            </div>
        </div>
    </div>
    <div class="pdh-customer-bar">
        <span>Customer:&nbsp;<strong>{{ $lc->customer_name ?? '—' }}</strong></span>
        <span>Printed:&nbsp;<strong>{{ now()->format('d-M-Y H:i') }}</strong></span>
        <span>By:&nbsp;<strong>{{ auth()->user()->name ?? '' }}</strong></span>
    </div>
    <div class="pdh-rule"></div>
</div>


<div class="row g-3">
    {{-- Left column --}}
    <div class="col-md-8">

        {{-- 1. Identification --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-identification" data-section="identification">
                <span><i class="fa fa-id-card me-2"></i> Identification</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-identification" title="Print this section" aria-label="Print Identification section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-identification">
                <div class="info-body">
                    <div class="row g-2">
                        @php $fields = [
                            'LC No (System)' => $lc->lc_no_system,
                            'LC No (Bank)'   => $lc->lc_no,
                            'PFI No'         => $lc->pfi_no,
                            'PFI Date'       => fmtDate($lc->pfi_date),
                            'LC Open Date'   => fmtDate($lc->lc_open_date),
                            'LC Expiry'      => fmtDate($lc->lc_expiry_date),
                            'LC Type'        => $lc->lc_type,
                            'LC Status'      => $lc->lc_status,
                            'Month'          => $lc->month,
                            'Customer'       => $lc->customer_name,
                            'Shipment From'  => $lc->shipment_from,
                            'Last Shipment'  => fmtDate($lc->last_shipment_date),
                            'Docs Received'  => fmtDate($lc->shipping_docs_received_date),
                        ] @endphp
                        @foreach($fields as $label => $val)
                        <div class="col-6 col-md-3">
                            <div class="info-label">{{ $label }}</div>
                            <div class="info-value">{{ $val ?? $dash }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Supplier & Goods --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-supplier" data-section="supplier">
                <span><i class="fa fa-industry me-2"></i> Supplier &amp; Goods</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-supplier" title="Print this section" aria-label="Print Supplier section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-supplier">
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-md-4"><div class="info-label">Supplier</div><div class="info-value">{{ $lc->supplier_name ?? $dash }}</div></div>
                        <div class="col-md-2"><div class="info-label">Country</div><div class="info-value">{{ $lc->supplier_country ?? $dash }}</div></div>
                        <div class="col-md-3"><div class="info-label">Importer</div><div class="info-value">{{ $importer?->name ?? $dash }}</div></div>
                        <div class="col-md-3"><div class="info-label">Customer PO Date</div><div class="info-value">{{ fmtDate($lc->customer_po_date) }}</div></div>
                        <div class="col-12"><div class="info-label">Item Description</div><div class="info-value">{{ $lc->item_description ?? $dash }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Bank & Documents --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-bank" data-section="bank">
                <span><i class="fa fa-university me-2"></i> Bank &amp; Documents</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-bank" title="Print this section" aria-label="Print Bank section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-bank">
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="info-label">Opening Bank</div>
                            <div class="info-value">{{ $bank ? $bank->name . ($bank->branch ? ' — ' . $bank->branch : '') : $dash }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Port of Destination</div>
                            <div class="info-value">{{ $port ? $port->name . ' (' . $port->type . ')' : $dash }}</div>
                        </div>
                        <div class="col-md-2"><div class="info-label">Country of Origin</div><div class="info-value">{{ $lc->country_of_origin ?? $dash }}</div></div>
                        <div class="col-md-2"><div class="info-label">Payment Mode</div><div class="info-value">{{ $lc->payment_mode ?? $dash }}</div></div>

                        <div class="col-md-3"><div class="info-label">Cover Note</div><div class="info-value">{{ $lc->cover_note ?? $dash }}</div></div>
                        <div class="col-md-3"><div class="info-label">PSI No</div><div class="info-value">{{ $lc->psi_no ?? $dash }}</div></div>
                        <div class="col-md-3"><div class="info-label">PSI Company</div><div class="info-value">{{ $psiCompany?->name ?? $dash }}</div></div>
                        <div class="col-md-3"><div class="info-label">Doc Status</div><div class="info-value">{{ $lc->doc_status ?? $dash }}</div></div>

                        <div class="col-md-4"><div class="info-label">Sanction Types</div><div class="info-value">{{ $lc->sanction_types ?? $dash }}</div></div>
                        <div class="col-md-4"><div class="info-label">Third Party</div><div class="info-value">{{ $lc->third_party ?? $dash }}</div></div>
                        <div class="col-12"><div class="info-label">Remarks</div><div class="info-value">{{ $lc->remarks ?? $dash }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. LC Details --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-lcdetails" data-section="lcdetails">
                <span><i class="fa fa-dollar-sign me-2"></i> LC Details</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-lcdetails" title="Print this section" aria-label="Print LC Details section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-lcdetails">
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><div class="info-label">PFI Value</div><div class="info-value">{{ ($lc->currency ?? '') . ' ' . number_format((float)$lc->pfi_value, 4) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Currency</div><div class="info-value">{{ $lc->currency ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC OP Rate</div><div class="info-value">{{ $lc->lc_open_rate ? 'BDT ' . number_format((float)$lc->lc_open_rate, 4) : $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Margin %</div><div class="info-value">{{ $lc->margin_percent ? number_format((float)$lc->margin_percent, 2) . '%' : $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Margin Amt</div><div class="info-value">{{ fmtAmt($lc->lc_margin_amt) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Opening Cost</div><div class="info-value">{{ fmtAmt($lc->lc_open_cost_bdt) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Freight Value</div><div class="info-value">{{ $lc->freight_value ? ($lc->currency ?? '') . ' ' . number_format((float)$lc->freight_value, 4) : $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Value</div><div class="info-value">{{ $lc->lc_value ? ($lc->currency ?? '') . ' ' . number_format((float)$lc->lc_value, 4) : $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Amount BDT</div><div class="info-value">{{ fmtAmt($lc->amount_bdt) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Total LC Cost</div><div class="info-value">{{ fmtAmt($totalLcCost) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Landed Cost</div><div class="info-value">{{ fmtAmt($lc->landed_cost) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Rate Amount</div><div class="info-value">{{ fmtAmt($lc->lc_rate_amount) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Doc RT Rate</div><div class="info-value">{{ $lc->doc_rt_rate ? 'BDT ' . number_format((float)$lc->doc_rt_rate, 4) : $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC RT Value</div><div class="info-value">{{ fmtAmt($lc->lc_rt_value) }}</div></div>
                        <div class="col-6 col-md-3">
                            <div class="info-label">LC Commission</div>
                            <div class="info-value">
                                @if($lc->lc_commission_percent || $lc->lc_commission_flat)
                                    {{ $lc->lc_commission_percent ? number_format((float)$lc->lc_commission_percent, 4) . '%' : '' }}
                                    {{ $lc->lc_commission_percent && $lc->lc_commission_flat ? ' / ' : '' }}
                                    {{ $lc->lc_commission_flat ? 'BDT ' . number_format((float)$lc->lc_commission_flat, 2) : '' }}
                                @else
                                    {{ $dash }}
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Charge Posting</div><div class="info-value">{{ $lc->lc_charge_posting ?? $dash }}</div></div>

                        <div class="col-6 col-md-3"><div class="info-label">Insurance Amount</div><div class="info-value">{{ fmtAmt($lc->insurance_amt) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Insurance Validity</div><div class="info-value">{{ fmtDate($lc->insurance_validity) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Comm. Currency</div><div class="info-value">{{ $lc->comm_currency ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Comm. Amount</div><div class="info-value">{{ fmtAmt($lc->comm_amount) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Amendment Charge</div><div class="info-value">{{ fmtAmt($lc->lc_amendment_charge) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Credit Report Charge</div><div class="info-value">{{ fmtAmt($lc->credit_report_charge) }}</div></div>

                        {{-- Other Charges --}}
                        <div class="col-12 mt-1">
                            <div class="info-label mb-1">Other Charges</div>
                            @if($lc->otherChargeItems->count())
                            <div style="overflow-x:auto">
                                <table class="table table-sm table-bordered mb-1 w-100" style="font-size:.8rem">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:32px;background:#e9ecef;padding:.3rem .5rem">#</th>
                                            <th style="background:#e9ecef;padding:.3rem .5rem">Charge Name</th>
                                            <th style="width:160px;background:#e9ecef;padding:.3rem .5rem">Amount (BDT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lc->otherChargeItems as $idx => $charge)
                                        <tr>
                                            <td class="text-center" style="padding:.3rem .5rem">{{ $idx + 1 }}</td>
                                            <td style="padding:.3rem .5rem">{{ $charge->name }}</td>
                                            <td style="padding:.3rem .5rem">{{ number_format((float)$charge->amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-end fw-bold" style="padding:.3rem .5rem;font-size:.78rem">Total</td>
                                            <td class="fw-bold" style="padding:.3rem .5rem">{{ number_format($lc->otherChargeItems->sum('amount'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                            <div class="info-value">{{ $dash }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Payment Tracking --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-payment" data-section="payment">
                <span><i class="fa fa-money-check-alt me-2"></i> Payment Tracking</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-payment" title="Print this section" aria-label="Print Payment section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-payment">
                <div class="info-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3"><div class="info-label">LC Closing Bill</div><div class="info-value">{{ fmtAmt($lc->lc_closing_bill) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Closing Bill Date</div><div class="info-value">{{ fmtDate($lc->lc_closing_bill_date) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Total Received</div><div class="info-value">{{ fmtAmt($lc->total_received_bdt) }}</div></div>
                    </div>
                    @if($lc->payments->count())
                    <div style="overflow-x:auto">
                        <table class="table table-bordered pay-table mb-0 w-100">
                            <thead><tr><th>#</th><th>Type</th><th>Receipt No</th><th>Date</th><th>Amount (BDT)</th></tr></thead>
                            <tbody>
                                @foreach($lc->payments as $idx => $pay)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ ucfirst($pay->payment_type) }}</td>
                                    <td>{{ $pay->receipt_no ?? $dash }}</td>
                                    <td>{{ $pay->date ? \Carbon\Carbon::parse($pay->date)->format('d-M-Y') : $dash }}</td>
                                    <td>{{ number_format((float)$pay->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold" style="font-size:.8rem">Total</td>
                                    <td class="fw-bold" style="font-size:.8rem">{{ number_format($lc->payments->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-2" style="font-size:.82rem"><i class="fa fa-money-bill-wave fa-2x mb-2 d-block opacity-50"></i>No payment receipts recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 6. Duty & Clearance --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-duty" data-section="duty">
                <span><i class="fa fa-clipboard-check me-2"></i> Duty &amp; Clearance</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-duty" title="Print this section" aria-label="Print Duty section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-duty">
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><div class="info-label">Duty Advance</div><div class="info-value">{{ fmtAmt($lc->duty_advance) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Duty Advance Date</div><div class="info-value">{{ fmtDate($lc->duty_advance_date) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Duty Advance Posting</div><div class="info-value">{{ $lc->duty_advance_posting ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"></div>
                        <div class="col-6 col-md-3"><div class="info-label">Bill of Entry No</div><div class="info-value">{{ $lc->bill_of_entry_no ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Bill of Entry Date</div><div class="info-value">{{ fmtDate($lc->bill_of_entry_date) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Customs Duty</div><div class="info-value">{{ fmtAmt($lc->customs_duty) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Customs Duty Posting</div><div class="info-value">{{ $lc->customs_duty_posting ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">CNF Party</div><div class="info-value">{{ $lc->cnf_party ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">CNF Total Cost</div><div class="info-value">{{ fmtAmt($lc->cnf_total_cost) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">CNF Cost Posting</div><div class="info-value">{{ $lc->cnf_cost_posting ?? $dash }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. VAT / Tax / Sales --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-vat" data-section="vat">
                <span><i class="fa fa-percent me-2"></i> VAT / Tax / Sales</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-vat" title="Print this section" aria-label="Print VAT section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-vat">
                <div class="info-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3"><div class="info-label">Payable / Receivable</div><div class="info-value">{{ fmtAmt($lc->payable_receivable) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Received Amount</div><div class="info-value">{{ fmtAmt($lc->received_amount) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Received Date</div><div class="info-value">{{ fmtDate($lc->received_date) }}</div></div>
                        <div class="col-6 col-md-3"></div>
                        <div class="col-6 col-md-3"><div class="info-label">VAT Return</div><div class="info-value">{{ fmtAmt($lc->vat_return) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">VAT Return Date</div><div class="info-value">{{ fmtDate($lc->vat_return_date) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">VAT Return Posting</div><div class="info-value">{{ $lc->vat_return_posting ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Income Tax</div><div class="info-value">{{ fmtAmt($lc->income_tax) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Bank Statement Amt</div><div class="info-value">{{ fmtAmt($lc->bank_statement_amt) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Commission (Paid)</div><div class="info-value">{{ fmtAmt($lc->lc_commission) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">LC Commission Date</div><div class="info-value">{{ fmtDate($lc->lc_commission_date) }}</div></div>
                        <div class="col-6 col-md-3"></div>
                        <div class="col-6 col-md-3"><div class="info-label">Sales Amount</div><div class="info-value">{{ fmtAmt($lc->sales_amount) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">Sales Posting</div><div class="info-value">{{ $lc->sales_posting ?? $dash }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">COSS Amount</div><div class="info-value">{{ fmtAmt($lc->coss_amount) }}</div></div>
                        <div class="col-6 col-md-3"><div class="info-label">COSS Posting</div><div class="info-value">{{ $lc->coss_posting ?? $dash }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 8. Product Line Items --}}
        @if($lc->items->count())
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-items" data-section="items">
                <span><i class="fa fa-boxes me-2"></i> Product Line Items</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-items" title="Print this section" aria-label="Print Product Items section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-items">
                <div style="overflow-x:auto">
                    <table class="table table-bordered exp-table mb-0 w-100">
                        <thead><tr><th>#</th><th>Product</th><th>Code</th><th>HS Code</th><th>Qty</th><th>Unit</th><th>Weight</th><th>W.Unit</th><th>Unit Price</th><th>Amount</th><th>Curr.</th></tr></thead>
                        <tbody>
                            @foreach($lc->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->product_code ?? $dash }}</td>
                                <td>{{ $item->hs_code ?? $dash }}</td>
                                <td>{{ $item->qty_count }}</td>
                                <td>{{ $item->qty_unit }}</td>
                                <td>{{ $item->weight ?? $dash }}</td>
                                <td>{{ $item->weight_unit ?? $dash }}</td>
                                <td>{{ number_format((float)$item->unit_price, 4) }}</td>
                                <td>{{ number_format((float)$item->line_amount, 4) }}</td>
                                <td>{{ $item->currency }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /col-md-8 --}}

    {{-- Right column: Expenses + Summary --}}
    <div class="col-md-4">
        <div class="info-card">
            <div class="info-header d-flex justify-content-between align-items-center" data-bs-target="#sec-expenses" data-section="expenses" style="cursor:pointer">
                <span><i class="fa fa-receipt me-2"></i> LC Expenses</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-light py-0 px-2" id="btnAddExpense" style="font-size:.72rem;z-index:1;position:relative">
                        <i class="fa fa-plus me-1"></i>Add
                    </button>
                    <button class="btn-print-section" data-print-target="sec-expenses" title="Print this section" aria-label="Print Expenses section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-expenses">
                <div class="info-body p-0">
                    @if($lc->expenses->count())
                    <table class="table table-sm exp-table mb-0 w-100">
                        <thead><tr><th>Head</th><th>Type</th><th>Amount</th><th>Date</th><th></th></tr></thead>
                        <tbody id="expenseRows">
                        @foreach($lc->expenses as $exp)
                        <tr data-id="{{ $exp->id }}">
                            <td>{{ $exp->expense_head_name ?? '—' }}</td>
                            <td><span class="badge badge-posting bg-secondary">{{ $exp->posting_type }}</span></td>
                            <td>{{ number_format((float)$exp->amount, 2) }}</td>
                            <td>{{ $exp->expense_date?->format('d-M-Y') }}</td>
                            <td>
                                <button class="btn btn-outline-danger btn-xs btn-del-exp" data-id="{{ $exp->id }}" style="padding:1px 5px;font-size:.68rem"><i class="fa fa-times"></i></button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <div id="noExpenses" class="text-center text-muted py-3" style="font-size:.82rem"><i class="fa fa-receipt fa-2x mb-2 d-block opacity-50"></i>No expenses yet.</div>
                    @endif
                </div>
                @if($lc->expenses->count())
                <div class="info-body border-top pt-2">
                    <div class="d-flex justify-content-between">
                        <strong style="font-size:.82rem">Total Expenses:</strong>
                        <strong style="font-size:.82rem">BDT {{ number_format($lc->expenses->sum('amount'), 2) }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Summary --}}
        <div class="info-card">
            <div class="info-header" data-bs-target="#sec-summary" data-section="summary">
                <span><i class="fa fa-chart-bar me-2"></i> Quick Summary</span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-print-section" data-print-target="sec-summary" title="Print this section" aria-label="Print Quick Summary section"><i class="fa fa-print"></i></button>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
            </div>
            <div class="collapse show" id="sec-summary">
                <div class="info-body">
                    @php
                        $totalExpenses      = $lc->expenses->sum('amount');
                        $totalOtherCharges  = $lc->otherChargeItems->sum('amount');
                        $lcCost             = $totalLcCost;
                        $cnfCost            = (float)($lc->cnf_total_cost ?? 0);
                        $advancePayment     = $lc->payments->where('payment_type', 'advance')->sum('amount');
                        $dutyAdvance        = (float)($lc->duty_advance ?? 0);
                        $totalAdvance       = $advancePayment + $dutyAdvance;
                    @endphp
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">LC No</span>
                        <span style="font-size:.82rem">{{ $lc->lc_no ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">PFI No</span>
                        <span style="font-size:.82rem">{{ $lc->pfi_no ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">PFI Value</span>
                        <span style="font-size:.82rem">{{ $lc->currency }} {{ number_format((float)($lc->pfi_value ?? 0), 4) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Customs Duty</span>
                        <span style="font-size:.82rem">BDT {{ number_format((float)($lc->customs_duty ?? 0), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Total LC Cost</span>
                        <span style="font-size:.82rem">BDT {{ number_format($lcCost, 2) }}</span>
                    </div>
                    @if($totalOtherCharges > 0)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Other Charges</span>
                        <span style="font-size:.82rem">BDT {{ number_format($totalOtherCharges, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">C&amp;F Cost</span>
                        <span style="font-size:.82rem">BDT {{ number_format($cnfCost, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Total Expenses</span>
                        <span style="font-size:.82rem">BDT {{ number_format($totalExpenses, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <strong style="font-size:.85rem">Grand Total</strong>
                        <strong style="font-size:.85rem; color:#1a6b60">BDT {{ number_format($lcCost + $totalOtherCharges + $cnfCost + $totalExpenses, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Advance Payment</span>
                        <span style="font-size:.82rem">BDT {{ number_format($advancePayment, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span style="font-size:.82rem">Duty Advance</span>
                        <span style="font-size:.82rem">BDT {{ number_format($dutyAdvance, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <strong style="font-size:.85rem">Total Advance</strong>
                        <strong style="font-size:.85rem; color:#17375e">BDT {{ number_format($totalAdvance, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#0c2340; color:#fff;">
                <h6 class="modal-title"><i class="fa fa-plus me-2"></i>Add LC Expense</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <input type="hidden" name="lc_id" value="{{ $lc->id }}">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Expense Head</label>
                        <select name="expense_head_id" id="expHeadSel" class="form-select form-select-sm">
                            <option value="">Select Expense Head...</option>
                            @foreach($expenseHeads as $eh)
                            <option value="{{ $eh->id }}" data-name="{{ $eh->name }}">{{ $eh->name }} ({{ $eh->category }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Amount (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Posting Type <span class="text-danger">*</span></label>
                            <select name="posting_type" id="postingType" class="form-select form-select-sm" required>
                                <option value="Other">Other</option>
                                <option value="Insurance">Insurance</option>
                                <option value="LC Opening">LC Opening</option>
                                <option value="LC Additional">LC Additional</option>
                                <option value="Doc Retirement">Doc Retirement</option>
                                <option value="Post Shipment">Post Shipment</option>
                                <option value="CNF Expenses">CNF Expenses</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Sub-type</label>
                            <select name="posting_sub_type" id="postingSubType" class="form-select form-select-sm">
                                <option value="">N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Reference</label>
                        <input type="text" name="reference" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Note</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveExpense"><i class="fa fa-save me-1"></i>Save Expense</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var LC_STORAGE_KEY = 'lc_sections_{{ $lc->id }}';

// ── Print helpers ─────────────────────────────────────────────────────────
function forceOpen(el) {
    // Override Bootstrap's CSS-class hiding with inline styles so the browser
    // renders the content before window.print() captures the page.
    el.style.display  = 'block';
    el.style.height   = 'auto';
    el.style.overflow = 'visible';
}
function restoreCollapsed(el) {
    el.style.display  = '';
    el.style.height   = '';
    el.style.overflow = '';
}

function printSection(collapseId) {
    var collapse  = document.getElementById(collapseId);
    var card      = collapse.closest('.info-card');
    var wasHidden = !collapse.classList.contains('show');
    if (wasHidden) forceOpen(collapse);
    document.body.classList.add('lc-print-section');
    card.classList.add('lc-print-active');
    // Give the browser one frame to render the forced-open state before printing
    setTimeout(function () {
        window.print();
        document.body.classList.remove('lc-print-section');
        card.classList.remove('lc-print-active');
        if (wasHidden) restoreCollapsed(collapse);
    }, 100);
}

function printAllSections() {
    var toRestore = [];
    document.querySelectorAll('.info-header[data-section]').forEach(function (h) {
        var el = document.querySelector(h.getAttribute('data-bs-target'));
        if (el && !el.classList.contains('show')) {
            forceOpen(el);
            toRestore.push(el);
        }
    });
    setTimeout(function () {
        window.print();
        toRestore.forEach(restoreCollapsed);
    }, 100);
}

// ── Collapse management (manual — Bootstrap delegation removed so print buttons
//    inside headers can never accidentally trigger a toggle) ──────────────────
function toggleSection(header) {
    var targetId = header.getAttribute('data-bs-target');
    var $collapse = $(targetId);
    var $header   = $(header);
    if ($collapse.hasClass('show')) {
        $collapse.collapse('hide');
    } else {
        $collapse.collapse('show');
    }
}

$(function () {
    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(LC_STORAGE_KEY) || '{}'); } catch(e) {}

    // Restore saved collapsed/expanded state
    $('.info-header[data-section]').each(function () {
        var key      = $(this).data('section');
        var targetId = $(this).attr('data-bs-target');
        if (saved[key] === false) {
            $(targetId).removeClass('show');
            $(this).addClass('collapsed');
        }
    });

    // Header click → toggle collapse (skip if click came from print button or any child of it)
    $(document).on('click', '.info-header[data-section]', function (e) {
        if ($(e.target).closest('[data-print-target]').length) return;
        toggleSection(this);
    });

    // Sync chevron + persist on Bootstrap collapse events
    $(document).on('hide.bs.collapse', '.collapse', function () {
        var $header = $('[data-bs-target="#' + this.id + '"]');
        var key = $header.data('section');
        if (key) { saved[key] = false; localStorage.setItem(LC_STORAGE_KEY, JSON.stringify(saved)); }
        $header.addClass('collapsed');
    });
    $(document).on('show.bs.collapse', '.collapse', function () {
        var $header = $('[data-bs-target="#' + this.id + '"]');
        var key = $header.data('section');
        if (key) { saved[key] = true; localStorage.setItem(LC_STORAGE_KEY, JSON.stringify(saved)); }
        $header.removeClass('collapsed');
    });

    // Add expense button
    $('#btnAddExpense').on('click', function (e) {
        e.stopPropagation();
        $('#expenseModal').modal('show');
    });

    // Section print buttons
    $('[data-print-target]').on('click', function (e) {
        e.stopPropagation();
        printSection($(this).data('print-target'));
    });

    // Print All button
    $('#btnPrintAll').on('click', function () {
        printAllSections();
    });

    // Expand / Collapse all
    $('#btnExpandAll').on('click', function () {
        $('.info-header[data-section]').each(function () {
            $($(this).attr('data-bs-target')).collapse('show');
        });
    });
    $('#btnCollapseAll').on('click', function () {
        $('.info-header[data-section]').each(function () {
            $($(this).attr('data-bs-target')).collapse('hide');
        });
    });
});

// Expense sub-types
var subTypes = { 'Doc Retirement': ['Bank Pay','IFDBC','Acceptance','LTR','STL','ATR','Margin Adjust'] };
$('#postingType').on('change', function () {
    var opts = subTypes[$(this).val()] || [];
    var sel = $('#postingSubType').empty().append('<option value="">N/A</option>');
    opts.forEach(o => sel.append(`<option value="${o}">${o}</option>`));
});

$('#btnSaveExpense').on('click', function () {
    if (!$('[name=amount]').val() || !$('[name=posting_type]').val()) {
        Swal.fire({ icon: 'warning', title: 'Amount and Posting Type are required.' }); return;
    }
    var btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    $.ajax({
        url: '{{ route('nas-trading.lc-expenses.store') }}',
        method: 'POST',
        data: $('#expenseForm').serialize() + '&_token={{ csrf_token() }}',
    })
    .done(function (r) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2000 });
        $('#expenseModal').modal('hide');
        var exp = r.expense;
        $('#noExpenses').hide();
        if (!$('#expenseRows').length) {
            location.reload();
        } else {
            $('#expenseRows').append(`
            <tr data-id="${exp.id}">
                <td>${exp.expense_head_name || '—'}</td>
                <td><span class="badge badge-posting bg-secondary">${exp.posting_type}</span></td>
                <td>${parseFloat(exp.amount).toFixed(2)}</td>
                <td>${exp.expense_date || ''}</td>
                <td><button class="btn btn-outline-danger btn-xs btn-del-exp" data-id="${exp.id}" style="padding:1px 5px;font-size:.68rem"><i class="fa fa-times"></i></button></td>
            </tr>`);
        }
        $('#expenseForm')[0].reset();
        $('[name=expense_date]').val('{{ now()->format('Y-m-d') }}');
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
    })
    .always(() => btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Expense'));
});

$(document).on('click', '.btn-del-exp', function () {
    var id = $(this).data('id'), row = $(this).closest('tr');
    Swal.fire({ title: 'Delete this expense?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url: '/nas-trading/lc-expenses/' + id, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                    .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1200, showConfirmButton: false }); row.remove(); })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
});
</script>
@endpush

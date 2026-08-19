<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Sheet — {{ $job->job_no }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Arial', sans-serif;
    font-size: 10.5px;
    color: #000;
    background: #fff;
}

/* ── Screen toolbar ───────────────────────────────────────────── */
.no-print {
    padding: 8px 16px;
    display: flex;
    gap: 8px;
    align-items: center;
    background: #f1f5f9;
    border-bottom: 1px solid #cbd5e1;
}
.no-print button {
    padding: 5px 20px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 4px;
    font-weight: 600;
}
.btn-print { background: #1a4a6b; color: #fff; border: 1px solid #1a4a6b; }
.btn-back  { background: #fff;    color: #374151; border: 1px solid #9ca3af; }

/* ── Page wrapper ─────────────────────────────────────────────── */
.page {
    width: 210mm;
    margin: 0 auto;
    padding: 10mm 14mm 14mm;
    background: #fff;
}

/* ── Letterhead ───────────────────────────────────────────────── */
.letterhead {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    border-bottom: 2.5px solid #000;
    padding-bottom: 6px;
    margin-bottom: 6px;
}
.co-block { flex: 1; }
.co-name  {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: .03em;
    line-height: 1.15;
}
.co-sub {
    font-size: 9px;
    color: #444;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-top: 2px;
}
.job-badge {
    text-align: right;
    flex-shrink: 0;
}
.job-badge .badge-no {
    font-size: 13px;
    font-weight: 700;
    border: 1.5px solid #000;
    padding: 2px 10px;
    display: inline-block;
    letter-spacing: .05em;
}
.job-badge .badge-lbl {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-top: 2px;
    color: #555;
}

/* ── Document title ───────────────────────────────────────────── */
.doc-title {
    text-align: center;
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: .15em;
    text-transform: uppercase;
    text-decoration: underline;
    margin: 5px 0 7px;
}

/* ── Summary strip ────────────────────────────────────────────── */
.summary-strip {
    display: flex;
    border: 1px solid #000;
    margin-bottom: 8px;
}
.summary-strip .s-cell {
    flex: 1;
    padding: 3px 7px;
    border-right: 1px solid #000;
}
.summary-strip .s-cell:last-child { border-right: none; }
.summary-strip .s-lbl {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #555;
    font-weight: 700;
}
.summary-strip .s-val {
    font-size: 10.5px;
    font-weight: 700;
    margin-top: 1px;
}

/* ── Section heading ──────────────────────────────────────────── */
.sec-hdr {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    border-left: 3px solid #000;
    padding: 1px 0 1px 6px;
    margin: 9px 0 0;
    color: #000;
}

/* ── Info table ───────────────────────────────────────────────── */
.info-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    margin-top: 2px;
}
.info-tbl td {
    border: 1px solid #888;
    padding: 3px 6px;
    vertical-align: top;
}
.info-tbl .lbl {
    font-weight: 700;
    width: 120px;
    white-space: nowrap;
    color: #000;
    /* No background — print-safe */
}
.info-tbl .val {
    color: #000;
    min-width: 80px;
}

/* ── Financial & Duty two-column block ────────────────────────── */
.fin-block {
    display: flex;
    gap: 8px;
    margin-top: 2px;
    page-break-inside: avoid;
}
.fin-left  { flex: 1; }
.fin-right { width: 200px; flex-shrink: 0; }

/* Financial summary table */
.fin-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}
.fin-tbl td {
    border: 1px solid #888;
    padding: 3px 6px;
    vertical-align: top;
    color: #000;
}
.fin-tbl .lbl {
    font-weight: 700;
    white-space: nowrap;
    width: 130px;
}
.fin-tbl .val { }
.fin-tbl .highlight td {
    font-weight: 700;
    border-top: 1.5px solid #000;
}

/* Duty breakdown table */
.duty-tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    border: 1px solid #888;
}
.duty-tbl thead th {
    padding: 4px 6px;
    font-weight: 700;
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #000;
    border-bottom: 1.5px solid #000;
    text-align: left;
}
.duty-tbl thead th.r { text-align: right; }
.duty-tbl tbody td {
    padding: 3px 6px;
    border-bottom: 1px solid #ddd;
    color: #000;
    font-size: 10px;
}
.duty-tbl tbody td.r { text-align: right; }
.duty-tbl tfoot td {
    padding: 4px 6px;
    font-weight: 700;
    color: #000;
    border-top: 1.5px solid #000;
    font-size: 10px;
}
.duty-tbl tfoot td.r { text-align: right; }

/* ── Signature block ──────────────────────────────────────────── */
.sig-strip {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 10px;
}
.sig-block { text-align: center; width: 130px; }
.sig-line  { border-top: 1px solid #000; margin-bottom: 4px; }
.sig-lbl   { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }

/* ── Footer ───────────────────────────────────────────────────── */
.doc-footer {
    margin-top: 12px;
    border-top: 1px solid #aaa;
    padding-top: 4px;
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    color: #666;
}

/* ── Print overrides ──────────────────────────────────────────── */
@media print {
    .no-print { display: none !important; }
    body { margin: 0; background: #fff; }
    .page { width: 100%; margin: 0; padding: 0; }
    @page { size: A4 portrait; margin: 12mm 14mm 14mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424;&nbsp; Print</button>
    <button class="btn-back"  onclick="window.close()">&#10005;&nbsp; Close</button>
</div>

<div class="page">

    {{-- ── Letterhead ──────────────────────────────────────────── --}}
    <div class="letterhead">
        <div class="co-block">
            <div class="co-name">Chevron Lines Ltd.</div>
            <div class="co-sub">Customs Clearing &amp; Forwarding Agents</div>
        </div>
        <div class="job-badge">
            <div class="badge-no">{{ $job->job_no }}</div>
            <div class="badge-lbl">Job Number</div>
        </div>
    </div>

    <div class="doc-title">C &amp; F &nbsp; Job Sheet</div>

    {{-- ── Summary strip ───────────────────────────────────────── --}}
    <div class="summary-strip">
        <div class="s-cell">
            <div class="s-lbl">Job Date</div>
            <div class="s-val">{{ $job->job_date?->format('d M Y') ?? '—' }}</div>
        </div>
        <div class="s-cell">
            <div class="s-lbl">Job Type</div>
            <div class="s-val">{{ $job->jobType?->name ?? '—' }}</div>
        </div>
        <div class="s-cell">
            <div class="s-lbl">Port</div>
            <div class="s-val">{{ $job->port?->name ?? '—' }}</div>
        </div>
        <div class="s-cell">
            <div class="s-lbl">Country of Origin</div>
            <div class="s-val">{{ $job->country_of_origin ?: '—' }}</div>
        </div>
    </div>
    @if($job->remarks)
    <div class="summary-strip" style="margin-top:-1px;">
        <div class="s-cell" style="flex:unset;width:100%;">
            <div class="s-lbl">Remark</div>
            <div class="s-val" style="font-size:10px;font-weight:400;">{{ $job->remarks }}</div>
        </div>
    </div>
    @endif

    {{-- ── 1. Party & Goods ────────────────────────────────────── --}}
    <div class="sec-hdr">Party &amp; Goods</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Party Name</td>
            <td class="val" colspan="3">{{ $job->party_name ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Party Address</td>
            <td class="val" colspan="3">{{ $job->party_address ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Goods Name</td>
            <td class="val" colspan="3">{{ $job->goods_name ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Pack Quantity</td>
            <td class="val">{{ $job->pack_quantity ? rtrim(rtrim(number_format($job->pack_quantity, 3), '0'), '.').' '.($job->pack_unit ?? '') : '—' }}</td>
            <td class="lbl">Gross Weight</td>
            <td class="val">{{ $job->gross_weight ? rtrim(rtrim(number_format($job->gross_weight, 3), '0'), '.').' '.($job->gross_weight_unit ?? 'KGS') : '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Net Weight</td>
            <td class="val" colspan="3">{{ $job->net_weight ? rtrim(rtrim(number_format($job->net_weight, 3), '0'), '.').' '.($job->net_weight_unit ?? 'KGS') : '—' }}</td>
        </tr>
    </table>

    {{-- ── 2. Custom Document & Detail ────────────────────────── --}}
    <div class="sec-hdr">Custom Document &amp; Detail</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Copy Doc. Rec. Date</td>
            <td class="val">{{ $job->copy_doc_received_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">Orig. Doc. Rec. Date</td>
            <td class="val">{{ $job->original_doc_received_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">ETA Date</td>
            <td class="val">{{ $job->eta_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">HBL/HAWB No</td>
            <td class="val">{{ $job->hbi_hawb_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">HBL/HAWB Date</td>
            <td class="val">{{ $job->hbi_hawb_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">MBL/MAWB No</td>
            <td class="val">{{ $job->mbl_mawb_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">MBL/MAWB Date</td>
            <td class="val">{{ $job->mbl_mawb_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">Invoice No</td>
            <td class="val">{{ $job->invoice_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Invoice Date</td>
            <td class="val">{{ $job->invoice_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">B/E No</td>
            <td class="val">{{ $job->be_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">B/E Date</td>
            <td class="val">{{ $job->be_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">LC No</td>
            <td class="val">{{ $job->lc_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">LC Date</td>
            <td class="val">{{ $job->lc_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">LCA No</td>
            <td class="val">{{ $job->lca_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">LCA Date</td>
            <td class="val">{{ $job->lca_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">PO No</td>
            <td class="val">{{ $job->po_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Mate Code</td>
            <td class="val" colspan="3">{{ $job->mate_code ?: '—' }}</td>
        </tr>
    </table>

    {{-- ── 3. Vessel & Transport ───────────────────────────────── --}}
    <div class="sec-hdr">Vessel &amp; Transport</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Lading No</td>
            <td class="val">{{ $job->lading_no ?: '—' }}</td>
            <td class="lbl">Lading Date</td>
            <td class="val">{{ $job->lading_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Flight No</td>
            <td class="val">{{ $job->flight_no ?: '—' }}</td>
            <td class="lbl">Flight Date</td>
            <td class="val">{{ $job->flight_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Vessel's Name</td>
            <td class="val">{{ $job->vessel_name ?: '—' }}</td>
            <td class="lbl">Boyge No</td>
            <td class="val">{{ $job->boyge_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Vessel's ETB Agent</td>
            <td class="val">{{ $job->vessel_etb_agent ?: '—' }}</td>
            <td class="lbl">AL No</td>
            <td class="val">{{ $job->al_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Sailed No</td>
            <td class="val">{{ $job->sailed_no ?: '—' }}</td>
            <td class="lbl">Arrived Date</td>
            <td class="val">{{ $job->arrived_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Common Lading Date</td>
            <td class="val">{{ $job->common_lading_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">W/Rent Due Date</td>
            <td class="val">{{ $job->w_rent_due_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Berthing</td>
            <td class="val">{{ $job->berthing ?: '—' }}</td>
            <td class="lbl">Berthing Date</td>
            <td class="val">{{ $job->berthing_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Shed No</td>
            <td class="val">{{ $job->shed_no ?: '—' }}</td>
            <td class="lbl">Yard No</td>
            <td class="val">{{ $job->yard_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Transport Name</td>
            <td class="val">{{ $job->transport_name ?: '—' }}</td>
            <td class="lbl">Transport No</td>
            <td class="val">{{ $job->transport_no ?: '—' }}</td>
        </tr>
    </table>

    {{-- ── 4. Port Operations ──────────────────────────────────── --}}
    <div class="sec-hdr">Port Operations</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Rot No</td>
            <td class="val">{{ $job->rot_no ?: '—' }}</td>
            <td class="lbl">B/L Weight Measurement</td>
            <td class="val">{{ $job->bl_weight_measurement ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Jetty Sarker Name</td>
            <td class="val">{{ $job->jetty_sarker_name ?: '—' }}</td>
            <td class="lbl">Contact No</td>
            <td class="val">{{ $job->contact_no ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Unit No</td>
            <td class="val" colspan="3">{{ $job->unit_no ?: '—' }}</td>
        </tr>
    </table>

    {{-- ── 5. Operations & Delivery ────────────────────────────── --}}
    <div class="sec-hdr">Operations &amp; Delivery</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">ETB Date</td>
            <td class="val">{{ $job->etb_date?->format('d M Y') ?? '—' }}</td>
            <td class="lbl">Delivery Date</td>
            <td class="val">{{ $job->delivery_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Consignee Name</td>
            <td class="val">{{ $job->consignee_name ?: '—' }}</td>
            <td class="lbl">Consignee Address</td>
            <td class="val">{{ $job->consignee_address ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Agent Name</td>
            <td class="val">{{ $job->agent_name ?: '—' }}</td>
            <td class="lbl">Agent Address</td>
            <td class="val">{{ $job->agent_address ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Container No</td>
            <td class="val">{{ $job->container_no ?: '—' }}</td>
            <td class="lbl">No of Container</td>
            <td class="val">{{ $job->no_of_container ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Commodity</td>
            <td class="val">{{ $job->commodity ?: '—' }}</td>
            <td class="lbl">POL</td>
            <td class="val">{{ $job->pol ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Destination</td>
            <td class="val">{{ $job->destination ?: '—' }}</td>
            <td class="lbl">ETD Date</td>
            <td class="val">{{ $job->etd_date?->format('d M Y') ?? '—' }}</td>
        </tr>
    </table>

    {{-- ── 6. Financial & Duty ─────────────────────────────────── --}}
    <div class="sec-hdr">Financial &amp; Duty</div>

    @php
    $dutyRows = [
        ['Duty / CD', 'duty'],
        ['R/D',       'rd'],
        ['SD',        'sup_tax'],
        ['VAT',       'vat'],
        ['AIT',       'ait'],
        ['AT',        'atv'],
        ['DF VAT',    'df_vat'],
        ['Other',     'other'],
    ];
    $totalDuty = collect($dutyRows)->sum(fn($r) => (float)($job->{$r[1].'_amount'} ?? 0));
    @endphp

    <div class="fin-block">

        {{-- Left: Financial summary --}}
        <div class="fin-left">
            <table class="fin-tbl">
                <tr>
                    <td class="lbl">Currency</td>
                    <td class="val">{{ $job->currency_type ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Exchange Rate (BDT)</td>
                    <td class="val">{{ $job->currency_rate ? rtrim(rtrim(number_format($job->currency_rate, 9), '0'), '.') : '—' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Invoice Value ({{ $job->currency_type ?: 'Orig.' }})</td>
                    <td class="val">{{ $job->invoice_value_1 ? number_format($job->invoice_value_1, 2) : '—' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Invoice Value (BDT)</td>
                    <td class="val">{{ $job->invoice_value_2 ? number_format($job->invoice_value_2, 2) : '—' }}</td>
                </tr>
                <tr class="highlight">
                    <td class="lbl">Assessable Value</td>
                    <td class="val" style="font-weight:700;">{{ $job->assessable_value ? number_format($job->assessable_value, 2) : '—' }}</td>
                </tr>
            </table>
        </div>

        {{-- Right: Duty breakdown --}}
        <div class="fin-right">
            <table class="duty-tbl">
                <thead>
                    <tr>
                        <th>Duty / Tax</th>
                        <th class="r">Amount (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dutyRows as [$label, $key])
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="r">{{ $job->{$key.'_amount'} ? number_format($job->{$key.'_amount'}, 2) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total Duty</td>
                        <td class="r">{{ $totalDuty ? number_format($totalDuty, 2) : '—' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    {{-- ── Signatures ──────────────────────────────────────────── --}}
    <div class="sig-strip">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-lbl">Prepared By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-lbl">Checked By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-lbl">Authorized By</div>
        </div>
    </div>


</div>

<script>window.onload = function () { window.print(); };</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Booking — {{ $booking->job_no }}</title>
    <style>
        :root {
            --brand: #1a6b60;
            --ink: #111;
            --muted: #4b5563;
            --line: #000;
            --soft: #d1d5db;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: var(--ink);
            background: #f3f4f6;
        }

        /* Screen toolbar */
        .no-print {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 8px;
            padding: 10px 14px;
            background: #fff;
            border-bottom: 1px solid var(--soft);
        }

        .no-print button {
            min-height: 36px;
            padding: 6px 18px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #333;
            transition: opacity .15s ease;
        }

        .no-print button:hover {
            opacity: .88;
        }

        .no-print button:focus-visible {
            outline: 3px solid #f59e0b;
            outline-offset: 2px;
        }

        .btn-print {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }

        .btn-close {
            background: #fff;
            color: #374151;
            border-color: #6b7280;
        }

        .page {
            width: 210mm;
            margin: 12px auto;
            padding: 10mm 14mm 12mm;
            background: #fff;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .15);
        }

        /* Company header */
        .co-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .co-logo img {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }

        .co-logo-default {
            width: 58px;
            height: 58px;
            background: var(--brand);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            font-weight: 700;
        }

        .co-name-wrap {
            vertical-align: middle;
            text-align: center;
        }

        .co-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--brand);
            letter-spacing: .5px;
        }

        .co-address {
            font-size: 9.5px;
            color: #333;
            margin-top: 2px;
        }

        .co-contact {
            font-size: 9.5px;
            color: var(--muted);
            margin-top: 1px;
        }

        .divider {
            border: none;
            border-top: 2px solid var(--brand);
            margin: 5px 0;
        }

        .divider-thin {
            border: none;
            border-top: 1px solid var(--soft);
            margin: 4px 0;
        }

        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            text-decoration: underline;
            margin: 7px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Info box */
        .header-outer {
            width: 100%;
            border: 1px solid var(--line);
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-to {
            width: 52%;
            padding: 7px 9px;
            vertical-align: top;
            line-height: 1.7;
            border-right: 1px solid var(--line);
        }

        .header-info {
            width: 48%;
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 7px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-table td.lbl {
            font-weight: 700;
            width: 42%;
            white-space: nowrap;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--brand);
            margin: 8px 0 3px;
        }

        /* Data tables */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid var(--line);
        }

        table.grid thead {
            display: table-header-group;
        }

        table.grid tr {
            page-break-inside: avoid;
        }

        table.grid th {
            background: #000;
            color: #fff;
            font-size: 8.5px;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid var(--line);
            line-height: 1.3;
            word-wrap: break-word;
        }

        table.grid td {
            font-size: 9px;
            padding: 3px 4px;
            border: 1px solid var(--line);
            vertical-align: middle;
            line-height: 1.4;
            word-wrap: break-word;
        }

        table.grid td.r {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        table.grid td.c {
            text-align: center;
        }

        table.grid tr.total-row td {
            font-weight: 700;
            background: #f3f4f6;
        }

        .empty-row td {
            text-align: center;
            color: var(--muted);
            padding: 8px;
        }

        /* Summary */
        .bottom-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .bottom-wrap>tbody>tr>td {
            vertical-align: top;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--line);
        }

        .summary td {
            padding: 3px 7px;
            border-bottom: 1px solid var(--soft);
            font-variant-numeric: tabular-nums;
        }

        .summary td.v {
            text-align: right;
            width: 38%;
        }

        .summary tr.grand td {
            font-weight: 700;
            font-size: 11px;
            background: #f3f4f6;
            border-top: 1px solid var(--line);
            border-bottom: none;
        }

        .amount-words {
            font-weight: 700;
            margin-top: 8px;
            line-height: 1.6;
        }

        .note-line {
            margin-top: 6px;
            color: #333;
            line-height: 1.5;
        }

        /* Signatures */
        .sig-wrap {
            margin-top: 38px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .sig-cell {
            width: 33.33%;
            padding: 0 14px;
            text-align: center;
            vertical-align: bottom;
        }

        .sig-name {
            min-height: 14px;
            font-weight: 700;
        }

        .sig-line {
            border-top: 1px solid var(--line);
            padding-top: 3px;
            font-size: 10px;
        }

        .page-footer {
            margin-top: 14px;
            border-top: 1px solid #bbb;
            padding-top: 4px;
            font-size: 8.5px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                background: #fff;
            }

            .page {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            table.grid th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.grid tr.total-row td,
            .summary tr.grand td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 14mm 12mm 14mm;
            }
        }

        @media (max-width: 800px) {
            .page {
                width: auto;
                margin: 0;
                padding: 12px;
                box-shadow: none;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()" aria-label="Print this booking">&#128424; Print</button>
        <button type="button" class="btn-close" onclick="window.close()" aria-label="Close this window">Close</button>
    </div>

    @php
        $items = $booking->items;
        $products = $booking->products;

        $subTotal = (float) $items->sum('amount');
        $totalQty = (float) $items->sum('qty');
        $totalDemAmt = (float) $items->sum('cus_demurrage_charge');
        $tdsAmount = (float) $booking->tds_amount;
        $vatAmount = (float) $booking->vat_amount;
        $aitAmount = (float) $booking->ait_amount;
        $grandTotal = (float) $booking->total_amount;

        $fmtDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
        $coName = $company?->name ?? 'NAS Freights And Logistics Ltd.';
        $prefixLabel = $booking->booking_prefix ? str_replace('_', ' ', $booking->booking_prefix) : '—';
        $statusLabel = strtoupper($booking->status ?: 'Draft');

        // Amount in words (Bangladeshi number system)
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tnsArr = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $hunds = function (int $n) use ($ones, $tnsArr): string {
            $o = '';
            if ($n >= 100) {
                $o .= $ones[(int) ($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $o .= $tnsArr[(int) ($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '') . ' ';
                $n = 0;
            }
            if ($n > 0) {
                $o .= $ones[$n] . ' ';
            }
            return $o;
        };
        $takaInt = (int) floor($grandTotal);
        $paisaInt = (int) round(($grandTotal - $takaInt) * 100);
        $n = $takaInt;
        $w = 'BDT ';
        if ($n >= 10000000) {
            $w .= $hunds((int) ($n / 10000000)) . 'Crore ';
            $n %= 10000000;
        }
        if ($n >= 100000) {
            $w .= $hunds((int) ($n / 100000)) . 'Lakh ';
            $n %= 100000;
        }
        if ($n >= 1000) {
            $w .= $hunds((int) ($n / 1000)) . 'Thousand ';
            $n %= 1000;
        }
        if ($n > 0) {
            $w .= $hunds((int) $n);
        }
        $w = trim($w) . ' Taka';
        if ($paisaInt > 0) {
            $w .= ' and ' . trim($hunds($paisaInt)) . ' Paisa';
        }
        $amountInWords = $grandTotal > 0 ? $w . ' Only' : '—';
    @endphp

    <div class="page">

        {{-- Company Header --}}
        <table class="co-header">
            <tr>
                <td class="co-logo" style="width:68px;vertical-align:middle;">
                    @if ($company?->logo)
                        <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $coName }} logo">
                    @else
                        <div class="co-logo-default" aria-hidden="true">NAS</div>
                    @endif
                </td>
                <td class="co-name-wrap">
                    <div class="co-name">{{ $coName }}</div>
                    @if ($company?->address)
                        <div class="co-address">{{ $company->address }}</div>
                    @endif
                    @if ($company?->phone || $company?->email)
                        <div class="co-contact">
                            @if ($company->phone) Phone: {{ $company->phone }} @endif
                            @if ($company->phone && $company->email) &nbsp;|&nbsp; @endif
                            @if ($company->email) Email: {{ $company->email }} @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <hr class="divider">
        <div class="doc-title">Transport Booking</div>
        <hr class="divider-thin">

        {{-- Info Box --}}
        <table class="header-outer">
            <tr>
                <td class="header-to">
                    <strong>Customer,</strong><br>
                    <strong>{{ $booking->customer_name ?: '—' }}</strong><br>
                    @if ($booking->delivery_address)
                        {!! nl2br(e($booking->delivery_address)) !!}<br>
                    @endif
                    @if ($booking->lc_no)
                        <strong>LC No:</strong> {{ $booking->lc_no }}<br>
                    @endif
                    @if ($booking->po_number)
                        <strong>PO No:</strong> {{ $booking->po_number }}<br>
                    @endif
                    @if ($booking->invoice_no)
                        <strong>Invoice No:</strong> {{ $booking->invoice_no }}
                    @endif
                </td>
                <td class="header-info">
                    <table class="info-table">
                        <tr>
                            <td class="lbl">Job No:</td>
                            <td><strong>{{ $booking->job_no }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Job Date:</td>
                            <td>{{ $fmtDate($booking->job_date) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Delivery Date:</td>
                            <td>{{ $fmtDate($booking->delivery_date) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Booking Type:</td>
                            <td>{{ $prefixLabel }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Sales Type:</td>
                            <td>{{ $booking->sales_type ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Sales Person:</td>
                            <td>{{ $booking->sales_person_name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Status:</td>
                            <td><strong>{{ $statusLabel }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Goods --}}
        <div class="section-label">Goods Details</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:6%">SL</th>
                    <th style="width:50%">Goods Name</th>
                    <th style="width:20%">Quantity</th>
                    <th style="width:24%">Net Weight</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $i => $product)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $product->goods_name ?: '—' }}</td>
                        <td class="r">{{ number_format((float) $product->qty, 2) }} {{ $product->qty_unit }}</td>
                        <td class="r">{{ number_format((float) $product->net_weight, 2) }} {{ $product->weight_unit }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="4">{{ $booking->goods_name ?: 'No goods recorded' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Vehicles / trips --}}
        <div class="section-label">Vehicle &amp; Trip Details</div>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:4%">SL</th>
                    <th style="width:11%">Vehicle No</th>
                    <th style="width:8%">Challan No</th>
                    <th style="width:7%">Capacity</th>
                    <th style="width:12%">Supplier</th>
                    <th style="width:10%">From</th>
                    <th style="width:10%">To</th>
                    <th style="width:5%">Qty</th>
                    <th style="width:8%">Rate</th>
                    <th style="width:6%">Dem. Days</th>
                    <th style="width:9%">Dem. Charge</th>
                    <th style="width:10%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $item->cover_van_no ?: '—' }}</td>
                        <td>{{ $item->challan_no ?: '—' }}</td>
                        <td>{{ $item->capacity ?: '—' }}</td>
                        <td>{{ $item->supplier_name ?: '—' }}</td>
                        <td>{{ $item->location_from ?: '—' }}</td>
                        <td>{{ $item->location_to ?: '—' }}</td>
                        <td class="c">{{ number_format((float) $item->qty, 2) }}</td>
                        <td class="r">{{ number_format((float) $item->customer_rate, 2) }}</td>
                        <td class="c">{{ (float) $item->demurrage_days > 0 ? rtrim(rtrim(number_format((float) $item->demurrage_days, 2), '0'), '.') : '—' }}</td>
                        <td class="r">{{ (float) $item->cus_demurrage_charge > 0 ? number_format((float) $item->cus_demurrage_charge, 2) : '—' }}</td>
                        <td class="r">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="12">No trips recorded</td>
                    </tr>
                @endforelse
                @if ($items->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="7" class="r">Total</td>
                        <td class="c">{{ number_format($totalQty, 2) }}</td>
                        <td></td>
                        <td></td>
                        <td class="r">{{ $totalDemAmt > 0 ? number_format($totalDemAmt, 2) : '' }}</td>
                        <td class="r">{{ number_format($subTotal, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- Summary + words/note --}}
        <table class="bottom-wrap">
            <tr>
                <td style="width:56%;padding-right:12px;">
                    <div class="amount-words">Total Taka In Word: {{ $amountInWords }}</div>
                    @if ($booking->note)
                        <div class="note-line"><strong>Note:</strong> {!! nl2br(e($booking->note)) !!}</div>
                    @endif
                </td>
                <td style="width:44%;">
                    <table class="summary">
                        <tr>
                            <td>Sub Total</td>
                            <td class="v">{{ number_format($subTotal, 2) }}</td>
                        </tr>
                        @if ($tdsAmount > 0)
                            <tr>
                                <td>TDS ({{ rtrim(rtrim(number_format((float) $booking->tds_percent, 2), '0'), '.') }}%)</td>
                                <td class="v">{{ number_format($tdsAmount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($vatAmount > 0)
                            <tr>
                                <td>VAT ({{ rtrim(rtrim(number_format((float) $booking->vat_percent, 2), '0'), '.') }}%)</td>
                                <td class="v">{{ number_format($vatAmount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($aitAmount > 0)
                            <tr>
                                <td>AIT ({{ rtrim(rtrim(number_format((float) $booking->ait_percent, 2), '0'), '.') }}%)</td>
                                <td class="v">{{ number_format($aitAmount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="grand">
                            <td>Total Amount</td>
                            <td class="v">{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Signatures --}}
        <table class="sig-wrap">
            <tr>
                <td class="sig-cell">
                    <div class="sig-name">{{ $booking->entry_by ?: '' }}</div>
                    <div class="sig-line">Prepared By</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-name"></div>
                    <div class="sig-line">Checked By</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-name"></div>
                    <div class="sig-line">Authorised Signatory</div>
                </td>
            </tr>
        </table>

        <div class="page-footer">
            <span>{{ $booking->branch?->name }}</span>
            <span>Print Date: {{ now()->format('d/m/Y g:i A') }}</span>
        </div>

    </div>
</body>

</html>

{{-- Renata print format (Transport Invoice). Standalone: independent of print-general. --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Invoice — {{ $customerBill->bill_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
        }

        .no-print {
            margin: 10px;
            display: flex;
            gap: 8px;
        }

        .no-print button {
            padding: 6px 18px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #333;
        }

        .btn-print {
            background: #1a6b60;
            color: #fff;
            border-color: #1a6b60;
        }

        .btn-close {
            background: #6c757d;
            color: #fff;
            border-color: #6c757d;
        }

        /* Horizontal spacing lives here (padding), not in @page's margin: @page only
           reserves top/bottom for the company pad. Screen and print therefore wrap text
           identically, which the pagination script's row measurements rely on. */
        .page {
            width: 210mm;
            margin: 0 auto;
            padding: 0 6mm;
        }

        .bill-title {
            border: 1px solid #000;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 5px 0;
            margin-bottom: 8px;
        }

        table.head {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.head td {
            padding: 3px 6px;
            font-size: 11px;
            height: 20px;
            vertical-align: bottom;
            border-bottom: 1px solid #ddd;
        }

        table.head td.lbl {
            font-weight: 700;
            white-space: nowrap;
        }

        table.head td.lbl.r {
            text-align: right;
        }

        table.head td.val-strong {
            font-weight: 700;
            font-size: 12px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }

        table.items thead {
            display: table-header-group;
        }

        table.items tr {
            page-break-inside: avoid;
        }

        table.items th {
            font-size: 9.5px;
            font-weight: 700;
            padding: 4px 2px;
            text-align: center;
            border: 1px solid #000;
            word-wrap: break-word;
            line-height: 1.3;
        }

        table.items td {
            font-size: 9.5px;
            padding: 2px 3px;
            border: 1px solid #000;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.4;
        }

        table.items td.r {
            text-align: right;
        }

        table.items td.c {
            text-align: center;
        }

        .summary-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .summary-table {
            border-collapse: collapse;
            width: 100%;
        }

        .summary-table td,
        .summary-table th {
            font-size: 12px;
            padding: 3px 7px;
            border: 1px solid #000;
        }

        .summary-table th {
            font-weight: 700;
            text-align: center;
        }

        .summary-table td.lbl {
            font-weight: 700;
            width: 60%;
        }

        .summary-table td.amt {
            text-align: right;
            width: 40%;
        }

        .summary-table tr.grand td {
            font-weight: 700;
            font-size: 13px;
        }

        .block {
            page-break-inside: avoid;
        }

        .words {
            margin-top: 12px;
            font-size: 12px;
        }

        .bank {
            margin-top: 14px;
        }

        .bank-title {
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .bank table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .bank td {
            padding: 2px 6px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }

        .bank td.lbl {
            font-weight: 700;
            white-space: nowrap;
        }

        .certify {
            margin-top: 16px;
            font-size: 12px;
            line-height: 1.6;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 44px;
        }

        .sig-table td {
            padding: 0 18px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .sig-line {
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        .page-footer {
            border-top: 1px solid #bbb;
            padding: 4px 0;
            font-size: 10px;
            color: #666;
        }

        .page-footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .tail {
            display: flow-root;
            page-break-inside: avoid;
        }

        tr.force-break,
        .tail.force-break {
            page-break-before: always;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                background: #fff;
            }

            /* Top/bottom space is reserved for the pre-printed company pad (same as the
               General format). Left/right spacing comes from .page's padding. */
            @page {
                size: A4 portrait;
                margin: 51mm 0 28mm 0;
            }
        }
    </style>
</head>

<body>

    {{-- Print / Close buttons (hidden on print) --}}
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i>&#128424;</i> Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    @php
        $items = $customerBill->items;
        $branch = \App\Models\NasFreights\NasFreightsBranch::find($customerBill->branch_id);
        $firstBooking = $items->first()?->booking;

        $companyName = 'NAS Freights And Logistics Ltd.';
        $poNo = $firstBooking?->po_number ?? '';

        $periodFrom = $customerBill->from_date ?? $customerBill->bill_date;
        $periodTo = $customerBill->to_date ?? $periodFrom;
        $billingPeriod = '';
        if ($periodFrom) {
            $billingPeriod = $periodFrom->format('Y-m') === $periodTo->format('Y-m')
                ? $periodFrom->format("F'y")
                : $periodFrom->format("M'y") . ' - ' . $periodTo->format("M'y");
        }

        $pct = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
        $aitPct = $pct($customerBill->ait_percent);
        $vatPct = $pct($customerBill->vat_percent);
        $tdsPct = $pct($customerBill->tds_percent);

        // One batched lookup for rows saved without booking_item_id (older bills), matched on booking + van no.
        $fallbackBookingItems = collect();
        $missingBookingIds = $items->filter(fn($it) => !$it->bookingItem && $it->booking_id)->pluck('booking_id')->unique();
        if ($missingBookingIds->isNotEmpty()) {
            $fallbackBookingItems = \App\Models\NasFreights\NasFreightsBookingItem::whereIn('booking_id', $missingBookingIds)
                ->get()
                ->keyBy(fn($b) => $b->booking_id . '_' . $b->cover_van_no);
        }

        $rows = $items->map(function ($item) use ($fallbackBookingItems) {
            $bItem = $item->bookingItem ?? $fallbackBookingItems->get($item->booking_id . '_' . $item->item_code);

            if ($bItem && ($bItem->location_from || $bItem->location_to)) {
                $from = (string) $bItem->location_from;
                $to = (string) $bItem->location_to;
            } else {
                $parts = explode(' - ', (string) $item->location, 2);
                $from = $parts[0];
                $to = $parts[1] ?? '';
            }

            $date = $item->delivery_date
                ? \Carbon\Carbon::parse($item->delivery_date)->format('d/m/Y')
                : ($item->booking?->delivery_date?->format('d/m/Y') ?? '—');

            return [
                'date' => $date,
                'challan' => $bItem?->challan_no ?: '—',
                'vehicle' => $item->item_code,
                'capacity' => $bItem?->capacity ?? '',
                'from' => $from,
                'to' => $to,
                'rate' => round((float) $item->d_qty * (float) $item->price - (float) $item->discount, 2),
                'ait' => (float) $item->ait_amount,
                'vat' => (float) $item->vat_amount,
                'net' => (float) $item->line_amount,
            ];
        });

        $totalRate = $rows->sum('rate');
        $totalAit = $rows->sum('ait');
        $totalVat = $rows->sum('vat');
        $totalDem = (float) $items->sum('demurrage_amount');
        $tdsAmt = (float) ($customerBill->tds_amount ?? 0);
        $grandAmt = (float) $customerBill->total_amount;

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
        $takaInt = (int) floor($grandAmt);
        $paisaInt = (int) round(($grandAmt - $takaInt) * 100);
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
        $amountInWords = $w . ' Only';
    @endphp

    <div class="page">

        <div class="bill-title">TRANSPORT INVOICE</div>

        {{-- Company / client header --}}
        <table class="head header-outer">
            <colgroup>
                <col style="width:22%">
                <col style="width:28%">
                <col style="width:17%">
                <col style="width:33%">
            </colgroup>
            <tr>
                <td class="lbl">Transport Company Name:</td>
                <td>{{ $companyName }}</td>
                <td class="lbl r">BIN No:</td>
                <td>007485303-0203</td>
            </tr>
            <tr>
                <td class="lbl">Address:</td>
                <td>{{ $branch?->address }}</td>
                <td class="lbl r">TIN No:</td>
                <td>610038500064</td>
            </tr>
            <tr>
                <td class="lbl">Contact No:</td>
                <td>{{ $branch?->phone }}</td>
                <td class="lbl r">Client Name:</td>
                <td class="val-strong">{{ $customerBill->customer_name }}</td>
            </tr>
            <tr>
                <td class="lbl">Email:</td>
                <td>pinto@nasfreightsbd.com</td>
                <td class="lbl r">Department:</td>
                <td>Supply Chain</td>
            </tr>
            <tr>
                <td class="lbl">Bill No:</td>
                <td style="font-weight:700">{{ $customerBill->bill_no }}</td>
                <td class="lbl r">Billing Period:</td>
                <td style="font-weight:700">{{ $billingPeriod }}</td>
            </tr>
            <tr>
                <td class="lbl">Bill Date:</td>
                <td style="font-weight:700">{{ $customerBill->bill_date?->format('d/m/Y') }}</td>
                <td class="lbl r">PO/Agreement No:</td>
                <td>{{ $poNo }}</td>
            </tr>
        </table>


        {{-- Items table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width:3%">SI</th>
                    <th style="width:9%">Date</th>
                    <th style="width:8%">Challan No</th>
                    <th style="width:10%">Vehicle No</th>
                    <th style="width:7%">Capacity (Ton)</th>
                    <th style="width:11%">From</th>
                    <th style="width:13%">To (Destination)</th>
                    <th style="width:10%">Rate (BDT)</th>
                    <th style="width:9%">AIT {{ $aitPct }}%</th>
                    <th style="width:9%">VAT {{ $vatPct }}%</th>
                    <th style="width:11%">Net Payable</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr class="item-row">
                        <td class="c">{{ $i + 1 }}</td>
                        <td class="c">{{ $row['date'] }}</td>
                        <td>{{ $row['challan'] }}</td>
                        <td>{{ $row['vehicle'] }}</td>
                        <td class="c">{{ $row['capacity'] }}</td>
                        <td>{{ $row['from'] }}</td>
                        <td>{{ $row['to'] }}</td>
                        <td class="r">{{ number_format($row['rate'], 2) }}</td>
                        <td class="r">{{ number_format($row['ait'], 2) }}</td>
                        <td class="r">{{ number_format($row['vat'], 2) }}</td>
                        <td class="r">{{ number_format($row['net'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Everything after the item rows. Kept together so the pagination script can reserve its height. --}}
        <div class="tail">

        {{-- Billing summary, right-aligned --}}
        <table class="summary-wrap">
            <tr>
                <td style="width:55%"></td>
                <td style="width:45%;padding:0;vertical-align:top">
                    <table class="summary-table">
                        <tr>
                            <th colspan="2">Billing Summary</th>
                        </tr>
                        <tr>
                            <td class="lbl">Total Charge</td>
                            <td class="amt">{{ number_format($totalRate, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Total AIT</td>
                            <td class="amt">{{ number_format($totalAit, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Total VAT</td>
                            <td class="amt">{{ number_format($totalVat, 2) }}</td>
                        </tr>
                        @if ($totalDem > 0)
                            <tr>
                                <td class="lbl">Demurrage</td>
                                <td class="amt">{{ number_format($totalDem, 2) }}</td>
                            </tr>
                        @endif
                        @if ($tdsAmt > 0)
                            <tr>
                                <td class="lbl">TDS ({{ $tdsPct }}%)</td>
                                <td class="amt">{{ number_format($tdsAmt, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="grand">
                            <td class="lbl">Grand Payable</td>
                            <td class="amt">{{ number_format($grandAmt, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="block">
            <p class="words"><strong>Total Taka in Words: {{ $amountInWords }}</strong></p>

            <div class="bank">
                <div class="bank-title">Bank Information</div>
                <table>
                    <colgroup>
                        <col style="width:12%">
                        <col style="width:38%">
                        <col style="width:14%">
                        <col style="width:36%">
                    </colgroup>
                    <tr>
                        <td class="lbl">Account Name:</td>
                        <td>{{ $companyName }}</td>
                        <td class="lbl">Account Number:</td>
                        <td>1111001335991</td>
                    </tr>
                    <tr>
                        <td class="lbl">Bank Name:</td>
                        <td>Mercantile Bank PLC</td>
                        <td class="lbl">Routing Number:</td>
                        <td>140260939</td>
                    </tr>
                    <tr>
                        <td class="lbl">Branch Name:</td>
                        <td>Tejgaon Gulshan Link Road</td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>

            <p class="certify">We hereby certify that the above transportation services have been completed as per agreed
                terms and conditions.</p>

            <table class="sig-table">
                <tr>
                    <td>
                        <div class="sig-line">Prepared By</div>
                    </td>
                    <td>
                        <div class="sig-line">Checked By</div>
                    </td>
                    <td>
                        <div class="sig-line">Approved By</div>
                    </td>
                    <td>
                        <div class="sig-line">Transport Provider</div>
                    </td>
                </tr>
            </table>

        </div>

        <div class="page-footer final-page-footer" style="margin-top:14px">
            <table>
                <tr>
                    <td style="width:35%;text-align:left"></td>
                    <td style="width:30%;text-align:center">Page <span class="pg-num">1</span> of <span class="pg-total">1</span></td>
                    <td style="width:35%;text-align:right">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
                </tr>
            </table>
        </div>

        </div>{{-- /.tail --}}

    </div>

    <script>
        // Per-page pagination, same method as the General format: measure the real rendered
        // row heights (this browser's font metrics and wrapping) instead of guessing a row
        // count server-side. A page footer row ("Page x of y") is inserted after the last
        // row of every page but the last. The 51mm top / 28mm bottom of each sheet is left
        // free for the pre-printed company pad.
        window.addEventListener('load', function () {
            var pageEl = document.querySelector('.page');
            var itemsTable = document.querySelector('table.items');
            var thead = itemsTable.querySelector('thead');
            var tbody = itemsTable.querySelector('tbody');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.item-row'));
            var tail = document.querySelector('.tail');
            var finalFooter = document.querySelector('.final-page-footer');
            if (!rows.length) return;

            var pxPerMm = pageEl.getBoundingClientRect().width / 210;
            // Flat 16mm safety buffer against screen/print rendering variance.
            var contentHeightPx = (297 - 51 - 28 - 16) * pxPerMm;

            var billTitle = document.querySelector('.bill-title');
            var headerOuter = document.querySelector('.header-outer');
            // 16px = title's bottom margin + the items table's top margin
            var headerBlockHeight = billTitle.getBoundingClientRect().height
                + headerOuter.getBoundingClientRect().height + 16;
            var theadHeight = thead.getBoundingClientRect().height;

            var firstPageBudget = contentHeightPx - headerBlockHeight - theadHeight;
            var otherPageBudget = contentHeightPx - theadHeight;

            function buildFooterRow(pageNum, totalPages) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="11" style="border:none;padding:0">' +
                    '<div class="page-footer"><table><tr>' +
                    '<td style="width:35%"></td>' +
                    '<td style="width:30%;text-align:center">Page ' + pageNum + ' of ' + totalPages + '</td>' +
                    '<td style="width:35%;text-align:right">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>' +
                    '</tr></table></div></td>';
                return tr;
            }

            // Reserve the footer row's own height on every page.
            var sampleFooter = buildFooterRow(1, 1);
            tbody.appendChild(sampleFooter);
            var footerRowHeight = sampleFooter.getBoundingClientRect().height;
            sampleFooter.remove();

            var breakIndexes = [];
            var used = firstPageBudget;
            rows.forEach(function (row, idx) {
                var h = row.getBoundingClientRect().height;
                if (idx > 0 && (used - h - footerRowHeight) < 0) {
                    breakIndexes.push(idx);
                    used = otherPageBudget;
                }
                used -= h;
            });

            // Summary, bank details, certification and signatures must sit together on the
            // last page. If they do not fit under the last rows, they get a page of their own.
            var tailHeight = tail.getBoundingClientRect().height + 8;
            var tailOnOwnPage = (used - tailHeight) < 0;

            var totalPages = breakIndexes.length + 1 + (tailOnOwnPage ? 1 : 0);

            var pageNum = 1;
            breakIndexes.forEach(function (idx) {
                var breakRow = rows[idx];
                breakRow.parentNode.insertBefore(buildFooterRow(pageNum, totalPages), breakRow);
                breakRow.classList.add('force-break');
                pageNum++;
            });

            if (tailOnOwnPage) {
                tbody.appendChild(buildFooterRow(pageNum, totalPages));
                tail.classList.add('force-break');
                pageNum++;
            }

            finalFooter.querySelector('.pg-num').textContent = totalPages;
            finalFooter.querySelector('.pg-total').textContent = totalPages;
        });
    </script>
</body>

</html>

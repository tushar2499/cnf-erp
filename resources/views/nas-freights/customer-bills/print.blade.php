<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Bill — {{ $customerBill->bill_no }}</title>
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

        /* Horizontal spacing lives here (padding), not in @page's margin —
           @page only reserves top/bottom (for the letterhead). This keeps
           the effective content width identical between screen preview and
           print, so the pagination script's row-height measurements (taken
           on screen load) match what will actually wrap/print. If left/right
           were handled by @page margin instead, .page would need a narrower
           width during print than on screen, and text would wrap differently
           between the two — silently invalidating the measurements. */
        .page {
            width: 210mm;
            margin: 0 auto;
            padding: 0 14mm;
        }

        .bill-title {
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .header-outer {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 5px;
            border-collapse: collapse;
        }

        .header-to {
            width: 46%;
            padding: 7px 9px;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.7;
            border-right: 1px solid #000;
        }

        .header-info {
            width: 54%;
            padding: 0;
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2.5px 6px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        .info-table td.lbl {
            font-weight: 700;
            width: 36%;
            white-space: nowrap;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items th {
            background: #000;
            color: #fff;
            font-size: 9.5px;
            padding: 3px 2px;
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

        table.items tfoot td,
        table.items tr.total-row td {
            background: #e8e8e8;
            font-weight: 700;
            font-size: 9.5px;
            padding: 3px 3px;
            border: 1px solid #000;
        }

        .summary-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .summary-table {
            border-collapse: collapse;
            width: 100%;
        }

        .summary-table td {
            font-size: 12px;
            padding: 3px 7px;
            border: 1px solid #000;
        }

        .summary-table td.lbl {
            font-weight: 600;
            width: 70%;
        }

        .summary-table td.amt {
            text-align: right;
            font-weight: 700;
            width: 30%;
        }

        .summary-table tr.gross td {
            background: #d0d0d0;
            font-weight: 700;
            font-size: 13px;
        }

        .footer {
            margin-top: 12px;
            font-size: 12px;
            line-height: 1.9;
        }

        .sig-wrap {
            margin-top: 28px;
        }

        .sig-block {
            width: 160px;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
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

        tr.force-break {
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

            /* Left/right spacing comes from .page's own padding (see above),
               not @page's margin, so screen and print always wrap text
               identically. Only top/bottom (the letterhead space) belongs
               on @page — those don't affect row heights the way width does. */
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
        $firstItem = $customerBill->items->first();
        $firstBooking = $firstItem?->booking;
        $uniqueBookings = $customerBill->items->pluck('booking')->filter()->unique('id');
        $allProducts = $uniqueBookings->flatMap(fn($b) => $b->products ?? collect());
        $goodsName =
            $allProducts->pluck('goods_name')->filter()->unique()->join(', ') ?: $firstBooking?->goods_name ?? '—';
        $totalQty = $allProducts->sum('qty') ?: $customerBill->items->sum('b_qty');
        $qtyUnit = $allProducts->first()?->qty_unit ?? '';
        $totalWeight = $allProducts->sum('net_weight');
        $weightUnit = $allProducts->first()?->weight_unit ?? '';
        $poNo = $firstBooking?->po_number ?? '';
        $lcNo = $firstBooking?->lc_no ?? '';
        $invoiceNo = $firstBooking?->invoice_no ?? '';

        $subTotal = $customerBill->items->sum('line_amount');
        $totalDem = $customerBill->items->sum('demurrage_amount');
        $totalDemDays = $customerBill->items->sum('demurrage_day');
        $tdsAmt = (float) ($customerBill->tds_amount ?? 0);
        $tdsPct = (float) ($customerBill->tds_percent ?? 0);
        $vatPct = (float) ($customerBill->vat_percent ?? 0);
        $vatAmt = (float) ($customerBill->vat_amount ?? 0);
        $grossAmt = $subTotal + $totalDem + $tdsAmt + $vatAmt;

        $ones = [
            '',
            'One',
            'Two',
            'Three',
            'Four',
            'Five',
            'Six',
            'Seven',
            'Eight',
            'Nine',
            'Ten',
            'Eleven',
            'Twelve',
            'Thirteen',
            'Fourteen',
            'Fifteen',
            'Sixteen',
            'Seventeen',
            'Eighteen',
            'Nineteen',
        ];
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
        $takaInt = (int) floor($grossAmt);
        $paisaInt = (int) round(($grossAmt - $takaInt) * 100);
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

        <div class="bill-title">TRANSPORT BILL</div>

        {{-- Header info box --}}
        <table class="header-outer">
            <tr>
                <td class="header-to">
                    <strong>To,</strong><br>
                    {{ $customerBill->customer_name }}<br>
                    {!! nl2br(e($customerBill->customer_address)) !!}
                </td>
                <td class="header-info">
                    <table class="info-table">
                        <tr>
                            <td class="lbl">Bill Date:</td>
                            <td><strong>{{ $customerBill->bill_date?->format('d/m/Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Bill No:</td>
                            <td><strong>{{ $customerBill->bill_no }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Goods Name:</td>
                            <td>{{ $goodsName }}</td>
                        </tr>
                        @if ($allProducts->isNotEmpty())
                        <tr>
                            <td class="lbl">Qty &amp; N.Weight:</td>
                            <td>
                                {{ $totalQty > 0 ? number_format($totalQty, 2) . ($qtyUnit ? ' ' . $qtyUnit : '') : '—' }}
                                @if ($totalWeight > 0)
                                    &amp; {{ number_format($totalWeight, 2) }} {{ $weightUnit }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="lbl">L/C No:</td>
                            <td>{{ $lcNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Invoice No:</td>
                            <td>{{ $invoiceNo }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">P/O No:</td>
                            <td>{{ $poNo }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Items table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width:3%">SL</th>
                    <th style="width:9%">Job No</th>
                    <th style="width:9%">Delivery Date</th>
                    <th style="width:10%">Cover Van No</th>
                    <th style="width:10%">Cover Van Type</th>
                    <th style="width:6%">Capacity</th>
                    <th style="width:4%">Qty</th>
                    <th style="width:18%">Destination</th>
                    <th style="width:9%">Net Amt</th>
                    <th style="width:5%">Dem. Days</th>
                    <th style="width:8%">Total Dem.</th>
                    <th style="width:9%">Total Amt</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customerBill->items as $i => $item)
                    @php
                        $bItem = $item->bookingItem
                            ?? ($item->booking_id
                                ? \App\Models\NasFreights\NasFreightsBookingItem::where('booking_id', $item->booking_id)
                                    ->where('cover_van_no', $item->item_code)->first()
                                : null);
                        $demDays = (float) ($item->demurrage_day ?? 0);
                        $demAmt = (float) ($item->demurrage_amount ?? 0);
                        $rowTot = $item->line_amount + $demAmt;
                        $capacity = $bItem?->capacity ?? '';
                        $vehicle = \App\Models\NasFreights\NasFreightsVehicle::where(
                            'vehicle_number',
                            $item->item_code,
                        )->first();
                        $vanType = $vehicle?->vehicle_type ?? '';
                    @endphp
                    <tr class="item-row">
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $item->booking?->job_no ?? '—' }}</td>
                        <td class="c">
                            {{ $item->booking?->delivery_date ? $item->booking->delivery_date->format('d M Y') : '—' }}
                        </td>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $vanType }}</td>
                        <td class="c">{{ $capacity }}</td>
                        <td class="r">{{ number_format($item->b_qty, 2) }}</td>
                        <td>{{ $item->location }}</td>
                        <td class="r">{{ number_format($item->line_amount, 2) }}</td>
                        <td class="c">{{ $demDays }}</td>
                        <td class="r">{{ number_format($demAmt, 2) }}</td>
                        <td class="r">{{ number_format($rowTot, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="8" style="text-align:right">Total Amount</td>
                    <td class="r">{{ number_format($subTotal, 2) }}</td>
                    <td class="c">{{ $totalDemDays }}</td>
                    <td class="r">{{ number_format($totalDem, 2) }}</td>
                    <td class="r">{{ number_format($subTotal + $totalDem, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Summary right-aligned --}}
        <table class="summary-wrap">
            <tr>
                <td style="width:55%"></td>
                <td style="width:45%;padding:0;vertical-align:top">
                    <table class="summary-table">
                        <tr>
                            <td class="lbl">TDS Amount ({{ number_format($tdsPct, 2) }}%)</td>
                            <td class="amt">{{ number_format($tdsAmt, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">VAT Amount ({{ number_format($vatPct, 2) }}%)</td>
                            <td class="amt">{{ number_format($vatAmt, 2) }}</td>
                        </tr>
                        <tr class="gross">
                            <td class="lbl">Gross Amount</td>
                            <td class="amt">{{ number_format($grossAmt, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>Total Taka In Word: {{ $amountInWords }}</strong></p>
            <p style="margin-top:6px;color:#c00000">Please make all CHEQUE payable to <strong>NAS Freights And Logistics
                    Ltd.</strong></p>
            <p style="margin-top:2px">A/C: Mercantile Bank PLC.(1111001335991)</p>
            <p style="margin-top:2px">For <strong>NAS Freights And Logistics Ltd.</strong></p>
        </div>

        <div class="sig-wrap">
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="width:70%"></td>
                    <td style="width:30%">
                        <div class="sig-block">
                            <div style="height:36px"></div>
                            <div class="sig-line"></div>
                            <div style="font-size:12px">Account Officer</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="page-footer final-page-footer">
            <table>
                <tr>
                    <td style="width:35%;text-align:left"></td>
                    <td style="width:30%;text-align:center">Page <span class="pg-num">1</span> of <span class="pg-total">1</span></td>
                    <td style="width:35%;text-align:right">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
                </tr>
            </table>
        </div>

    </div>

    <script>
        // Real per-page pagination: measure actual rendered row heights (this
        // browser's real font metrics/wrapping) instead of guessing a fixed
        // row count server-side — a static guess can't account for how much
        // text an individual destination/job-no cell wraps to, which varies
        // bill to bill. Runs once on load, before the user hits Print.
        window.addEventListener('load', function () {
            var pageEl = document.querySelector('.page');
            var itemsTable = document.querySelector('table.items');
            var thead = itemsTable.querySelector('thead');
            var tbody = itemsTable.querySelector('tbody');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.item-row'));
            if (!rows.length) return;

            var pxPerMm = pageEl.getBoundingClientRect().width / 210;
            // 5% safety buffer against any minor screen/print rendering variance.
            var contentHeightPx = (297 - 51 - 28) * pxPerMm * 0.95;

            var billTitle = document.querySelector('.bill-title');
            var headerOuter = document.querySelector('.header-outer');
            var headerBlockHeight = billTitle.getBoundingClientRect().height
                + headerOuter.getBoundingClientRect().height + 10;
            var theadHeight = thead.getBoundingClientRect().height;

            var firstPageBudget = contentHeightPx - headerBlockHeight - theadHeight;
            var otherPageBudget = contentHeightPx - theadHeight;

            var breakIndexes = [];
            var used = firstPageBudget;
            rows.forEach(function (row, idx) {
                var h = row.getBoundingClientRect().height;
                if (idx > 0 && (used - h) < 0) {
                    breakIndexes.push(idx);
                    used = otherPageBudget;
                }
                used -= h;
            });

            var totalPages = breakIndexes.length + 1;

            function buildFooterRow(pageNum) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="12" style="border:none;padding:0">' +
                    '<div class="page-footer"><table><tr>' +
                    '<td style="width:35%"></td>' +
                    '<td style="width:30%;text-align:center">Page ' + pageNum + ' of ' + totalPages + '</td>' +
                    '<td style="width:35%;text-align:right">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>' +
                    '</tr></table></div></td>';
                return tr;
            }

            var pageNum = 1;
            breakIndexes.forEach(function (idx) {
                var breakRow = rows[idx];
                breakRow.parentNode.insertBefore(buildFooterRow(pageNum), breakRow);
                breakRow.classList.add('force-break');
                pageNum++;
            });

            var finalFooter = document.querySelector('.final-page-footer');
            finalFooter.querySelector('.pg-num').textContent = totalPages;
            finalFooter.querySelector('.pg-total').textContent = totalPages;
        });
    </script>
</body>

</html>

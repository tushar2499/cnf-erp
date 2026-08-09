<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payment Order — {{ $supplierBill->pay_order_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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

        /* Company header */
        .co-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .co-logo {
            width: 60px;
            vertical-align: middle;
        }

        .co-logo img {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }

        .co-logo-default {
            width: 56px;
            height: 56px;
            background: #1a6b60;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .co-name-wrap {
            vertical-align: middle;
            text-align: center;
        }

        .co-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a6b60;
            letter-spacing: 0.5px;
        }

        .co-address {
            font-size: 9px;
            color: #333;
            margin-top: 2px;
        }

        .co-contact {
            font-size: 9px;
            color: #555;
            margin-top: 1px;
        }

        .divider {
            border: none;
            border-top: 2px solid #1a6b60;
            margin: 4px 0;
        }

        .divider-thin {
            border: none;
            border-top: 1px solid #ccc;
            margin: 3px 0;
        }

        .bill-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
            margin: 6px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Info box */
        .header-outer {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 6px;
            border-collapse: collapse;
        }

        .header-to {
            width: 55%;
            padding: 7px 9px;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.8;
            border-right: 1px solid #000;
        }

        .header-info {
            width: 45%;
            padding: 0;
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 7px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-table td.lbl {
            font-weight: 700;
            width: 45%;
            white-space: nowrap;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* Items table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
        }

        table.items th {
            background: #000;
            color: #fff;
            font-size: 7.5px;
            padding: 4px 2px;
            text-align: center;
            border: 1px solid #000;
            word-wrap: break-word;
            line-height: 1.3;
        }

        table.items td {
            font-size: 7.5px;
            padding: 2.5px 3px;
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
            font-weight: 700;
            font-size: 7.5px;
            padding: 3px 3px;
            border: 1px solid #000;
        }

        /* Summary */
        .amount-section {
            margin-top: 8px;
            font-size: 10px;
            line-height: 2;
        }

        .amount-words {
            font-weight: 700;
        }

        .note-line {
            margin-top: 3px;
            font-size: 9px;
            color: #555;
        }

        .cheque-line {
            margin-top: 5px;
            color: #c00000;
            font-size: 9.5px;
        }

        /* Signature */
        .sig-wrap {
            margin-top: 30px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
        }

        .page-footer {
            border-top: 1px solid #bbb;
            padding: 4px 0;
            font-size: 8px;
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

            table.items th {
                color: #000;
                border: 1px solid #000 !important;
            }

            table.items td {
                border: 1px solid #000 !important;
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

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">&#128424; Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    @php
        $totalNod = $supplierBill->items->sum('b_qty');
        $totalRate = $supplierBill->items->sum('price');
        $totalDemDay = $supplierBill->items->sum('demurrage_day');
        $totalDemAmt = $supplierBill->items->sum('demurrage_amount');
        $totalAmt = $supplierBill->items->sum('line_amount');

        // Amount in words (Bangladeshi number system)
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
        $grandTotal = (float) $supplierBill->total_amount;
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
        $amountInWords = $w . ' Only';
    @endphp

    <div class="page">

        <div class="bill-title">Supplier Payment Order</div>

        <hr class="divider-thin">

        {{-- Info Box --}}
        <table class="header-outer">
            <tr>
                <td class="header-to">
                    <strong>To,</strong><br>
                    <strong>{{ $supplierBill->supplier_name ?: '—' }}</strong><br>
                    @if ($supplier?->address)
                        {!! nl2br(e($supplier->address)) !!}
                    @endif
                </td>
                <td class="header-info">
                    <table class="info-table">
                        <tr>
                            <td class="lbl">Payment Date:</td>
                            <td>{{ $supplierBill->bill_date?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Payment Order No:</td>
                            <td><strong>{{ $supplierBill->pay_order_no }}</strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Period:</td>
                            <td>{{ $supplierBill->from_date?->format('d/m/Y') }} –
                                {{ $supplierBill->to_date?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Remarks:</td>
                            <td>{{ $supplierBill->note ?: '—' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Items Table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width:4%">SL</th>
                    <th style="width:9%">Job No</th>
                    <th style="width:10%">Delivery Date</th>
                    <th style="width:11%">Cover Van No</th>
                    <th style="width:20%">Location</th>
                    <th style="width:6%">NOD</th>
                    <th style="width:9%">Rate</th>
                    <th style="width:8%">Dem. Days</th>
                    <th style="width:10%">Dem. Amount</th>
                    <th style="width:13%">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplierBill->items as $i => $item)
                    <tr class="item-row">
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $item->booking?->job_no ?? '—' }}</td>
                        <td class="c">
                            {{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->format('d M Y') : '—' }}
                        </td>
                        <td>{{ $item->item_code ?? '—' }}</td>
                        <td>{{ $item->location ?? '—' }}</td>
                        <td class="c">{{ number_format($item->b_qty, 2) }}</td>
                        <td class="r">{{ number_format($item->price, 2) }}</td>
                        <td class="c">{{ number_format((float) ($item->demurrage_day ?? 0), 2) }}</td>
                        <td class="r">{{ number_format((float) ($item->demurrage_amount ?? 0), 2) }}</td>
                        <td class="r">{{ number_format($item->line_amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total</td>
                    <td class="c">{{ number_format($totalNod, 2) }}</td>
                    <td class="r">{{ number_format($totalRate, 2) }}</td>
                    <td class="c">{{ $totalDemDay }}</td>
                    <td class="r">{{ number_format($totalDemAmt, 2) }}</td>
                    <td class="r">{{ number_format($totalAmt, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Amount section --}}
        <div class="amount-section">
            <div class="note-line"><strong>NOTE:</strong> INCLUDING TAX &amp; VAT</div>
            <div class="amount-words" style="margin-top:5px">
                Total Taka In Word: {{ $amountInWords }}
            </div>
            <div class="cheque-line">
                Please make all CHEQUE payable to
                <strong>{{ $company?->name ?? 'NAS Freights And Logistics Ltd.' }}</strong>
            </div>
        </div>

        {{-- Signature --}}
        <div class="sig-wrap">
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="width:65%"></td>
                    <td style="width:35%;vertical-align:bottom">
                        <div class="sig-block">
                            <div style="height:38px"></div>
                            <div class="sig-line"></div>
                            <div style="font-size:10px;font-weight:700">For
                                {{ $company?->name ?? 'NAS Freights And Logistics Ltd.' }}</div>
                            <div style="font-size:9px;color:#555;margin-top:2px">Authorised Signatory</div>
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
        // text an individual location/job-no cell wraps to, which varies
        // order to order. Runs once on load, before the user hits Print.
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
                tr.innerHTML = '<td colspan="10" style="border:none;padding:0">' +
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

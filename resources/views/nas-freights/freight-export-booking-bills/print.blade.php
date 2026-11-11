<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freight Invoice — {{ $bill->bill_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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

        .btn-print { background: #1a6b60; color: #fff; border-color: #1a6b60; }
        .btn-close  { background: #6c757d; color: #fff; border-color: #6c757d; }

        .page {
            width: 210mm;
            margin: 0 auto;
            padding: 0 8mm;
        }

        .to-section {
            font-size: 11px;
            line-height: 1.7;
            margin-bottom: 10px;
        }

        /* Long addresses wrap at commas, not mid-word */
        .to-address { max-width: 60mm; }
        .to-address .addr-part { display: inline-block; max-width: 100%; }

        .bill-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-decoration: underline;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .ref-date-line {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        /* Main two-column invoice table */
        table.inv {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
        }

        table.inv th {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 6px;
            text-align: center;
            border: 1px solid #000;
        }

        table.inv td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 0;
        }

        /* Shipment details nested table inside left cell */
        table.ship {
            width: 100%;
            border-collapse: collapse;
        }

        table.ship tr td {
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 2.5px 5px;
            font-size: 10px;
            vertical-align: top;
        }

        table.ship tr:last-child td {
            border-bottom: 1px solid #ddd;
        }

        table.ship td.k {
            font-weight: 700;
            white-space: nowrap;
            width: 44%;
        }

        table.ship td.sep {
            width: 4%;
            white-space: nowrap;
        }

        /* Right-side nested table (items + summary) */
        table.items-inner {
            width: 100%;
            border-collapse: collapse;
        }

        table.items-inner tr td {
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 2.5px 5px;
            font-size: 10px;
        }

        table.items-inner tr:last-child td {
            border-bottom: none;
        }

        table.items-inner td.amt {
            text-align: right;
            white-space: nowrap;
        }

        table.items-inner tr.sub-row td,
        table.items-inner tr.total-row td {
            font-weight: 700;
            font-size: 11px;
            border-top: 1px solid #000;
        }

        table.items-inner tr.words-row td {
            font-weight: 700;
            font-size: 10.5px;
            border-top: 1px solid #000;
            padding: 5px;
            line-height: 1.6;
        }

        .footer-ref {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-top: 4px;
            margin-bottom: 2px;
        }

        .company-name {
            font-size: 11px;
            font-weight: 700;
            text-decoration: underline;
            margin-top: 3px;
        }

        .sig-wrap { margin-top: 30px; }

        .sig-block { width: 180px; }

        .sig-dots {
            font-size: 11px;
            letter-spacing: 1px;
            color: #333;
        }

        .page-footer {
            border-top: 1px solid #bbb;
            padding: 4px 0;
            font-size: 9px;
            color: #666;
            margin-top: 10px;
        }

        .page-footer table { width: 100%; border-collapse: collapse; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; background: #fff; }

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
        $booking = $bill->exportBooking;
        $items   = $bill->items;

        /* Cargo totals (weights normalised to KGS) */
        $weightToKgs = ['KG' => 1, 'MT' => 1000, 'LB' => 0.45359237];
        /* Quantity = packages (package_qty, else quantity), totalled per package type (BAG, CARTON, CRATE …) */
        $quantityByType = $booking->items
            ->map(fn ($cargo) => [
                'type'  => strtoupper(trim((string) $cargo->package_type)),
                'count' => (int) ($cargo->package_qty ?: $cargo->quantity),
            ])
            ->groupBy('type')
            ->map(fn ($rows) => $rows->sum('count'))
            ->filter(fn ($count) => $count > 0);
        $totalKgs      = (float) $booking->items->sum(
            fn ($cargo) => (float) $cargo->gross_weight * ($weightToKgs[strtoupper((string) $cargo->weight_unit)] ?? 1)
        );
        $totalCbm      = (float) $booking->items->sum('volume_cbm');

        /* Address → HTML: each saved line break stays a separate line; comma-separated parts wrap as whole units */
        $formatAddress = fn (?string $address): string => collect(preg_split('/\R/', (string) $address))
            ->map(fn ($line) => collect(explode(',', $line))
                ->map(fn ($part) => trim($part))
                ->filter()
                ->map(fn ($part) => '<span class="addr-part">'.e($part).'</span>')
                ->implode(', '))
            ->filter()
            ->implode('<br>');

        /* Bill Ref / Job Ref: base value, with the bill's own prefix text appended when it has one */
        $billRefBase = $booking->party_bill_ref_no ?: $bill->bill_no;
        $billRefNo   = $bill->bill_prefix_ref ? $billRefBase.$bill->bill_prefix_ref : $billRefBase;
        $jobRefNo    = $bill->jobRefNo();

        $isOverseas  = $bill->bill_type === 'Overseas Agent';
        $isForeign   = $bill->currency !== 'BDT';
        $showForeign = $isForeign;       // customer+foreign or overseas
        $showBdt     = ! $isOverseas;   // customer bills only
        $dualCols    = $showForeign && $showBdt; // customer + foreign → both columns

        $grandTotalBdt     = (float) ($bill->total_bdt_amount + $bill->vat_amount_bdt);
        $grandTotalForeign = (float) ($bill->total_amount + $bill->vat_amount);
        $hasVat            = $bill->vat_amount > 0 || $bill->vat_title;

        /* Amount in words (BDT — only used for customer bills) */
        $ones   = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
        $tnsArr = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $hunds  = function (int $n) use ($ones, $tnsArr): string {
            $o = '';
            if ($n >= 100) { $o .= $ones[(int)($n/100)].' Hundred '; $n %= 100; }
            if ($n >= 20)  { $o .= $tnsArr[(int)($n/10)].($n%10 ? ' '.$ones[$n%10] : '').' '; $n = 0; }
            if ($n > 0)    { $o .= $ones[$n].' '; }
            return $o;
        };
        $takaInt  = (int) floor($grandTotalBdt);
        $paisaInt = (int) round(($grandTotalBdt - $takaInt) * 100);
        $n = $takaInt; $w = '';
        if ($n >= 10000000) { $w .= $hunds((int)($n/10000000)).'Crore ';  $n %= 10000000; }
        if ($n >= 100000)   { $w .= $hunds((int)($n/100000)).'Lakh ';     $n %= 100000; }
        if ($n >= 1000)     { $w .= $hunds((int)($n/1000)).'Thousand ';   $n %= 1000; }
        if ($n > 0)         { $w .= $hunds((int)$n); }
        $w = trim($w).' Taka';
        if ($paisaInt > 0) { $w .= ' and '.trim($hunds($paisaInt)).' Paisa'; }
        $amountInWords = $w.' Only';

        /* Rowspan: items(≥1) + subtotal + (vat?) + total + (words? — customer only) */
        $itemCount      = $items->count();
        $totalRightRows = max($itemCount, 1) + 2 + ($hasVat ? 1 : 0) + ($showBdt ? 1 : 0);
    @endphp

    <div class="page">

        {{-- To: address —  customer or overseas agent --}}
        <div class="to-section">
            <strong>To,</strong><br>
            @if($isOverseas)
                <strong>{{ $booking->overseasAgent?->name ?? '—' }}</strong><br>
                @if(!empty($booking->overseasAgent?->address))
                    <div class="to-address">{!! $formatAddress($booking->overseasAgent->address) !!}</div>
                @endif
            @else
                <strong>{{ $booking->customer?->name ?? '—' }}</strong><br>
                @if(!empty($booking->customer?->address))
                    <div class="to-address">{!! $formatAddress($booking->customer->address) !!}</div>
                @endif
            @endif
        </div>

        {{-- Title --}}
        <div class="bill-title">{{ $isOverseas ? 'Agent Invoice' : 'Freight Invoice' }}</div>

        {{-- Bill Ref / Date --}}
        <div class="ref-date-line">
            <span>BILL REF NO: {{ $billRefNo }}</span>
            <span>DATE: {{ ($booking->party_bill_date ?? $bill->bill_date)?->format('d.m.Y') }}</span>
        </div>

        {{-- Main invoice table --}}
        <table class="inv">
            <thead>
                <tr>
                    <th style="width:44%">Shipment Details</th>
                    <th style="{{ $dualCols ? 'width:28%' : 'width:38%' }}">Description</th>
                    @if($showForeign)
                        <th style="width:{{ $dualCols ? '14%' : '18%' }}">Amount ({{ $bill->currency }})</th>
                    @endif
                    @if($showBdt)
                        <th style="width:{{ $dualCols ? '14%' : '18%' }}">Amount {{ $dualCols ? '(BDT)' : 'in BDT' }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>

                {{-- First row: shipment cell spans all right rows --}}
                <tr>
                    <td rowspan="{{ $totalRightRows }}" style="vertical-align:top">
                        <table class="ship">
                            @if($booking->invoice_no)
                            <tr>
                                <td class="k">S.B. NO</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->invoice_no }}</td>
                            </tr>
                            @endif
                            @if($booking->export_bl_no)
                            <tr>
                                <td class="k">MAWB / B/L NO.</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->export_bl_no }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="k">DATE</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->booking_date?->format('d.m.Y') ?? '—' }}</td>
                            </tr>
                            @if($booking->pol)
                            <tr>
                                <td class="k">PORT OF LOADING</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->pol }}</td>
                            </tr>
                            @endif
                            @if($booking->pod)
                            <tr>
                                <td class="k">DESTINATION</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->pod }}</td>
                            </tr>
                            @endif
                            @if($booking->commodity_description)
                            <tr>
                                <td class="k">COMMODITY</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->commodity_description }}</td>
                            </tr>
                            @endif
                            @if($quantityByType->isNotEmpty())
                            <tr>
                                <td class="k">QUANTITY</td>
                                <td class="sep">:</td>
                                <td>
                                    @foreach($quantityByType as $type => $count)
                                        {{ number_format($count) }}{{ $type !== '' ? ' '.$type : '' }}@if(! $loop->last)<br>@endif
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                            @if($totalKgs > 0)
                            <tr>
                                <td class="k">GROSS WEIGHT</td>
                                <td class="sep">:</td>
                                <td>{{ number_format($totalKgs) }} KGS</td>
                            </tr>
                            @endif
                            @if($totalCbm > 0)
                            <tr>
                                <td class="k">CBM</td>
                                <td class="sep">:</td>
                                <td>{{ number_format($totalCbm) }}</td>
                            </tr>
                            @endif
                            @if($booking->exp_no)
                            <tr>
                                <td class="k">EXP NO</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->exp_no }}</td>
                            </tr>
                            @endif
                            @if($booking->partyInvoices->isNotEmpty())
                            <tr>
                                <td class="k">PARTY INVOICE NO</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->partyInvoices->pluck('invoice_no')->implode(', ') }}</td>
                            </tr>
                            @endif
                            @if($booking->partyInvoices->whereNotNull('invoice_date')->isNotEmpty())
                            <tr>
                                <td class="k">PARTY INVOICE DATE</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->partyInvoices->whereNotNull('invoice_date')->map(fn ($invoice) => $invoice->invoice_date->format('d.m.Y'))->implode(', ') }}</td>
                            </tr>
                            @endif
                            @if(! $isOverseas && $booking->overseasAgent)
                            <tr>
                                <td class="k">CONSIGNEE</td>
                                <td class="sep">:</td>
                                <td>{{ $booking->overseasAgent->name }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>

                    {{-- First item row (or blank placeholder) --}}
                    @if($itemCount > 0)
                        @php $it = $items->first(); @endphp
                        <td style="padding:2.5px 5px;font-size:10px;">1. {{ $it->name }}</td>
                        @if($showForeign)
                            <td style="padding:2.5px 5px;font-size:10px;text-align:right;white-space:nowrap;">{{ number_format($it->amount, 2) }}</td>
                        @endif
                        @if($showBdt)
                            <td style="padding:2.5px 5px;font-size:10px;text-align:right;white-space:nowrap;">{{ number_format($it->amount_bdt, 2) }}</td>
                        @endif
                    @else
                        <td style="padding:2.5px 5px;">&nbsp;</td>
                        @if($showForeign)<td></td>@endif
                        @if($showBdt)<td></td>@endif
                    @endif
                </tr>

                {{-- Remaining item rows --}}
                @foreach($items->slice(1) as $i => $item)
                <tr>
                    <td style="padding:2.5px 5px;font-size:10px;border-top:1px solid #ddd;">{{ $i + 2 }}. {{ $item->name }}</td>
                    @if($showForeign)
                        <td style="padding:2.5px 5px;font-size:10px;text-align:right;white-space:nowrap;border-top:1px solid #ddd;">{{ number_format($item->amount, 2) }}</td>
                    @endif
                    @if($showBdt)
                        <td style="padding:2.5px 5px;font-size:10px;text-align:right;white-space:nowrap;border-top:1px solid #ddd;">{{ number_format($item->amount_bdt, 2) }}</td>
                    @endif
                </tr>
                @endforeach

                {{-- Sub-Total --}}
                <tr>
                    <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;border-top:2px solid #000;">Sub-Total</td>
                    @if($showForeign)
                        <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;white-space:nowrap;border-top:2px solid #000;">{{ number_format($bill->total_amount, 2) }}</td>
                    @endif
                    @if($showBdt)
                        <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;white-space:nowrap;border-top:2px solid #000;">{{ number_format($bill->total_bdt_amount, 2) }}</td>
                    @endif
                </tr>

                {{-- VAT row (conditional) --}}
                @if($hasVat)
                <tr>
                    <td style="padding:3px 5px;font-size:10.5px;text-align:right;border-top:1px solid #ddd;">{{ $bill->vat_title ?: 'VAT' }}</td>
                    @if($showForeign)
                        <td style="padding:3px 5px;font-size:10.5px;text-align:right;white-space:nowrap;border-top:1px solid #ddd;">{{ number_format($bill->vat_amount, 2) }}</td>
                    @endif
                    @if($showBdt)
                        <td style="padding:3px 5px;font-size:10.5px;text-align:right;white-space:nowrap;border-top:1px solid #ddd;">{{ number_format($bill->vat_amount_bdt, 2) }}</td>
                    @endif
                </tr>
                @endif

                {{-- Total Amount --}}
                <tr>
                    <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;border-top:1px solid #000;"><strong>Total Amount</strong></td>
                    @if($showForeign)
                        <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;white-space:nowrap;border-top:1px solid #000;"><strong>{{ number_format($grandTotalForeign, 2) }}</strong></td>
                    @endif
                    @if($showBdt)
                        <td style="padding:3px 5px;font-weight:700;font-size:11px;text-align:right;white-space:nowrap;border-top:1px solid #000;"><strong>{{ number_format($grandTotalBdt, 2) }}</strong></td>
                    @endif
                </tr>

                {{-- Total Receivable in words (customer bills only) --}}
                @if($showBdt)
                <tr>
                    <td colspan="{{ $dualCols ? 3 : 2 }}" style="padding:5px 6px;font-weight:700;font-size:10.5px;border-top:2px solid #000;line-height:1.6;">
                        Total Receivable Taka {{ number_format($grandTotalBdt, 2) }}<br>
                        <span style="font-weight:400">({{ $amountInWords }})</span>
                        @if($dualCols)
                            <br><span style="font-weight:700;">Exchange Rate: 1 {{ $bill->currency }} = BDT {{ number_format($bill->exchange_rate, 2) }}</span>
                        @endif
                    </td>
                </tr>
                @endif

            </tbody>
        </table>

        {{-- Footer ref line --}}
        <div class="footer-ref" style="margin-top:5px;">
            <span>JOB REF NO: {{ $jobRefNo }}</span>
        </div>

        {{-- Company --}}
        <div class="company-name">NAS FREIGHTS AND LOGISTICS LTD.</div>

        {{-- Signature --}}
        <div class="sig-wrap">
            <div class="sig-block">
                <div class="sig-dots">.................................</div>
                <div style="font-size:11px;margin-top:2px;">Authorized Signature</div>
            </div>
        </div>


    </div>

</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transport Bill — {{ $customerBill->bill_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background:#fff; }

.no-print { margin: 10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; border-color:#1a6b60; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:210mm; margin:0 auto; padding:6mm 14mm 10mm 14mm; }

.pad-header { height:30mm; width:100%; }

.bill-title { text-align:center; font-size:14px; font-weight:700; text-decoration:underline; margin-bottom:8px; letter-spacing:1px; }

.header-outer { width:100%; border:1px solid #000; margin-bottom:5px; border-collapse:collapse; }
.header-to { width:46%; padding:7px 9px; vertical-align:top; font-size:10px; line-height:1.7; border-right:1px solid #000; }
.header-info { width:54%; padding:0; vertical-align:top; }
.info-table { width:100%; border-collapse:collapse; }
.info-table td { padding:2.5px 6px; font-size:10px; border-bottom:1px solid #ddd; vertical-align:top; }
.info-table td.lbl { font-weight:700; width:36%; white-space:nowrap; }
.info-table tr:last-child td { border-bottom:none; }

table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th { background:#000; color:#fff; font-size:7.5px; padding:3px 2px; text-align:center; border:1px solid #000; word-wrap:break-word; line-height:1.3; }
table.items td { font-size:7.5px; padding:2px 3px; border:1px solid #000; vertical-align:middle; word-wrap:break-word; line-height:1.4; }
table.items td.r { text-align:right; }
table.items td.c { text-align:center; }
table.items tfoot td { background:#e8e8e8; font-weight:700; font-size:7.5px; padding:3px 3px; border:1px solid #000; }
table.items tfoot td.r { text-align:right; }
table.items tfoot td.c { text-align:center; }

.summary-wrap { width:100%; border-collapse:collapse; margin-top:2px; }
.summary-table { border-collapse:collapse; width:100%; }
.summary-table td { font-size:10px; padding:3px 7px; border:1px solid #000; }
.summary-table td.lbl { font-weight:600; width:70%; }
.summary-table td.amt { text-align:right; font-weight:700; width:30%; }
.summary-table tr.gross td { background:#d0d0d0; font-weight:700; font-size:10.5px; }

.footer { margin-top:12px; font-size:10px; line-height:1.9; }
.sig-wrap { margin-top:28px; }
.sig-block { width:160px; text-align:center; }
.sig-line { border-top:1px solid #000; margin-bottom:3px; }

.powered { margin-top:14px; border-top:1px solid #bbb; padding-top:4px; font-size:8px; color:#666; }
.powered table { width:100%; border-collapse:collapse; }

@media print {
    .no-print { display:none !important; }
    body { margin:0; background:#fff; }
    .page { width:100%; margin:0; padding:0; }
    @page { size:A4 portrait; margin:6mm 14mm 10mm 14mm; }
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
$firstItem    = $customerBill->items->first();
$firstBooking = $firstItem?->booking;
$allProducts  = $customerBill->items->flatMap(fn($i) => $i->booking?->products ?? collect());
$goodsName    = $allProducts->pluck('goods_name')->filter()->unique()->join(', ') ?: ($firstBooking?->goods_name ?? '—');
$totalQty     = $allProducts->sum('qty') ?: $customerBill->items->sum('b_qty');
$qtyUnit      = $allProducts->first()?->qty_unit ?? '';
$totalWeight  = $allProducts->sum('net_weight');
$weightUnit   = $allProducts->first()?->weight_unit ?? '';
$poNo         = $firstBooking?->po_number ?? '';
$lcNo         = $firstBooking?->lc_no ?? '';
$lcDate       = $firstBooking?->job_date?->format('d.m.Y') ?? '';
$invoiceNo    = $firstBooking?->invoice_no ?? '';
$invoiceDate  = $firstBooking?->delivery_date?->format('d.m.Y') ?? '';

$subTotal     = $customerBill->items->sum('line_amount');
$totalDem     = $customerBill->items->sum('demurrage_amount');
$totalDemDays = $customerBill->items->sum('demurrage_day');
$tdsAmt       = (float)($customerBill->tds_amount ?? 0);
$tdsPct       = (float)($customerBill->tds_percent ?? 0);
$vatPct       = (float)($customerBill->vat_percent ?? 0);
$vatAmt       = (float)($customerBill->vat_amount ?? 0);
$grossAmt     = $subTotal + $totalDem + $tdsAmt + $vatAmt;

$ones   = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
           'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
$tnsArr = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
$hunds  = function(int $n) use ($ones, $tnsArr): string {
    $o = '';
    if ($n >= 100) { $o .= $ones[(int)($n/100)].' Hundred '; $n %= 100; }
    if ($n >= 20)  { $o .= $tnsArr[(int)($n/10)].($n%10?' '.$ones[$n%10]:'').' '; $n=0; }
    if ($n > 0)    { $o .= $ones[$n].' '; }
    return $o;
};
$takaInt  = (int) floor($grossAmt);
$paisaInt = (int) round(($grossAmt - $takaInt) * 100);
$n = $takaInt; $w = 'BDT ';
if ($n >= 10000000) { $w .= $hunds((int)($n/10000000)).'Crore '; $n %= 10000000; }
if ($n >= 100000)   { $w .= $hunds((int)($n/100000)).'Lakh '; $n %= 100000; }
if ($n >= 1000)     { $w .= $hunds((int)($n/1000)).'Thousand '; $n %= 1000; }
if ($n > 0)         { $w .= $hunds((int)$n); }
$w = trim($w).' Taka';
if ($paisaInt > 0)  { $w .= ' and '.trim($hunds($paisaInt)).' Paisa'; }
$amountInWords = $w.' Only';
@endphp

<div class="page">

    {{-- Blank space for pre-printed pad letterhead --}}
    <div class="pad-header"></div>

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
                    <tr><td class="lbl">Bill Date:</td><td>{{ $customerBill->bill_date?->format('d/m/Y') }}</td></tr>
                    <tr><td class="lbl">Bill No:</td><td><strong>{{ $customerBill->bill_no }}</strong></td></tr>
                    <tr><td class="lbl">Goods Name:</td><td>{{ $goodsName }}</td></tr>
                    <tr>
                        <td class="lbl">Qty &amp; N.Weight:</td>
                        <td>
                            {{ $totalQty > 0 ? number_format($totalQty,2).($qtyUnit ? ' '.$qtyUnit : '') : '—' }}
                            @if($totalWeight > 0) &amp; {{ number_format($totalWeight,2) }} {{ $weightUnit }}@endif
                        </td>
                    </tr>
                    <tr><td class="lbl">P/O No:</td><td>{{ $poNo }}</td></tr>
                    <tr><td class="lbl">L/C No:</td><td>{{ $lcNo }}{{ $lcNo && $lcDate ? ' DT: '.$lcDate : '' }}</td></tr>
                    <tr><td class="lbl">Invoice No:</td><td>{{ $invoiceNo }}{{ $invoiceNo && $invoiceDate ? ' DT: '.$invoiceDate : '' }}</td></tr>
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
            @foreach($customerBill->items as $i => $item)
            @php
                $bItem    = $item->bookingItem;
                $demDays  = (float)($item->demurrage_day ?? 0);
                $demAmt   = (float)($item->demurrage_amount ?? 0);
                $rowTot   = $item->line_amount + $demAmt;
                $capacity = $bItem?->capacity ?? '';
                $vehicle  = \App\Models\NasFreights\NasFreightsVehicle::where('vehicle_number', $item->item_code)->first();
                $vanType  = $vehicle?->vehicle_type ?? '';
            @endphp
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $item->booking?->job_no ?? '—' }}</td>
                <td class="c">{{ $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->format('d M Y') : '—' }}</td>
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
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="text-align:right">Total Amount (without VAT)</td>
                <td class="r">{{ number_format($subTotal, 2) }}</td>
                <td class="c">{{ $totalDemDays }}</td>
                <td class="r">{{ number_format($totalDem, 2) }}</td>
                <td class="r">{{ number_format($subTotal + $totalDem, 2) }}</td>
            </tr>
        </tfoot>
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
                        <td class="lbl">VAT ({{ number_format($vatPct, 2) }}%)</td>
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
        <p style="margin-top:6px;color:#c00000">Please make all CHEQUE payable to <strong>NAS Freights And Logistics Ltd.</strong></p>
        <p style="margin-top:2px">A/C: Mercantile Bank PLC.(1111001335991)</p>
        <p style="margin-top:2px">For <strong>NAS Freights And Logistics Ltd.</strong></p>
    </div>

    <div class="sig-wrap">
        <table style="width:100%;border-collapse:collapse"><tr>
            <td style="width:70%"></td>
            <td style="width:30%">
                <div class="sig-block">
                    <div style="height:36px"></div>
                    <div class="sig-line"></div>
                    <div style="font-size:10px">Account Officer</div>
                </div>
            </td>
        </tr></table>
    </div>

    <div class="powered">
        <table>
            <tr>
                <td>Powered By: <a href="https://a4bbd.com/" style="color:#666;text-decoration:none">Advertising For Business - A4B</a></td>
                <td style="text-align:center">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>

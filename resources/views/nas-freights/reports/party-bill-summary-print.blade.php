<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transport Bill Summary — {{ request('from_date') }} to {{ request('to_date') }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:9px; color:#000; background:#fff; }
.no-print { margin:10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; } .btn-close { background:#6c757d; color:#fff; }
/* Horizontal spacing lives in .page's own padding, not @page's margin, so
   screen and print always wrap text identically — the pagination script
   measures real row heights on screen load, and those measurements only
   stay valid for print if nothing about the available width changes
   between the two contexts. Only top/bottom (letterhead space) belongs on
   @page, since vertical margin doesn't affect how cells wrap. */
.page { width:210mm; margin:0 auto; padding:0 10mm; }
.co-name { text-align:center; font-size:14pt; font-weight:bold; margin-bottom:2px; }
.co-address { text-align:center; font-size:8.5pt; color:#333; margin-bottom:1px; }
.divider { border-top:1.5px solid #000; margin:4px 0; }
.report-title { text-align:center; font-size:12pt; font-weight:bold; text-decoration:underline; margin:5px 0 6px; }
.info-box { width:100%; border-collapse:collapse; border:1px solid #000; margin-bottom:8px; }
.info-box td { padding:6px 10px; font-size:9pt; vertical-align:top; }
.info-box td.left { width:50%; border-right:1px solid #000; line-height:1.8; }
.info-box td.right { line-height:2; }
table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th { background:none; color:#000; font-size:7pt; font-weight:bold; padding:3px 2px; text-align:center; border:1px solid #000; word-wrap:break-word; }
table.items td { font-size:7.5pt; padding:2px 3px; border:1px solid #ccc; vertical-align:middle; word-wrap:break-word; line-height:1.4; }
table.items td.r { text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
table.items td.c { text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.even-row { background:#f5faf9; }
table.items tfoot td, table.items tr.total-row td { background:none; color:#000; font-weight:bold; font-size:8pt; padding:3px; border:1px solid #000; border-top:2px solid #000; }
table.items tfoot td.r, table.items tr.total-row td.r { text-align:right; }
.amount-words { margin-top:10px; font-size:9.5pt; }
.sig-wrap { margin-top:22px; font-size:9.5pt; }
.sig-wrap .sig-space { height:34px; }
.sig-wrap .sig-line { border-top:1px solid #000; padding-top:3px; font-weight:bold; width:200px; }
.page-footer { margin-top:6px; border-top:1px solid #ccc; padding-top:2px; font-size:6.5pt; color:#888; }
.page-footer table { width:100%; border-collapse:collapse; }
@media print {
    .no-print { display:none !important; }
    @page { size:A4 portrait; margin:51mm 0 28mm 0; }
}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>
@php
$totalNet  = $bills->sum('sub_total');
$totalTds  = $bills->sum('tds_amount');
$totalVat  = $bills->sum('vat_amount');
$totalAmt  = $bills->sum('total_amount');
$fromLabel = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M, Y') : '—';
$toLabel   = request('to_date')   ? \Carbon\Carbon::parse(request('to_date'))->format('d M, Y')   : '—';
$coName    = $company?->name ?? 'NAS Freights And Logistics Ltd.';

$ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
$tnsArr = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
$hunds = function (int $n) use ($ones, $tnsArr): string {
    $o = '';
    if ($n >= 100) {
        $o .= $ones[(int) ($n / 100)].' Hundred ';
        $n %= 100;
    }
    if ($n >= 20) {
        $o .= $tnsArr[(int) ($n / 10)].($n % 10 ? ' '.$ones[$n % 10] : '').' ';
        $n = 0;
    }
    if ($n > 0) {
        $o .= $ones[$n].' ';
    }
    return $o;
};
$takaInt = (int) floor($totalAmt);
$paisaInt = (int) round(($totalAmt - $takaInt) * 100);
$n = $takaInt;
$w = 'BDT ';
if ($n >= 10000000) {
    $w .= $hunds((int) ($n / 10000000)).'Crore ';
    $n %= 10000000;
}
if ($n >= 100000) {
    $w .= $hunds((int) ($n / 100000)).'Lakh ';
    $n %= 100000;
}
if ($n >= 1000) {
    $w .= $hunds((int) ($n / 1000)).'Thousand ';
    $n %= 1000;
}
if ($n > 0) {
    $w .= $hunds((int) $n);
}
$w = trim($w).' Taka';
if ($paisaInt > 0) {
    $w .= ' and '.trim($hunds($paisaInt)).' Paisa';
}
$amountInWords = $w.' Only';
@endphp
<div class="page">

    <div class="report-title">Transport Bill Summary</div>
    <table class="info-box">
        <tr>
            <td class="left">
                <strong>To,</strong><br>
                @if($customer)<strong>{{ $customer->name }}</strong><br>{!! nl2br(e($customer->address ?? '')) !!}@else<em>All Customers</em>@endif
            </td>
            <td class="right">
                <strong>Billing Period:</strong> &nbsp; From: {{ $fromLabel }} &nbsp; To: {{ $toLabel }}<br>
                <strong>Total Bill Count:</strong> &nbsp; {{ $bills->count() }}
            </td>
        </tr>
    </table>
    <table class="items">
        <thead>
            <tr>
                <th style="width:3%">SL</th>
                <th style="width:7%">Job No</th>
                <th style="width:10%">Bill No</th>
                <th style="width:8%">Bill Date</th>
                <th style="width:9%">LC No</th>
                <th style="width:11%">Invoice No</th>
                <th style="width:9%">Net Amount</th>
                <th style="width:4%">TDS %</th>
                <th style="width:7%">TDS Amt</th>
                <th style="width:5%">Vat %</th>
                <th style="width:10%">Vat Amt</th>
                <th style="width:10%">Total Amt</th>
                <th style="width:7%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $i => $bill)
            @php
                $jobNos = $bill->items->pluck('booking.job_no')->filter()->unique()->implode(', ');
                $lcNos  = $bill->items->pluck('booking.lc_no')->filter()->unique()->implode(', ');
                $invNos = $bill->items->pluck('booking.invoice_no')->filter()->unique()->implode(', ');
                $cls    = ($i % 2 === 1) ? ' class="even-row item-row"' : ' class="item-row"';
            @endphp
            <tr{{ $cls }}>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $jobNos ?: '—' }}</td>
                <td>{{ $bill->bill_no }}</td>
                <td class="c">{{ $bill->bill_date?->format('d M y') }}</td>
                <td>{{ $lcNos ?: '—' }}</td>
                <td>{{ $invNos ?: '—' }}</td>
                <td class="r">{{ number_format($bill->sub_total, 2) }}</td>
                <td class="c">{{ number_format($bill->tds_percent, 2) }}</td>
                <td class="r">{{ number_format($bill->tds_amount, 2) }}</td>
                <td class="c">{{ number_format($bill->vat_percent, 2) }}</td>
                <td class="r">{{ number_format($bill->vat_amount, 2) }}</td>
                <td class="r">{{ number_format($bill->total_amount, 2) }}</td>
                <td>{{ $bill->note ?: ' ' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align:right">Total ({{ $bills->count() }} bills)</td>
                <td class="r">{{ number_format($totalNet, 2) }}</td>
                <td></td>
                <td class="r">{{ number_format($totalTds, 2) }}</td>
                <td></td>
                <td class="r">{{ number_format($totalVat, 2) }}</td>
                <td class="r">{{ number_format($totalAmt, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="amount-words"><strong>Total Taka In Word: {{ $amountInWords }}</strong></div>

    <table class="sig-wrap">
        <tr>
            <td>Thanking You</td>
        </tr>
        <tr>
            <td class="sig-space"></td>
        </tr>
        <tr>
            <td class="sig-line">{{ $coName }}</td>
        </tr>
    </table>

    <div class="page-footer"><table><tr>
        <td>Powered By: Advertising For Business - A4B</td>
        <td style="text-align:right">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
    </tr></table></div>
</div>
</body>
</html>

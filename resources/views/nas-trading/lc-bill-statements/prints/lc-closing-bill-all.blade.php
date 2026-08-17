<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LC Closing Bills — {{ $lcBillStatement->bill_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page {
    size: A4 portrait;
    margin-top: 1.3in;
    margin-bottom: 0.9in;
    margin-left: 0.4in;
    margin-right: 0.4in;
}
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #f2f2f2; }
.no-print { padding:10px 20px; background:#fff; border-bottom:1px solid #ddd; margin-bottom:16px; display:flex; gap:8px; align-items:center; }
.btn-act { display:inline-flex; align-items:center; gap:6px; border:none; padding:7px 18px; font-size:12px; font-weight:600; border-radius:5px; cursor:pointer; line-height:1.4; }
.btn-act-print { background:#0d6efd; color:#fff; }
.btn-act-print:hover { background:#0b5ed7; }
.btn-act-close { background:#6c757d; color:#fff; }
.btn-act-close:hover { background:#5c636a; }
@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
    .doc { max-width: 100%; padding: 0; }
    #print-wrap .doc { break-after: page; }
    #print-wrap .doc:last-child { break-after: auto; }
}
#print-wrap .doc { width: 100%; max-width: 720px; margin: 0 auto 24px; background: #fff; padding: 24px 28px; }
.header-row { display:flex; justify-content:space-between; margin-bottom:10px; font-size:11px; }
.consignee-block { margin:10px 0 6px; font-size:11px; line-height:1.7; }
.subject { margin:10px 0 10px; font-size:11px; font-weight:bold; }
table { width:100%; border-collapse:collapse; }
table th, table td { border:1px solid #000; padding:4px 6px; font-size:10px; }
table thead th { background:#e8e8e8; font-weight:bold; text-align:center; }
.text-right { text-align:right; }
.text-center { text-align:center; }
.in-word { margin:10px 0; font-size:10px; }
.footer-block { margin-top:30px; font-size:11px; line-height:1.9; }
.sig-space { height:40px; }
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-act btn-act-print" onclick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg> Print All</button>
    <button class="btn-act btn-act-close" onclick="window.close()"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg> Close</button>
</div>

@php
function lcClosingAllNumberToWords(float $amount): string {
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
             'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $convert = function (int $n) use (&$convert, $ones, $tens): string {
        if ($n === 0) return '';
        if ($n < 20) return $ones[$n];
        if ($n < 100) return $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
        if ($n < 1000) return $ones[intdiv($n, 100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : '');
        if ($n < 100000) return $convert(intdiv($n, 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
        if ($n < 10000000) return $convert(intdiv($n, 100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convert($n % 100000) : '');
        return $convert(intdiv($n, 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
    };
    $taka   = (int) abs($amount);
    $poisha = (int) round((abs($amount) - $taka) * 100);
    $words  = $taka > 0 ? $convert($taka) : 'Zero';
    if ($poisha > 0) { $words .= ' and ' . $convert($poisha) . ' Poisha'; }
    return $words;
}
@endphp

<div id="print-wrap">
@foreach ($lcBillStatement->items as $item)
@php
    $lc         = $item->lc;
    $lcCost     = (float) ($lc?->total_lc_cost ?? 0);
    $bankCharge = (float) ($lc?->bank_charge ?? 0);
    $insurance  = (float) ($lc?->insurance_amt ?? 0);
    $totalCost  = $lcCost + $bankCharge + $insurance;
    $advance    = (float) ($lc?->advance_received_bdt ?? 0);
    $dues       = $totalCost - $advance;
    $invoiceNo  = $lc?->lc_closing_bill ?? $lcBillStatement->bill_no;
    $invoiceDate = $lc?->lc_closing_bill_date ?? $lcBillStatement->bill_date;
@endphp
<div class="doc">
    <div class="header-row">
        <span><strong>Invoice No:</strong> {{ $invoiceNo }}&nbsp;&nbsp;&nbsp; <strong>Date:</strong> {{ $invoiceDate?->format('d-m-Y') }}</span>
    </div>

    <div class="consignee-block">
        <strong>Consignee:</strong><br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">
        Subj: LC Closing bill Under LC No. {{ $lc?->lc_no ?? '-' }}
        Dt: {{ $lc?->lc_open_date?->format('d.m.Y') ?? '-' }}
        PFI No. {{ $lc?->pfi_no ?? '-' }}
        Dt: {{ $lc?->pfi_date?->format('d.m.Y') ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">SL</th>
                <th>Description</th>
                <th style="width:140px">BDT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>LC Cost Total</td>
                <td class="text-right">{{ $lcCost ? number_format($lcCost, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Bank Charge</td>
                <td class="text-right">{{ $bankCharge ? number_format($bankCharge, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Insurance Charge</td>
                <td class="text-right">{{ $insurance ? number_format($insurance, 2) : '-' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="border-left:1px solid #000;font-weight:bold;">Total Cost BDT.</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($totalCost, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right" style="border-left:1px solid #000;">(-) Advance BDT.</td>
                <td class="text-right">{{ $advance ? number_format($advance, 2) : '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right" style="border-left:1px solid #000;font-weight:bold;">Dues BDT.</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($dues, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="in-word">
        <strong>In Word:</strong> BDT {{ lcClosingAllNumberToWords($dues) }} only.
    </div>

    <div class="footer-block">
        Thanking You.<br><br>
        Yours Faithfully,<br>
        <div class="sig-space"></div>
        <br>
        <strong>Deputy Manager</strong><br>
        <strong>NAS Trading</strong>
    </div>

    @if ($lcBillStatement->enclosed)
    <div style="margin-top:20px;font-size:11px;">
        Enclosed: {{ $lcBillStatement->enclosed }}
    </div>
    @endif
</div>
@endforeach
</div>
</body>
</html>

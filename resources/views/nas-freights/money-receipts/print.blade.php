<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Money Receipt — {{ $moneyReceipt->receipt_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background:#fff; }

.no-print { margin: 10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; border-color:#1a6b60; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:210mm; margin:0 auto; padding:10mm 16mm 12mm 16mm; }

/* Company header */
.co-header { width:100%; border-collapse:collapse; margin-bottom:6px; }
.co-logo img { width:58px; height:58px; object-fit:contain; }
.co-logo-default { width:58px; height:58px; background:#1a6b60; border-radius:6px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; color:#fff; font-weight:700; text-align:center; line-height:1; }
.co-name-wrap { vertical-align:middle; text-align:center; }
.co-name { font-size:16px; font-weight:700; color:#1a6b60; letter-spacing:0.5px; }
.co-address { font-size:9px; color:#444; margin-top:2px; }
.co-contact { font-size:9px; color:#666; margin-top:1px; }

.divider { border:none; border-top:2px solid #1a6b60; margin:5px 0; }
.divider-thin { border:none; border-top:1px solid #ccc; margin:4px 0; }

.bill-title { text-align:center; font-size:15px; font-weight:700; text-decoration:underline;
    margin:7px 0 10px; letter-spacing:1px; text-transform:uppercase; }

/* Receipt no + date top-right */
.meta-bar { width:100%; border-collapse:collapse; margin-bottom:10px; }
.meta-bar td.left  { vertical-align:top; }
.meta-bar td.right { text-align:right; vertical-align:top; font-size:10.5px; line-height:1.9; }

/* Main receipt box */
.receipt-box { border:1px solid #000; border-radius:2px; padding:12px 14px; margin-bottom:10px; }
.row-line { width:100%; border-collapse:collapse; margin-bottom:6px; }
.row-line td.lbl { font-weight:700; width:38%; font-size:11px; white-space:nowrap; vertical-align:top; padding:2px 0; }
.row-line td.colon { width:2%; text-align:center; padding:2px 4px; vertical-align:top; }
.row-line td.val { font-size:11px; vertical-align:top; padding:2px 0; border-bottom:1px dotted #999; }

.amount-big { font-size:22px; font-weight:700; color:#1a6b60; text-align:center;
    border:2px solid #1a6b60; border-radius:4px; padding:8px 0; margin:10px 0 6px; letter-spacing:1px; }
.amount-words { text-align:center; font-size:11px; font-style:italic; margin-bottom:4px; }

/* Footer */
.note-bar { margin-top:6px; font-size:10px; color:#555; }

/* Signature row */
.sig-wrap { margin-top:32px; }
.sig-cell { vertical-align:bottom; text-align:center; }
.sig-line { border-top:1px solid #000; padding-top:3px; font-size:10px; }

.powered { margin-top:14px; border-top:1px solid #ccc; padding-top:4px; font-size:8px; color:#888; }
.powered table { width:100%; border-collapse:collapse; }

@media print {
    .no-print { display:none !important; }
    body { margin:0; background:#fff; }
    .page { width:100%; margin:0; padding:0; }
    @page { size:A4 portrait; margin:10mm 16mm 12mm 16mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

@php
$amount = (float) $moneyReceipt->amount_received;

// Amount in words
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
$takaInt  = (int) floor($amount);
$paisaInt = (int) round(($amount - $takaInt) * 100);
$n = $takaInt; $w = 'BDT ';
if ($n >= 10000000) { $w .= $hunds((int)($n/10000000)).'Crore '; $n %= 10000000; }
if ($n >= 100000)   { $w .= $hunds((int)($n/100000)).'Lakh '; $n %= 100000; }
if ($n >= 1000)     { $w .= $hunds((int)($n/1000)).'Thousand '; $n %= 1000; }
if ($n > 0)         { $w .= $hunds((int)$n); }
$w = trim($w).' Taka';
if ($paisaInt > 0)  { $w .= ' and '.trim($hunds($paisaInt)).' Paisa'; }
$amountInWords = $w.' Only';

$coName = $company?->name ?? 'NAS Freights And Logistics Ltd.';
@endphp

<div class="page">

    {{-- Company Header --}}
    <table class="co-header">
        <tr>
            <td class="co-logo" style="width:68px;vertical-align:middle;">
                @if($company?->logo)
                    <img src="{{ asset('storage/'.$company->logo) }}" alt="Logo">
                @else
                    <div class="co-logo-default">NAS</div>
                @endif
            </td>
            <td class="co-name-wrap">
                <div class="co-name">{{ $coName }}</div>
                @if($company?->address)
                    <div class="co-address">{{ $company->address }}</div>
                @endif
                @if($company?->phone || $company?->email)
                    <div class="co-contact">
                        @if($company->phone) Phone: {{ $company->phone }} @endif
                        @if($company->phone && $company->email) &nbsp;|&nbsp; @endif
                        @if($company->email) Email: {{ $company->email }} @endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">
    <div class="bill-title">Money Receipt</div>
    <hr class="divider-thin">

    {{-- Receipt No & Date --}}
    <table class="meta-bar">
        <tr>
            <td class="left"></td>
            <td class="right">
                <strong>Receipt No:</strong> {{ $moneyReceipt->receipt_no }}<br>
                <strong>Date:</strong> {{ $moneyReceipt->receipt_date?->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- Receipt Box --}}
    <div class="receipt-box">

        <table class="row-line">
            <tr>
                <td class="lbl">Received From</td>
                <td class="colon">:</td>
                <td class="val"><strong>{{ $moneyReceipt->customer_name }}</strong></td>
            </tr>
        </table>

        <div class="amount-big">BDT {{ number_format($amount, 2) }}</div>
        <div class="amount-words">( {{ $amountInWords }} )</div>

        <table class="row-line" style="margin-top:8px">
            <tr>
                <td class="lbl">Being Payment of Bill</td>
                <td class="colon">:</td>
                <td class="val">
                    {{ $moneyReceipt->bill_no }}
                    @if($moneyReceipt->bill_amount)
                        &nbsp;—&nbsp; Bill Amount: <strong>BDT {{ number_format($moneyReceipt->bill_amount, 2) }}</strong>
                    @endif
                </td>
            </tr>
        </table>

        <table class="row-line">
            <tr>
                <td class="lbl">Payment Mode</td>
                <td class="colon">:</td>
                <td class="val">{{ $moneyReceipt->payment_mode }}</td>
            </tr>
        </table>

        @if($moneyReceipt->reference_no)
        <table class="row-line">
            <tr>
                <td class="lbl">Reference / Cheque No</td>
                <td class="colon">:</td>
                <td class="val">{{ $moneyReceipt->reference_no }}</td>
            </tr>
        </table>
        @endif

        @if($moneyReceipt->note)
        <table class="row-line">
            <tr>
                <td class="lbl">Note</td>
                <td class="colon">:</td>
                <td class="val">{{ $moneyReceipt->note }}</td>
            </tr>
        </table>
        @endif

    </div>

    {{-- Signature --}}
    <div class="sig-wrap">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td style="width:50%" class="sig-cell">
                    <div style="height:38px"></div>
                    <div class="sig-line">Received By (Customer Signature)</div>
                </td>
                <td style="width:10%"></td>
                <td style="width:40%" class="sig-cell">
                    <div style="height:38px"></div>
                    <div class="sig-line">For {{ $coName }}</div>
                    <div style="font-size:9px;color:#555;margin-top:2px">Authorised Signatory</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="powered">
        <table>
            <tr>
                <td>Powered By: <a href="https://a4bbd.com/" style="color:#888;text-decoration:none">Advertising For Business - A4B</a></td>
                <td style="text-align:center">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
                <td style="text-align:right">Entry By: {{ $moneyReceipt->entry_by }}</td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>

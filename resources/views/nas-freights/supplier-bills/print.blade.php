<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supplier Payment Order — {{ $supplierBill->pay_order_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background:#fff; }

.no-print { margin: 10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; border-color:#1a6b60; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:210mm; margin:0 auto; padding:8mm 14mm 10mm 14mm; }

/* Company header */
.co-header { width:100%; border-collapse:collapse; margin-bottom:6px; }
.co-logo { width:60px; vertical-align:middle; }
.co-logo img { width:56px; height:56px; object-fit:contain; }
.co-logo-default { width:56px; height:56px; background:#1a6b60; border-radius:6px;
    display:flex; align-items:center; justify-content:center; font-size:20px;
    color:#fff; font-weight:700; line-height:1; text-align:center; }
.co-name-wrap { vertical-align:middle; text-align:center; }
.co-name { font-size:16px; font-weight:700; color:#1a6b60; letter-spacing:0.5px; }
.co-address { font-size:9px; color:#333; margin-top:2px; }
.co-contact { font-size:9px; color:#555; margin-top:1px; }

.divider { border:none; border-top:2px solid #1a6b60; margin:4px 0; }
.divider-thin { border:none; border-top:1px solid #ccc; margin:3px 0; }

.bill-title { text-align:center; font-size:14px; font-weight:700; text-decoration:underline;
    margin:6px 0; letter-spacing:1px; text-transform:uppercase; }

/* Info box */
.header-outer { width:100%; border:1px solid #000; margin-bottom:6px; border-collapse:collapse; }
.header-to { width:55%; padding:7px 9px; vertical-align:top; font-size:10px; line-height:1.8; border-right:1px solid #000; }
.header-info { width:45%; padding:0; vertical-align:top; }
.info-table { width:100%; border-collapse:collapse; }
.info-table td { padding:3px 7px; font-size:10px; border-bottom:1px solid #eee; vertical-align:top; }
.info-table td.lbl { font-weight:700; width:45%; white-space:nowrap; }
.info-table tr:last-child td { border-bottom:none; }

/* Items table */
table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th { background:#1a6b60; color:#fff; font-size:7.5px; padding:4px 2px;
    text-align:center; border:1px solid #000; word-wrap:break-word; line-height:1.3; }
table.items td { font-size:7.5px; padding:2.5px 3px; border:1px solid #ccc;
    vertical-align:middle; word-wrap:break-word; line-height:1.4; }
table.items td.r { text-align:right; }
table.items td.c { text-align:center; }
table.items tfoot td { background:#e4ede4; font-weight:700; font-size:7.5px;
    padding:3px 3px; border:1px solid #000; }
table.items tfoot td.r { text-align:right; }
table.items tfoot td.c { text-align:center; }

/* Summary */
.amount-section { margin-top:8px; font-size:10px; line-height:2; }
.amount-words { font-weight:700; }
.note-line { margin-top:3px; font-size:9px; color:#555; }
.cheque-line { margin-top:5px; color:#c00000; font-size:9.5px; }

/* Signature */
.sig-wrap { margin-top:30px; }
.sig-block { text-align:center; }
.sig-line { border-top:1px solid #000; margin-bottom:3px; }

.powered { margin-top:12px; border-top:1px solid #bbb; padding-top:4px; font-size:8px; color:#666; }
.powered table { width:100%; border-collapse:collapse; }

@media print {
    .no-print { display:none !important; }
    body { margin:0; background:#fff; }
    .page { width:100%; margin:0; padding:0; }
    @page { size:A4 portrait; margin:8mm 14mm 10mm 14mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

@php
$totalNod    = $supplierBill->items->sum('b_qty');
$totalRate   = $supplierBill->items->sum('price');
$totalDemDay = $supplierBill->items->sum('demurrage_day');
$totalDemAmt = $supplierBill->items->sum('demurrage_amount');
$totalAmt    = $supplierBill->items->sum('line_amount');

// Amount in words (Bangladeshi number system)
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
$grandTotal = (float)$supplierBill->total_amount;
$takaInt    = (int) floor($grandTotal);
$paisaInt   = (int) round(($grandTotal - $takaInt) * 100);
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

    {{-- Company Header --}}
    <table class="co-header">
        <tr>
            <td class="co-logo">
                @if($company?->logo)
                    <img src="{{ asset('storage/'.$company->logo) }}" alt="Logo">
                @else
                    <div class="co-logo-default">NAS</div>
                @endif
            </td>
            <td class="co-name-wrap">
                <div class="co-name">{{ $company?->name ?? 'NAS Freights And Logistics Ltd.' }}</div>
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

    <div class="bill-title">Supplier Payment Order</div>

    <hr class="divider-thin">

    {{-- Info Box --}}
    <table class="header-outer">
        <tr>
            <td class="header-to">
                <strong>To,</strong><br>
                <strong>{{ $supplierBill->supplier_name ?: '—' }}</strong><br>
                @if($supplier?->address)
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
                        <td>{{ $supplierBill->from_date?->format('d/m/Y') }} – {{ $supplierBill->to_date?->format('d/m/Y') }}</td>
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
            @foreach($supplierBill->items as $i => $item)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $item->booking?->job_no ?? '—' }}</td>
                <td class="c">
                    {{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->format('d M Y') : '—' }}
                </td>
                <td>{{ $item->item_code ?? '—' }}</td>
                <td>{{ $item->location ?? '—' }}</td>
                <td class="c">{{ number_format($item->b_qty, 2) }}</td>
                <td class="r">{{ number_format($item->price, 2) }}</td>
                <td class="c">{{ number_format((float)($item->demurrage_day ?? 0), 2) }}</td>
                <td class="r">{{ number_format((float)($item->demurrage_amount ?? 0), 2) }}</td>
                <td class="r">{{ number_format($item->line_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;font-weight:700">Total</td>
                <td class="c">{{ number_format($totalNod, 2) }}</td>
                <td class="r">{{ number_format($totalRate, 2) }}</td>
                <td class="c">{{ $totalDemDay }}</td>
                <td class="r">{{ number_format($totalDemAmt, 2) }}</td>
                <td class="r">{{ number_format($totalAmt, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Amount section --}}
    <div class="amount-section">
        <div class="note-line"><strong>NOTE:</strong> INCLUDING TAX &amp; VAT</div>
        <div class="amount-words" style="margin-top:5px">
            Total Taka In Word: {{ $amountInWords }}
        </div>
        <div class="cheque-line">
            Please make all CHEQUE payable to <strong>{{ $company?->name ?? 'NAS Freights And Logistics Ltd.' }}</strong>
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
                        <div style="font-size:10px;font-weight:700">For {{ $company?->name ?? 'NAS Freights And Logistics Ltd.' }}</div>
                        <div style="font-size:9px;color:#555;margin-top:2px">Authorised Signatory</div>
                    </div>
                </td>
            </tr>
        </table>
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

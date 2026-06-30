<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthwise Transport Bill Details</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:9px; color:#000; background:#fff; }
.no-print { margin:10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; } .btn-close { background:#6c757d; color:#fff; }
.page { width:297mm; margin:0 auto; padding:6mm 10mm 10mm; }
.co-name { text-align:center; font-size:14pt; font-weight:bold; margin-bottom:2px; }
.co-address { text-align:center; font-size:8.5pt; color:#333; margin-bottom:1px; }
.divider { border-top:1.5px solid #000; margin:4px 0; }
.report-title { text-align:center; font-size:12pt; font-weight:bold; text-decoration:underline; margin:5px 0 6px; text-transform:uppercase; }
.info-box { width:100%; border-collapse:collapse; border:1px solid #000; margin-bottom:8px; }
.info-box td { padding:6px 10px; font-size:9pt; vertical-align:top; }
.info-box td.left { width:50%; border-right:1px solid #000; line-height:1.8; }
.info-box td.right { line-height:2; }
table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th { background:#000; color:#fff; font-size:7pt; padding:3px 2px; text-align:center; border:1px solid #000; word-wrap:break-word; line-height:1.2; }
table.items td { font-size:7.5pt; padding:2px 3px; border:1px solid #ccc; vertical-align:middle; word-wrap:break-word; line-height:1.4; }
table.items td.r { text-align:right; } table.items td.c { text-align:center; }
.even-row { background:#f5faf9; }
table.items tfoot td { background:#d4e8d4; font-weight:bold; font-size:8pt; padding:3px; border:1px solid #000; }
table.items tfoot td.r { text-align:right; }
.powered { margin-top:6px; border-top:1px solid #ccc; padding-top:2px; font-size:6.5pt; color:#888; }
.powered table { width:100%; border-collapse:collapse; }
@media print {
    .no-print { display:none !important; }
    .page { width:100%; margin:0; padding:0; }
    @page { size:A4 landscape; margin:6mm 10mm 10mm; }
}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>
@php
$vanNos   = $items->pluck('item_code')->filter()->unique();
$vehicles = \App\Models\NasFreights\NasFreightsVehicle::whereIn('vehicle_number', $vanNos)->pluck('vehicle_type', 'vehicle_number');
$totalNet = 0; $totalDem = 0; $totalVat = 0; $totalAmt = 0;
$fromLabel = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M, Y') : '—';
$toLabel   = request('to_date')   ? \Carbon\Carbon::parse(request('to_date'))->format('d M, Y')   : '—';
$coName    = $company?->name ?? 'NAS Freights And Logistics Ltd.';
@endphp
<div class="page">
    <div class="co-name">{{ $coName }}</div>
    @if($company?->address)<div class="co-address">{{ $company->address }}</div>@endif
    <div class="divider"></div>
    <div class="report-title">Monthwise Transport Bill Details</div>
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
                <th style="width:11%">Bill No</th>
                <th style="width:7%">Bill Date</th>
                <th style="width:9%">Cover Van No</th>
                <th style="width:9%">Vehicle Type</th>
                <th style="width:6%">Capacity</th>
                <th style="width:11%">Source</th>
                <th style="width:11%">Destination</th>
                <th style="width:8%">Net Amount</th>
                <th style="width:7%">Total Dem.</th>
                <th style="width:5%">Vat %</th>
                <th style="width:7%">Vat Amt</th>
                <th style="width:7%">Total Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            @php
                $bill     = $item->bill;
                $bItem    = $item->bookingItem;
                $src      = $bItem?->location_from ?? '';
                $dst      = $bItem?->location_to ?? '';
                $cap      = $bItem?->capacity ?? '';
                $dem      = (float)($item->demurrage_amount ?: 0);
                $vatPct   = (float)($bill?->vat_percent ?? 0);
                $vatAmt   = round($item->line_amount * $vatPct / 100, 2);
                $rowTotal = $item->line_amount + $dem + $vatAmt;
                $vanType  = $vehicles[$item->item_code] ?? '';
                $totalNet += $item->line_amount; $totalDem += $dem; $totalVat += $vatAmt; $totalAmt += $rowTotal;
                $cls = ($i % 2 === 1) ? ' class="even-row"' : '';
            @endphp
            <tr{{ $cls }}>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $bill?->bill_no }}</td>
                <td class="c">{{ $bill?->bill_date?->format('d M Y') }}</td>
                <td>{{ $item->item_code }}</td>
                <td>{{ $vanType }}</td>
                <td class="c">{{ $cap }}</td>
                <td>{{ $src }}</td>
                <td>{{ $dst }}</td>
                <td class="r">{{ number_format($item->line_amount, 2) }}</td>
                <td class="r">{{ number_format($dem, 2) }}</td>
                <td class="c">{{ number_format($vatPct, 2) }}</td>
                <td class="r">{{ number_format($vatAmt, 2) }}</td>
                <td class="r">{{ number_format($rowTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="text-align:right;font-weight:bold">Total ({{ $items->count() }} items)</td>
                <td class="r">{{ number_format($totalNet, 2) }}</td>
                <td class="r">{{ number_format($totalDem, 2) }}</td>
                <td></td>
                <td class="r">{{ number_format($totalVat, 2) }}</td>
                <td class="r">{{ number_format($totalAmt, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="powered"><table><tr>
        <td>Powered By: Advertising For Business - A4B</td>
        <td style="text-align:center">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
    </tr></table></div>
</div>
</body>
</html>

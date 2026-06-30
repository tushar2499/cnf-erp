<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:7.5pt; color:#000; }

.co-name { text-align:center; font-size:13pt; font-weight:bold; margin-bottom:2px; }
.co-address { text-align:center; font-size:8pt; color:#333; margin-bottom:4px; }
.divider { border-top:1.5px solid #000; margin:3px 0; }
.report-title { text-align:center; font-size:11pt; font-weight:bold; margin:4px 0 2px; text-decoration:underline; }
.date-bar { text-align:center; font-size:8pt; margin-bottom:5px; }

table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th {
    background:#1a6b60; color:#fff; font-size:6.5pt; padding:3px 2px;
    text-align:center; border:1px solid #000; word-wrap:break-word; line-height:1.2;
}
table.items td {
    font-size:7pt; padding:2px 2px; border:1px solid #bbb;
    vertical-align:middle; word-wrap:break-word; line-height:1.3;
}
table.items td.r { text-align:right; }
table.items td.c { text-align:center; }
.even-row { background:#f5faf9; }
table.items tfoot td {
    background:#d4e8d4; font-weight:bold; font-size:7.5pt;
    padding:3px 2px; border:1px solid #000;
}
table.items tfoot td.r { text-align:right; }

.powered { margin-top:6px; border-top:1px solid #ccc; padding-top:2px; font-size:6pt; color:#888; }
</style>
</head>
<body>

@php
$totalSupplier = $rows->sum('supplier_rate');
$totalCustomer = $rows->sum('customer_rate');
$totalProfit   = $rows->sum(fn($r) => $r->customer_rate - $r->supplier_rate);
$statusLabel   = request('status') ? request('status').' ' : '';
$coName        = $company?->name ?? 'NAS Freights And Logistics Ltd.';
@endphp

<div class="co-name">{{ $coName }}</div>
@if($company?->address)
<div class="co-address">{{ $company->address }}</div>
@endif

<div class="divider"></div>
<div class="report-title">{{ $statusLabel }}Booking Report</div>

<div class="date-bar">
    @if(request('from_date') || request('to_date'))
        From Date: {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') : '—' }}
        &nbsp;&nbsp;&nbsp;&nbsp;
        To Date: {{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') : '—' }}
    @endif
    @if(request('customer_name')) &nbsp;&nbsp; Customer: {{ request('customer_name') }} @endif
    @if(request('supplier_name')) &nbsp;&nbsp; Supplier: {{ request('supplier_name') }} @endif
</div>

<table class="items">
    <thead>
        <tr>
            <th style="width:3%">SL</th>
            <th style="width:8%">Job No</th>
            <th style="width:6%">Job Date</th>
            <th style="width:6%">Entry Date</th>
            <th style="width:5%">Entry By</th>
            <th style="width:5%">Sales Person</th>
            <th style="width:10%">Customer</th>
            <th style="width:10%">Supplier</th>
            <th style="width:7%">Cover Van</th>
            <th style="width:14%">Location</th>
            <th style="width:7%">Supplier Rate</th>
            <th style="width:7%">Customer Rate</th>
            <th style="width:7%">Profit</th>
            <th style="width:4%">Remarks</th>
            <th style="width:4%">Billed</th>
            <th style="width:6%">Bill No</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $item)
        @php
            $b      = $item->booking;
            $loc    = trim(($item->location_from ?? '') . ($item->location_to ? ' - '.$item->location_to : ''));
            $profit = $item->customer_rate - $item->supplier_rate;
            $cls    = ($i % 2 === 1) ? ' class="even-row"' : '';
        @endphp
        <tr{{ $cls }}>
            <td class="c">{{ $i + 1 }}</td>
            <td>{{ $b?->job_no }}</td>
            <td class="c">{{ $b?->job_date?->format('d M Y') }}</td>
            <td class="c">{{ $b?->created_at?->format('d M Y') }}</td>
            <td>{{ $b?->entry_by }}</td>
            <td>{{ $b?->sales_person_name }}</td>
            <td>{{ $b?->customer_name }}</td>
            <td>{{ $item->supplier_name }}</td>
            <td>{{ $item->cover_van_no }}</td>
            <td>{{ $loc }}</td>
            <td class="r">{{ number_format($item->supplier_rate, 2) }}</td>
            <td class="r">{{ number_format($item->customer_rate, 2) }}</td>
            <td class="r">{{ number_format($profit, 2) }}</td>
            <td>{{ $b?->note }}</td>
            <td class="c">{{ $item->is_billed ? 'Billed' : 'Pending' }}</td>
            <td>{{ $item->bill_no ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10" style="text-align:right;font-weight:bold;">Total ({{ $rows->count() }} rows)</td>
            <td class="r">{{ number_format($totalSupplier, 2) }}</td>
            <td class="r">{{ number_format($totalCustomer, 2) }}</td>
            <td class="r">{{ number_format($totalProfit, 2) }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

<div class="powered">
    Powered By: Advertising For Business - A4B &nbsp;&nbsp;&nbsp; Print Date: {{ now()->format('d/m/Y g:i A') }}
</div>

</body>
</html>

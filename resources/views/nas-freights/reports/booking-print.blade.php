<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Report</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:9px; color:#000; background:#fff; }

.no-print { margin:10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a6b60; color:#fff; border-color:#1a6b60; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:297mm; margin:0 auto; padding:6mm 10mm 10mm; }

.co-header { width:100%; border-collapse:collapse; margin-bottom:5px; }
.co-logo img { width:54px; height:54px; object-fit:contain; }
.co-logo-default { width:54px; height:54px; background:#1a6b60; border-radius:5px;
    display:flex; align-items:center; justify-content:center;
    font-size:16px; color:#fff; font-weight:700; text-align:center; line-height:1; }
.co-name-wrap { vertical-align:middle; text-align:center; }
.co-name { font-size:16px; font-weight:700; color:#000; }
.co-address { font-size:9px; color:#333; margin-top:2px; }

.divider { border:none; border-top:1.5px solid #000; margin:4px 0; }

.report-title { text-align:center; font-size:13px; font-weight:700; margin:5px 0 4px; }

.date-bar { text-align:center; font-size:10px; margin-bottom:6px; }
.date-bar span { display:inline-block; margin:0 30px; }

table.items { width:100%; border-collapse:collapse; table-layout:fixed; }
table.items th { background:#000; color:#fff; font-size:7px; padding:3px 2px;
    text-align:center; border:1px solid #000; word-wrap:break-word; line-height:1.3; font-weight:700; }
table.items td { font-size:7.5px; padding:2px 3px; border:1px solid #ccc;
    vertical-align:middle; word-wrap:break-word; line-height:1.4; }
table.items td.r { text-align:right; }
table.items td.c { text-align:center; }
table.items tfoot td { background:#d4e8d4; font-weight:700; font-size:8px;
    padding:3px; border:1px solid #000; }
table.items tfoot td.r { text-align:right; }
table.items tfoot td.c { text-align:center; }

.powered { margin-top:10px; border-top:1px solid #bbb; padding-top:3px; font-size:7px; color:#888; }
.powered table { width:100%; border-collapse:collapse; }

@media print {
    .no-print { display:none !important; }
    body { margin:0; background:#fff; }
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
$totalSupplier = $rows->sum('supplier_rate');
$totalCustomer = $rows->sum('customer_rate');
$totalProfit   = $rows->sum(fn($r) => $r->customer_rate - $r->supplier_rate);

$statusLabel  = request('status') ? request('status').' ' : '';
$coName       = $company?->name ?? 'NAS Freights And Logistics Ltd.';
@endphp

<div class="page">

    {{-- Company Header --}}
    <table class="co-header">
        <tr>
            <td style="width:70px;vertical-align:middle;">
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
            </td>
            <td style="width:70px;"></td>
        </tr>
    </table>

    <hr class="divider">

    <div class="report-title">{{ $statusLabel }}Booking Report</div>

    <div class="date-bar">
        @if(request('from_date') || request('to_date'))
        <span><strong>From Date:</strong> &nbsp; {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') : '—' }}</span>
        <span><strong>To Date:</strong> &nbsp; {{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') : '—' }}</span>
        @endif
        @if(request('customer_name')) <span><strong>Customer:</strong> {{ request('customer_name') }}</span> @endif
        @if(request('supplier_name')) <span><strong>Supplier:</strong> {{ request('supplier_name') }}</span> @endif
        @if(request('vehicle'))       <span><strong>Vehicle:</strong> {{ request('vehicle') }}</span> @endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:3%">SL</th>
                <th style="width:8%">Job No</th>
                <th style="width:6.5%">Job Date</th>
                <th style="width:6.5%">Entry Date</th>
                <th style="width:5%">Entry By</th>
                <th style="width:5%">Sales Person</th>
                <th style="width:10%">Customer</th>
                <th style="width:10%">Supplier</th>
                <th style="width:8%">Cover Van Details</th>
                <th style="width:14%">Location</th>
                <th style="width:7%">Supplier Rate</th>
                <th style="width:7%">Customer Rate</th>
                <th style="width:7%">Profit</th>
                <th style="width:8%">Remarks</th>
                <th style="width:5%">Billed</th>
                <th style="width:7%">Bill No</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $item)
            @php
                $b      = $item->booking;
                $loc    = trim(($item->location_from ?? '') . ($item->location_to ? ' - '.$item->location_to : ''));
                $profit = $item->customer_rate - $item->supplier_rate;
            @endphp
            <tr>
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
                <td colspan="10" style="text-align:right;font-weight:700">Total ({{ $rows->count() }} rows)</td>
                <td class="r">{{ number_format($totalSupplier, 2) }}</td>
                <td class="r">{{ number_format($totalCustomer, 2) }}</td>
                <td class="r">{{ number_format($totalProfit, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="powered">
        <table>
            <tr>
                <td>Powered By: <a href="https://a4bbd.com/" style="color:#888;text-decoration:none">Advertising For Business - A4B</a></td>
                <td style="text-align:center">Print Date: {{ now()->format('d/m/Y g:i A') }}</td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>

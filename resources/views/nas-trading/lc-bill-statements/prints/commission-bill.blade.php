<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LC Commission Bill — {{ $item->serial_number ?? $lcBillStatement->bill_no }}</title>
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
}
.doc { width: 100%; max-width: 720px; margin: 0 auto; background: #fff; padding: 24px 28px; }
.header-row { display:flex; justify-content:space-between; margin-bottom:6px; font-size:11px; }
.to-block { margin:8px 0 12px; font-size:11px; line-height:1.6; }
.doc-title { text-align:center; font-weight:bold; font-size:13px; margin:10px 0 10px; }
table { width:100%; border-collapse:collapse; }
table th, table td { border:1px solid #000; padding:4px 6px; font-size:10px; }
table thead th { background:#e8e8e8; font-weight:bold; text-align:center; }
table tfoot td { font-weight:bold; background:#f5f5f5; }
.text-right { text-align:right; }
.text-center { text-align:center; }
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-act btn-act-print" onclick="window.print()"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg> Print</button>
    <button class="btn-act btn-act-close" onclick="window.close()"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg> Close</button>
</div>

@php
    $commission = (float) ($item->lc?->lc_commission_flat ?? 0);
    $invoiceValue = $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-';
@endphp

<div class="doc">
    <div class="header-row">
        <span>Bill No. {{ $item->serial_number ?? $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }};
    </div>

    <div class="doc-title">LC Commission Bill</div>

    <table>
        <thead>
            <tr>
                <th>PFI NO.</th>
                <th>LC No</th>
                <th>LC Date</th>
                <th>LC Retirement<br>Invoice Value</th>
                <th>LC Retirement<br>Date</th>
                <th>LC Retirement<br>Value BDT</th>
                <th>Commission</th>
                <th>Commission<br>Amount BDT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                <td class="text-center">{{ $item->lc?->lc_open_date?->format('d.m.Y') ?? '-' }}</td>
                <td class="text-right">{{ $invoiceValue }}</td>
                <td class="text-center">{{ $item->lc?->lc_retirement_date?->format('d.m.Y') ?? '-' }}</td>
                <td class="text-right">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                <td class="text-center">{{ $item->lc?->lc_commission_percent ? rtrim(rtrim(number_format($item->lc->lc_commission_percent, 4), '0'), '.').'%' : '-' }}</td>
                <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total Commission Amount BDT</td>
                <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>C&F Bill — {{ $item->serial_number ?? $lcBillStatement->bill_no }}</title>
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
.to-block { margin:8px 0 6px; font-size:11px; line-height:1.7; }
.subject { margin:8px 0 10px; font-size:11px; }
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
    $cnf     = (float) ($item->lc?->total_lc_cost ?? 0);
    $advance = (float) ($item->lc?->advance_received_bdt ?? 0);
    $dues    = $cnf - $advance;
@endphp

<div class="doc">
    <div class="header-row">
        <span>Bill No. {{ $item->serial_number ?? $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">
        Sub: C&amp;F Bill for LC No. {{ $item->lc?->lc_no ?? '-' }} / PFI No. {{ $item->lc?->pfi_no ?? '-' }}
    </div>

    <div class="doc-title">C&amp;F Bill</div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">SL</th>
                <th>PFI No.</th>
                <th>LC/TT No.</th>
                <th>Total C&amp;F Bill BDT</th>
                <th>Advance From DIIL BDT</th>
                <th>Dues BDT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                <td class="text-right">{{ $cnf ? number_format($cnf, 2) : '-' }}</td>
                <td class="text-right">{{ $advance ? number_format($advance, 2) : '-' }}</td>
                <td class="text-right">{{ number_format($dues, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ $cnf ? number_format($cnf, 2) : '-' }}</td>
                <td class="text-right">{{ $advance ? number_format($advance, 2) : '-' }}</td>
                <td class="text-right">{{ number_format($dues, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>

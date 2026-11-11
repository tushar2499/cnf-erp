<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Bill</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:9px; color:#000; background:#fff; }

.no-print { margin:10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a4a6b; color:#fff; border-color:#1a4a6b; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:210mm; margin:0 auto; padding:6mm 10mm 10mm; }

.co-name { font-size:16px; font-weight:700; color:#000; text-align:center; }
.co-address { font-size:9px; color:#333; text-align:center; margin-top:2px; }
.divider { border:none; border-top:1.5px solid #000; margin:4px 0; }
.date-bar { text-align:center; font-size:9px; margin-bottom:6px; color:#333; }

table.bill-table { width:100%; border-collapse:collapse; font-size:7.5px; }
table.bill-table thead th { background:#dce8f5; color:#1a4a6b; padding:3px 4px; border:1px solid #aac5e0; text-align:center; }
table.bill-table tbody td { padding:3px 4px; border:1px solid #ccd9e8; vertical-align:middle; }
table.bill-table tbody tr:nth-child(even) { background:#f4f8ff; }
table.bill-table tfoot td { background:#c5d9f0; font-weight:700; padding:3px 4px; border:1px solid #aac5e0; }
table.bill-table tfoot td.r { text-align:right; }
.r { text-align:right; }
.c { text-align:center; }
.head-total { display:block; margin-top:2px; padding-top:2px; border-top:1px dashed #7fa3c7; font-size:7px; font-weight:700; }

.summary-section { margin-top:14px; border-top:2px solid #1a4a6b; padding-top:8px; page-break-inside:avoid; }
table.summary-tbl { width:100%; font-size:8.5px; border-collapse:collapse; }
table.summary-tbl td { padding:3px 6px; border:1px solid #ccd9e8; }
table.summary-tbl .lbl { width:60%; background:#eef4fb; font-weight:600; }
table.summary-tbl .val { text-align:right; font-weight:700; }
table.summary-tbl .total-row td { background:#1a4a6b; color:#fff; font-size:9.5px; }

.powered { margin-top:8px; border-top:1px solid #ccc; padding-top:3px; font-size:7px; color:#888; display:flex; justify-content:space-between; }

@media print {
    .no-print { display:none !important; }
    body { margin:0; }
    .page { width:100%; margin:0; padding:0; }
    @page { size:A4 portrait; margin:8mm 10mm 10mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<div class="page">

    <div class="co-name">Chevron Lines Ltd.</div>
    <div class="co-address">Job Bill Report</div>
    <div class="divider"></div>

    @if(request('from_date') || request('to_date') || request('job_no'))
    <div class="date-bar">
        @if(request('from_date')) From: {{ \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') }} @endif
        @if(request('to_date')) &nbsp; To: {{ \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') }} @endif
        @if(request('job_no')) &nbsp;|&nbsp; Job No: {{ request('job_no') }} @endif
    </div>
    @endif

    <table class="bill-table">
        <thead>
            <tr>
                <th style="width:4%">SL</th>
                <th>Branch</th>
                <th>Job No</th>
                <th>Party Name</th>
                <th style="width:10%">Bill Date</th>
                <th style="width:10%">Bill No</th>
                <th style="width:9%">Bill Type</th>
                <th>Expense Category</th>
                <th>Expense Head</th>
                <th style="width:11%">Amount<span class="head-total">Total {{ number_format($totalBill, 2) }}</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
            <tr>
                <td class="c">{{ $index + 1 }}</td>
                <td>{{ $row->branch_name ?? '—' }}</td>
                <td>{{ $row->job_no ?? '—' }}</td>
                <td>{{ $row->party_name ?? '—' }}</td>
                <td class="c">{{ $row->bill_date ? \Carbon\Carbon::parse($row->bill_date)->format('d M Y') : '—' }}</td>
                <td>{{ $row->bill_no ?? '—' }}</td>
                <td>{{ $row->bill_type ?? '—' }}</td>
                <td>{{ $row->expense_category ?? '—' }}</td>
                <td>{{ $row->expense_head ?? '—' }}</td>
                <td class="r">{{ number_format($row->bill_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="c" style="color:#999">No bill records found.</td></tr>
            @endforelse
        </tbody>
        @if($rows->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="9" class="r">Total</td>
                <td class="r">{{ number_format($totalBill, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    @if($rows->isNotEmpty())
    <div class="summary-section">
        <table class="summary-tbl" style="max-width:380px">
            <tr class="total-row">
                <td class="lbl">Total Job Bill Amount</td>
                <td class="val">{{ number_format($totalBill, 2) }}</td>
            </tr>
            <tr>
                <td class="lbl">Total Bill Rows</td>
                <td class="val">{{ $rows->count() }}</td>
            </tr>
            <tr>
                <td class="lbl">Jobs Covered</td>
                <td class="val">{{ $totalJobs }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="powered">
        <span>Powered By: Advertising For Business - A4B</span>
        <span>Print Date: {{ now()->format('d/m/Y g:i A') }}</span>
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Expense Summary</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:9px; color:#000; background:#fff; }

.no-print { margin:10px; display:flex; gap:8px; }
.no-print button { padding:6px 18px; font-size:13px; cursor:pointer; border-radius:4px; border:1px solid #333; }
.btn-print { background:#1a4a6b; color:#fff; border-color:#1a4a6b; }
.btn-close  { background:#6c757d; color:#fff; border-color:#6c757d; }

.page { width:210mm; margin:0 auto; padding:6mm 10mm 10mm; }

.co-header { width:100%; border-collapse:collapse; margin-bottom:5px; }
.co-name { font-size:16px; font-weight:700; color:#000; text-align:center; }
.co-address { font-size:9px; color:#333; text-align:center; margin-top:2px; }
.divider { border:none; border-top:1.5px solid #000; margin:4px 0; }
.report-title { text-align:center; font-size:13px; font-weight:700; margin:5px 0 4px; text-decoration:underline; }
.date-bar { text-align:center; font-size:9px; margin-bottom:6px; color:#333; }

.expense-group { margin-bottom:12px; page-break-inside:avoid; }
.group-header { background:#1a4a6b; color:#fff; padding:4px 8px; font-size:8.5px; font-weight:700; }
.group-header span { font-weight:400; color:#b8d4f0; margin-left:6px; }

table.exp-table { width:100%; border-collapse:collapse; font-size:8px; }
table.exp-table thead th { background:#dce8f5; color:#1a4a6b; padding:3px 5px; border:1px solid #aac5e0; text-align:center; }
table.exp-table tbody td { padding:3px 5px; border:1px solid #ccd9e8; vertical-align:middle; }
table.exp-table tbody tr:nth-child(even) { background:#f4f8ff; }
table.exp-table tfoot td { background:#c5d9f0; font-weight:700; padding:3px 5px; border:1px solid #aac5e0; }
table.exp-table tfoot td.r { text-align:right; }

.summary-section { margin-top:14px; border-top:2px solid #1a4a6b; padding-top:8px; }
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

@php
$grandReceiptable    = 0;
$grandNonReceiptable = 0;
$grandTotal          = 0;
$countYes            = 0;
$countNo             = 0;
@endphp

<div class="page">

    <div class="co-name">Chevron Lines Ltd.</div>
    <div class="co-address">Job Expense Summary Report</div>
    <div class="divider"></div>

    @if(request('from_date') || request('to_date') || request('job_no') || request('employee_id'))
    <div class="date-bar">
        @if(request('from_date')) From: {{ \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') }} @endif
        @if(request('to_date')) &nbsp; To: {{ \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') }} @endif
        @if(request('job_no')) &nbsp;|&nbsp; Job No: {{ request('job_no') }} @endif
    </div>
    @endif

    @foreach($expenses as $expense)
    @php
        $items             = $expense->items;
        $subTotal          = $items->sum('expense_amount');
        $subReceiptable    = $items->where('receiptable', true)->sum('expense_amount');
        $subNonReceiptable = $items->where('receiptable', false)->sum('expense_amount');
        $grandReceiptable    += $subReceiptable;
        $grandNonReceiptable += $subNonReceiptable;
        $grandTotal          += $subTotal;
        $countYes += $items->where('receiptable', true)->count();
        $countNo  += $items->where('receiptable', false)->count();
        $empName  = $expense->employee?->name ?? '—';
    @endphp
    <div class="expense-group">
        <div class="group-header">
            Expense No: {{ $expense->expense_no }}
            <span>| Job No: {{ $expense->job_no }}</span>
            <span>| Date: {{ $expense->date?->format('d M Y') }}</span>
            <span>| Employee: {{ $empName }}</span>
            @if($expense->be_no) <span>| BE No: {{ $expense->be_no }}</span> @endif
        </div>
        <table class="exp-table">
            <thead>
                <tr>
                    <th style="width:4%">SL</th>
                    <th>Expense Head</th>
                    <th style="width:12%">Date</th>
                    <th style="width:10%">Receiptable</th>
                    <th style="width:14%">Approved By</th>
                    <th>Remarks</th>
                    <th style="width:13%;text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $si => $item)
                <tr>
                    <td style="text-align:center">{{ $si + 1 }}</td>
                    <td>{{ $item->expenseHead?->name ?? '—' }}</td>
                    <td style="text-align:center">{{ $item->expense_date?->format('d M Y') }}</td>
                    <td style="text-align:center">{{ $item->receiptable ? 'Yes' : 'No' }}</td>
                    <td>—</td>
                    <td>{{ $item->note ?? '' }}</td>
                    <td style="text-align:right">{{ number_format($item->expense_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#999">No items</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align:right">Sub Total</td>
                    <td class="r">{{ number_format($subTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    {{-- Grand Summary --}}
    @if($expenses->isNotEmpty())
    <div class="summary-section">
        <table class="summary-tbl" style="max-width:380px">
            <tr>
                <td class="lbl">Total Receiptable Amount</td>
                <td class="val">{{ number_format($grandReceiptable, 2) }}</td>
            </tr>
            <tr>
                <td class="lbl">Total Non-Receiptable Amount</td>
                <td class="val">{{ number_format($grandNonReceiptable, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="lbl">Total Cost Amount</td>
                <td class="val">{{ number_format($grandTotal, 2) }}</td>
            </tr>
            <tr>
                <td class="lbl">Receiptable Items (Yes)</td>
                <td class="val">{{ $countYes }}</td>
            </tr>
            <tr>
                <td class="lbl">Non-Receiptable Items (No)</td>
                <td class="val">{{ $countNo }}</td>
            </tr>
            <tr>
                <td class="lbl">Total Expense Vouchers</td>
                <td class="val">{{ $expenses->count() }}</td>
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

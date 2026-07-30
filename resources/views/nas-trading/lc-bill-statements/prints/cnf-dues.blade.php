<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>C&F Bill Statement — {{ $lcBillStatement->bill_no }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background:#fff; padding:20px; }
.no-print { margin-bottom:12px; }
@media print { .no-print { display:none !important; } body { padding:0; } }

.doc { width:100%; max-width:820px; margin:0 auto; }
.header-row { display:flex; justify-content:space-between; margin-bottom:6px; font-size:11px; }
.to-block { margin:8px 0 6px; font-size:11px; line-height:1.6; }
.subject { margin:10px 0 4px; font-size:11px; }
.doc-title { text-align:center; font-weight:bold; font-size:12px; margin:10px 0 8px; }
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
    <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">Print</button>
    <button onclick="window.close()" style="padding:6px 18px;font-size:12px;cursor:pointer;margin-left:8px;">Close</button>
</div>

<div class="doc">
    <div class="header-row">
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
        <span>Bill No. {{ $lcBillStatement->bill_no }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">
        Sub: Dues C&amp;F bill statement of {{ $lcBillStatement->customer?->company_name }}.
    </div>

    <div class="doc-title">C&amp;F Bill Statement</div>

    @php
        $totalCnf      = 0;
        $totalAdvance  = 0;
        $totalDues     = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:30px">SL</th>
                <th>PFI NO</th>
                <th>LC/TT NO</th>
                <th>Total C&amp;F Bill BDT</th>
                <th>Advance From DIIL BDT</th>
                <th>Dues BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lcBillStatement->items as $i => $item)
                @php
                    $cnf     = (float) ($item->lc?->cnf_total_cost ?? 0);
                    $advance = (float) ($item->lc?->advance_received_bdt ?? 0);
                    $dues    = $cnf - $advance;
                    $totalCnf     += $cnf;
                    $totalAdvance += $advance;
                    $totalDues    += $dues;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                    <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                    <td class="text-right">{{ $cnf ? number_format($cnf, 2) : '-' }}</td>
                    <td class="text-right">{{ $advance ? number_format($advance, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($dues, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalCnf, 2) }}</td>
                <td class="text-right">{{ number_format($totalAdvance, 2) }}</td>
                <td class="text-right">{{ number_format($totalDues, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LC Commission Bill Statement — {{ $lcBillStatement->bill_no }}</title>
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
.no-print { padding: 10px 20px; background: #fff; border-bottom: 1px solid #ddd; margin-bottom: 16px; }
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
    <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">Print</button>
    <button onclick="window.close()" style="padding:6px 18px;font-size:12px;cursor:pointer;margin-left:8px;">Close</button>
</div>

<div class="doc">
    <div class="header-row">
        <span>LC/CB No. {{ $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }};<br>
        {{ $lcBillStatement->customer?->address }}:
    </div>

    <div class="doc-title">LC Commission Bill Statement</div>

    @php
        $totalCommission = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Bill No.</th>
                <th>PFI NO.</th>
                <th>LC No</th>
                <th>LC Date</th>
                <th>LC Retirement<br>/Invoice Value</th>
                <th>LC Retirement<br>Date</th>
                <th>LC Retirement<br>Value BDT</th>
                <th>LC Commission<br>Amount BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lcBillStatement->items as $item)
                @php
                    $commission = (float) ($item->lc?->lc_commission_flat ?? 0);
                    $totalCommission += $commission;
                    $invoiceValue = $item->lc?->lc_rt_value
                        ? number_format($item->lc->lc_rt_value, 2)
                        : '-';
                @endphp
                <tr>
                    <td>{{ $item->serial_number ?? '-' }}</td>
                    <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                    <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                    <td class="text-center">{{ $item->lc?->lc_open_date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ $invoiceValue }}</td>
                    <td class="text-center">{{ $item->lc?->lc_retirement_date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                    <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total LC Commission Amount BDT</td>
                <td class="text-right">{{ number_format($totalCommission, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>

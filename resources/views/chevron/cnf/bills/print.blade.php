<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C &amp; F Bill — {{ $bill->bill_no }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: Arial, sans-serif;
    font-size: 11px;
    color: #000;
    background: #fff;
    padding: 20px 30px;
}
.bill-title {
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    text-decoration: underline;
    letter-spacing: .08em;
    margin-bottom: 10px;
}
.bill-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
    font-size: 11px;
}
.bill-meta-left, .bill-meta-right { width: 48%; }
.bill-meta-row { display: flex; margin-bottom: 2px; }
.bill-meta-label { font-weight: bold; min-width: 90px; }
.bill-meta-value { flex: 1; }

.party-block { margin-bottom: 8px; font-size: 11px; }
.party-block .row { display: flex; margin-bottom: 2px; }
.party-block .lbl { font-weight: bold; min-width: 90px; }

/* Info grid table */
.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 10.5px;
}
.info-table td {
    border: 1px solid #555;
    padding: 2px 5px;
    vertical-align: top;
}
.info-table .lbl {
    font-weight: bold;
    background: #f5f5f5;
    width: 130px;
    white-space: nowrap;
}
.info-table .wide-lbl { font-weight: bold; background: #f5f5f5; width: 130px; }

/* Charge table */
.charge-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
    font-size: 10.5px;
}
.charge-table th {
    border: 1px solid #555;
    padding: 3px 6px;
    background: #e8e8e8;
    font-weight: bold;
    text-align: left;
}
.charge-table th.text-right { text-align: right; }
.charge-table td {
    border: 1px solid #aaa;
    padding: 2px 6px;
    vertical-align: middle;
}
.charge-table td.amount { text-align: right; white-space: nowrap; }
.charge-table .cat-row td {
    font-weight: bold;
    text-decoration: underline;
    background: #f9f9f9;
    border-color: #555;
    color: #000;
    padding: 3px 6px;
}
.charge-table .total-row td {
    font-weight: bold;
    background: #f1f1f1;
    border-top: 2px solid #000;
}
.charge-table .less-row td {
    font-weight: bold;
}
.charge-table .balance-row td {
    font-weight: bold;
    font-size: 12px;
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
}
.in-words {
    border: 1px solid #555;
    padding: 4px 6px;
    font-size: 10.5px;
    font-weight: bold;
    margin-bottom: 16px;
    background: #fafafa;
}
.signatures {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    font-size: 11px;
    font-weight: bold;
}
.sig-block { text-align: center; }
.sig-line { border-top: 1px solid #000; width: 160px; margin: 0 auto 4px; }

@media print {
    body { padding: 10px 18px; }
    @page { size: A4 portrait; margin: 10mm 12mm; }
}
</style>
</head>
<body>

<div class="bill-title">C &amp; F BILL</div>

<div class="bill-meta">
    <div class="bill-meta-left">
        <div class="bill-meta-row"><span class="bill-meta-label">Bill No :</span> <span class="bill-meta-value">{{ $bill->bill_no }}</span></div>
        <div class="bill-meta-row"><span class="bill-meta-label">Bill Date :</span> <span class="bill-meta-value">{{ $bill->bill_date?->format('d M Y') }}</span></div>
        <div class="bill-meta-row"><span class="bill-meta-label">Party Name :</span> <span class="bill-meta-value">{{ $bill->party_name }}</span></div>
        <div class="bill-meta-row"><span class="bill-meta-label">Address :</span> <span class="bill-meta-value">{{ $bill->party_address }}</span></div>
    </div>
    <div class="bill-meta-right">
        <div class="bill-meta-row"><span class="bill-meta-label">Job No :</span> <span class="bill-meta-value">{{ $bill->job_no }}</span></div>
        <div class="bill-meta-row"><span class="bill-meta-label">L.C No :</span> <span class="bill-meta-value">{{ $bill->lc_no }}{{ $bill->lc_ref ? ' / '.$bill->lc_ref : '' }}</span></div>
    </div>
</div>

{{-- Info Grid --}}
<table class="info-table">
    <tr>
        <td class="lbl">Description of Goods</td>
        <td colspan="3">{{ $bill->goods_description }}</td>
    </tr>
    <tr>
        <td class="lbl">P/O No</td>
        <td>{{ $bill->po_no }}</td>
        <td class="lbl">Origin. Doc. Rec Date</td>
        <td>{{ $bill->job?->copy_doc_received_date?->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Mate Code</td>
        <td>{{ $bill->mate_code }}</td>
        <td class="lbl">ETB Date</td>
        <td>{{ $bill->job?->etb_date?->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Quantity</td>
        <td>{{ $bill->quantity ? number_format($bill->quantity, 2).' '.($bill->quantity_unit ?? '') : '' }}{{ $bill->quantity_remark ? ' '.$bill->quantity_remark : '' }}</td>
        <td class="lbl">Delivery Date</td>
        <td>{{ $bill->delivery_date?->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Weight</td>
        <td>{{ $bill->gross_weight ? number_format($bill->gross_weight, 2).' '.($bill->gross_weight_unit ?? 'KG') : '' }}</td>
        <td class="lbl">Invoice No</td>
        <td>{{ $bill->invoice_no }}</td>
    </tr>
    <tr>
        <td class="lbl">MAWB. B.L No</td>
        <td>{{ $bill->bl_no }}{{ $bill->bl_ref ? ' / '.$bill->bl_ref : '' }}</td>
        <td class="lbl">Invoice Date</td>
        <td>{{ $bill->invoice_date?->format('d M Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">B.E No</td>
        <td>{{ $bill->be_no }}</td>
        <td class="lbl">Invoice Value</td>
        <td>{{ $bill->invoice_value ? number_format($bill->invoice_value, 2) : '' }}</td>
    </tr>
    <tr>
        <td class="lbl">B.E Date</td>
        <td>{{ $bill->be_date?->format('d M Y') }}</td>
        <td class="lbl">{{ $bill->currency_type ? $bill->currency_type.' Rate' : 'Currency Rate' }}</td>
        <td>{{ $bill->currency_rate ? number_format($bill->currency_rate, 2) : '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Assessable Value</td>
        <td>{{ $bill->assessable_value ? number_format($bill->assessable_value, 2) : '' }}</td>
        <td class="lbl">Invoice Value BDT</td>
        <td>{{ $bill->invoice_value_bdt ? number_format($bill->invoice_value_bdt, 2) : '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Remarks</td>
        <td colspan="3">{{ $bill->remarks }}</td>
    </tr>
</table>

{{-- Charge Table --}}
<table class="charge-table">
    <thead>
        <tr>
            <th>Particulars</th>
            <th class="text-right" style="width:160px;">Amount (TK)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($grouped as $catName => $items)
        <tr class="cat-row">
            <td colspan="2">{{ strtoupper($catName) }}</td>
        </tr>
        @foreach($items as $item)
        <tr>
            <td style="padding-left:16px;">
                {{ $item->expenseHead?->name ?? '—' }}
                @if($item->note)<span style="color:#555;"> &mdash; {{ $item->note }}</span>@endif
                @if($item->rate && $item->qty)
                    <span style="color:#555;font-size:9.5px;"> ({{ number_format($item->rate,2) }} × {{ rtrim(rtrim(number_format($item->qty,3),'0'),'.') }})</span>
                @endif
            </td>
            <td class="amount">{{ number_format($item->amount, 2) }}</td>
        </tr>
        @endforeach
        @endforeach

        {{-- Sub-total if commission exists --}}
        @if($bill->commission_amount > 0)
        <tr>
            <td style="padding-left:16px;">
                @if($bill->commission_on === 'ASSESSABLE')
                    @php $commLabel = '@'.number_format($bill->commission_rate,2).'% Commission on Assessable Value'; @endphp
                @elseif($bill->commission_on === 'INVOICE VALUE')
                    @php $commLabel = '@'.number_format($bill->commission_rate,2).'% Commission on Invoice Value'; @endphp
                @else
                    @php $commLabel = $bill->commission_on.' Commission'; @endphp
                @endif
                {{ $commLabel }}
            </td>
            <td class="amount">{{ number_format($bill->commission_amount, 2) }}</td>
        </tr>
        @endif

        <tr class="total-row">
            <td class="text-right" style="text-align:right;"><strong>TOTAL</strong></td>
            <td class="amount"><strong>{{ number_format($bill->total_payable, 2) }}</strong></td>
        </tr>
        <tr class="less-row">
            <td><strong>LESS CUSTOMS DUTY &amp; TAX</strong></td>
            <td class="amount"><strong>{{ number_format($bill->less_customs_duty_tax, 2) }}</strong></td>
        </tr>
        @if($bill->income_tax_cnf_com > 0)
        <tr class="less-row">
            <td><strong>LESS INCOME TAX C&amp;F</strong></td>
            <td class="amount"><strong>{{ number_format($bill->income_tax_cnf_com, 2) }}</strong></td>
        </tr>
        @endif
        <tr class="balance-row">
            <td style="text-align:right;"><strong>BALANCE</strong></td>
            <td class="amount"><strong>{{ number_format($bill->due_amount, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<div class="in-words">
    TK IN WORD: {{ $inWords }}
</div>

<div class="signatures">
    <div class="sig-block">
        <div class="sig-line"></div>
        Prepared By
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        CHECKED BY
    </div>
</div>

<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>

@extends('nas-trading.layouts.print')

@section('title', 'LC Closing Bill — LC/CL/' . $item->serial_number)

@push('styles')
.header-row { margin-bottom: 10px; }
.subject { font-weight: bold; margin-top: 10px; }
.no-border-row td { border-left: none; border-right: none; border-top: none; }
@endpush

@section('content')
@php
    $lc = $item->lc;
    $lcCost = (float) ($lc?->lc_rt_value ?? 0);
    $bankCharge = (float) ($lc?->lc_open_cost_bdt ?? 0);
    $insurance = (float) ($lc?->insurance_amt ?? 0);
    $amendment = (float) ($lc?->lc_amendment_charge ?? 0);
    $creditReport = (float) ($lc?->credit_report_charge ?? 0);
    $otherCharges = $lc ? (float) $lc->otherChargeItems->sum('amount') : 0.0;
    $totalCost = $lcCost + $bankCharge + $insurance + $amendment + $creditReport + $otherCharges;
    $advance = $lc ? (float) $lc->payments->where('payment_type', 'advance')->sum('amount') : 0.0;
    $dues = $totalCost - $advance;
    $invoiceDate = $lc?->lc_closing_bill_date ?? $lcBillStatement->bill_date;

    function lcClosingNumberToWords(float $amount): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n === 0) { return ''; }
            if ($n < 20) { return $ones[$n]; }
            if ($n < 100) { return $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : ''); }
            if ($n < 1000) { return $ones[intdiv($n, 100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : ''); }
            if ($n < 100000) { return $convert(intdiv($n, 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : ''); }
            if ($n < 10000000) { return $convert(intdiv($n, 100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convert($n % 100000) : ''); }
            return $convert(intdiv($n, 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };
        $taka = (int) abs($amount);
        $poisha = (int) round((abs($amount) - $taka) * 100);
        $words = $taka > 0 ? $convert($taka) : 'Zero';
        if ($poisha > 0) { $words .= ' and ' . $convert($poisha) . ' Poisha'; }
        return $words;
    }
@endphp

<div class="doc">
    <div class="header-row">
        <span><strong>Invoice No:</strong> LC/CL/{{ $item->serial_number }}&nbsp;&nbsp;&nbsp; <strong>Date:</strong>
            {{ $invoiceDate?->format('d-m-Y') }}</span>
    </div>

    <div class="consignee-block">
        <strong>Consignee:</strong><br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">
        Subj: LC Closing bill Under LC No. {{ $lc?->lc_no ?? '-' }}
        Dt: {{ $lc?->lc_open_date?->format('d.m.Y') ?? '-' }}
        PFI No. {{ $lc?->pfi_no ?? '-' }}
        Dt: {{ $lc?->pfi_date?->format('d.m.Y') ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">SL</th>
                <th>Description</th>
                <th style="width:140px">BDT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>LC RT Value</td>
                <td class="text-right">{{ $lcCost ? number_format($lcCost, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Bank Charge</td>
                <td class="text-right">{{ $bankCharge ? number_format($bankCharge, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Insurance Amount</td>
                <td class="text-right">{{ $insurance ? number_format($insurance, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>LC Amendment Charge</td>
                <td class="text-right">{{ $amendment ? number_format($amendment, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Credit Report Charge</td>
                <td class="text-right">{{ $creditReport ? number_format($creditReport, 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Other Charges</td>
                <td class="text-right">{{ $otherCharges ? number_format($otherCharges, 2) : '-' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="border-left:1px solid #000;font-weight:bold;">Total Cost BDT.</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($totalCost, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right" style="border-left:1px solid #000;">(-) Advance BDT.</td>
                <td class="text-right">{{ $advance ? number_format($advance, 2) : '-' }}</td>
            </tr>
            <tr class="dues-row">
                <td colspan="2" class="text-right" style="border-left:1px solid #000;font-weight:bold;">Dues BDT.</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($dues, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="in-word">
        <strong>In Word:</strong> BDT {{ lcClosingNumberToWords($dues) }} only.
    </div>

    <div class="footer-block">
        Thanking You.<br>
        Yours Faithfully,<br>
        <strong>NAS Trading</strong>
        <div class="sig-space"></div>
    </div>

    @if ($lcBillStatement->enclosed)
        <div style="margin-top:20px;font-size:11px;">
            Enclosed: {{ $lcBillStatement->enclosed }}
        </div>
    @endif
</div>
@endsection

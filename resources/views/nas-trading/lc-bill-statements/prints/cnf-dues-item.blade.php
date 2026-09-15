@extends('nas-trading.layouts.print')

@section('title', 'C&F Bill — NAS/C&F/' . $item->serial_number)

@push('styles')
.to-block { margin-bottom: 6px; line-height: 1.7; }
@endpush

@section('content')
@php
    $cnf = (float) ($item->lc?->billOfEntries->sum(fn($be) => ($be->customs_duty ?? 0) + ($be->cnf_total_costing ?? 0)) ?? 0);
    $advance = (float) ($item->lc?->billOfEntries->flatMap->dutyAdvances->sum('amount') ?? 0);
    $dues = $cnf - $advance;

    function cnfDuesItemNumberToWords(float $amount): string
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
        <span>Bill No: NAS/C&F/{{ $item->serial_number }}</span>
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
                <th>Advanced BDT</th>
                <th>Amount BDT</th>
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
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">{{ number_format($dues, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="in-word">
        <strong>In Word:</strong> BDT {{ cnfDuesItemNumberToWords($dues) }} only.
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

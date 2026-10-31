@extends('nas-trading.layouts.print')

@section('title', 'LC Closing Bills — LC/CLBS/' . $lcBillStatement->bill_no)

@section('print-label', 'Print All')

@push('styles')
.header-row { margin-bottom: 10px; }
.subject { font-weight: bold; margin-top: 10px; }
#print-wrap .doc { margin: 0 auto 24px; }
@media print {
    .doc { margin-bottom: 0; }
    #print-wrap .doc { break-after: page; }
    #print-wrap .doc:last-child { break-after: auto; }
}
@endpush

@section('content')
@php
@endphp

<div id="print-wrap">
    @foreach ($lcBillStatement->items as $item)
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
        @endphp
        <div class="doc">
            <div class="header-row">
                <span>Bill No: LC/CL/{{ $item->serial_number }}</span>
                <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
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
                    <tr>
                        <td colspan="2" class="text-right" style="border-left:1px solid #000;font-weight:bold;">Dues BDT.</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format($dues, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="in-word">
                <strong>In Word:</strong> BDT {{ numberToWords($dues) }} only.
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
    @endforeach
</div>
@endsection

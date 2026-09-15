@extends('nas-trading.layouts.print')

@section('title', 'LC Closing Statement — ' . $lcBillStatement->bill_no)

@push('page-css')
@page {
    size: A4 landscape;
    margin-top: 1.0in;
    margin-bottom: 0.8in;
    margin-left: 0.4in;
    margin-right: 0.4in;
}
@endpush

@push('styles')
.doc { max-width: 1000px; }
.to-block { margin-bottom: 6px; line-height: 1.7; }
.subject { margin-bottom: 4px; }
table th, table td { font-size: 9px; }
@endpush

@section('content')
@php $totDues = 0; @endphp

<div class="doc">
    <div class="header-row">
        <span>Bill No: LC/CLBS/{{ $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">Sub: LC Closing Bill Statement.</div>

    <div class="doc-title">LC Closing Bill Statement</div>

    <table>
        <thead>
            <tr>
                <th style="width:28px">SL</th>
                <th>Invoice No</th>
                <th>PFI No</th>
                <th>LC No</th>
                <th>LC Date</th>
                <th>Amount BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lcBillStatement->items as $i => $item)
                @php
                    $lc = $item->lc;
                    $lcCost = (float) ($lc?->lc_rt_value ?? 0);
                    $bank = (float) ($lc?->lc_open_cost_bdt ?? 0);
                    $ins = (float) ($lc?->insurance_amt ?? 0);
                    $amendment = (float) ($lc?->lc_amendment_charge ?? 0);
                    $creditReport = (float) ($lc?->credit_report_charge ?? 0);
                    $otherCharges = $lc ? (float) $lc->otherChargeItems->sum('amount') : 0.0;
                    $totalCost = $lcCost + $bank + $ins + $amendment + $creditReport + $otherCharges;
                    $advance = $lc ? (float) $lc->payments->where('payment_type', 'advance')->sum('amount') : 0.0;
                    $dues = $totalCost - $advance;
                    $totDues += $dues;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>LC/CL/{{ $item->serial_number }}</td>
                    <td>{{ $lc?->pfi_no ?? '-' }}</td>
                    <td>{{ $lc?->lc_no ?? '-' }}</td>
                    <td class="text-center">{{ $lc?->lc_open_date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ number_format($dues, 2) }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totDues, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

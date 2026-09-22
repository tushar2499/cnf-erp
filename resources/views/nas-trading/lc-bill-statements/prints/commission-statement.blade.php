@extends('nas-trading.layouts.print')

@section('title', 'LC Commission Bill Statement — ' . $lcBillStatement->bill_no)

@section('content')
<div class="doc">
    <div class="header-row">
        <span>Bill No: LC/CBL/{{ $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">Sub: LC Commission Bill Statement</div>

    <div class="doc-title">LC Commission Bill Statement</div>

    @php $totalCommission = 0; @endphp

    <table>
        <thead>
            <tr>
                <th style="width:28px">SL</th>
                <th>Bill No.</th>
                <th>PFI NO.</th>
                <th>LC No</th>
                <th>LC Date</th>
                <th>Invoice Value</th>
                <th>LC Retirement<br>Date</th>
                <th>LC Retirement<br>Value BDT</th>
                <th>LC Commission<br>Amount BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lcBillStatement->items as $i => $item)
                @php
                    $commission = (float) ($item->lc?->lc_commission_flat ?? 0);
                    $totalCommission += $commission;
                    $invoiceValue = $item->lc?->invoice_value ? number_format($item->lc->invoice_value, 2) : '-';
                    $firstRt = $item->lc?->rtValues?->sortBy('date')->first();
                    $retirementDate = $firstRt?->date ?? $item->lc?->lc_retirement_date;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ 'LC/COM/' . $item->serial_number }}</td>
                    <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                    <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                    <td class="text-center">{{ $item->lc?->lc_open_date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ $invoiceValue }}</td>
                    <td class="text-center">{{ $retirementDate?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                    <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="8" class="text-right">Total LC Commission Amount BDT</td>
                <td class="text-right">{{ number_format($totalCommission, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="in-word">
        <strong>In Word:</strong> BDT {{ numberToWords($totalCommission) }} only.
    </div>
</div>
@endsection

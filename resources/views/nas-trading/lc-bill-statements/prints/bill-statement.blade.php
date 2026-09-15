@extends('nas-trading.layouts.print')

@section('title', 'Bill Statement — ' . $lcBillStatement->bill_no)

@section('content')
<div class="doc">
    <div class="header-row">
        <span>Bill NO: NAS/C&FBS/{{ $lcBillStatement->bill_no }}</span>
        <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
    </div>

    <div class="to-block">
        To,<br>
        {{ $lcBillStatement->customer?->company_name }}<br>
        {{ $lcBillStatement->customer?->address }}
    </div>

    <div class="subject">Sub: C&amp;F Bill Statement</div>

    <div class="doc-title">C&amp;F Bill Statement</div>

    @php $totalAmount = 0; @endphp

    <table>
        <thead>
            <tr>
                <th style="width:30px">SL</th>
                <th>Invoice No</th>
                <th>LC/TT No.</th>
                <th>Date</th>
                <th>PFI No</th>
                <th>Date</th>
                <th>Amount BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lcBillStatement->items as $i => $item)
                @php
                    $cnf = (float) ($item->lc?->billOfEntries->sum(fn($be) => ($be->customs_duty ?? 0) + ($be->cnf_total_costing ?? 0)) ?? 0);
                    $advance = (float) ($item->lc?->billOfEntries->flatMap->dutyAdvances->sum('amount') ?? 0);
                    $amount = $cnf - $advance;
                    $totalAmount += $amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ 'NAS/C&F/' . $item->serial_number }}</td>
                    <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                    <td class="text-center">{{ $item->lc?->customerBill?->bill_date?->format('d.m.Y') ?? '-' }}</td>
                    <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                    <td class="text-center">{{ $item->lc?->pfi_date?->format('d.m.Y') ?? '-' }}</td>
                    <td class="text-right">{{ $amount ? number_format($amount, 2) : '-' }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="6" class="text-right">Total Amount BDT</td>
                <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

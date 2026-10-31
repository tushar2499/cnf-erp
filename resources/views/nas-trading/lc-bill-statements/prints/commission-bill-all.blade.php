@extends('nas-trading.layouts.print')

@section('title', 'LC Commission Bills — LC/CBL/' . $lcBillStatement->bill_no)

@section('print-label', 'Print All')

@push('styles')
#print-wrap .doc { margin: 0 auto 24px; }
@media print {
    .doc { margin-bottom: 0; }
    #print-wrap .doc { break-after: page; }
    #print-wrap .doc:last-child { break-after: auto; }
}
@endpush

@section('content')
<div id="print-wrap">
    @foreach ($lcBillStatement->items as $item)
        @php
            $commission = (float) ($item->lc?->lc_commission_flat ?? 0);
            $invoiceValue = $item->lc?->invoice_value ? number_format($item->lc->invoice_value, 2) : '-';
            $firstRt = $item->lc?->rtValues?->sortBy('date')->first();
            $retirementDate = $firstRt?->date ?? $item->lc?->lc_retirement_date;
        @endphp
        <div class="doc">
            <div class="header-row">
                <span>Bill No. LC/COM/{{ $item->serial_number }}</span>
                <span>Date: {{ $lcBillStatement->bill_date?->format('d.m.Y') }}</span>
            </div>

            <div class="to-block">
                To,<br>
                {{ $lcBillStatement->customer?->company_name }}<br>
                {{ $lcBillStatement->customer?->address }}
            </div>

            <div class="doc-title">LC Commission Bill</div>

            <table>
                <thead>
                    <tr>
                        <th>PFI NO.</th>
                        <th>LC No</th>
                        <th>LC Date</th>
                        <th>Invoice Value</th>
                        <th>LC Retirement<br>Date</th>
                        <th>LC Retirement<br>Value BDT</th>
                        <th>Commission</th>
                        <th>Commission<br>Amount BDT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                        <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                        <td class="text-center">{{ $item->lc?->lc_open_date?->format('d-m-Y') ?? '-' }}</td>
                        <td class="text-right">{{ $invoiceValue }}</td>
                        <td class="text-center">{{ $retirementDate?->format('d.m.Y') ?? '-' }}</td>
                        <td class="text-right">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                        <td class="text-center">{{ $item->lc?->lc_commission_percent ? rtrim(rtrim(number_format($item->lc->lc_commission_percent, 4), '0'), '.') . '%' : '-' }}</td>
                        <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-right">Total Commission Amount BDT</td>
                        <td class="text-right">{{ $commission ? number_format($commission, 2) : '-' }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="in-word">
                <strong>In Word:</strong> BDT {{ numberToWords($commission) }} only.
            </div>
        </div>
    @endforeach
</div>
@endsection

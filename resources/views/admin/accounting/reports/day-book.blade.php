@extends('admin.layouts.app')

@section('title', 'Day Book')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-calendar-day me-2 text-success"></i> Day Book</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['period'], 'companies' => $companies, 'companyId' => $companyId, 'from' => $from, 'to' => $to])
    <div class="col-md-2">
        <label class="form-label" for="type">Voucher type</label>
        <select name="type" id="type" class="form-select form-select-sm">
            <option value="">All types</option>
            @foreach(\App\Models\Accounting\AccVoucher::TYPES as $key => $label)
                <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endcomponent

<div class="card report-card">
    <div class="card-body">
        <div class="report-title">
            <h5>{{ $companyName }}</h5>
            <div class="fw-semibold">Day Book</div>
            <div class="period">{{ \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d-m-Y') }}</div>
        </div>
        <table class="table table-bordered table-sm report-table">
            <thead>
                <tr>
                    <th style="width:85px">Date</th>
                    <th style="width:120px">Voucher</th>
                    @unless($companyId)<th>Company</th>@endunless
                    <th>Account</th>
                    <th>Party</th>
                    <th class="num">Debit</th>
                    <th class="num">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                    @foreach($voucher->lines as $i => $line)
                        <tr>
                            @if($i === 0)
                                <td rowspan="{{ $voucher->lines->count() + 1 }}">{{ $voucher->voucher_date->format('d-m-Y') }}</td>
                                <td rowspan="{{ $voucher->lines->count() + 1 }}">
                                    <a href="{{ route('admin.accounting.vouchers.show', $voucher) }}" target="_blank">{{ $voucher->voucher_no }}</a><br>
                                    <small class="text-muted">{{ \App\Models\Accounting\AccVoucher::TYPES[$voucher->voucher_type] }}</small>
                                </td>
                                @unless($companyId)<td rowspan="{{ $voucher->lines->count() + 1 }}">{{ $voucher->company->name }}</td>@endunless
                            @endif
                            <td>{{ $line->account->code }} {{ $line->account->name }}</td>
                            <td>{{ $line->party_name }}</td>
                            <td class="num">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                            <td class="num">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                        </tr>
                    @endforeach
                    <tr><td colspan="4" class="small text-muted fst-italic">{{ $voucher->narration }}</td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No vouchers.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="{{ $companyId ? 4 : 5 }}">Total ({{ $vouchers->count() }} vouchers)</td>
                    <td class="num">{{ number_format($vouchers->sum('total_amount'), 2) }}</td>
                    <td class="num">{{ number_format($vouchers->sum('total_amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

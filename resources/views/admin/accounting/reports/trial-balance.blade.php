@extends('admin.layouts.app')

@section('title', 'Trial Balance')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-scale-balanced me-2 text-success"></i> Trial Balance</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['period'], 'companies' => $companies, 'companyId' => $companyId, 'from' => $from, 'to' => $to])
    <div class="col-md-2">
        <label class="form-label" for="level">Show level</label>
        <select name="level" id="level" class="form-select form-select-sm">
            @foreach(\App\Models\Accounting\AccAccount::LEVEL_NAMES as $key => $label)
                <option value="{{ $key }}" @selected($level === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endcomponent

@php
    $t = $report['totals'];
    $fmt = fn ($v) => abs($v) < 0.005 ? '' : number_format($v, 2);
    $ledgerUrl = fn ($id) => route('admin.accounting.reports.ledger', ['account_id' => $id, 'company_id' => $companyId, 'from' => $from, 'to' => $to]);
@endphp

<div class="card report-card">
    <div class="card-body">
        <div class="report-title">
            <h5>{{ $companyName }}</h5>
            <div class="fw-semibold">Trial Balance ({{ \App\Models\Accounting\AccAccount::LEVEL_NAMES[$level] }} level)</div>
            <div class="period">{{ \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d-m-Y') }}</div>
        </div>
        <table class="table table-bordered table-sm report-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:110px">Code</th>
                    <th rowspan="2">Account</th>
                    <th colspan="2" class="text-center">Opening</th>
                    <th colspan="2" class="text-center">Period</th>
                    <th colspan="2" class="text-center">Closing</th>
                </tr>
                <tr>
                    <th class="num">Debit</th><th class="num">Credit</th>
                    <th class="num">Debit</th><th class="num">Credit</th>
                    <th class="num">Debit</th><th class="num">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr>
                        <td>{{ $row['code'] }}</td>
                        <td>
                            @if($row['account_id'])
                                <a href="{{ $ledgerUrl($row['account_id']) }}" class="text-decoration-none">{{ $row['name'] }}</a>
                            @else
                                {{ $row['name'] }}
                            @endif
                        </td>
                        <td class="num">{{ $fmt(max($row['opening'], 0)) }}</td>
                        <td class="num">{{ $fmt(max(-$row['opening'], 0)) }}</td>
                        <td class="num">{{ $fmt($row['debit']) }}</td>
                        <td class="num">{{ $fmt($row['credit']) }}</td>
                        <td class="num">{{ $fmt(max($row['closing'], 0)) }}</td>
                        <td class="num">{{ $fmt(max(-$row['closing'], 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No posted transactions.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="num">{{ number_format($t['opening_dr'], 2) }}</td>
                    <td class="num">{{ number_format($t['opening_cr'], 2) }}</td>
                    <td class="num">{{ number_format($t['debit'], 2) }}</td>
                    <td class="num">{{ number_format($t['credit'], 2) }}</td>
                    <td class="num">{{ number_format($t['closing_dr'], 2) }}</td>
                    <td class="num">{{ number_format($t['closing_cr'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
        @if(abs($t['closing_dr'] - $t['closing_cr']) > 0.009)
            <div class="alert alert-danger small mb-0">Trial balance does not agree: difference {{ number_format($t['closing_dr'] - $t['closing_cr'], 2) }}.</div>
        @endif
    </div>
</div>
@endsection

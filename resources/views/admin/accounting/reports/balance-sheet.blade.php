@extends('admin.layouts.app')

@section('title', 'Balance Sheet')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-landmark me-2 text-success"></i> Balance Sheet</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['as_on'], 'companies' => $companies, 'companyId' => $companyId, 'asOn' => $asOn])
@endcomponent

@php
    $ledgerUrl = fn ($id) => route('admin.accounting.reports.ledger', ['account_id' => $id, 'company_id' => $companyId, 'from' => '2000-01-01', 'to' => $asOn]);
@endphp

<div class="card report-card">
    <div class="card-body">
        <div class="report-title">
            <h5>{{ $companyName }}</h5>
            <div class="fw-semibold">Balance Sheet</div>
            <div class="period">As on {{ \Illuminate\Support\Carbon::parse($asOn)->format('d-m-Y') }}</div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <table class="table table-bordered table-sm report-table">
                    <thead><tr><th>Assets</th><th class="num" style="width:160px">Amount (BDT)</th></tr></thead>
                    <tbody>
                        @include('admin.accounting.reports._section', ['section' => $report['assets'], 'title' => 'Assets'])
                    </tbody>
                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-bordered table-sm report-table">
                    <thead><tr><th>Liabilities &amp; Equity</th><th class="num" style="width:160px">Amount (BDT)</th></tr></thead>
                    <tbody>
                        @include('admin.accounting.reports._section', ['section' => $report['liabilities'], 'title' => 'Liabilities'])
                        @include('admin.accounting.reports._section', ['section' => $report['equity'], 'title' => 'Equity'])
                        <tr class="subgroup-row"><td>Profit / (Loss) to date</td><td class="num">{{ number_format($report['profit'], 2) }}</td></tr>
                    </tbody>
                    <tfoot>
                        <tr class="total-row"><td>Total Liabilities &amp; Equity</td><td class="num">{{ number_format($report['total_claims'], 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if(abs($report['difference']) > 0.009)
            <div class="alert alert-danger small mb-0">Balance sheet does not agree: difference {{ number_format($report['difference'], 2) }}.</div>
        @endif
    </div>
</div>
@endsection

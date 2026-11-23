@extends('admin.layouts.app')

@section('title', 'Profit & Loss')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-chart-line me-2 text-success"></i> Profit &amp; Loss</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['period'], 'companies' => $companies, 'companyId' => $companyId, 'from' => $from, 'to' => $to])
@endcomponent

@php
    $ledgerUrl = fn ($id) => route('admin.accounting.reports.ledger', ['account_id' => $id, 'company_id' => $companyId, 'from' => $from, 'to' => $to]);
    $net = $report['net_profit'];
@endphp

<div class="card report-card">
    <div class="card-body">
        <div class="report-title">
            <h5>{{ $companyName }}</h5>
            <div class="fw-semibold">Profit &amp; Loss Statement</div>
            <div class="period">{{ \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d-m-Y') }}</div>
        </div>
        <table class="table table-bordered table-sm report-table mx-auto" style="max-width:820px">
            <thead><tr><th>Particulars</th><th class="num" style="width:180px">Amount (BDT)</th></tr></thead>
            <tbody>
                @include('admin.accounting.reports._section', ['section' => $report['income'], 'title' => 'Income'])
                @include('admin.accounting.reports._section', ['section' => $report['expense'], 'title' => 'Expenses'])
            </tbody>
            <tfoot>
                <tr class="total-row {{ $net >= 0 ? 'table-success' : 'table-danger' }}">
                    <td>Net {{ $net >= 0 ? 'Profit' : 'Loss' }}</td>
                    <td class="num">{{ number_format(abs($net), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

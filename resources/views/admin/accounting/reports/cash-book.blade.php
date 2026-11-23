@extends('admin.layouts.app')

@section('title', 'Cash / Bank Book')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-money-bill-wave me-2 text-success"></i> Cash / Bank Book</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['period'], 'companies' => $companies, 'companyId' => $companyId, 'from' => $from, 'to' => $to])
    <div class="col-md-3">
        <label class="form-label" for="account_id">Cash / bank account</label>
        <select name="account_id" id="account_id" class="form-select form-select-sm">
            <option value="">All cash &amp; bank accounts</option>
            @foreach($cashAccounts as $acc)
                <option value="{{ $acc->id }}" @selected($accountId === $acc->id)>{{ $acc->code }} – {{ $acc->name }}</option>
            @endforeach
        </select>
    </div>
@endcomponent

<div class="card report-card">
    <div class="card-body">
        <div class="report-title">
            <h5>{{ $companyName }}</h5>
            <div class="fw-semibold">Cash / Bank Book</div>
            <div class="period">{{ \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d-m-Y') }}</div>
        </div>

        @forelse($books as $book)
            <h6 class="fw-bold mt-3">{{ $book['account']->code }} – {{ $book['account']->name }}</h6>
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th style="width:85px">Date</th>
                        <th style="width:120px">Voucher</th>
                        <th>Particulars</th>
                        <th>Narration</th>
                        <th class="num">Receipt</th>
                        <th class="num">Payment</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="subgroup-row">
                        <td colspan="6">Opening balance</td>
                        <td class="num">@include('admin.accounting.reports._balance', ['amount' => $book['opening']])</td>
                    </tr>
                    @foreach($book['rows'] as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                            <td><a href="{{ route('admin.accounting.vouchers.show', $row['voucher_id']) }}" target="_blank">{{ $row['voucher_no'] }}</a></td>
                            <td>{{ $row['particulars'] }}</td>
                            <td>{{ $row['narration'] }}</td>
                            <td class="num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                            <td class="num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                            <td class="num">@include('admin.accounting.reports._balance', ['amount' => $row['balance']])</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4">Total / Closing balance</td>
                        <td class="num">{{ number_format($book['total_debit'], 2) }}</td>
                        <td class="num">{{ number_format($book['total_credit'], 2) }}</td>
                        <td class="num">@include('admin.accounting.reports._balance', ['amount' => $book['closing']])</td>
                    </tr>
                </tfoot>
            </table>
        @empty
            <div class="text-center text-muted py-3">No cash or bank transactions in this period.</div>
        @endforelse
    </div>
</div>
@endsection

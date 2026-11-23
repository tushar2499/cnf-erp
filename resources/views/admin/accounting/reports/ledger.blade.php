@extends('admin.layouts.app')

@section('title', 'Ledger')

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-book me-2 text-success"></i> Ledger</h4>
</div>

@component('admin.accounting.reports._filters', ['fields' => ['period'], 'companies' => $companies, 'companyId' => $companyId, 'from' => $from, 'to' => $to])
    <div class="col-md-3">
        <label class="form-label" for="account_id">Account <span class="text-danger">*</span></label>
        <select name="account_id" id="account_id" class="form-select form-select-sm" style="width:100%">
            @if($account)
                <option value="{{ $account->id }}" selected>{{ $account->code }} – {{ $account->name }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="party_type">Party type</label>
        <select name="party_type" id="party_type" class="form-select form-select-sm">
            <option value="">All parties</option>
            @foreach($partyTypes as $key => $label)
                <option value="{{ $key }}" @selected(request('party_type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="party_id">Party</label>
        <select name="party_id" id="party_id" class="form-select form-select-sm" style="width:100%">
            @if(request('party_id') && $ledger)
                <option value="{{ request('party_id') }}" selected>{{ collect($ledger['rows'])->firstWhere('party_name')['party_name'] ?? '#'.request('party_id') }}</option>
            @endif
        </select>
    </div>
@endcomponent

@if($account && $account->is_group)
    <div class="alert alert-warning">{{ $account->code }} {{ $account->name }} is a group. Select a ledger account.</div>
@elseif($ledger)
    <div class="card report-card">
        <div class="card-body">
            <div class="report-title">
                <h5>{{ $companyName }}</h5>
                <div class="fw-semibold">Ledger — {{ $account->code }} {{ $account->name }}</div>
                <div class="period">{{ \Illuminate\Support\Carbon::parse($from)->format('d-m-Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d-m-Y') }}</div>
            </div>
            <table class="table table-bordered table-sm report-table" id="reportTable">
                <thead>
                    <tr>
                        <th style="width:85px">Date</th>
                        <th>Voucher</th>
                        <th>Particulars</th>
                        <th>Party</th>
                        <th>Narration</th>
                        <th class="num">Debit</th>
                        <th class="num">Credit</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="subgroup-row">
                        <td colspan="7">Opening balance</td>
                        <td class="num">@include('admin.accounting.reports._balance', ['amount' => $ledger['opening']])</td>
                    </tr>
                    @foreach($ledger['rows'] as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                            <td><a href="{{ route('admin.accounting.vouchers.show', $row['voucher_id']) }}" target="_blank">{{ $row['voucher_no'] }}</a></td>
                            <td>{{ $row['particulars'] }}</td>
                            <td>{{ $row['party_name'] }}</td>
                            <td>{{ $row['narration'] }}</td>
                            <td class="num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                            <td class="num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                            <td class="num">@include('admin.accounting.reports._balance', ['amount' => $row['balance']])</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5">Total / Closing balance</td>
                        <td class="num">{{ number_format($ledger['total_debit'], 2) }}</td>
                        <td class="num">{{ number_format($ledger['total_credit'], 2) }}</td>
                        <td class="num">@include('admin.accounting.reports._balance', ['amount' => $ledger['closing']])</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-light border">Select an account and click <b>Show</b>.</div>
@endif
@endsection

@push('scripts')
<script>
$(function () {
    $('#account_id').select2({
        theme: 'bootstrap-5', placeholder: 'Search account…',
        ajax: { url: '{{ route('admin.accounting.accounts.ledgers') }}', delay: 300, data: p => ({ q: p.term }), processResults: d => d },
    });
    $('#party_id').select2({
        theme: 'bootstrap-5', placeholder: 'Any party', allowClear: true,
        ajax: {
            url: '{{ route('admin.accounting.vouchers.parties') }}', delay: 300,
            data: p => ({ q: p.term, company_id: $('#company_id').val(), type: $('#party_type').val() }),
            processResults: d => d,
        },
    });
    $('#party_type, #company_id').on('change', function () { $('#party_id').val(null).trigger('change'); });
});
</script>
@endpush

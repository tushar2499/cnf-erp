@extends('admin.layouts.app')

@section('title', 'Voucher '.$voucher->voucher_no)

@include('admin.accounting.partials.print-styles')

@section('content')
<div class="page-header no-print">
    <h4><i class="fa fa-file-invoice-dollar me-2 text-success"></i> Voucher {{ $voucher->voucher_no }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.accounting.vouchers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Vouchers</a>
        @if(! $voucher->is_auto && $voucher->isPosted() && auth()->user()->hasPermission('admin.accounting.voucher.edit'))
            <a href="{{ route('admin.accounting.vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i> Edit</a>
        @endif
        <button class="btn btn-sm btn-primary" onclick="window.print()"><i class="fa fa-print me-1"></i> Print</button>
    </div>
</div>

<div class="card report-card">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-0 fw-bold">{{ $voucher->company->name }}</h5>
            <div class="fs-6 text-uppercase fw-semibold mt-1">{{ \App\Models\Accounting\AccVoucher::TYPES[$voucher->voucher_type] }} Voucher</div>
            @unless($voucher->isPosted())
                <div class="text-danger fw-bold mt-1">CANCELLED — {{ $voucher->cancel_reason }}</div>
            @endunless
        </div>

        <div class="row small mb-3">
            <div class="col-6">
                <div><b>Voucher No:</b> {{ $voucher->voucher_no }}</div>
                <div><b>Date:</b> {{ $voucher->voucher_date->format('d-m-Y') }}</div>
            </div>
            <div class="col-6 text-end">
                @if($voucher->is_auto)
                    <div><b>Source:</b> {{ $sourceLabel ?? $voucher->source_type }}</div>
                    <div><b>Document:</b> {{ $voucher->source_doc_no }}</div>
                @else
                    <div><b>Entry:</b> Manual</div>
                @endif
            </div>
        </div>

        <table class="table table-bordered table-sm report-table">
            <thead class="table-light">
                <tr>
                    <th style="width:14%">Code</th>
                    <th>Account</th>
                    <th>Party</th>
                    <th class="text-end" style="width:15%">Debit</th>
                    <th class="text-end" style="width:15%">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voucher->lines as $line)
                    <tr>
                        <td>{{ $line->account->code }}</td>
                        <td>{{ $line->account->name }}@if($line->narration)<br><small class="text-muted">{{ $line->narration }}</small>@endif</td>
                        <td>{{ $line->party_name }}</td>
                        <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                        <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Total</td>
                    <td class="text-end">{{ number_format($voucher->lines->sum('debit'), 2) }}</td>
                    <td class="text-end">{{ number_format($voucher->lines->sum('credit'), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="small"><b>Narration:</b> {{ $voucher->narration }}</p>

        <div class="row text-center small signatures">
            <div class="col"><div class="sign-line">Prepared by<br>{{ $voucher->creator?->name }}</div></div>
            <div class="col"><div class="sign-line">Checked by</div></div>
            <div class="col"><div class="sign-line">Approved by</div></div>
        </div>
    </div>
</div>
@endsection

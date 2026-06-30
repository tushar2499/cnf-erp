@extends('nas-freights.layouts.app')

@section('title', 'Money Receipt — ' . $moneyReceipt->receipt_no)

@section('content')
<div class="page-header">
    <h4><i class="fa fa-money-bill-wave me-2 text-info"></i> Money Receipt</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-freights.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-print me-1"></i> Print
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header py-2" style="background:#198754;color:#fff;font-weight:600;">
        <i class="fa fa-receipt me-2"></i> {{ $moneyReceipt->receipt_no }}
        <span class="badge bg-light text-success ms-2">PAID</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Receipt No</th><td>{{ $moneyReceipt->receipt_no }}</td></tr>
                    <tr><th class="text-muted">Receipt Date</th><td>{{ $moneyReceipt->receipt_date?->format('d-M-Y') }}</td></tr>
                    <tr><th class="text-muted">Customer</th><td>{{ $moneyReceipt->customer_name }}</td></tr>
                    <tr><th class="text-muted">Bill No</th><td>{{ $moneyReceipt->bill_no }}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Bill Amount</th><td>{{ number_format($moneyReceipt->bill_amount, 2) }}</td></tr>
                    <tr><th class="text-muted">Amount Received</th><td class="fw-bold text-success">{{ number_format($moneyReceipt->amount_received, 2) }}</td></tr>
                    <tr><th class="text-muted">Payment Mode</th><td>{{ $moneyReceipt->payment_mode }}</td></tr>
                    <tr><th class="text-muted">Reference No</th><td>{{ $moneyReceipt->reference_no ?: '—' }}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Entry By</th><td>{{ $moneyReceipt->entry_by }}</td></tr>
                    <tr><th class="text-muted">Entry Date</th><td>{{ $moneyReceipt->created_at?->format('d-M-Y H:i') }}</td></tr>
                    @if($moneyReceipt->note)
                    <tr><th class="text-muted">Note</th><td>{{ $moneyReceipt->note }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('nas-freights.layouts.app')

@section('title', 'Supplier Payment — ' . $supplierPayment->payment_no)

@section('content')
<div class="page-header">
    <h4><i class="fa fa-hand-holding-usd me-2 text-info"></i> Supplier Payment</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-freights.supplier-payments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-print me-1"></i> Print
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header py-2" style="background:#0d6efd;color:#fff;font-weight:600;">
        <i class="fa fa-money-check me-2"></i> {{ $supplierPayment->payment_no }}
        <span class="badge bg-light text-primary ms-2">PAID</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Payment No</th><td>{{ $supplierPayment->payment_no }}</td></tr>
                    <tr><th class="text-muted">Payment Date</th><td>{{ $supplierPayment->payment_date?->format('d-M-Y') }}</td></tr>
                    <tr><th class="text-muted">Supplier</th><td>{{ $supplierPayment->supplier_name }}</td></tr>
                    <tr><th class="text-muted">Pay Order No</th><td>{{ $supplierPayment->bill_no }}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Bill Amount</th><td>{{ number_format($supplierPayment->bill_amount, 2) }}</td></tr>
                    <tr><th class="text-muted">Amount Paid</th><td class="fw-bold text-primary">{{ number_format($supplierPayment->amount_paid, 2) }}</td></tr>
                    <tr><th class="text-muted">Payment Mode</th><td>{{ $supplierPayment->payment_mode }}</td></tr>
                    <tr><th class="text-muted">Reference No</th><td>{{ $supplierPayment->reference_no ?: '—' }}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-sm table-borderless" style="font-size:.82rem">
                    <tr><th class="text-muted w-50">Entry By</th><td>{{ $supplierPayment->entry_by }}</td></tr>
                    <tr><th class="text-muted">Entry Date</th><td>{{ $supplierPayment->created_at?->format('d-M-Y H:i') }}</td></tr>
                    @if($supplierPayment->note)
                    <tr><th class="text-muted">Note</th><td>{{ $supplierPayment->note }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

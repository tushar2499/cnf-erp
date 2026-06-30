@extends('nas-trading.layouts.app')
@section('title', 'Receipt — ' . $moneyReceipt->receipt_no)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.info-header { background:#198754; color:#fff; padding:.45rem 1rem; font-size:.8rem; font-weight:700; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.72rem; color:#6c757d; text-transform:uppercase; }
.info-value { font-size:.85rem; font-weight:600; }
.receipt-box { border:2px dashed #198754; border-radius:.5rem; padding:1.5rem; text-align:center; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-money-bill-wave me-2 text-success"></i> {{ $moneyReceipt->receipt_no }}</h4>
    <a href="{{ route('nas-trading.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-receipt me-2"></i> Receipt Details</div>
            <div class="info-body">
                <div class="receipt-box mb-3">
                    <div style="font-size:.8rem;color:#6c757d">Receipt No</div>
                    <div style="font-size:1.5rem;font-weight:700;color:#198754">{{ $moneyReceipt->receipt_no }}</div>
                    <div style="font-size:.85rem">{{ $moneyReceipt->receipt_date?->format('d-M-Y') }}</div>
                </div>
                <div class="row g-2">
                    @php $fields = [
                        ['Customer',        $moneyReceipt->customer_name ?? '-'],
                        ['Bill No',         $moneyReceipt->bill_no ?? '-'],
                        ['Bill Amount',     'BDT ' . number_format($moneyReceipt->bill_amount ?? 0, 2)],
                        ['Amount Received', 'BDT ' . number_format($moneyReceipt->amount_received, 2)],
                        ['Payment Mode',    $moneyReceipt->payment_mode ?? '-'],
                        ['Reference No',    $moneyReceipt->reference_no ?? '-'],
                    ] @endphp
                    @foreach($fields as [$label, $value])
                    <div class="col-6">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value {{ $label === 'Amount Received' ? 'text-success fs-6' : '' }}">{{ $value }}</div>
                    </div>
                    @endforeach
                    @if($moneyReceipt->note)
                    <div class="col-12">
                        <div class="info-label">Note</div>
                        <div class="info-value">{{ $moneyReceipt->note }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

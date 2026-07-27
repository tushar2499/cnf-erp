@extends('nas-trading.layouts.app')
@section('title', 'Bill — ' . $customerBill->bill_no)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.info-header { background:#0c2340; color:#fff; padding:.45rem 1rem; font-size:.8rem; font-weight:700; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.72rem; color:#6c757d; text-transform:uppercase; }
.info-value { font-size:.85rem; font-weight:600; }
.bill-items th { background:#1a6b60; color:#fff; font-size:.77rem; padding:.4rem .5rem; }
.bill-items td { font-size:.8rem; padding:.35rem .5rem; vertical-align:middle; }
.print-header { display:none; }
@media print {
    .top-navbar, .mobile-context-bar, .sidebar, footer, .d-print-none { display:none !important; }
    .main-content { margin-left:0 !important; padding:0.5rem !important; }
    body { background:#fff !important; font-size:9px !important; }
    .print-header { display:block; margin-bottom:.6rem; padding-bottom:.4rem; border-bottom:1px solid #0c2340; }
    .print-header .co-name { font-size:.95rem; margin:0; }
    .print-header .co-meta { font-size:.65rem; margin:0; }
    .print-header .bill-title { font-size:.8rem; margin:.25rem 0 0; }
    .info-card { border:1px solid #ccc; border-radius:0; margin-bottom:.35rem; page-break-inside:avoid; }
    .info-header { background:#0c2340 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; padding:.3rem .6rem; font-size:.7rem; }
    .info-body { padding:.4rem .6rem; }
    .info-label { font-size:.62rem; }
    .info-value { font-size:.72rem; }
    .bill-items th { background:#1a6b60 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; padding:.25rem .35rem; font-size:.65rem; }
    .bill-items td { padding:.2rem .35rem; font-size:.65rem; }
    .table-success { background:#d1e7dd !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .row { page-break-inside:avoid; margin:0 !important; }
    .row.g-3 > * { padding:0 !important; }
    .col-md-8, .col-md-4 { width:100% !important; flex:0 0 100% !important; max-width:100% !important; }
    .info-card .row.g-2 > * { padding:0 .2rem !important; }
    .info-card .col-md-3 { width:50% !important; flex:0 0 50% !important; max-width:50% !important; }
    a[href]::after { content:none !important; }
    .container-fluid { padding:0 !important; }
    .table { margin-bottom:0 !important; }
    .table-sm td, .table-sm th { padding:.2rem .35rem; font-size:.65rem; }
}
</style>
@endpush

@section('content')

{{-- Print-only header (hidden on screen) --}}
<div class="print-header">
    <p class="co-name">{{ $activeCompany->name ?? 'NAS Trading' }}</p>
    @if(isset($activeBranch))<p class="co-meta">{{ $activeBranch->name }}</p>@endif
    <p class="bill-title">Customer Bill</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <div>
        <h4 class="mb-0"><i class="fa fa-file-invoice-dollar me-2 text-success"></i> {{ $customerBill->bill_no }}</h4>
        <small class="text-muted">{{ $customerBill->bill_date?->format('d-M-Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        @if($customerBill->status === 'Draft')
        <a href="{{ route('nas-trading.customer-bills.edit', $customerBill->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i>Edit</a>
        <button class="btn btn-sm btn-outline-success" id="btnConfirm"><i class="fa fa-check me-1"></i>Confirm</button>
        @endif
        @if($customerBill->status === 'Confirmed')
        <a href="{{ route('nas-trading.money-receipts.create') }}?bill_id={{ $customerBill->id }}" class="btn btn-sm btn-success"><i class="fa fa-money-bill-wave me-1"></i>Receive Payment</a>
        <a href="{{ route('nas-trading.deliveries.create') }}?bill_id={{ $customerBill->id }}" class="btn btn-sm btn-outline-info"><i class="fa fa-truck me-1"></i>Create Delivery</a>
        @endif
        <button class="btn btn-sm btn-outline-dark" onclick="window.print()"><i class="fa fa-print me-1"></i>Print</button>
        <a href="{{ route('nas-trading.customer-bills.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-info-circle me-2"></i> Bill Information</div>
            <div class="info-body">
                <div class="row g-2">
                    <div class="col-md-3"><div class="info-label">Bill No</div><div class="info-value">{{ $customerBill->bill_no }}</div></div>
                    <div class="col-md-3"><div class="info-label">Bill Date</div><div class="info-value">{{ $customerBill->bill_date?->format('d-M-Y') }}</div></div>
                    <div class="col-md-3"><div class="info-label">LC No</div><div class="info-value">{{ $customerBill->lc->lc_no ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="info-label">Status</div>
                        <div class="info-value">
                            @php $badge = ['Draft' => 'secondary', 'Confirmed' => 'success', 'Paid' => 'primary'] @endphp
                            <span class="badge bg-{{ $badge[$customerBill->status] ?? 'secondary' }}">{{ $customerBill->status }}</span>
                        </div>
                    </div>
                    <div class="col-md-3"><div class="info-label">PFI No</div><div class="info-value">{{ $customerBill->pfi_no ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="info-label">Customer</div><div class="info-value">{{ $customerBill->customer_name }}</div></div>
                    @if($customerBill->customer_address)
                    <div class="col-3"><div class="info-label">Customer Address</div><div class="info-value">{{ $customerBill->customer_address }}</div></div>
                    @endif
                    <div class="col-md-3"><div class="info-label">Currency</div><div class="info-value">{{ $customerBill->currency }}</div></div>
                    <div class="col-md-3"><div class="info-label">Item Description</div><div class="info-value">{{ $customerBill->lc->item_description ?? '-' }}</div></div>
                    @if($customerBill->note)
                    <div class="col-12"><div class="info-label">Note</div><div class="info-value">{{ $customerBill->note }}</div></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-header"><i class="fa fa-list-ul me-2"></i> Bill Items</div>
            <div style="overflow-x:auto">
                <table class="table table-bordered bill-items mb-0 w-100">
                    <thead><tr>
                        <th style="width:40px">#</th>
                        <th>Description</th>
                        <th style="width:120px">Amount</th>
                        <th>Note</th>
                    </tr></thead>
                    <tbody>
                        @forelse($customerBill->items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end fw-bold">{{ number_format($item->amount, 2) }}</td>
                            <td class="text-muted" style="font-size:.75rem">{{ $item->note }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No items</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background:#f0f4f8">
                            <td colspan="2" class="text-end fw-bold" style="font-size:.82rem">Total</td>
                            <td class="text-end fw-bold" style="font-size:.82rem">{{ number_format($customerBill->items->sum('amount'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-calculator me-2"></i> Bill Totals</div>
            <div class="info-body">
                @php
                    $advancePayment = $customerBill->lc?->payments?->where('payment_type', 'advance')->sum('amount') ?? 0;
                    $dutyAdvance    = (float)($customerBill->lc?->duty_advance ?? 0);
                    $totalAdvance   = $advancePayment + $dutyAdvance;
                    $transportAmt   = $customerBill->sub_total + $customerBill->vat_amount - $totalAdvance;
                @endphp
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Sub Total</td><td class="text-end fw-bold">{{ number_format($customerBill->sub_total, 2) }}</td></tr>
                    <tr><td class="text-muted">VAT ({{ floatval($customerBill->vat_pct) }}%)</td><td class="text-end fw-bold">{{ number_format($customerBill->vat_amount, 2) }}</td></tr>
                    <tr class="table-success"><td class="fw-bold fs-6">Total Amount</td><td class="text-end fw-bold fs-6">{{ number_format($customerBill->sub_total + $customerBill->vat_amount, 2) }} {{ $customerBill->currency }}</td></tr>
                </table>
                <hr class="my-2">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.82rem;color:#6c757d">LC Advance Payment</span>
                    <span style="font-size:.82rem">BDT {{ number_format($advancePayment, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.82rem;color:#6c757d">Duty Advance</span>
                    <span style="font-size:.82rem">BDT {{ number_format($dutyAdvance, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold" style="font-size:.85rem">Total Advance</span>
                    <strong style="font-size:.9rem;color:#0c2340">BDT {{ number_format($totalAdvance, 2) }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold" style="font-size:.85rem">Balance Amount</span>
                    <strong class="text-success" style="font-size:1rem">BDT {{ number_format($transportAmt, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#btnConfirm').on('click', function () {
    Swal.fire({ title: 'Confirm this bill?', text: 'Bill will move to Confirmed and appear in Due List.', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, Confirm' })
    .then(res => {
        if (res.isConfirmed) {
            $.post('{{ route('nas-trading.customer-bills.confirm', $customerBill->id) }}', { _token: '{{ csrf_token() }}' })
            .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()))
            .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
        }
    });
});
</script>
@endpush

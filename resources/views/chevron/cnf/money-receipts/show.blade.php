@extends('chevron.layouts.app')

@section('title', 'Money Receipt — ' . $moneyReceipt->receipt_no)

@push('styles')
<style>
    .mr-label { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); margin-bottom: .15rem; }
    .mr-value { font-size: .9rem; font-weight: 500; margin-bottom: 0; overflow-wrap: anywhere; }
    .mr-total { border-left: 4px solid var(--bs-primary); background: var(--bs-primary-bg-subtle); }
    .mr-table th, .mr-table td { font-size: .8rem; vertical-align: middle; }
    .mr-table td.num, .mr-table th.num, .num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .mr-table tfoot th { background: var(--bs-light-bg-subtle, #f8f9fa); }
    .mr-badge { font-size: .72rem; font-weight: 600; }
</style>
@endpush

@section('content')
@php
    $typeMeta = match ($moneyReceipt->receipt_type) {
        \App\Models\Chevron\ChevronMoneyReceipt::TYPE_ADVANCE => ['Advance', 'bg-info text-dark', 'fa-briefcase'],
        \App\Models\Chevron\ChevronMoneyReceipt::TYPE_BILL    => ['Bill', 'bg-success', 'fa-file-invoice'],
        default                                               => ['Legacy', 'bg-secondary', 'fa-clock'],
    };
    $statusMeta = match ($moneyReceipt->status) {
        'Approved'  => ['Approved', 'bg-success', 'fa-check-circle'],
        'Submitted' => ['Submitted', 'bg-warning text-dark', 'fa-paper-plane'],
        default     => ['Active', 'bg-primary', 'fa-circle'],
    };

    $isBill     = $moneyReceipt->receipt_type === \App\Models\Chevron\ChevronMoneyReceipt::TYPE_BILL;
    $isAdvance  = $moneyReceipt->receipt_type === \App\Models\Chevron\ChevronMoneyReceipt::TYPE_ADVANCE;
    $billRows   = $moneyReceipt->allocations->whereNotNull('bill_id')->values();
    $advanceRows = $moneyReceipt->allocations->whereNull('bill_id')->sortBy(fn ($a) => $a->job_id === null ? 1 : 0)->values();
    $allocatedTotal = (float) $moneyReceipt->allocations->sum('amount');
    $paymentTotal   = (float) $moneyReceipt->items->sum('amount');
@endphp

<div class="page-header">
    <h4>
        <i class="fa fa-receipt me-2 text-primary"></i> Money Receipt
        <small class="text-muted ms-1">{{ $moneyReceipt->receipt_no }}</small>
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('chevron.cnf.money-receipts.print', $moneyReceipt->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
            <i class="fa fa-print me-1"></i> Print
        </a>
        @if ($canEdit)
            <a href="{{ route('chevron.cnf.money-receipts.edit', $moneyReceipt->id) }}" class="btn btn-sm btn-primary">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
        @endif
    </div>
</div>

{{-- Summary --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="fs-5 fw-bold">{{ $moneyReceipt->receipt_no }}</span>
                    <span class="badge mr-badge {{ $typeMeta[1] }}"><i class="fa {{ $typeMeta[2] }} me-1"></i>{{ $typeMeta[0] }}</span>
                    <span class="badge mr-badge {{ $statusMeta[1] }}"><i class="fa {{ $statusMeta[2] }} me-1"></i>{{ $statusMeta[0] }}</span>
                </div>

                <dl class="row g-3 mb-0">
                    <div class="col-sm-6">
                        <dt class="mr-label">Party</dt>
                        <dd class="mr-value">{{ $moneyReceipt->party_name }}</dd>
                    </div>
                    <div class="col-sm-3">
                        <dt class="mr-label">Receipt Date</dt>
                        <dd class="mr-value">{{ $moneyReceipt->receipt_date?->format('d-M-Y') }}</dd>
                    </div>
                    <div class="col-sm-3">
                        <dt class="mr-label">Payment Method</dt>
                        <dd class="mr-value">{{ $moneyReceipt->pay_type }}</dd>
                    </div>
                    <div class="col-sm-6">
                        <dt class="mr-label">Branch</dt>
                        <dd class="mr-value">{{ $moneyReceipt->branch?->name ?? '—' }}</dd>
                    </div>
                    @if ($isBill)
                        <div class="col-sm-3">
                            <dt class="mr-label">Bill Payable (at receipt)</dt>
                            <dd class="mr-value num text-start">{{ number_format($moneyReceipt->payable_amount, 2) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="col-lg-4">
                <div class="mr-total rounded p-3 h-100">
                    <div class="mr-label">Total Collected</div>
                    <div class="fs-3 fw-bold num text-start">BDT {{ number_format($moneyReceipt->total_amount, 2) }}</div>
                    <div class="small text-muted mt-1">{{ $amountInWords }}</div>
                </div>
            </div>
        </div>

        @if (filled($moneyReceipt->description))
            <hr>
            <div class="mr-label">Description</div>
            <p class="mr-value mb-0">{!! nl2br(e($moneyReceipt->description)) !!}</p>
        @endif
    </div>
</div>

{{-- Collected against --}}
@if ($isBill)
    <div class="card mb-3">
        <div class="card-header fw-bold">
            <i class="fa fa-file-invoice me-2"></i>Collected Against Bills
            <span class="badge bg-secondary ms-1">{{ $billRows->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 mr-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Bill No</th>
                            <th>Bill Date</th>
                            <th>Job No</th>
                            <th class="num">Bill Amount</th>
                            <th class="num">Collected (this receipt)</th>
                            <th class="num">Balance Now</th>
                            <th>Bill Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($billRows as $row)
                            @php
                                $bill = $row->bill;
                                $balance = $bill ? $bill->balance : null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $bill?->bill_no ?? '—' }}</td>
                                <td>{{ $bill?->bill_date?->format('d-M-Y') ?? '—' }}</td>
                                <td>{{ $row->job?->job_no ?? $bill?->job_no ?? '—' }}</td>
                                <td class="num">{{ $bill ? number_format($bill->due_amount, 2) : '—' }}</td>
                                <td class="num fw-semibold">{{ number_format($row->amount, 2) }}</td>
                                <td class="num">{{ $balance !== null ? number_format($balance, 2) : '—' }}</td>
                                <td>
                                    @if ($balance !== null && $balance <= 0)
                                        <span class="badge mr-badge bg-success"><i class="fa fa-check-circle me-1"></i>Settled</span>
                                    @elseif ($balance !== null)
                                        <span class="badge mr-badge bg-warning text-dark"><i class="fa fa-hourglass-half me-1"></i>Partially paid</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total collected</th>
                            <th class="num">{{ number_format($allocatedTotal, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@elseif ($isAdvance)
    <div class="card mb-3">
        <div class="card-header fw-bold">
            <i class="fa fa-briefcase me-2"></i>Advance Allocation
            <span class="badge bg-secondary ms-1">{{ $advanceRows->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 mr-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Job No</th>
                            <th class="num">Advance Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($advanceRows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($row->job)
                                        <span class="fw-semibold">{{ $row->job->job_no }}</span>
                                    @else
                                        <span class="text-muted"><i class="fa fa-layer-group me-1"></i>General advance (no job)</span>
                                    @endif
                                </td>
                                <td class="num fw-semibold">{{ number_format($row->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total advance</th>
                            <th class="num">{{ number_format($allocatedTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-secondary d-flex align-items-start gap-2" role="note">
        <i class="fa fa-info-circle mt-1"></i>
        <div>
            <strong>Legacy receipt.</strong> This receipt was created before bill and advance tracking, so it is not linked to a bill or a job.
            @if ($canEdit) Edit it to choose Advance or Bill. @endif
        </div>
    </div>
@endif

{{-- Payment details --}}
<div class="card mb-3">
    <div class="card-header fw-bold">
        <i class="fa fa-list me-2"></i>Payment Details
        <span class="badge bg-secondary ms-1">{{ $moneyReceipt->items->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 mr-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Type</th>
                        <th>Account</th>
                        <th>Cheque / Card Holder</th>
                        <th>Cheque / Card No</th>
                        <th>Cheque Date</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($moneyReceipt->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->payment_type }}</td>
                            <td>
                                @if ($item->account)
                                    {{ $item->account->account_no }}@if ($item->account->account_name) <span class="text-muted">— {{ $item->account->account_name }}</span>@endif
                                @else
                                    {{ $item->account_no ?: '—' }}
                                @endif
                            </td>
                            <td>{{ $item->cheque_card_holder ?: '—' }}</td>
                            <td>{{ $item->cheque_card_no ?: '—' }}</td>
                            <td>{{ $item->cheque_date?->format('d-M-Y') ?? '—' }}</td>
                            <td class="num fw-semibold">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No payment rows.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total collected</th>
                        <th class="num">{{ number_format($paymentTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<p class="text-muted small mb-0">
    Created {{ $moneyReceipt->created_at?->format('d-M-Y h:i A') ?? '—' }}
    @if ($moneyReceipt->updated_at && $moneyReceipt->updated_at->ne($moneyReceipt->created_at))
        · Last updated {{ $moneyReceipt->updated_at->format('d-M-Y h:i A') }}
    @endif
</p>
@endsection

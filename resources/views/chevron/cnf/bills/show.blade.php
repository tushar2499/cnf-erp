@extends('chevron.layouts.app')

@section('title', 'Bill — ' . $bill->bill_no)

@push('styles')
<style>
    .bv-label { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); margin-bottom: .15rem; }
    .bv-value { font-size: .9rem; font-weight: 500; margin-bottom: 0; overflow-wrap: anywhere; }
    .bv-table th, .bv-table td { font-size: .8rem; vertical-align: middle; }
    .bv-table td.num, .bv-table th.num, .num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .bv-table tfoot th, .bv-table tfoot td { background: var(--bs-light-bg-subtle, #f8f9fa); }
    .bv-table tr.bv-cat td { background: var(--bs-tertiary-bg, #f8f9fa); font-weight: 600; font-size: .72rem; letter-spacing: .04em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .bv-badge { font-size: .72rem; font-weight: 600; }
    .bv-tile { border-left: 4px solid var(--bs-secondary); background: var(--bs-tertiary-bg, #f8f9fa); }
    .bv-tile.tile-primary { border-left-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
    .bv-tile.tile-success { border-left-color: var(--bs-success); background: var(--bs-success-bg-subtle); }
    .bv-tile.tile-warning { border-left-color: var(--bs-warning); background: var(--bs-warning-bg-subtle); }
    .bv-tile.tile-danger { border-left-color: var(--bs-danger); background: var(--bs-danger-bg-subtle); }
    .bv-tile .fs-4 { font-variant-numeric: tabular-nums; }
</style>
@endpush

@section('content')
@php
    $statusMeta = match ($bill->status) {
        'Approved'  => ['Approved', 'bg-success', 'fa-check-circle'],
        'Submitted' => ['Submitted', 'bg-warning text-dark', 'fa-paper-plane'],
        default     => ['Active', 'bg-primary', 'fa-circle'],
    };

    $totalPayable  = round((float) $bill->total_payable, 2);
    $lessDuty      = round((float) $bill->less_customs_duty_tax, 2);
    $lessIncomeTax = round((float) $bill->income_tax_cnf_com, 2);
    $netPayable    = round($totalPayable - $lessDuty - $lessIncomeTax, 2);
    $advance       = round((float) $bill->advance_amount, 2);
    $dueAmount     = round((float) $bill->due_amount, 2);
    $received      = round((float) $bill->received_amount, 2);
    $outstanding   = round($dueAmount - $received, 2);
    $jobAdvance    = round((float) $advanceCollections->sum('amount'), 2);

    $paymentMeta = $outstanding <= 0
        ? ['Settled', 'bg-success', 'fa-check-circle', 'tile-success']
        : ($received > 0 ? ['Partially paid', 'bg-warning text-dark', 'fa-hourglass-half', 'tile-warning'] : ['Unpaid', 'bg-danger', 'fa-exclamation-circle', 'tile-danger']);

    $commissionLabel = match ($bill->commission_on) {
        'ASSESSABLE'    => '@'.number_format((float) $bill->commission_rate, 2).'% Commission on Assessable Value',
        'INVOICE VALUE' => '@'.number_format((float) $bill->commission_rate, 2).'% Commission on Invoice Value',
        default         => $bill->commission_on.' Commission',
    };

    $currencyName = $bill->job?->currency_type ?: $bill->currency_type;
    $currencyRate = $bill->job?->currency_rate ?: $bill->currency_rate;
    $hasAdvanceInfo = $advance > 0 || $advanceCollections->isNotEmpty();
@endphp

<div class="page-header">
    <h4>
        <i class="fa fa-file-invoice me-2 text-success"></i> Bill
        <small class="text-muted ms-1">{{ $bill->bill_no }}</small>
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
        @if ($canPrint)
            <a href="{{ route('chevron.cnf.bills.print', $bill->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark">
                <i class="fa fa-print me-1"></i> Print
            </a>
        @endif
        @if ($canEdit)
            <a href="{{ route('chevron.cnf.bills.edit', $bill->id) }}" class="btn btn-sm btn-primary">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
        @endif
    </div>
</div>

{{-- Amount tiles --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="bv-tile tile-primary rounded p-3 h-100">
            <div class="bv-label">Bill Balance</div>
            <div class="fs-4 fw-bold">{{ number_format($dueAmount, 2) }}</div>
            <div class="small text-muted">After advance adjustment</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bv-tile rounded p-3 h-100">
            <div class="bv-label">Advance Adjusted</div>
            <div class="fs-4 fw-bold">{{ number_format($advance, 2) }}</div>
            <div class="small text-muted">
                @if ($advanceCollections->isNotEmpty())
                    {{ number_format($jobAdvance, 2) }} collected for this job
                @else
                    Taken at billing
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bv-tile rounded p-3 h-100">
            <div class="bv-label">Payments Received</div>
            <div class="fs-4 fw-bold">{{ number_format($received, 2) }}</div>
            <div class="small text-muted">{{ $receiptCollections->count() }} {{ \Illuminate\Support\Str::plural('receipt', $receiptCollections->count()) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bv-tile {{ $paymentMeta[3] }} rounded p-3 h-100">
            <div class="bv-label">Outstanding</div>
            <div class="fs-4 fw-bold">{{ number_format($outstanding, 2) }}</div>
            <span class="badge bv-badge {{ $paymentMeta[1] }}"><i class="fa {{ $paymentMeta[2] }} me-1"></i>{{ $paymentMeta[0] }}</span>
        </div>
    </div>
</div>

{{-- Summary --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="fs-5 fw-bold">{{ $bill->bill_no }}</span>
            @if ($bill->bill_type)
                <span class="badge bv-badge bg-secondary">{{ $bill->bill_type }}</span>
            @endif
            <span class="badge bv-badge {{ $statusMeta[1] }}"><i class="fa {{ $statusMeta[2] }} me-1"></i>{{ $statusMeta[0] }}</span>
        </div>

        <dl class="row g-3 mb-0">
            <div class="col-md-6">
                <dt class="bv-label">Party</dt>
                <dd class="bv-value">{{ $bill->party_name ?: '—' }}</dd>
            </div>
            <div class="col-md-6">
                <dt class="bv-label">Address</dt>
                <dd class="bv-value">{{ $bill->party_address ?: '—' }}</dd>
            </div>
            <div class="col-sm-6 col-md-3">
                <dt class="bv-label">Bill Date</dt>
                <dd class="bv-value">{{ $bill->bill_date?->format('d-M-Y') ?? '—' }}</dd>
            </div>
            <div class="col-sm-6 col-md-3">
                <dt class="bv-label">Delivery Date</dt>
                <dd class="bv-value">{{ $bill->delivery_date?->format('d-M-Y') ?? '—' }}</dd>
            </div>
            <div class="col-sm-6 col-md-3">
                <dt class="bv-label">Job No</dt>
                <dd class="bv-value">{{ $bill->job_no ?: '—' }}</dd>
            </div>
            <div class="col-sm-6 col-md-3">
                <dt class="bv-label">Branch</dt>
                <dd class="bv-value">{{ $bill->branch?->name ?? '—' }}</dd>
            </div>
        </dl>

        @if (filled($bill->remarks))
            <hr>
            <div class="bv-label">Remarks</div>
            <p class="bv-value mb-0">{!! nl2br(e($bill->remarks)) !!}</p>
        @endif
    </div>
</div>

{{-- Job particulars --}}
<div class="card mb-3">
    <div class="card-header fw-bold"><i class="fa fa-ship me-2"></i>Job Particulars</div>
    <div class="card-body">
        <dl class="row g-3 mb-0">
            <div class="col-12">
                <dt class="bv-label">Description of Goods</dt>
                <dd class="bv-value">{{ $bill->goods_description ?: '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">P/O No</dt>
                <dd class="bv-value">{{ $bill->po_no ?: '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Mate Code</dt>
                <dd class="bv-value">{{ $bill->mate_code ?: '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Quantity</dt>
                <dd class="bv-value">{{ $bill->quantity ? number_format($bill->quantity, 2).' '.($bill->quantity_unit ?? '') : '—' }}{{ $bill->quantity_remark ? ' '.$bill->quantity_remark : '' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Weight</dt>
                <dd class="bv-value">{{ $bill->gross_weight ? number_format($bill->gross_weight, 2).' '.($bill->gross_weight_unit ?? 'KG') : '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">L.C No</dt>
                <dd class="bv-value">{{ $bill->lc_no ?: '—' }}{{ $bill->lc_ref ? ' / '.$bill->lc_ref : '' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">B.E No / Date</dt>
                <dd class="bv-value">{{ $bill->be_no ?: '—' }}{{ $bill->be_date ? ' · '.$bill->be_date->format('d-M-Y') : '' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Invoice No / Date</dt>
                <dd class="bv-value">{{ $bill->invoice_no ?: '—' }}{{ $bill->invoice_date ? ' · '.$bill->invoice_date->format('d-M-Y') : '' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">MAWB / B.L No / Date</dt>
                <dd class="bv-value">{{ $bill->bl_no ?: '—' }}{{ $bill->bl_ref ? ' / '.$bill->bl_ref : '' }}{{ $bill->bl_date ? ' · '.$bill->bl_date->format('d-M-Y') : '' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Assessable Value</dt>
                <dd class="bv-value num text-start">{{ $bill->assessable_value ? number_format($bill->assessable_value, 2) : '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Invoice Value{{ $currencyName ? ' ('.$currencyName.')' : '' }}</dt>
                <dd class="bv-value num text-start">{{ $bill->invoice_value ? number_format($bill->invoice_value, 2) : '—' }}</dd>
            </div>
            <div class="col-sm-6 col-lg-3">
                <dt class="bv-label">Invoice Value BDT</dt>
                <dd class="bv-value num text-start">{{ $bill->invoice_value_bdt ? number_format($bill->invoice_value_bdt, 2) : '—' }}</dd>
            </div>
            @if ($currencyRate)
                <div class="col-sm-6 col-lg-3">
                    <dt class="bv-label">{{ $currencyName ? $currencyName.' Rate' : 'Currency Rate' }}</dt>
                    <dd class="bv-value num text-start">{{ number_format($currencyRate, 2) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>

{{-- Charges --}}
<div class="card mb-3">
    <div class="card-header fw-bold">
        <i class="fa fa-list me-2"></i>Charges
        <span class="badge bg-secondary ms-1">{{ $bill->items->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 bv-table">
                <thead class="table-light">
                    <tr>
                        <th>Particulars</th>
                        <th class="num" style="width:170px">Amount (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grouped as $catName => $items)
                        <tr class="bv-cat"><td colspan="2">{{ $catName }}</td></tr>
                        @foreach ($items as $item)
                            <tr>
                                <td class="ps-4">
                                    {{ $item->expenseHead?->name ?? '—' }}
                                    @if ($item->note)<span class="text-muted"> — {{ $item->note }}</span>@endif
                                    @if ($item->rate && $item->qty)
                                        <span class="text-muted small"> ({{ number_format($item->rate, 2) }} × {{ rtrim(rtrim(number_format($item->qty, 3), '0'), '.') }})</span>
                                    @endif
                                </td>
                                <td class="num">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if ($grouped->isEmpty())
                        <tr><td colspan="2" class="text-center text-muted py-3">No charge rows.</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-end fw-semibold">Sub total</td>
                        <td class="num fw-semibold">{{ number_format((float) $bill->sub_total, 2) }}</td>
                    </tr>
                    @if ((float) $bill->commission_amount > 0)
                        <tr>
                            <td class="text-end">{{ $commissionLabel }}</td>
                            <td class="num">{{ number_format((float) $bill->commission_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-end fw-semibold">Total payable</td>
                        <td class="num fw-semibold">{{ number_format($totalPayable, 2) }}</td>
                    </tr>
                    @if ($lessDuty > 0)
                        <tr>
                            <td class="text-end">Less customs duty &amp; tax</td>
                            <td class="num">− {{ number_format($lessDuty, 2) }}</td>
                        </tr>
                    @endif
                    @if ($lessIncomeTax > 0)
                        <tr>
                            <td class="text-end">Less income tax C&amp;F</td>
                            <td class="num">− {{ number_format($lessIncomeTax, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-end fw-semibold">Net payable</td>
                        <td class="num fw-semibold">{{ number_format($netPayable, 2) }}</td>
                    </tr>
                    <tr class="table-success">
                        <td class="text-end">Less advance payment adjusted</td>
                        <td class="num">− {{ number_format($advance, 2) }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td class="text-end fw-bold">Bill balance</td>
                        <td class="num fw-bold">{{ number_format($dueAmount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer small text-muted">{{ $inWords }}</div>
</div>

{{-- Advance payment collection --}}
@if ($hasAdvanceInfo)
    <div class="card mb-3">
        <div class="card-header fw-bold">
            <i class="fa fa-briefcase me-2"></i>Advance Payment Collection
            <span class="badge bg-secondary ms-1">{{ $advanceCollections->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 bv-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Date</th>
                            <th>Money Receipt No</th>
                            <th class="num">Amount (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($advanceCollections as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['receipt_date']?->format('d-M-Y') ?? '—' }}</td>
                                <td class="fw-semibold">
                                    @if ($canOpenReceipts)
                                        <a href="{{ route('chevron.cnf.money-receipts.show', $row['receipt_id']) }}">{{ $row['receipt_no'] }}</a>
                                    @else
                                        {{ $row['receipt_no'] }}
                                    @endif
                                </td>
                                <td class="num">{{ number_format($row['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    No advance receipt is recorded against this job. The advance was taken from the customer's general advance balance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        @if ($advanceCollections->isNotEmpty())
                            <tr>
                                <th colspan="3" class="text-end">Advance collected for this job</th>
                                <th class="num">{{ number_format($jobAdvance, 2) }}</th>
                            </tr>
                        @endif
                        <tr>
                            <th colspan="3" class="text-end">Advance adjusted in this bill</th>
                            <th class="num">{{ number_format($advance, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- Payments received --}}
<div class="card mb-3">
    <div class="card-header fw-bold">
        <i class="fa fa-receipt me-2"></i>Payments Received Against This Bill
        <span class="badge bg-secondary ms-1">{{ $receiptCollections->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 bv-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Date</th>
                        <th>Money Receipt No</th>
                        <th>Method</th>
                        <th class="num">Amount (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receiptCollections as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['receipt_date']?->format('d-M-Y') ?? '—' }}</td>
                            <td class="fw-semibold">
                                @if ($canOpenReceipts)
                                    <a href="{{ route('chevron.cnf.money-receipts.show', $row['receipt_id']) }}">{{ $row['receipt_no'] }}</a>
                                @else
                                    {{ $row['receipt_no'] }}
                                @endif
                            </td>
                            <td>{{ $row['pay_type'] ?: '—' }}</td>
                            <td class="num">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No payment has been received against this bill yet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total received</th>
                        <th class="num">{{ number_format($received, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Outstanding balance</th>
                        <th class="num">{{ number_format($outstanding, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<p class="text-muted small mb-0">
    Created {{ $bill->created_at?->format('d-M-Y h:i A') ?? '—' }}
    @if ($bill->updated_at && $bill->updated_at->ne($bill->created_at))
        · Last updated {{ $bill->updated_at->format('d-M-Y h:i A') }}
    @endif
</p>
@endsection

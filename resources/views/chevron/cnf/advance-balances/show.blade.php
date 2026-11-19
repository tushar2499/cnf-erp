@extends('chevron.layouts.app')

@section('title', 'Advance Statement — ' . $customer->name)

@push('styles')
<style>
    .ab-label { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--bs-secondary-color); }
    .ab-stat { border-left: 4px solid var(--bs-secondary); }
    .ab-stat.is-received { border-left-color: var(--bs-success); }
    .ab-stat.is-adjusted { border-left-color: var(--bs-warning); }
    .ab-stat.is-balance { border-left-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
    .ab-amount, .num { font-variant-numeric: tabular-nums; }
    .ab-amount { font-weight: 700; }
    .ab-table th, .ab-table td { font-size: .8rem; vertical-align: middle; }
    .ab-table .num { text-align: right; white-space: nowrap; }
    .ab-badge { font-size: .72rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4>
        <i class="fa fa-wallet me-2 text-primary"></i> Advance Statement
        <small class="text-muted ms-1">{{ $customer->name }}</small>
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('chevron.cnf.advance-balances.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> All Balances
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card ab-stat is-received h-100"><div class="card-body py-3">
            <div class="ab-label">Advance Received</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($received, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card ab-stat is-adjusted h-100"><div class="card-body py-3">
            <div class="ab-label">Adjusted Against Bills</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($adjusted, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card ab-stat is-balance h-100"><div class="card-body py-3">
            <div class="ab-label">Available Balance</div>
            <div class="fs-4 ab-amount">BDT {{ number_format($balance, 2) }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">
        <i class="fa fa-list me-2"></i>Transactions
        <span class="badge bg-secondary ms-1">{{ $entries->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 ab-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:44px">#</th>
                        <th>Date</th>
                        <th>Receipt No</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th class="num">Received</th>
                        <th class="num">Adjusted</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $entry['date']?->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{ route('chevron.cnf.money-receipts.show', $entry['receipt']->id) }}" class="fw-semibold text-decoration-none">
                                    {{ $entry['receipt']->receipt_no }}
                                </a>
                            </td>
                            <td>
                                @if ($entry['kind'] === 'Received')
                                    <span class="badge ab-badge bg-success"><i class="fa fa-arrow-down me-1"></i>Advance received</span>
                                @else
                                    <span class="badge ab-badge bg-warning text-dark"><i class="fa fa-arrow-up me-1"></i>Adjusted to bill</span>
                                @endif
                            </td>
                            <td>{{ $entry['reference'] }}</td>
                            <td class="num">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                            <td class="num">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                            <td class="num fw-semibold">{{ number_format($entry['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No advance transactions for this customer in this branch.</td></tr>
                    @endforelse
                </tbody>
                @if ($entries->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th class="num">{{ number_format($received, 2) }}</th>
                            <th class="num">{{ number_format($adjusted, 2) }}</th>
                            <th class="num">{{ number_format($balance, 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

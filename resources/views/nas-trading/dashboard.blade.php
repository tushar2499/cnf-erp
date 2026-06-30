@extends('nas-trading.layouts.app')
@section('title', 'Dashboard')
@push('styles')
<style>
.stat-card { border-left:4px solid #dee2e6; border-radius:.5rem; background:#fff; overflow:hidden; }
.stat-value { font-size:1.8rem; font-weight:700; line-height:1; }
.stat-label { font-size:.78rem; color:#6c757d; margin-top:.25rem; text-transform:uppercase; letter-spacing:.04em; }
.panel { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.panel-header { background:#0c2340; color:#fff; padding:.55rem 1rem; font-size:.82rem; font-weight:600; }
.dt-sm th { background:#1a6b60; color:#fff; font-size:.76rem; padding:.4rem .55rem; }
.dt-sm td { font-size:.79rem; padding:.37rem .55rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-tachometer-alt me-2 text-warning"></i> Dashboard</h4>
    <span class="text-muted small">{{ now()->format('l, d F Y') }}</span>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#0d6efd;">
            <div class="stat-value text-primary">{{ number_format($stats['open_lcs']) }}</div>
            <div class="stat-label">Open LCs</div>
            <a href="{{ route('nas-trading.lcs.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#dc3545;">
            <div class="stat-value text-danger">{{ number_format($stats['confirmed_bills']) }}</div>
            <div class="stat-label">Unpaid Bills (Due)</div>
            <a href="{{ route('nas-trading.due-lists.customer') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#ffc107;">
            <div class="stat-value text-warning">{{ number_format($stats['pending_deliveries']) }}</div>
            <div class="stat-label">Pending Deliveries</div>
            <a href="{{ route('nas-trading.deliveries.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#198754;">
            <div class="stat-value text-success" style="font-size:1.3rem">{{ number_format($stats['total_due'], 0) }}</div>
            <div class="stat-label">Total Due (BDT)</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#6f42c1;">
            <div class="stat-value" style="color:#6f42c1">{{ number_format($stats['total_lcs']) }}</div>
            <div class="stat-label">Total LCs</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#17a2b8;">
            <div class="stat-value text-info">{{ number_format($stats['draft_bills']) }}</div>
            <div class="stat-label">Draft Bills</div>
            <a href="{{ route('nas-trading.customer-bills.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#20c997;">
            <div class="stat-value" style="color:#20c997">{{ number_format($stats['in_transit_ships']) }}</div>
            <div class="stat-label">Shipments In Transit</div>
            <a href="{{ route('nas-trading.shipments.index') }}" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3" style="border-left-color:#198754;">
            <div class="stat-value text-success" style="font-size:1.3rem">{{ number_format($stats['receipts_today'], 0) }}</div>
            <div class="stat-label">Received Today (BDT)</div>
            <a href="{{ route('nas-trading.money-receipts.index') }}" class="stretched-link"></a>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent LCs --}}
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-file-contract me-2"></i> Recent LC Entries</span>
                <a href="{{ route('nas-trading.lcs.create') }}" class="btn btn-sm btn-info text-white py-0" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>New LC</a>
            </div>
            <div style="overflow-x:auto">
                <table class="table table-hover table-striped dt-sm mb-0 w-100">
                    <thead><tr>
                        <th>LC No</th><th>Customer</th><th>PFI No</th><th>LC Date</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse($recentLcs as $lc)
                        <tr>
                            <td><a href="{{ route('nas-trading.lcs.show', $lc->id) }}" class="fw-bold text-decoration-none">{{ $lc->lc_no_system }}</a></td>
                            <td>{{ $lc->customer_name }}</td>
                            <td>{{ $lc->pfi_no }}</td>
                            <td>{{ $lc->lc_open_date?->format('d-M-Y') ?? '-' }}</td>
                            <td>
                                @php $sc = ['Open'=>'primary','Closed'=>'success','Cancelled'=>'danger','Amended'=>'warning'] @endphp
                                <span class="badge bg-{{ $sc[$lc->lc_status] ?? 'secondary' }} {{ $lc->lc_status === 'Amended' ? 'text-dark' : '' }}">{{ $lc->lc_status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No LC entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentLcs->count())
            <div class="text-center p-2">
                <a href="{{ route('nas-trading.lcs.index') }}" style="font-size:.8rem">View all LCs →</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Overdue Bills --}}
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-exclamation-circle me-2 text-warning"></i> Pending Customer Bills</span>
                <a href="{{ route('nas-trading.due-lists.customer') }}" class="btn btn-sm btn-outline-light py-0" style="font-size:.75rem">View All</a>
            </div>
            <div style="overflow-x:auto">
                <table class="table table-hover dt-sm mb-0 w-100">
                    <thead><tr><th>Bill No</th><th>Customer</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('nas-trading.customer-bills.show', $bill->id) }}" class="fw-bold text-decoration-none">{{ $bill->bill_no }}</a></td>
                            <td>{{ $bill->customer_name }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No pending bills.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

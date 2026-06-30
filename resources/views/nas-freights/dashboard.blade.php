@extends('nas-freights.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
/* ── stat cards ── */
.kpi-card { border-radius: .6rem; color: #fff; position: relative; overflow: hidden; min-height: 110px; }
.kpi-card .kpi-icon { font-size: 3rem; opacity: .15; position: absolute; right: 1rem; bottom: .5rem; }
.kpi-card .kpi-val  { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.kpi-card .kpi-lbl  { font-size: .72rem; opacity: .85; text-transform: uppercase; letter-spacing: .06em; margin-top: .25rem; }
.kpi-card .kpi-sub  { font-size: .7rem; opacity: .75; margin-top: .3rem; }

.bg-grad-teal    { background: linear-gradient(135deg, #1a6b60, #0d9488); }
.bg-grad-blue    { background: linear-gradient(135deg, #1e40af, #3b82f6); }
.bg-grad-orange  { background: linear-gradient(135deg, #b45309, #f59e0b); }
.bg-grad-red     { background: linear-gradient(135deg, #991b1b, #ef4444); }
.bg-grad-purple  { background: linear-gradient(135deg, #5b21b6, #8b5cf6); }
.bg-grad-green   { background: linear-gradient(135deg, #166534, #22c55e); }

/* ── status breakdown ── */
.status-bar { height: 6px; border-radius: 3px; }
.status-item { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; border-bottom: 1px solid #f1f3f5; font-size: .78rem; }
.status-item:last-child { border-bottom: none; }
.status-badge { font-size: .65rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; color: #fff !important; }

/* ── mini tables ── */
.dash-table th, .dash-table td { font-size: .72rem; padding: .3rem .5rem; white-space: nowrap; }
.dash-table thead th { background: #1a6b60; color: #fff; font-weight: 600; }
.dash-table_wrapper, .dash-table-wrap { overflow-x: auto; }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="page-header mb-3">
    <h4><i class="fa fa-tachometer-alt me-2 text-info"></i> NAS Freights Dashboard</h4>
    <span class="text-muted small"><i class="fa fa-calendar me-1"></i>{{ now()->format('l, d F Y') }}</span>
</div>

{{-- ── Row 1: KPI Cards ── --}}
<div class="row g-3 mb-3">
    {{-- Total Bookings --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-teal p-3 h-100">
            <div class="kpi-val">{{ number_format($stats['bookings_total']) }}</div>
            <div class="kpi-lbl">Total Bookings</div>
            <div class="kpi-sub">This month: {{ $stats['bookings_month'] }}</div>
            <i class="fa fa-clipboard-list kpi-icon"></i>
        </div>
    </div>
    {{-- Pending Jobs --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-orange p-3 h-100">
            <div class="kpi-val">{{ $stats['bookings_draft'] }}</div>
            <div class="kpi-lbl">Pending Jobs</div>
            <div class="kpi-sub">Approved: {{ $stats['bookings_approved'] }}</div>
            <i class="fa fa-hourglass-half kpi-icon"></i>
        </div>
    </div>
    {{-- Customer Due --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-red p-3 h-100">
            <div class="kpi-val" style="font-size:1.3rem">৳{{ number_format($stats['cust_due_amount'], 0) }}</div>
            <div class="kpi-lbl">Customer Due</div>
            <div class="kpi-sub">{{ $stats['cust_bills_confirmed'] }} confirmed bill(s)</div>
            <i class="fa fa-file-invoice-dollar kpi-icon"></i>
        </div>
    </div>
    {{-- Supplier Due --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-purple p-3 h-100">
            <div class="kpi-val" style="font-size:1.3rem">৳{{ number_format($stats['sup_due_amount'], 0) }}</div>
            <div class="kpi-lbl">Supplier Due</div>
            <div class="kpi-sub">{{ $stats['sup_bills_confirmed'] }} order(s) pending</div>
            <i class="fa fa-truck kpi-icon"></i>
        </div>
    </div>
    {{-- Received This Month --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-green p-3 h-100">
            <div class="kpi-val" style="font-size:1.3rem">৳{{ number_format($stats['receipts_month'], 0) }}</div>
            <div class="kpi-lbl">Received (Month)</div>
            <div class="kpi-sub">Total: ৳{{ number_format($stats['receipts_total'], 0) }}</div>
            <i class="fa fa-money-bill-wave kpi-icon"></i>
        </div>
    </div>
    {{-- Paid This Month --}}
    <div class="col-6 col-lg-2">
        <div class="card kpi-card bg-grad-blue p-3 h-100">
            <div class="kpi-val" style="font-size:1.3rem">৳{{ number_format($stats['payments_month'], 0) }}</div>
            <div class="kpi-lbl">Paid Suppliers (Month)</div>
            <div class="kpi-sub">Total: ৳{{ number_format($stats['payments_total'], 0) }}</div>
            <i class="fa fa-hand-holding-usd kpi-icon"></i>
        </div>
    </div>
</div>

{{-- ── Row 2: Status Breakdown + Stakeholders ── --}}
<div class="row g-3 mb-3">
    {{-- Booking Status --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2" style="background:#1a6b60;color:#fff;font-size:.8rem;font-weight:600">
                <i class="fa fa-clipboard-list me-2"></i> Booking Status
            </div>
            <div class="card-body py-2 px-3">
                @php
                    $bTotal = max(1, $stats['bookings_total']);
                    $bItems = [
                        ['label' => 'Approved', 'count' => $stats['bookings_approved'], 'color' => '#15803d', 'badge' => 'bg-success'],
                        ['label' => 'Draft',    'count' => $stats['bookings_draft'],    'color' => '#374151', 'badge' => 'bg-secondary'],
                        ['label' => 'Rejected', 'count' => $stats['bookings_rejected'], 'color' => '#b91c1c', 'badge' => 'bg-danger'],
                    ];
                @endphp
                @foreach($bItems as $item)
                <div class="status-item">
                    <span><span class="status-badge {{ $item['badge'] }}">{{ $item['label'] }}</span></span>
                    <span class="fw-bold" style="color:{{ $item['color'] }}">{{ $item['count'] }}</span>
                </div>
                <div class="status-bar mb-2" style="background:#e9ecef">
                    <div class="status-bar" style="width:{{ round($item['count']/$bTotal*100) }}%;background:{{ $item['color'] }}"></div>
                </div>
                @endforeach
                <div class="text-end mt-1">
                    <a href="{{ route('nas-freights.bookings.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.7rem;padding:2px 8px">View All</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Bill Status --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2" style="background:#0c2340;color:#fff;font-size:.8rem;font-weight:600">
                <i class="fa fa-file-invoice-dollar me-2"></i> Customer Bills
            </div>
            <div class="card-body py-2 px-3">
                @php
                    $cbTotal = max(1, $stats['cust_bills_draft'] + $stats['cust_bills_confirmed'] + $stats['cust_bills_paid']);
                    $cbItems = [
                        ['label' => 'Draft',     'count' => $stats['cust_bills_draft'],     'color' => '#374151', 'badge' => 'bg-secondary'],
                        ['label' => 'Confirmed', 'count' => $stats['cust_bills_confirmed'], 'color' => '#15803d', 'badge' => 'bg-success'],
                        ['label' => 'Paid',      'count' => $stats['cust_bills_paid'],      'color' => '#1d4ed8', 'badge' => 'bg-primary'],
                    ];
                @endphp
                @foreach($cbItems as $item)
                <div class="status-item">
                    <span><span class="status-badge {{ $item['badge'] }}">{{ $item['label'] }}</span></span>
                    <span class="fw-bold" style="color:{{ $item['color'] }}">{{ $item['count'] }}</span>
                </div>
                <div class="status-bar mb-2" style="background:#e9ecef">
                    <div class="status-bar" style="width:{{ round($item['count']/$cbTotal*100) }}%;background:{{ $item['color'] }}"></div>
                </div>
                @endforeach
                <div class="text-end mt-1">
                    <a href="{{ route('nas-freights.customer-bills.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.7rem;padding:2px 8px">View All</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Supplier Bill Status + Stakeholders --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-2" style="background:#5b21b6;color:#fff;font-size:.8rem;font-weight:600">
                <i class="fa fa-file-invoice me-2"></i> Payment Orders
            </div>
            <div class="card-body py-2 px-3">
                @php
                    $sbTotal = max(1, $stats['sup_bills_draft'] + $stats['sup_bills_confirmed'] + $stats['sup_bills_paid']);
                    $sbItems = [
                        ['label' => 'Draft',     'count' => $stats['sup_bills_draft'],     'color' => '#374151', 'badge' => 'bg-secondary'],
                        ['label' => 'Confirmed', 'count' => $stats['sup_bills_confirmed'], 'color' => '#15803d', 'badge' => 'bg-success'],
                        ['label' => 'Paid',      'count' => $stats['sup_bills_paid'],      'color' => '#1d4ed8', 'badge' => 'bg-primary'],
                    ];
                @endphp
                @foreach($sbItems as $item)
                <div class="status-item">
                    <span><span class="status-badge {{ $item['badge'] }}">{{ $item['label'] }}</span></span>
                    <span class="fw-bold" style="color:{{ $item['color'] }}">{{ $item['count'] }}</span>
                </div>
                <div class="status-bar mb-2" style="background:#e9ecef">
                    <div class="status-bar" style="width:{{ round($item['count']/$sbTotal*100) }}%;background:{{ $item['color'] }}"></div>
                </div>
                @endforeach
                <div class="d-flex justify-content-between align-items-center mt-2 pt-1" style="border-top:1px solid #f1f3f5">
                    <span style="font-size:.72rem;color:#6b7280"><i class="fa fa-users me-1"></i>Customers: <strong>{{ $stats['total_customers'] }}</strong></span>
                    <span style="font-size:.72rem;color:#6b7280"><i class="fa fa-truck-loading me-1"></i>Suppliers: <strong>{{ $stats['total_suppliers'] }}</strong></span>
                    <a href="{{ route('nas-freights.supplier-bills.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.7rem;padding:2px 8px">View All</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 3: Recent Bookings ── --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#1a6b60;color:#fff;font-size:.82rem;font-weight:600">
                <span><i class="fa fa-clipboard-list me-2"></i> Recent Bookings</span>
                <a href="{{ route('nas-freights.bookings.create') }}" class="btn btn-sm btn-light text-success" style="font-size:.72rem;padding:2px 10px">
                    <i class="fa fa-plus me-1"></i> New Booking
                </a>
            </div>
            <div class="card-body p-0 dash-table-wrap">
                <table class="table table-hover table-striped table-bordered mb-0 dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Job No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Goods</th>
                            <th>Cover Van</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Billed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $i => $b)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a href="{{ route('nas-freights.bookings.index') }}" class="text-decoration-none fw-bold">{{ $b->job_no }}</a></td>
                            <td>{{ $b->job_date?->format('d-M-Y') }}</td>
                            <td>{{ $b->customer_name }}</td>
                            <td>{{ $b->goods_name }}</td>
                            <td>{{ $b->cover_van_no }}</td>
                            <td>{{ session('nas_freights_branch_name') }}</td>
                            <td>
                                @if($b->status === 'Approved') <span class="badge bg-success" style="font-size:.62rem">APPROVED</span>
                                @elseif($b->status === 'Rejected') <span class="badge bg-danger" style="font-size:.62rem">REJECTED</span>
                                @else <span class="badge bg-secondary" style="font-size:.62rem">DRAFT</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($billedBookingIds[$b->id]))
                                    <span class="badge bg-success" style="font-size:.62rem">BILLED</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size:.62rem">NOT BILLED</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-3"><i class="fa fa-inbox me-1"></i> No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 4: Customer Due + Supplier Due ── --}}
<div class="row g-3">
    {{-- Customer Due --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#991b1b;color:#fff;font-size:.82rem;font-weight:600">
                <span><i class="fa fa-user-clock me-2"></i> Customer Due Bills</span>
                <a href="{{ route('nas-freights.due-lists.customer') }}" class="btn btn-sm btn-light text-danger" style="font-size:.72rem;padding:2px 10px">View All</a>
            </div>
            <div class="card-body p-0 dash-table-wrap">
                <table class="table table-hover table-striped table-bordered mb-0 dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bill No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Days</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerDueBills as $i => $bill)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-bold">{{ $bill->bill_no }}</td>
                            <td>{{ $bill->bill_date?->format('d-M-Y') }}</td>
                            <td>{{ $bill->customer_name }}</td>
                            <td class="text-end fw-bold">৳{{ number_format($bill->total_amount, 2) }}</td>
                            <td class="text-center">
                                @php $days = now()->startOfDay()->diffInDays($bill->bill_date->copy()->startOfDay()); @endphp
                                @if($days > 0) <span class="badge bg-danger" style="font-size:.62rem">{{ $days }}d</span>
                                @else <span class="badge bg-secondary" style="font-size:.62rem">Today</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('nas-freights.money-receipts.create') }}?bill_id={{ $bill->id }}" class="btn btn-sm btn-outline-success" style="padding:1px 5px;font-size:.65rem" title="Receive"><i class="fa fa-money-bill-wave"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3"><i class="fa fa-check-circle me-1 text-success"></i> No outstanding customer bills.</td></tr>
                        @endforelse
                    </tbody>
                    @if($customerDueBills->count())
                    <tfoot>
                        <tr style="background:#fef2f2;font-weight:700;font-size:.75rem">
                            <td colspan="4" class="text-end">Total:</td>
                            <td class="text-end text-danger">৳{{ number_format($customerDueBills->sum('total_amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Supplier Due --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#5b21b6;color:#fff;font-size:.82rem;font-weight:600">
                <span><i class="fa fa-truck-loading me-2"></i> Supplier Due Orders</span>
                <a href="{{ route('nas-freights.due-lists.supplier') }}" class="btn btn-sm btn-light text-purple" style="font-size:.72rem;padding:2px 10px;color:#5b21b6">View All</a>
            </div>
            <div class="card-body p-0 dash-table-wrap">
                <table class="table table-hover table-striped table-bordered mb-0 dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Days</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplierDueBills as $i => $bill)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-bold">{{ $bill->pay_order_no }}</td>
                            <td>{{ $bill->bill_date?->format('d-M-Y') }}</td>
                            <td>{{ $bill->supplier_name }}</td>
                            <td class="text-end fw-bold">৳{{ number_format($bill->total_amount, 2) }}</td>
                            <td class="text-center">
                                @php $days = now()->startOfDay()->diffInDays($bill->bill_date->copy()->startOfDay()); @endphp
                                @if($days > 0) <span class="badge bg-danger" style="font-size:.62rem">{{ $days }}d</span>
                                @else <span class="badge bg-secondary" style="font-size:.62rem">Today</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('nas-freights.supplier-payments.create') }}?bill_id={{ $bill->id }}" class="btn btn-sm btn-outline-primary" style="padding:1px 5px;font-size:.65rem" title="Pay"><i class="fa fa-money-check"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3"><i class="fa fa-check-circle me-1 text-success"></i> No outstanding payment orders.</td></tr>
                        @endforelse
                    </tbody>
                    @if($supplierDueBills->count())
                    <tfoot>
                        <tr style="background:#f5f3ff;font-weight:700;font-size:.75rem">
                            <td colspan="4" class="text-end">Total:</td>
                            <td class="text-end" style="color:#5b21b6">৳{{ number_format($supplierDueBills->sum('total_amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

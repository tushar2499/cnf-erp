@extends('chevron.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
/* ── Stat Cards ─────────────────────────────────── */
.kpi-card {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    transition: transform .18s, box-shadow .18s;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.13); }
.kpi-card .kpi-body { padding: 1.15rem 1.25rem 1rem; }
.kpi-card .kpi-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
}
.kpi-card .kpi-value { font-size: 1.85rem; font-weight: 800; line-height: 1.1; }
.kpi-card .kpi-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6b7a99; margin-top: .18rem; }
.kpi-card .kpi-sub   { font-size: .72rem; color: #6b7a99; margin-top: .45rem; }
.kpi-card .kpi-sub .badge-up   { background: #d1fae5; color: #065f46; border-radius: 6px; padding: 1px 7px; font-weight: 600; }
.kpi-card .kpi-sub .badge-neutral { background: #e0e7ff; color: #3730a3; border-radius: 6px; padding: 1px 7px; font-weight: 600; }
.kpi-card .kpi-stripe { height: 4px; }

/* ── Chart Cards ────────────────────────────────── */
.chart-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.chart-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: .85rem; padding: .9rem 1.25rem; }

/* ── Recent Table ───────────────────────────────── */
.recent-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.recent-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: .85rem; padding: .9rem 1.25rem; }
.recent-card .table td, .recent-card .table th { font-size: .78rem; vertical-align: middle; }
.recent-card .table th { background: #f8f9fb; font-weight: 700; color: #6b7a99; text-transform: uppercase; letter-spacing: .05em; }

/* ── Mini progress bars (job status) ───────────── */
.status-pill { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; }

/* ── Top customers ──────────────────────────────── */
.cust-bar-wrap { background: #f1f5f9; border-radius: 6px; height: 6px; margin-top: 3px; }
.cust-bar-fill { height: 6px; border-radius: 6px; background: linear-gradient(90deg,#6366f1,#818cf8); }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fa fa-tachometer-alt me-2 text-primary"></i> Dashboard</h4>
        <span class="text-muted small">{{ now()->format('l, d F Y') }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('chevron.cnf.jobs.create') }}"    class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i> New Job</a>
        <a href="{{ route('chevron.cnf.bills.create') }}"   class="btn btn-sm btn-outline-primary"><i class="fa fa-file-invoice me-1"></i> New Bill</a>
        <a href="{{ route('chevron.cnf.money-receipts.create') }}" class="btn btn-sm btn-outline-success"><i class="fa fa-money-bill-wave me-1"></i> New Receipt</a>
    </div>
</div>

{{-- KPI Cards Row 1 --}}
<div class="row g-3 mb-3">

    {{-- Total Jobs --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card h-100">
            <div class="kpi-stripe bg-primary"></div>
            <div class="kpi-body d-flex align-items-start gap-3">
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary"><i class="fa fa-file-alt"></i></div>
                <div>
                    <div class="kpi-value text-primary">{{ number_format($totalJobs) }}</div>
                    <div class="kpi-label">Total C&amp;F Jobs</div>
                    <div class="kpi-sub"><span class="badge-up">+{{ $jobsThisMonth }} this month</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Jobs --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card h-100">
            <div class="kpi-stripe bg-success"></div>
            <div class="kpi-body d-flex align-items-start gap-3">
                <div class="kpi-icon bg-success bg-opacity-10 text-success"><i class="fa fa-check-circle"></i></div>
                <div>
                    <div class="kpi-value text-success">{{ number_format($activeJobs) }}</div>
                    <div class="kpi-label">Active Jobs</div>
                    <div class="kpi-sub">
                        <span class="badge-neutral">{{ $pendingJobs }} pending</span>&nbsp;
                        <span class="text-muted">{{ $closedJobs }} closed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Receivable --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card h-100">
            <div class="kpi-stripe" style="background:#f59e0b"></div>
            <div class="kpi-body d-flex align-items-start gap-3">
                <div class="kpi-icon text-warning" style="background:rgba(245,158,11,.12)"><i class="fa fa-hand-holding-usd"></i></div>
                <div>
                    <div class="kpi-value" style="color:#d97706">৳{{ number_format($totalReceivable, 0) }}</div>
                    <div class="kpi-label">Total Receivable</div>
                    <div class="kpi-sub text-muted">from {{ number_format($totalBills) }} bill{{ $totalBills == 1 ? '' : 's' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Receipts --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card h-100">
            <div class="kpi-stripe" style="background:#0ea5e9"></div>
            <div class="kpi-body d-flex align-items-start gap-3">
                <div class="kpi-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="fa fa-money-bill-wave"></i></div>
                <div>
                    <div class="kpi-value" style="color:#0ea5e9">৳{{ number_format($totalReceipts, 0) }}</div>
                    <div class="kpi-label">Total Receipts</div>
                    <div class="kpi-sub"><span class="badge-up">৳{{ number_format($receiptsThisMonth, 0) }} this month</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards Row 2 (smaller) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100" style="border:none;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07)">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="kpi-icon" style="width:40px;height:40px;font-size:1rem;background:rgba(139,92,246,.12);color:#7c3aed;border-radius:10px;display:flex;align-items:center;justify-content:center"><i class="fa fa-users"></i></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:#7c3aed;line-height:1">{{ number_format($totalCustomers) }}</div>
                    <div class="kpi-label">Customers</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100" style="border:none;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07)">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="kpi-icon" style="width:40px;height:40px;font-size:1rem;background:rgba(20,184,166,.12);color:#0d9488;border-radius:10px;display:flex;align-items:center;justify-content:center"><i class="fa fa-user-tie"></i></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:#0d9488;line-height:1">{{ number_format($totalEmployees) }}</div>
                    <div class="kpi-label">Employees</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100" style="border:none;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07)">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="kpi-icon" style="width:40px;height:40px;font-size:1rem;background:rgba(239,68,68,.12);color:#dc2626;border-radius:10px;display:flex;align-items:center;justify-content:center"><i class="fa fa-file-invoice"></i></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:#dc2626;line-height:1">৳{{ number_format($totalNetPayable, 0) }}</div>
                    <div class="kpi-label">Bills Net Payable</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100" style="border:none;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07)">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="kpi-icon" style="width:40px;height:40px;font-size:1rem;background:rgba(245,158,11,.12);color:#b45309;border-radius:10px;display:flex;align-items:center;justify-content:center"><i class="fa fa-receipt"></i></div>
                <div>
                    <div style="font-size:1.4rem;font-weight:800;color:#b45309;line-height:1">৳{{ number_format($approvedExpenses, 0) }}</div>
                    <div class="kpi-label">Approved Expenses</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">

    {{-- Monthly Jobs + Bills Chart --}}
    <div class="col-lg-8">
        <div class="card chart-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fa fa-chart-bar me-2 text-primary"></i> Monthly Overview (Last 6 Months)</span>
                <div class="d-flex gap-2">
                    <span class="d-flex align-items-center gap-1" style="font-size:.72rem;color:#6b7a99"><span style="display:inline-block;width:12px;height:3px;background:#6366f1;border-radius:2px"></span> Jobs</span>
                    <span class="d-flex align-items-center gap-1" style="font-size:.72rem;color:#6b7a99"><span style="display:inline-block;width:12px;height:3px;background:#22c55e;border-radius:2px"></span> Bill Net (BDT)</span>
                </div>
            </div>
            <div class="card-body" style="position:relative;height:260px">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bill Status Donut --}}
    <div class="col-lg-4">
        <div class="card chart-card h-100">
            <div class="card-header"><i class="fa fa-chart-pie me-2 text-warning"></i> Bill Status Breakdown</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="position:relative;min-height:260px">
                @if($totalBills > 0)
                <div style="height:190px;width:190px;position:relative">
                    <canvas id="billStatusChart"></canvas>
                </div>
                <div class="d-flex gap-3 mt-3 flex-wrap justify-content-center">
                    @php
                        $statusColors = ['Active' => '#6366f1', 'Submitted' => '#f59e0b', 'Approved' => '#22c55e'];
                    @endphp
                    @foreach($billStatusCounts as $status => $cnt)
                    <span style="font-size:.75rem;color:#374151">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $statusColors[$status] ?? '#94a3b8' }};margin-right:4px"></span>
                        {{ $status }} <strong>{{ $cnt }}</strong>
                    </span>
                    @endforeach
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="fa fa-chart-pie fa-3x mb-2 d-block opacity-25"></i>
                    No bill data yet
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Bottom Row: Recent Jobs + Top Customers --}}
<div class="row g-3 mb-4">

    {{-- Recent Jobs --}}
    <div class="col-lg-8">
        <div class="card recent-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fa fa-file-alt me-2 text-primary"></i> Recent C&amp;F Jobs</span>
                <a href="{{ route('chevron.cnf.jobs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Job No</th>
                            <th>Party</th>
                            <th>Port</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentJobs as $job)
                        <tr>
                            <td>
                                <a href="{{ route('chevron.cnf.jobs.edit', $job->id) }}" class="fw-semibold text-decoration-none">
                                    {{ $job->job_no }}
                                </a>
                            </td>
                            <td class="text-truncate" style="max-width:160px" title="{{ $job->party_name }}">{{ $job->party_name ?? '—' }}</td>
                            <td>{{ $job->port?->name ?? '—' }}</td>
                            <td>{{ $job->job_date?->format('d M y') ?? '—' }}</td>
                            <td>
                                @if($job->status === 'Active')
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                @elseif($job->status === 'Pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $job->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fa fa-inbox me-2"></i>No jobs yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Customers + Recent Bills --}}
    <div class="col-lg-4 d-flex flex-column gap-3">

        {{-- Top Customers --}}
        <div class="card recent-card flex-fill">
            <div class="card-header"><i class="fa fa-trophy me-2 text-warning"></i> Top Customers by Jobs</div>
            <div class="card-body py-2">
                @php $maxJobs = $topCustomers->max('job_count') ?: 1; @endphp
                @forelse($topCustomers as $c)
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center" style="font-size:.78rem">
                        <span class="fw-semibold text-truncate" style="max-width:170px" title="{{ $c->party_name }}">{{ $c->party_name ?? 'Unknown' }}</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $c->job_count }} job{{ $c->job_count > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="cust-bar-wrap">
                        <div class="cust-bar-fill" style="width:{{ round($c->job_count / $maxJobs * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3" style="font-size:.8rem"><i class="fa fa-users me-2"></i>No data yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Bills --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card recent-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fa fa-file-invoice me-2 text-danger"></i> Recent Bills</span>
                <a href="{{ route('chevron.cnf.bills.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Bill No</th>
                            <th>Party</th>
                            <th>Date</th>
                            <th>Net Payable</th>
                            <th>Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                        <tr>
                            <td><a href="{{ route('chevron.cnf.bills.edit', $bill->id) }}" class="fw-semibold text-decoration-none">{{ $bill->bill_no }}</a></td>
                            <td class="text-truncate" style="max-width:180px" title="{{ $bill->party_name }}">{{ $bill->party_name }}</td>
                            <td>{{ $bill->bill_date?->format('d M y') ?? '—' }}</td>
                            <td class="text-end">৳{{ number_format($bill->net_payable, 2) }}</td>
                            <td class="text-end {{ $bill->due_amount > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                ৳{{ number_format($bill->due_amount, 2) }}
                            </td>
                            <td>
                                @if($bill->status === 'Approved')
                                    <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                                @elseif($bill->status === 'Submitted')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Submitted</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-inbox me-2"></i>No bills yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6b7a99';

// ── Monthly Overview Chart ────────────────────────────
(function () {
    var labels   = @json($monthlyLabels);
    var jobData  = @json($monthlyJobData);
    var billData = @json($monthlyBillData);

    new Chart(document.getElementById('monthlyChart'), {
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Jobs',
                    data: jobData,
                    backgroundColor: 'rgba(99,102,241,.18)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'yLeft',
                },
                {
                    type: 'line',
                    label: 'Bill Net (BDT)',
                    data: billData,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#22c55e',
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yRight',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { cornerRadius: 8 } },
            scales: {
                yLeft: {
                    type: 'linear', position: 'left',
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: 'rgba(0,0,0,.05)' },
                },
                yRight: {
                    type: 'linear', position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: v => '৳' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
                    },
                },
                x: { grid: { display: false } },
            },
        },
    });
})();

// ── Bill Status Donut ─────────────────────────────────
@if($totalBills > 0)
(function () {
    var data   = @json($billStatusCounts->values());
    var labels = @json($billStatusCounts->keys());
    var colors = { Active: '#6366f1', Submitted: '#f59e0b', Approved: '#22c55e' };
    var bgColors = labels.map(l => colors[l] || '#94a3b8');

    new Chart(document.getElementById('billStatusChart'), {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: data, backgroundColor: bgColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    cornerRadius: 8,
                    callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed }
                },
            },
        },
    });
})();
@endif
</script>
@endpush

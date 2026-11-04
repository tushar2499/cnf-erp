@extends('chevron.layouts.app')

@section('title', 'Job Expense Vs Bill')

@push('styles')
<style>
.filter-card { background:#f8f9fa; border:1px solid #dee2e6; border-radius:.5rem; padding:16px 20px 8px; margin-bottom:18px; }
.table-scroll { max-height:calc(100vh - 300px); overflow:auto; border:1px solid #c5d5e8; border-radius:4px; }
table.vs-table { width:100%; border-collapse:collapse; font-size:12px; margin-bottom:0; }
table.vs-table thead th { background:#e8f0fb; color:#1a4a6b; padding:6px 8px; border:1px solid #c5d5e8; white-space:nowrap; position:sticky; top:0; z-index:2; }
.head-total { display:block; margin-top:3px; padding-top:3px; border-top:1px dashed #9bb8d6; font-size:11px; font-weight:700; color:#12405f; }
table.vs-table tbody td { padding:5px 8px; border:1px solid #dde6f0; vertical-align:middle; }
table.vs-table tbody tr:nth-child(even) { background:#f4f8ff; }
table.vs-table tfoot td { background:#d4e8ff; font-weight:700; padding:6px 8px; border:1px solid #b0ccee; }
table.vs-table tfoot td.r { text-align:right; }
.summary-box { background:#fff; border:1px solid #b0ccee; border-radius:6px; padding:14px 18px; margin-top:16px; }
.summary-box table { width:100%; font-size:13px; }
.summary-box td { padding:4px 8px; }
.summary-box .lbl { color:#555; width:55%; }
.summary-box .val { font-weight:700; text-align:right; }
.badge-yes { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; padding:1px 7px; border-radius:20px; font-size:11px; }
.badge-no  { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; padding:1px 7px; border-radius:20px; font-size:11px; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold"><i class="fa fa-scale-balanced me-2" style="color:#1a4a6b"></i>Job Expense Vs Bill</h5>
</div>

{{-- Filter Form --}}
<form method="GET" action="{{ route('chevron.reports.job-expense-vs-bill') }}" id="filterForm">
<div class="filter-card">
    <div class="row g-2">
        <div class="col-md-2">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">From Date</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">To Date</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Job No</label>
            <select name="job_no" id="jobSelect" class="form-select form-select-sm">
                @if(request('job_no'))
                    <option value="{{ request('job_no') }}" selected>{{ request('job_no') }}</option>
                @endif
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2 pb-1">
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa fa-search me-1"></i>Search</button>
            <a href="{{ route('chevron.reports.job-expense-vs-bill') }}" class="btn btn-outline-secondary btn-sm px-2">Clear</a>
        </div>
    </div>
</div>
</form>

@if($rows->isNotEmpty())
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('chevron.reports.job-expense-vs-bill.print') }}?{{ http_build_query(request()->except('_token')) }}"
       target="_blank" class="btn btn-outline-dark btn-sm px-3">
        <i class="fa fa-print me-1"></i>Print
    </a>
</div>

<div class="table-scroll">
    <table class="vs-table">
        <thead>
            <tr>
                <th style="width:4%">SL</th>
                <th>Job No</th>
                <th>Party Name</th>
                <th style="width:10%">Expense Date</th>
                <th style="width:10%">Expense No</th>
                <th>Expense By</th>
                <th>Expense Category</th>
                <th>Expense Head</th>
                <th style="width:9%">Receiptable</th>
                <th style="width:11%;text-align:right">Amount<span class="head-total">Total {{ number_format($totalExpense, 2) }}</span></th>
                <th style="width:11%;text-align:right">Bill Amount<span class="head-total">Total {{ number_format($totalBill, 2) }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            @php
                $jobId = $row->jobExpense?->job_id;
                $billAmount = $jobId ? ($billTotals[$jobId] ?? 0) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->jobExpense?->job_no ?? '—' }}</td>
                <td>{{ $row->jobExpense?->job?->party_name ?? '—' }}</td>
                <td class="text-center">{{ ($row->expense_date ?? $row->jobExpense?->date)?->format('d M Y') ?? '—' }}</td>
                <td>{{ $row->jobExpense?->expense_no ?? '—' }}</td>
                <td>{{ $row->jobExpense?->employee?->name ?? '—' }}</td>
                <td>{{ $row->expenseHead?->expenseCategory?->name ?? '—' }}</td>
                <td>{{ $row->expenseHead?->name ?? '—' }}</td>
                <td class="text-center">
                    @if($row->receiptable === 'Yes')
                        <span class="badge-yes">Yes</span>
                    @else
                        <span class="badge-no">No</span>
                    @endif
                </td>
                <td class="text-end">{{ number_format($row->expense_amount, 2) }}</td>
                <td class="text-end">{{ number_format($billAmount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="text-end">Total</td>
                <td class="r">{{ number_format($totalExpense, 2) }}</td>
                <td class="r">{{ number_format($totalBill, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Grand Summary --}}
<div class="summary-box">
    <div class="row">
        <div class="col-md-6">
            <table>
                <tr>
                    <td class="lbl">Total Job Expense Amount</td>
                    <td class="val">{{ number_format($totalExpense, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Total Bill Amount</td>
                    <td class="val">{{ number_format($totalBill, 2) }}</td>
                </tr>
                <tr style="border-top:2px solid #1a4a6b">
                    <td class="lbl fw-bold" style="color:#1a4a6b">Difference (Expense - Bill)</td>
                    <td class="val" style="color:#1a4a6b;font-size:15px">{{ number_format($totalExpense - $totalBill, 2) }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table>
                <tr>
                    <td class="lbl">Total Expense Rows</td>
                    <td class="val fw-bold">{{ $rows->count() }}</td>
                </tr>
                <tr>
                    <td class="lbl">Jobs Covered</td>
                    <td class="val fw-bold">{{ $rows->pluck('jobExpense.job_id')->filter()->unique()->count() }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@elseif(request()->hasAny(['from_date', 'to_date', 'job_no']))
<div class="alert alert-info">No expense records found for the selected filters.</div>
@else
<div class="text-center text-muted py-5">
    <i class="fa fa-filter fa-2x mb-3 d-block"></i>
    Select filters above and click Search.
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    $('#jobSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search Job No...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('chevron.reports.job-expense-vs-bill.search-jobs') }}',
            dataType: 'json',
            delay: 250,
            data: p => ({ q: p.term }),
            processResults: d => ({ results: d }),
        },
    });
})();
</script>
@endpush

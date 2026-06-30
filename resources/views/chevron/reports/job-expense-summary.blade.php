@extends('chevron.layouts.app')

@section('title', 'Job Expense Summary')

@push('styles')
<style>
.filter-card { background:#f8f9fa; border:1px solid #dee2e6; border-radius:.5rem; padding:16px 20px 8px; margin-bottom:18px; }
.expense-group { margin-bottom:18px; }
.group-header { background:#1a4a6b; color:#fff; padding:7px 12px; border-radius:4px 4px 0 0; font-size:13px; font-weight:600; }
.group-header span { font-weight:400; font-size:12px; margin-left:6px; color:#b8d4f0; }
table.exp-table { width:100%; border-collapse:collapse; font-size:12px; margin-bottom:0; }
table.exp-table thead th { background:#e8f0fb; color:#1a4a6b; padding:6px 8px; border:1px solid #c5d5e8; white-space:nowrap; }
table.exp-table tbody td { padding:5px 8px; border:1px solid #dde6f0; vertical-align:middle; }
table.exp-table tbody tr:nth-child(even) { background:#f4f8ff; }
table.exp-table tfoot td { background:#d4e8ff; font-weight:700; padding:6px 8px; border:1px solid #b0ccee; }
table.exp-table tfoot td.r { text-align:right; }
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
    <h5 class="mb-0 fw-bold"><i class="fa fa-chart-line me-2" style="color:#1a4a6b"></i>Job Expense Summary</h5>
</div>

{{-- Filter Form --}}
<form method="GET" action="{{ route('chevron.reports.job-expense-summary') }}" id="filterForm">
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
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Job No</label>
            <input type="text" name="job_no" class="form-control form-control-sm" placeholder="Job No..." value="{{ request('job_no') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Employee (Created By)</label>
            <select name="employee_id" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ $emp->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2 pb-1">
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa fa-search me-1"></i>Search</button>
            <a href="{{ route('chevron.reports.job-expense-summary') }}" class="btn btn-outline-secondary btn-sm px-2">Clear</a>
        </div>
    </div>
</div>
</form>

@if($expenses->isNotEmpty())
@php
    $grandReceiptable    = 0;
    $grandNonReceiptable = 0;
    $grandTotal          = 0;
    $countYes            = 0;
    $countNo             = 0;
@endphp

<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('chevron.reports.job-expense-summary.print') }}?{{ http_build_query(request()->except('_token')) }}"
       target="_blank" class="btn btn-outline-dark btn-sm px-3">
        <i class="fa fa-print me-1"></i>Print
    </a>
</div>

@foreach($expenses as $expense)
@php
    $items     = $expense->items;
    $subTotal  = $items->sum('expense_amount');
    $subReceiptable    = $items->where('receiptable', true)->sum('expense_amount');
    $subNonReceiptable = $items->where('receiptable', false)->sum('expense_amount');
    $grandReceiptable    += $subReceiptable;
    $grandNonReceiptable += $subNonReceiptable;
    $grandTotal          += $subTotal;
    $countYes += $items->where('receiptable', true)->count();
    $countNo  += $items->where('receiptable', false)->count();
    $empName  = $expense->employee?->name ?? '—';
@endphp
<div class="expense-group">
    <div class="group-header">
        Expense No: {{ $expense->expense_no }}
        <span>| Job No: {{ $expense->job_no }}</span>
        <span>| Date: {{ $expense->date?->format('d M Y') }}</span>
        <span>| Employee: {{ $empName }}</span>
        @if($expense->be_no) <span>| BE No: {{ $expense->be_no }}</span> @endif
    </div>
    <table class="exp-table">
        <thead>
            <tr>
                <th style="width:4%">SL</th>
                <th>Expense Head</th>
                <th style="width:11%">Expense Date</th>
                <th style="width:10%">Receiptable</th>
                <th style="width:12%">Approved By</th>
                <th>Remarks</th>
                <th style="width:12%;text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $si => $item)
            <tr>
                <td class="text-center">{{ $si + 1 }}</td>
                <td>{{ $item->expenseHead?->name ?? '—' }}</td>
                <td class="text-center">{{ $item->expense_date?->format('d M Y') }}</td>
                <td class="text-center">
                    @if($item->receiptable)
                        <span class="badge-yes">Yes</span>
                    @else
                        <span class="badge-no">No</span>
                    @endif
                </td>
                <td>—</td>
                <td>{{ $item->note ?? '' }}</td>
                <td class="text-end">{{ number_format($item->expense_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">No items</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end">Sub Total</td>
                <td class="r">{{ number_format($subTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endforeach

{{-- Grand Summary --}}
<div class="summary-box">
    <div class="row">
        <div class="col-md-6">
            <table>
                <tr>
                    <td class="lbl">Total Receiptable Amount</td>
                    <td class="val">{{ number_format($grandReceiptable, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Total Non-Receiptable Amount</td>
                    <td class="val">{{ number_format($grandNonReceiptable, 2) }}</td>
                </tr>
                <tr style="border-top:2px solid #1a4a6b">
                    <td class="lbl fw-bold" style="color:#1a4a6b">Total Cost Amount</td>
                    <td class="val" style="color:#1a4a6b;font-size:15px">{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-3">
            <table>
                <tr>
                    <td class="lbl">Receiptable (Yes)</td>
                    <td class="val"><span class="badge-yes">{{ $countYes }}</span></td>
                </tr>
                <tr>
                    <td class="lbl">Non-Receiptable (No)</td>
                    <td class="val"><span class="badge-no">{{ $countNo }}</span></td>
                </tr>
                <tr>
                    <td class="lbl">Total Expense Vouchers</td>
                    <td class="val fw-bold">{{ $expenses->count() }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@elseif(request()->hasAny(['from_date', 'to_date', 'job_no', 'employee_id']))
<div class="alert alert-info">No expense records found for the selected filters.</div>
@else
<div class="text-center text-muted py-5">
    <i class="fa fa-filter fa-2x mb-3 d-block"></i>
    Select filters above and click Search.
</div>
@endif
@endsection

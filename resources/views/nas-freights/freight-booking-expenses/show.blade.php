@extends('nas-freights.layouts.app')
@section('title', 'Expense — '.$freightBookingExpense->expense_no)

@push('styles')
    <style>
        .exp-topbar { background: linear-gradient(135deg, #0d2626 0%, #0d6e6e 60%, #14b8a6 100%); color: #fff; padding: .55rem 1rem; display: flex; align-items: center; justify-content: space-between; margin: -1.5rem -1.5rem 1.25rem; }
        .exp-topbar .title { font-size: 1rem; font-weight: 700; letter-spacing: .02em; }
        .info-label { font-size: .78rem; color: #6c757d; font-weight: 500; }
        .info-value { font-size: .9rem; font-weight: 600; }
        #itemsDetailTable th, #itemsDetailTable td { font-size: .8rem; padding: .35rem .5rem; }
        #itemsDetailTable thead th { background: #1e293b; color: #e2e8f0; }
    </style>
@endpush

@section('content')
<div class="exp-topbar">
    <div class="d-flex gap-2">
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-sm btn-light text-dark">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
    <div class="title">
        Expense — {{ $freightBookingExpense->expense_no }}
    </div>
    <div>
        <a href="{{ route($routePrefix.'.edit', $freightBookingExpense->id) }}" class="btn btn-sm btn-light text-dark">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header fw-600"><i class="fa fa-info-circle me-2"></i> Expense Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="info-label">Expense No</div>
                        <div class="info-value">{{ $freightBookingExpense->expense_no }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Date</div>
                        <div class="info-value">{{ $freightBookingExpense->date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking No</div>
                        <div class="info-value">{{ $freightBookingExpense->booking_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Booking Type</div>
                        <div class="info-value">{{ ucfirst($freightBookingExpense->booking_type ?? '—') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Employee</div>
                        <div class="info-value">{{ $freightBookingExpense->employee?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Invoice No</div>
                        <div class="info-value">{{ $freightBookingExpense->invoice_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Invoice Value (USD)</div>
                        <div class="info-value">{{ $freightBookingExpense->invoice_value_usd ? number_format($freightBookingExpense->invoice_value_usd, 2) : '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">B/L No</div>
                        <div class="info-value">{{ $freightBookingExpense->bl_no ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Branch</div>
                        <div class="info-value">{{ $freightBookingExpense->branch?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-label">Status</div>
                        @php
                            $statusClass = match($freightBookingExpense->status) {
                                'Approved'  => 'bg-success',
                                'Submitted' => 'bg-warning text-dark',
                                default     => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $freightBookingExpense->status }}</span>
                    </div>
                    @if($freightBookingExpense->remarks)
                    <div class="col-12">
                        <div class="info-label">Remarks</div>
                        <div class="info-value">{{ $freightBookingExpense->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-600"><i class="fa fa-calculator me-2"></i> Totals</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="info-label">Total Expense Amount</div>
                        <div class="fs-5 fw-700 text-info">{{ number_format($freightBookingExpense->total_expense_amount, 2) }}</div>
                    </div>
                    <i class="fa fa-money-bill-wave fa-2x text-info opacity-50"></i>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="info-label">Total Approved Amount</div>
                        <div class="fs-5 fw-700 text-success">{{ number_format($freightBookingExpense->total_approved_amount, 2) }}</div>
                    </div>
                    <i class="fa fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
                <div class="text-muted small mt-3">{{ $freightBookingExpense->items->count() }} item(s)</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-600"><i class="fa fa-list me-2"></i> Expense Items</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="itemsDetailTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Expense Head</th>
                        <th>Category</th>
                        <th>Receiptable</th>
                        <th>Expense Date</th>
                        <th class="text-end">Expense Amt</th>
                        <th class="text-end">Approved Amt</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freightBookingExpense->items as $i => $item)
                    <tr>
                        <td class="text-center text-muted small">{{ $i + 1 }}</td>
                        <td>{{ $item->expenseHead?->name ?? '—' }}</td>
                        <td>{{ $item->expenseHead?->expenseCategory?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $item->receiptable === 'Yes' ? 'bg-success' : 'bg-secondary' }}">{{ $item->receiptable }}</span>
                        </td>
                        <td>{{ $item->expense_date ? $item->expense_date->format('d M Y') : '—' }}</td>
                        <td class="text-end">{{ number_format($item->expense_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($item->approved_amount, 2) }}</td>
                        <td>{{ $item->note ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-600">Total</td>
                        <td class="text-end fw-600">{{ number_format($freightBookingExpense->total_expense_amount, 2) }}</td>
                        <td class="text-end fw-600">{{ number_format($freightBookingExpense->total_approved_amount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

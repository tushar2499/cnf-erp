{{-- Partial: read-only job expense for the bill form. Rendered inside #bookingExpenseWrap and swapped by JS when the booking/job changes. --}}
@if(auth()->user()->hasPermission('freight.export-booking.expense-manage'))
@php
    $jobExpense      = $booking->expense;
    $jobExpenseItems = $jobExpense?->items ?? collect();
@endphp
<div class="panel booking-expense-panel">
    <div class="panel-header">
        <button type="button" class="expense-toggle" data-bs-toggle="collapse" data-bs-target="#bookingExpenseCollapse"
                aria-expanded="true" aria-controls="bookingExpenseCollapse">
            <i class="fa fa-receipt"></i> Job Expense
            <i class="fa fa-chevron-down expense-chevron" aria-hidden="true"></i>
        </button>
    </div>
    <div class="collapse show" id="bookingExpenseCollapse">
        @if($jobExpenseItems->isEmpty())
            <div class="panel-body text-center text-muted" style="font-size:.8rem;">
                <i class="fa fa-receipt me-1"></i> No expense added yet for this booking/job.
            </div>
        @else
            <table class="table table-bordered table-sm mb-0 expense-table">
                <thead>
                    <tr>
                        <th style="width:32px">#</th>
                        <th>Expense Head</th>
                        <th class="text-end">Expense</th>
                        <th class="text-end">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobExpenseItems as $i => $expenseItem)
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td>
                            <span class="fw-semibold">{{ $expenseItem->expenseHead?->name ?? '—' }}</span>
                            @if($expenseItem->receiptable === 'Yes')
                                <span class="badge bg-success bg-opacity-75 ms-1">Receipt</span>
                            @endif
                            @if($expenseItem->expense_date || $expenseItem->note)
                                <div class="expense-meta">
                                    {{ $expenseItem->expense_date?->format('d M Y') }}{{ $expenseItem->expense_date && $expenseItem->note ? ' · ' : '' }}{{ $expenseItem->note }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($expenseItem->expense_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($expenseItem->approved_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="expense-total-row">
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($jobExpense->total_expense_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($jobExpense->total_approved_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>
@endif

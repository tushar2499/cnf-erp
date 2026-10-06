{{-- Read-only expense summary for an export booking/job --}}
@if(auth()->user()->hasPermission('freight.export-booking.expense-manage'))
@include('nas-freights.freight-export-bookings._sub-table-styles')
<div class="section-card mt-1">
    <div class="form-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:linear-gradient(135deg,#14532d,#059669)">
        <span><i class="fa fa-receipt me-1"></i> Expense Details
            @if($exportBooking->expense && $exportBooking->expense->total_expense_amount > 0)
                <span class="ms-2 badge bg-light text-dark fw-bold" style="font-size:.78rem;">
                    Total: {{ number_format($exportBooking->expense->total_expense_amount, 2) }}
                </span>
                @if($exportBooking->expense->total_approved_amount > 0)
                    <span class="ms-1 badge bg-success bg-opacity-75 fw-bold" style="font-size:.78rem;">
                        Approved: {{ number_format($exportBooking->expense->total_approved_amount, 2) }}
                    </span>
                @endif
            @endif
        </span>
        <a href="{{ route('nas-freights.freight-export-bookings.expense.edit', $exportBooking->id) }}"
           class="btn btn-sm btn-light py-0 px-2 section-action">
            <i class="fa fa-edit me-1"></i>{{ $exportBooking->expense ? 'Edit Expense' : 'Add Expense' }}
        </a>
    </div>
    @if(!$exportBooking->expense || $exportBooking->expense->items->isEmpty())
        <div class="section-body text-center text-muted py-3" style="font-size:.8rem;">
            <i class="fa fa-receipt me-1"></i> No expense added yet.
        </div>
    @else
        <div class="sub-table-wrap sub-table-wrap--expense">
            <table class="table table-bordered table-hover mb-0 sub-table sub-table--expense">
                <thead>
                    <tr style="background:#059669; color:#fff;">
                        <th class="col-index">#</th>
                        <th>Expense Head</th>
                        <th>Receiptable</th>
                        <th class="text-end">Expense Amount</th>
                        <th class="text-end">Approved Amount</th>
                        <th>Expense Date</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exportBooking->expense->items as $i => $item)
                    <tr>
                        <td class="text-center fw-bold" data-label="#"><span class="cell-value">{{ $i + 1 }}</span></td>
                        <td class="fw-semibold" data-label="Expense Head"><span class="cell-value">{{ $item->expenseHead?->name ?? '—' }}</span></td>
                        <td data-label="Receiptable">
                            <span class="cell-value">
                                <span class="badge {{ $item->receiptable === 'Yes' ? 'bg-success bg-opacity-75' : 'bg-secondary bg-opacity-50' }}">{{ $item->receiptable }}</span>
                            </span>
                        </td>
                        <td class="text-end" data-label="Expense Amount"><span class="cell-value">{{ number_format($item->expense_amount, 2) }}</span></td>
                        <td class="text-end" data-label="Approved Amount"><span class="cell-value">{{ number_format($item->approved_amount, 2) }}</span></td>
                        <td class="text-nowrap" data-label="Expense Date"><span class="cell-value">{{ $item->expense_date?->format('d M Y') ?? '—' }}</span></td>
                        <td data-label="Note"><span class="cell-value">{{ $item->note ?? '—' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f0fdf4; font-weight:700; border-top:2px solid #14532d;">
                        <td colspan="3" class="text-end stack-hide">Total</td>
                        <td class="text-end" style="color:#14532d;" data-label="Total Expense"><span class="cell-value">{{ number_format($exportBooking->expense->total_expense_amount, 2) }}</span></td>
                        <td class="text-end" style="color:#059669;" data-label="Total Approved"><span class="cell-value">{{ number_format($exportBooking->expense->total_approved_amount, 2) }}</span></td>
                        <td colspan="2" class="stack-hide"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endif

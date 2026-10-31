{{-- Read-only bill status for an export booking/job (one bill allowed per bill type) --}}
@php
    $billUser = auth()->user();
    $canViewBill   = $billUser->hasPermission('freight.export-booking-bill.view');
    $canPrintBill  = $billUser->hasPermission('freight.export-booking-bill.print');
    $canEditBill   = $billUser->hasPermission('freight.export-booking-bill.edit');
    $canCreateBill = $billUser->hasPermission('freight.export-booking-bill.create');
    $canListBill   = $billUser->hasPermission('freight.export-booking-bill.list');
    $billTypes     = \App\Models\NasFreights\NasFreightsFreightExportBookingBill::billTypes();
    $billsByType   = $exportBooking->bills->keyBy('bill_type');
    $billsCreated  = $billsByType->count();
@endphp

@if($canViewBill || $canPrintBill || $canEditBill || $canCreateBill || $canListBill)
@include('nas-freights.freight-export-bookings._sub-table-styles')
<div class="section-card mt-1" id="bookingBillsSection">
    <div class="form-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:linear-gradient(135deg,#155e75,#0e7490)">
        <span><i class="fa fa-file-invoice-dollar me-1"></i> Bills
            @if($billsCreated === 0)
                <span class="ms-2 badge bg-warning text-dark fw-bold" style="font-size:.74rem;">
                    <i class="fa fa-exclamation-circle me-1"></i>No bill created yet
                </span>
            @else
                <span class="ms-2 badge bg-light text-dark fw-bold" style="font-size:.74rem;">
                    <i class="fa fa-check-circle me-1 text-success"></i>{{ $billsCreated }} of {{ count($billTypes) }} bills created
                </span>
            @endif
        </span>
        @if($canCreateBill && $billsCreated < count($billTypes))
        <a href="{{ route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $exportBooking->id]) }}"
           class="btn btn-sm btn-light py-0 px-2 section-action">
            <i class="fa fa-plus me-1"></i>Create Bill
        </a>
        @endif
    </div>
    <div class="sub-table-wrap sub-table-wrap--bills">
        <table class="table table-bordered table-hover mb-0 sub-table sub-table--bills">
            <thead>
                <tr style="background:#0e7490; color:#fff;">
                    <th>Bill Type</th>
                    <th>Bill No</th>
                    <th>Bill Date</th>
                    <th class="text-end">Total (BDT)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billTypes as $billType)
                @php $bill = $billsByType->get($billType); @endphp
                <tr>
                    <td data-label="Bill Type">
                        <span class="cell-value"><span class="badge {{ $billType === 'Customer' ? 'bg-primary' : 'bg-warning text-dark' }}">{{ $billType }}</span></span>
                    </td>
                    @if($bill)
                    <td class="fw-semibold text-nowrap" data-label="Bill No"><span class="cell-value">{{ $bill->bill_no }}</span></td>
                    <td class="text-nowrap" data-label="Bill Date"><span class="cell-value">{{ $bill->bill_date?->format('d M Y') ?? '—' }}</span></td>
                    <td class="text-end" data-label="Total (BDT)">
                        <span class="cell-value">
                            {{ number_format($bill->total_bdt_amount, 2) }}
                            @if($bill->currency && $bill->currency !== 'BDT')
                                <span class="d-block text-muted" style="font-size:.66rem;">{{ number_format($bill->total_amount, 2) }} {{ $bill->currency }}</span>
                            @endif
                        </span>
                    </td>
                    <td class="text-nowrap" data-label="Status">
                        <span class="cell-value"><span class="badge {{ $bill->status === 'Confirmed' ? 'bg-success' : 'bg-secondary' }}">{{ $bill->status }}</span></span>
                    </td>
                    <td data-label="Action">
                        <div class="d-flex flex-nowrap gap-1">
                            @if($canViewBill)
                            <a href="{{ route('nas-freights.freight-export-booking-bills.show', $bill->id) }}"
                               class="btn btn-sm btn-outline-info py-0 px-1" title="View bill" aria-label="View bill {{ $bill->bill_no }}"><i class="fa fa-eye"></i></a>
                            @endif
                            @if($canPrintBill)
                            <a href="{{ route('nas-freights.freight-export-booking-bills.print', $bill->id) }}" target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-success py-0 px-1" title="Print bill" aria-label="Print bill {{ $bill->bill_no }}"><i class="fa fa-print"></i></a>
                            @endif
                            @if($canEditBill)
                            <a href="{{ route('nas-freights.freight-export-booking-bills.edit', $bill->id) }}"
                               class="btn btn-sm btn-outline-primary py-0 px-1" title="Edit bill" aria-label="Edit bill {{ $bill->bill_no }}"><i class="fa fa-edit"></i></a>
                            @endif
                        </div>
                    </td>
                    @else
                    <td class="text-muted stack-hide">—</td>
                    <td class="text-muted stack-hide">—</td>
                    <td class="text-muted text-end stack-hide">—</td>
                    <td data-label="Status">
                        <span class="cell-value"><span class="badge bg-light text-muted border"><i class="fa fa-minus-circle me-1"></i>Not created</span></span>
                    </td>
                    <td data-label="Action">
                        @if($canCreateBill)
                        <a href="{{ route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $exportBooking->id]) }}"
                           class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.7rem;" aria-label="Create {{ $billType }} bill">
                            <i class="fa fa-plus me-1"></i>Create
                        </a>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

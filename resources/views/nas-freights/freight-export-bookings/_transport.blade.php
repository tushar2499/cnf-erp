{{-- Read-only cover van / transport summary for an export booking/job --}}
@if(auth()->user()->hasPermission('freight.export-booking.transport-manage'))
@include('nas-freights.freight-export-bookings._sub-table-styles')
<div class="section-card mt-1">
    <div class="form-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:linear-gradient(135deg,#0c2340,#1a6b60)">
        <span><i class="fa fa-truck me-1"></i> Cover Van / Transport Details
            @if($exportBooking->transport_amount > 0)
                <span class="ms-2 badge bg-light text-dark fw-bold" style="font-size:.78rem;">
                    Total: {{ number_format($exportBooking->transport_amount, 2) }}
                </span>
            @endif
        </span>
        <a href="{{ route('nas-freights.freight-export-bookings.transport.edit', $exportBooking->id) }}"
           class="btn btn-sm btn-light py-0 px-2 section-action">
            <i class="fa fa-edit me-1"></i>{{ $exportBooking->transportItems->isEmpty() ? 'Add Transport' : 'Edit Transport' }}
        </a>
    </div>
    @if($exportBooking->transportItems->isEmpty())
        <div class="section-body text-center text-muted py-3" style="font-size:.8rem;">
            <i class="fa fa-truck me-1"></i> No transport details added yet.
        </div>
    @else
        <div class="sub-table-wrap sub-table-wrap--transport">
            <table class="table table-bordered table-hover mb-0 sub-table sub-table--transport">
                <thead>
                    <tr style="background:#1a6b60; color:#fff;">
                        <th class="col-index">#</th>
                        <th>Cover Van No</th>
                        <th>Challan No</th>
                        <th>Capacity</th>
                        <th>Supplier</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Sup. Rate</th>
                        <th class="text-end">Cus. Rate</th>
                        <th class="text-end">Demrr. Days</th>
                        <th class="text-end">Cus. Demurrage</th>
                        <th class="text-end">Sup. Demurrage</th>
                        <th class="text-end">Amount</th>
                        <th>From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exportBooking->transportItems as $i => $ti)
                    <tr>
                        <td class="text-center fw-bold" data-label="#"><span class="cell-value">{{ $i + 1 }}</span></td>
                        <td class="fw-semibold text-nowrap" data-label="Cover Van No"><span class="cell-value">{{ $ti->cover_van_no ?? '—' }}</span></td>
                        <td class="text-nowrap" data-label="Challan No"><span class="cell-value">{{ $ti->challan_no ?? '—' }}</span></td>
                        <td class="text-nowrap" data-label="Capacity"><span class="cell-value">{{ $ti->capacity ?? '—' }}</span></td>
                        <td data-label="Supplier"><span class="cell-value">{{ $ti->supplier_name ?? '—' }}</span></td>
                        <td class="text-end" data-label="Qty"><span class="cell-value">{{ number_format($ti->qty, 2) }}</span></td>
                        <td class="text-end" data-label="Sup. Rate"><span class="cell-value">{{ number_format($ti->supplier_rate, 2) }}</span></td>
                        <td class="text-end" data-label="Cus. Rate"><span class="cell-value">{{ number_format($ti->customer_rate, 2) }}</span></td>
                        <td class="text-end" data-label="Demrr. Days"><span class="cell-value">{{ $ti->demurrage_days }}</span></td>
                        <td class="text-end" data-label="Cus. Demurrage"><span class="cell-value">{{ number_format($ti->cus_demurrage_charge, 2) }}</span></td>
                        <td class="text-end" data-label="Sup. Demurrage"><span class="cell-value">{{ number_format($ti->sup_demurrage_charge, 2) }}</span></td>
                        <td class="text-end fw-bold" style="color:#0a4f3c;" data-label="Amount"><span class="cell-value">{{ number_format($ti->amount, 2) }}</span></td>
                        <td data-label="From"><span class="cell-value">{{ $ti->location_from ?? '—' }}</span></td>
                        <td data-label="To"><span class="cell-value">{{ $ti->location_to ?? '—' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f0f8ff; font-weight:700; border-top:2px solid #0c2340;">
                        <td colspan="11" class="text-end stack-hide">Total Transport Amount</td>
                        <td class="text-end" style="color:#0a4f3c;" data-label="Total Transport Amount"><span class="cell-value">{{ number_format($exportBooking->transport_amount, 2) }}</span></td>
                        <td colspan="2" class="stack-hide"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endif

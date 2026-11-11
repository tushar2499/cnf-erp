{{-- Partial: booking/job summary for the export booking bill form.
     Optional $openInNewTab (default true): form pages keep the unsaved bill intact; the read-only show page navigates in place. --}}
<div class="row g-2">
    @if(auth()->user()->hasPermission('freight.export-booking.view'))
    @php $openInNewTab = $openInNewTab ?? true; @endphp
    <div class="col-12">
        <a href="{{ route('nas-freights.freight-export-bookings.show', $booking->id) }}"
           @if($openInNewTab) target="_blank" rel="noopener" @endif
           class="btn btn-sm btn-outline-info w-100 d-inline-flex align-items-center justify-content-center gap-2"
           style="min-height:32px;">
            <i class="fa fa-ship" aria-hidden="true"></i> Go to Booking/Job Page
            @if($openInNewTab)
                <i class="fa fa-arrow-up-right-from-square small" aria-hidden="true"></i>
                <span class="visually-hidden">(opens in a new tab)</span>
            @endif
        </a>
    </div>
    @endif
    <div class="col-6">
        <div class="info-label">Booking/Job No</div>
        <div class="info-value fw-bold text-success">{{ $booking->export_booking_no }}</div>
    </div>
    <div class="col-6">
        <div class="info-label">Booking/Job Date</div>
        <div class="info-value">{{ $booking->booking_date?->format('d M Y') ?? '—' }}</div>
    </div>
    <div class="col-12">
        <div class="info-label">Customer (Exporter)</div>
        <div class="info-value fw-semibold">{{ $booking->customer?->name ?? '—' }}</div>
        @if($booking->customer?->customer_id)
            <div style="font-size:.68rem; color:#6b7280;">{{ $booking->customer->customer_id }}</div>
        @endif
    </div>
    <div class="col-12">
        <div class="info-label">Overseas Agent</div>
        <div class="info-value">
            @if($booking->overseasAgent)
                <span class="fw-semibold">{{ $booking->overseasAgent->name }}</span>
                @if($booking->overseasAgent->country)
                    <span style="font-size:.68rem; color:#6b7280;"> — {{ $booking->overseasAgent->country }}</span>
                @endif
            @else
                —
            @endif
        </div>
    </div>
    <div class="col-6">
        <div class="info-label">Service Type</div>
        <div class="info-value">{{ $booking->service_type ?? '—' }}</div>
    </div>
    <div class="col-6">
        <div class="info-label">Incoterms</div>
        <div class="info-value">{{ $booking->incoterms ?? '—' }}</div>
    </div>
    <div class="col-6">
        <div class="info-label">POL</div>
        <div class="info-value">{{ $booking->pol ?? '—' }}</div>
    </div>
    <div class="col-6">
        <div class="info-label">POD</div>
        <div class="info-value">{{ $booking->pod ?? '—' }}</div>
    </div>
    @if($booking->export_bl_no)
    <div class="col-6">
        <div class="info-label">B/L No</div>
        <div class="info-value fw-semibold">{{ $booking->export_bl_no }}</div>
    </div>
    @endif
    @if($booking->invoice_no)
    <div class="col-6">
        <div class="info-label">S.B. No</div>
        <div class="info-value">{{ $booking->invoice_no }}</div>
    </div>
    @endif
    @if($booking->exp_no)
    <div class="col-6">
        <div class="info-label">EXP No</div>
        <div class="info-value">{{ $booking->exp_no }}</div>
    </div>
    @endif
    @if($booking->currency)
    <div class="col-6">
        <div class="info-label">Booking/Job Currency</div>
        <div class="info-value">{{ $booking->currency }}</div>
    </div>
    @endif
    @if($booking->exchange_rate !== null && $booking->currency && $booking->currency !== 'BDT')
    <div class="col-6">
        <div class="info-label">Booking/Job Exchange Rate</div>
        <div class="info-value">1 {{ $booking->currency }} = {{ number_format((float) $booking->exchange_rate, 3) }} BDT</div>
    </div>
    @endif
    <div class="col-12">
        <div class="info-label">Status</div>
        <div class="info-value">
            <span class="badge bg-secondary">{{ $booking->status }}</span>
        </div>
    </div>

    {{-- Existing bills on this booking/job --}}
    @php $existingBills = $booking->bills ?? collect(); @endphp
    @if($existingBills->count())
    <div class="col-12 mt-1">
        <div class="info-label mb-1">Bills on This Booking/Job</div>
        @foreach($existingBills as $eb)
        <div class="d-flex align-items-center justify-content-between p-1 rounded mb-1" style="background:#f1f5f9; font-size:.76rem;">
            <span>
                <span class="badge {{ $eb->bill_type === 'Customer' ? 'bg-primary' : 'bg-warning text-dark' }} me-1">{{ $eb->bill_type }}</span>
                {{ $eb->bill_no }}
            </span>
            <span class="text-muted">{{ number_format($eb->total_bdt_amount, 2) }} BDT</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Read by the bill form JS: decides whether the Bill Ref / Job No prefix inputs show --}}
    <span id="bookingPrefixData" class="d-none"
        data-customer-bills="{{ $existingBills->where('bill_type', 'Customer')->count() }}"></span>
</div>

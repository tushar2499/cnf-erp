@extends('nas-freights.layouts.app')

@section('title', 'Customer Bill — ' . $customerBill->bill_no)

@push('styles')
<style>
.info-label { font-size:.78rem; color:#6b7280; font-weight:600; }
.info-val   { font-size:.83rem; font-weight:500; }
#itemsTable th { background:#1a6b60; color:#fff; font-size:.75rem; padding:.35rem .5rem; white-space:nowrap; }
#itemsTable td { font-size:.78rem; padding:.35rem .5rem; white-space:nowrap; vertical-align:middle; }
#itemsTable tfoot td { background:#e8f4f1; font-weight:700; font-size:.8rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-invoice-dollar me-2 text-info"></i> Customer Bill — {{ $customerBill->bill_no }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-freights.customer-bills.print', $customerBill->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
            <i class="fa fa-print me-1"></i> Print
        </a>
        <a href="{{ route('nas-freights.customer-bills.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header" style="background:#0c2340;color:#fff;">
        <i class="fa fa-info-circle me-2"></i> Bill Details
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="info-label">Bill No</div>
                <div class="info-val">{{ $customerBill->bill_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Bill Date</div>
                <div class="info-val">{{ $customerBill->bill_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">From Date</div>
                <div class="info-val">{{ $customerBill->from_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">To Date</div>
                <div class="info-val">{{ $customerBill->to_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Customer</div>
                <div class="info-val">{{ $customerBill->customer_name ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Delivery Type</div>
                <div class="info-val">{{ $customerBill->delivery_type ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Bill Type</div>
                <div class="info-val">{{ $customerBill->bill_type ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Bill By</div>
                <div class="info-val">{{ $customerBill->bill_by ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">TDS %</div>
                <div class="info-val">{{ $customerBill->tds_percent }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">TDS Amount</div>
                <div class="info-val">{{ number_format($customerBill->tds_amount, 2) }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Sub Total</div>
                <div class="info-val">{{ number_format($customerBill->sub_total, 2) }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Total Amount</div>
                <div class="info-val fw-bold text-success fs-6">{{ number_format($customerBill->total_amount, 2) }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Status</div>
                <div class="info-val">
                    @if($customerBill->status === 'Approved')
                        <span class="badge bg-success">APPROVED</span>
                    @elseif($customerBill->status === 'Submitted')
                        <span class="badge bg-warning text-dark">SUBMITTED</span>
                    @else
                        <span class="badge bg-secondary">DRAFT</span>
                    @endif
                </div>
            </div>
            @if($customerBill->customer_address)
            <div class="col-md-6">
                <div class="info-label">Bill Address</div>
                <div class="info-val">{{ $customerBill->customer_address }}</div>
            </div>
            @endif
            @if($customerBill->note)
            <div class="col-md-6">
                <div class="info-label">Note</div>
                <div class="info-val">{{ $customerBill->note }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background:#0c2340;color:#fff;">
        <i class="fa fa-list me-2"></i> Bill Items ({{ $customerBill->items->count() }})
    </div>
    <div class="card-body p-0">
        <div style="overflow-x:auto">
            <table class="table table-bordered table-hover table-striped mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Booking Date</th>
                        <th>Delivery Date</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Location</th>
                        <th class="text-end">B.Qty</th>
                        <th class="text-end">D.Qty</th>
                        <th class="text-end">Due Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Dem. Days</th>
                        <th class="text-end">Dem. Amount</th>
                        <th class="text-end">Disc%</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">AIT%</th>
                        <th class="text-end">Line Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customerBill->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->format('d-M-Y') : '—' }}</td>
                        <td>{{ $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->format('d-M-Y') : '—' }}</td>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->location }}</td>
                        <td class="text-end">{{ number_format($item->b_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->d_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->due_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->price, 2) }}</td>
                        <td class="text-end">{{ number_format((float)($item->demurrage_day ?? 0), 2) }}</td>
                        <td class="text-end">{{ number_format((float)($item->demurrage_amount ?? 0), 2) }}</td>
                        <td class="text-end">{{ number_format($item->disc_percent, 2) }}</td>
                        <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                        <td class="text-end">{{ number_format($item->ait_percent, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->line_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="15" class="text-end">Total Amount:</td>
                        <td class="text-end text-success">{{ number_format($customerBill->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('nas-freights.layouts.app')

@section('title', 'Payment Order — ' . $supplierBill->pay_order_no)

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
    <h4><i class="fa fa-file-invoice me-2 text-info"></i> Payment Order — {{ $supplierBill->pay_order_no }}</h4>
    <a href="{{ route('nas-freights.supplier-bills.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card mb-3">
    <div class="card-header" style="background:#0c2340;color:#fff;">
        <i class="fa fa-info-circle me-2"></i> Payment Order Details
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="info-label">Pay Order No</div>
                <div class="info-val">{{ $supplierBill->pay_order_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Bill Date</div>
                <div class="info-val">{{ $supplierBill->bill_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">From Date</div>
                <div class="info-val">{{ $supplierBill->from_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">To Date</div>
                <div class="info-val">{{ $supplierBill->to_date?->format('d-M-Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Supplier</div>
                <div class="info-val">{{ $supplierBill->supplier_name ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Bill By</div>
                <div class="info-val">{{ $supplierBill->bill_by ?: '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Total Amount</div>
                <div class="info-val fw-bold text-success fs-6">{{ number_format($supplierBill->total_amount, 2) }}</div>
            </div>
            <div class="col-md-3">
                <div class="info-label">Status</div>
                <div class="info-val">
                    @if($supplierBill->status === 'Approved')
                        <span class="badge bg-success">APPROVED</span>
                    @elseif($supplierBill->status === 'Submitted')
                        <span class="badge bg-warning text-dark">SUBMITTED</span>
                    @else
                        <span class="badge bg-secondary">DRAFT</span>
                    @endif
                </div>
            </div>
            @if($supplierBill->note)
            <div class="col-md-6">
                <div class="info-label">Note</div>
                <div class="info-val">{{ $supplierBill->note }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background:#0c2340;color:#fff;">
        <i class="fa fa-list me-2"></i> Items ({{ $supplierBill->items->count() }})
    </div>
    <div class="card-body p-0">
        <div style="overflow-x:auto">
            <table class="table table-bordered table-hover table-striped mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Entry Date</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Location</th>
                        <th class="text-end">B.Qty</th>
                        <th class="text-end">D.Qty</th>
                        <th class="text-end">Due Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Demurrage Day</th>
                        <th class="text-end">Demurrage Amt</th>
                        <th class="text-end">Line Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplierBill->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->format('d-M-Y') : '—' }}</td>
                        <td>{{ $item->entry_date   ? \Carbon\Carbon::parse($item->entry_date)->format('d-M-Y')   : '—' }}</td>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->location }}</td>
                        <td class="text-end">{{ number_format($item->b_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->d_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->due_qty, 2) }}</td>
                        <td class="text-end">{{ number_format($item->price, 2) }}</td>
                        <td class="text-end">{{ number_format((float)($item->demurrage_day ?? 0), 2) }}</td>
                        <td class="text-end">{{ number_format((float)($item->demurrage_amount ?? 0), 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->line_amount, 2) }}</td>
                        <td>{{ $item->notes }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="12" class="text-end">Total Amount:</td>
                        <td class="text-end text-success">{{ number_format($supplierBill->total_amount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

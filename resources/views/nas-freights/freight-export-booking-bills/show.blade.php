@extends('nas-freights.layouts.app')

@section('title', 'Bill — '.$bill->bill_no)

@push('styles')
<style>
.panel { border: 1px solid #dee2e6; border-radius: .4rem; overflow: hidden; margin-bottom: 1rem; }
.panel-header { display: flex; align-items: center; gap: .45rem; padding: .45rem .85rem; font-size: .8rem; font-weight: 700; color: #fff; background: #1e293b; }
.panel-body { padding: .75rem .85rem; }
.info-label { font-size: .67rem; font-weight: 700; color: #6b7a99; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .1rem; }
.info-value { font-size: .82rem; color: #1e293b; }
#itemsTable th { background: #1e293b; color: #e2e8f0; font-size: .76rem; padding: .4rem .6rem; }
#itemsTable td { font-size: .8rem; padding: .35rem .6rem; }
.total-row td { font-weight: 700; background: #f0fdf4 !important; border-top: 2px solid #14b8a6 !important; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div></div>
    <div class="fw-bold" style="font-size:.95rem; color:#0a4f3c;">
        Export Booking Bill &nbsp;<span class="badge bg-light text-dark border fs-6">{{ $bill->bill_no }}</span>
        &nbsp;<span class="badge {{ $bill->status === 'Confirmed' ? 'bg-success' : 'bg-secondary' }}">{{ $bill->status }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-freights.freight-export-booking-bills.print', $bill->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
            <i class="fa fa-print me-1"></i> Print
        </a>
        <a href="{{ route('nas-freights.freight-export-booking-bills.edit', $bill->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('nas-freights.freight-export-booking-bills.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-header" style="background:#155e75;"><i class="fa fa-ship"></i> Booking Details</div>
            <div class="panel-body">
                @include('nas-freights.freight-export-booking-bills._booking-info', ['booking' => $bill->exportBooking])
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header"><i class="fa fa-file-invoice-dollar"></i> Bill Details</div>
            <div class="panel-body">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <div class="info-label">Bill Type</div>
                        <div class="info-value">
                            <span class="badge {{ $bill->bill_type === 'Customer' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                {{ $bill->bill_type }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Bill Date</div>
                        <div class="info-value">{{ $bill->bill_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="info-label">Currency</div>
                        <div class="info-value fw-bold">{{ $bill->currency }}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="info-label">Exchange Rate</div>
                        <div class="info-value">{{ number_format($bill->exchange_rate, 4) }}</div>
                    </div>
                    @if($bill->remarks)
                    <div class="col-12">
                        <div class="info-label">Remarks</div>
                        <div class="info-value">{{ $bill->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><i class="fa fa-list-ul me-1"></i> Bill Items</div>
            <div class="panel-body p-0">
                <table class="table table-bordered mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Description</th>
                            <th class="text-end" style="width:160px">Amount ({{ $bill->currency }})</th>
                            <th class="text-end" style="width:160px">Amount (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->items as $i => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->amount_bdt, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="text-end">Total</td>
                            <td class="text-end">{{ number_format($bill->total_amount, 2) }} {{ $bill->currency }}</td>
                            <td class="text-end">{{ number_format($bill->total_bdt_amount, 2) }} BDT</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

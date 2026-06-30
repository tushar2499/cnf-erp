@extends('nas-freights.layouts.app')
@section('title', 'Bill Details')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<style>
.select2-container .select2-selection--single { height:38px; border:1px solid #ced4da; border-radius:.375rem; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px; padding-left:10px; color:#212529; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
.filter-card { background:#f8f9fa; border:1px solid #dee2e6; border-radius:.5rem; padding:16px 20px 8px; margin-bottom:18px; }
table.report-table { font-size:12px; }
table.report-table thead th { background:#1a6b60; color:#fff; white-space:nowrap; padding:6px 8px; vertical-align:middle; }
table.report-table tbody td { padding:5px 8px; vertical-align:middle; }
table.report-table tfoot td { background:#e4ede4; font-weight:700; padding:6px 8px; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold"><i class="fa fa-list-alt me-2 text-success"></i>Monthwise Transport Bill Details</h5>
</div>

<form method="GET" action="{{ route('nas-freights.reports.bill-details') }}">
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
        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Customer</label>
            <select name="customer_id" id="sel-customer" class="form-control form-control-sm" style="width:100%">
                <option value="">All Customers</option>
                @if(request('customer_id') && request('customer_name'))
                    <option value="{{ request('customer_id') }}" selected>{{ request('customer_name') }}</option>
                @endif
            </select>
            <input type="hidden" name="customer_name" id="customer_name_val" value="{{ request('customer_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Bill Type</label>
            <select name="bill_type" class="form-select form-select-sm">
                <option value="">All Types</option>
                @foreach(\App\Models\NasFreights\NasFreightsCustomerBill::billTypes() as $t)
                    <option value="{{ $t }}" {{ request('bill_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2 pb-1">
            <button type="submit" class="btn btn-success btn-sm px-4"><i class="fa fa-search me-1"></i>Search</button>
            <a href="{{ route('nas-freights.reports.bill-details') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
        </div>
    </div>
</div>
</form>

@if($items->isNotEmpty())
@php
    $qs        = http_build_query(request()->except('_token'));
    $vanNos    = $items->pluck('item_code')->filter()->unique();
    $vehicles  = \App\Models\NasFreights\NasFreightsVehicle::whereIn('vehicle_number', $vanNos)->pluck('vehicle_type', 'vehicle_number');
    $totalNet  = 0; $totalDem = 0; $totalVat = 0; $totalAmt = 0;
    $fromLabel = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M, Y') : '—';
    $toLabel   = request('to_date')   ? \Carbon\Carbon::parse(request('to_date'))->format('d M, Y')   : '—';
@endphp

{{-- Header info box --}}
<table class="table table-bordered mb-3" style="font-size:12px">
    <tr>
        <td style="width:55%">
            <strong>To,</strong><br>
            @if($customer)
                <strong>{{ $customer->name }}</strong><br>
                {!! nl2br(e($customer->address ?? '')) !!}
            @else
                <em>All Customers</em>
            @endif
        </td>
        <td>
            <strong>Billing Period:</strong> From: {{ $fromLabel }} To: {{ $toLabel }}<br>
            <strong>Total Bill Count:</strong> {{ $bills->count() }}
        </td>
    </tr>
</table>

{{-- Export buttons --}}
<div class="d-flex gap-2 mb-2">
    <a href="{{ route('nas-freights.reports.bill-details.print') }}?{{ $qs }}" target="_blank" class="btn btn-outline-dark btn-sm px-3"><i class="fa fa-print me-1"></i>Print</a>
    <a href="{{ route('nas-freights.reports.bill-details.pdf') }}?{{ $qs }}" class="btn btn-outline-danger btn-sm px-3"><i class="fa fa-file-pdf me-1"></i>PDF</a>
    <a href="{{ route('nas-freights.reports.bill-details.excel') }}?{{ $qs }}" class="btn btn-outline-success btn-sm px-3"><i class="fa fa-file-excel me-1"></i>Excel</a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-hover report-table">
    <thead>
        <tr>
            <th>SL</th>
            <th>Bill No</th>
            <th>Bill Date</th>
            <th>Cover Van No</th>
            <th>Vehicle Type</th>
            <th>Capacity</th>
            <th>Source</th>
            <th>Destination</th>
            <th class="text-end">Net Amount</th>
            <th class="text-end">Total Dem.</th>
            <th class="text-end">Vat %</th>
            <th class="text-end">Vat Amt</th>
            <th class="text-end">Total Amt</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
        @php
            $bill     = $item->bill;
            $bItem    = $item->bookingItem;
            $src      = $bItem?->location_from ?? '';
            $dst      = $bItem?->location_to ?? '';
            $cap      = $bItem?->capacity ?? '';
            $dem      = (float)($item->demurrage_amount ?: 0);
            $vatPct   = (float)($bill?->vat_percent ?? 0);
            $vatAmt   = round($item->line_amount * $vatPct / 100, 2);
            $rowTotal = $item->line_amount + $dem + $vatAmt;
            $vanType  = $vehicles[$item->item_code] ?? '';
            $totalNet += $item->line_amount;
            $totalDem += $dem;
            $totalVat += $vatAmt;
            $totalAmt += $rowTotal;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $bill?->bill_no }}</td>
            <td>{{ $bill?->bill_date?->format('d M Y') }}</td>
            <td>{{ $item->item_code }}</td>
            <td>{{ $vanType }}</td>
            <td>{{ $cap }}</td>
            <td>{{ $src }}</td>
            <td>{{ $dst }}</td>
            <td class="text-end">{{ number_format($item->line_amount, 2) }}</td>
            <td class="text-end">{{ number_format($dem, 2) }}</td>
            <td class="text-end">{{ number_format($vatPct, 2) }}</td>
            <td class="text-end">{{ number_format($vatAmt, 2) }}</td>
            <td class="text-end fw-bold">{{ number_format($rowTotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8" class="text-end">Total ({{ $items->count() }} items)</td>
            <td class="text-end">{{ number_format($totalNet, 2) }}</td>
            <td class="text-end">{{ number_format($totalDem, 2) }}</td>
            <td></td>
            <td class="text-end">{{ number_format($totalVat, 2) }}</td>
            <td class="text-end">{{ number_format($totalAmt, 2) }}</td>
        </tr>
    </tfoot>
</table>
</div>

@elseif(request()->hasAny(['from_date','to_date','customer_id','bill_type']))
<div class="alert alert-info">No records found for the selected filters.</div>
@else
<div class="text-center text-muted py-5">
    <i class="fa fa-filter fa-2x mb-3 d-block"></i>
    Select filters above and click Search.
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('#sel-customer').select2({
        placeholder: 'Search customer...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("nas-freights.bookings.search-customers") }}',
            dataType: 'json',
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d })
        }
    }).on('select2:select', function (e) {
        $('#customer_name_val').val(e.params.data.name || e.params.data.text);
    }).on('select2:clear', function () {
        $('#customer_name_val').val('');
    });
});
</script>
@endpush

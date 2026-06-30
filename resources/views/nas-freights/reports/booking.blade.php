@extends('nas-freights.layouts.app')

@section('title', 'Booking Report')

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
.profit-cell { color:#1a6b60; font-weight:600; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold"><i class="fa fa-chart-bar me-2 text-success"></i>Booking Report</h5>
</div>

{{-- Filter Form --}}
<form method="GET" action="{{ route('nas-freights.reports.booking') }}" id="filterForm">
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
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Customer</label>
            <select name="customer_id" id="sel-customer" class="form-control form-control-sm" style="width:100%">
                <option value="">All Customers</option>
                @if(request('customer_id') && request('customer_name'))
                    <option value="{{ request('customer_id') }}" selected>{{ request('customer_name') }}</option>
                @endif
            </select>
            <input type="hidden" name="customer_name" id="customer_name_val" value="{{ request('customer_name') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Supplier</label>
            <select name="supplier_id" id="sel-supplier" class="form-control form-control-sm" style="width:100%">
                <option value="">All Suppliers</option>
                @if(request('supplier_id') && request('supplier_name'))
                    <option value="{{ request('supplier_id') }}" selected>{{ request('supplier_name') }}</option>
                @endif
            </select>
            <input type="hidden" name="supplier_name" id="supplier_name_val" value="{{ request('supplier_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                @foreach(['Draft', 'Approved'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Vehicle / Cover Van</label>
            <input type="text" name="vehicle" class="form-control form-control-sm" placeholder="Cover Van No..." value="{{ request('vehicle') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" style="font-size:12px">Location From</label>
            <input type="text" name="location_from" class="form-control form-control-sm" placeholder="Location from..." value="{{ request('location_from') }}">
        </div>
        <div class="col-md-6 d-flex align-items-end gap-2 pb-1">
            <button type="submit" class="btn btn-success btn-sm px-4"><i class="fa fa-search me-1"></i>Search</button>
            <a href="{{ route('nas-freights.reports.booking') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
            @if($rows->isNotEmpty())
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('nas-freights.reports.booking.print') }}?{{ http_build_query(request()->except('_token')) }}"
                   target="_blank" class="btn btn-outline-dark btn-sm px-3">
                    <i class="fa fa-print me-1"></i>Print
                </a>
                <a href="{{ route('nas-freights.reports.booking.pdf') }}?{{ http_build_query(request()->except('_token')) }}"
                   class="btn btn-outline-danger btn-sm px-3">
                    <i class="fa fa-file-pdf me-1"></i>PDF
                </a>
                <a href="{{ route('nas-freights.reports.booking.excel') }}?{{ http_build_query(request()->except('_token')) }}"
                   class="btn btn-outline-success btn-sm px-3">
                    <i class="fa fa-file-excel me-1"></i>Excel
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Results Table --}}
@if($rows->isNotEmpty())
@php
    $totalSupplier  = $rows->sum('supplier_rate');
    $totalCustomer  = $rows->sum('customer_rate');
    $totalProfit    = $rows->sum(fn($r) => $r->customer_rate - $r->supplier_rate);
@endphp
<div class="table-responsive">
<table class="table table-bordered table-hover report-table">
    <thead>
        <tr>
            <th>SL</th>
            <th>Job No</th>
            <th>Job Date</th>
            <th>Entry Date</th>
            <th>Entry By</th>
            <th>Sales Person</th>
            <th>Customer</th>
            <th>Supplier</th>
            <th>Cover Van Details</th>
            <th>Location</th>
            <th class="text-end">Supplier Rate</th>
            <th class="text-end">Customer Rate</th>
            <th class="text-end">Profit</th>
            <th>Remarks</th>
            <th>Billed</th>
            <th>Bill No</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $item)
        @php
            $b       = $item->booking;
            $loc     = trim(($item->location_from ?? '') . ($item->location_to ? ' - '.$item->location_to : ''));
            $profit  = $item->customer_rate - $item->supplier_rate;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $b?->job_no }}</td>
            <td>{{ $b?->job_date?->format('d M Y') }}</td>
            <td>{{ $b?->created_at?->format('d M Y') }}</td>
            <td>{{ $b?->entry_by }}</td>
            <td>{{ $b?->sales_person_name }}</td>
            <td>{{ $b?->customer_name }}</td>
            <td>{{ $item->supplier_name }}</td>
            <td>{{ $item->cover_van_no }}</td>
            <td>{{ $loc }}</td>
            <td class="text-end">{{ number_format($item->supplier_rate, 2) }}</td>
            <td class="text-end">{{ number_format($item->customer_rate, 2) }}</td>
            <td class="text-end profit-cell">{{ number_format($profit, 2) }}</td>
            <td>{{ $b?->note }}</td>
            <td>
                @if($item->is_billed)
                    <span class="badge bg-success">Billed</span>
                @else
                    <span class="badge bg-warning text-dark">Pending</span>
                @endif
            </td>
            <td>{{ $item->bill_no ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10" class="text-end">Total</td>
            <td class="text-end">{{ number_format($totalSupplier, 2) }}</td>
            <td class="text-end">{{ number_format($totalCustomer, 2) }}</td>
            <td class="text-end">{{ number_format($totalProfit, 2) }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>
</div>
<div class="text-muted small mt-1">{{ $rows->count() }} row(s) found.</div>

@elseif(request()->hasAny(['from_date','to_date','customer_id','supplier_id','vehicle','location_from','status']))
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

    $('#sel-supplier').select2({
        placeholder: 'Search supplier...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("nas-freights.supplier-bills.search-suppliers") }}',
            dataType: 'json',
            data: d => ({ q: d.term }),
            processResults: d => ({ results: d })
        }
    }).on('select2:select', function (e) {
        $('#supplier_name_val').val(e.params.data.name || e.params.data.text);
    }).on('select2:clear', function () {
        $('#supplier_name_val').val('');
    });
});
</script>
@endpush

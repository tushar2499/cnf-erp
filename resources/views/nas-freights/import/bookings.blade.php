@extends('nas-freights.layouts.app')
@section('title', 'Import Bookings')

@push('styles')
<style>
.import-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; }
.import-card-header { background:#0c2340; color:#fff; padding:.6rem 1rem; font-weight:600; font-size:.85rem; }
.preview-table th { background:#1a6b60; color:#fff; font-size:.78rem; padding:.4rem .6rem; white-space:nowrap; }
.preview-table td { font-size:.8rem; padding:.35rem .6rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h4><i class="fa fa-file-import me-2 text-info"></i> Import Bookings</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Upload Form --}}
<div class="import-card mb-3">
    <div class="import-card-header"><i class="fa fa-upload me-2"></i> Upload File</div>
    <div class="p-3">
        <form method="POST" action="{{ route('nas-freights.import.bookings.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.83rem;">Select Excel File (.xlsx / .xls)</label>
                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                    <div class="form-text">Expected columns: Job No, Job Date, Customer, Sales Person, Location, Cover Van Details, Supplier Rate, Customer Rate, Profit, Remarks, Entry Date, Entry By, Total</div>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-sm btn-info text-white">
                        <i class="fa fa-eye me-1"></i> Preview
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Preview --}}
@isset($rows)
@if(!empty($headers))
<div class="alert alert-info py-2" style="font-size:.8rem;">
    <strong>Detected headers:</strong> {{ implode(', ', $headers) }}
</div>
@endif
<div class="import-card">
    <div class="import-card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-table me-2"></i> Preview — {{ $total }} row(s) found</span>
    </div>
    <div class="p-3">
        <div style="overflow-x:auto">
            <table class="table table-bordered table-hover preview-table mb-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job No</th>
                        <th>Job Date</th>
                        <th>Customer</th>
                        <th>Sales Person</th>
                        <th>Location</th>
                        <th>Cover Van</th>
                        <th>Supplier</th>
                        <th>Supplier Rate</th>
                        <th>Customer Rate</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['job_no'] }}</td>
                        <td>{{ $row['job_date'] ?? '-' }}</td>
                        <td>{{ $row['customer_name'] ?? '-' }}</td>
                        <td>{{ $row['sales_person_name'] ?? '-' }}</td>
                        <td>
                            {{ $row['_item']['location_from'] ?? '-' }}
                            @if(!empty($row['_item']['location_to']))
                            → {{ $row['_item']['location_to'] }}
                            @endif
                        </td>
                        <td>{{ $row['cover_van_no'] ?? '-' }}</td>
                        <td>{{ $row['_item']['supplier_name'] ?? '-' }}</td>
                        <td class="text-end">{{ number_format($row['_item']['supplier_rate'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['_item']['customer_rate'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['_item']['customer_rate'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <form method="POST" action="{{ route('nas-freights.import.bookings.import') }}">
            @csrf
            <input type="hidden" name="file_path" value="{{ $storedPath }}">
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-check me-1"></i> Confirm Import ({{ $total }} rows)
            </button>
            <a href="{{ route('nas-freights.import.bookings') }}" class="btn btn-secondary btn-sm ms-2">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
        </form>
    </div>
</div>
@endisset

@endsection

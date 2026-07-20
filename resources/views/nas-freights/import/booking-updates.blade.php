@extends('nas-freights.layouts.app')
@section('title', 'Import Booking Updates')

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
    <h4><i class="fa fa-file-import me-2 text-info"></i> Import Booking Updates</h4>
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

<div class="import-card mb-3">
    <div class="import-card-header"><i class="fa fa-upload me-2"></i> Upload File</div>
    <div class="p-3">
        <form method="POST" action="{{ route('nas-freights.import.booking-updates.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.83rem;">Select Excel File (.xlsx / .xls)</label>
                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                    <div class="form-text">Expected columns: Job No, Job Date, Delivery Date, Entry By, Branch, Customer, Item Details, T.Qty, Item Line Amount, AIT, TDS, VAT, T.Amount, Discount, Forfeited, Status, Delivery Status, Note, Type</div>
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

@isset($rows)
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
                        <th>Delivery Date</th>
                        <th>Qty</th>
                        <th>AIT Amt</th>
                        <th>AIT %</th>
                        <th>TDS Amt</th>
                        <th>TDS %</th>
                        <th>VAT Amt</th>
                        <th>VAT %</th>
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Forfeited</th>
                        <th>Status</th>
                        <th>Delivery Status</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['job_no'] }}</td>
                        <td>{{ $row['delivery_date'] ?? '-' }}</td>
                        <td class="text-end">{{ $row['qty'] }}</td>
                        <td class="text-end">{{ number_format($row['ait_amount'], 2) }}</td>
                        <td class="text-end">{{ $row['ait_percent'] }}%</td>
                        <td class="text-end">{{ number_format($row['tds_amount'], 2) }}</td>
                        <td class="text-end">{{ $row['tds_percent'] }}%</td>
                        <td class="text-end">{{ number_format($row['vat_amount'], 2) }}</td>
                        <td class="text-end">{{ $row['vat_percent'] }}%</td>
                        <td class="text-end fw-bold">{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['discount'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['forfeited_amount'], 2) }}</td>
                        <td><span class="badge bg-secondary">{{ $row['status'] }}</span></td>
                        <td>{{ $row['delivery_status'] ?? '-' }}</td>
                        <td>{{ $row['sales_type'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('nas-freights.import.booking-updates.import') }}">
            @csrf
            <input type="hidden" name="file_path" value="{{ $storedPath }}">
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-check me-1"></i> Confirm Import ({{ $total }} rows)
            </button>
            <a href="{{ route('nas-freights.import.booking-updates') }}" class="btn btn-secondary btn-sm ms-2">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
        </form>
    </div>
</div>
@endisset

@endsection

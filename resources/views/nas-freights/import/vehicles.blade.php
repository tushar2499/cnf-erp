@extends('nas-freights.layouts.app')
@section('title', 'Import Vehicles')

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
    <h4><i class="fa fa-file-import me-2 text-info"></i> Import Vehicles</h4>
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
        <form method="POST" action="{{ route('nas-freights.import.vehicles.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.83rem;">Select Excel File (.xlsx / .xls)</label>
                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                    <div class="form-text">Expected columns: Vehicle Number, Vehicle Name, U O M (Purchase), Price, SO Available, PO Available</div>
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
                        <th>Vehicle Number</th>
                        <th>Vehicle Name</th>
                        <th>UOM</th>
                        <th>Price</th>
                        <th>SO Available</th>
                        <th>PO Available</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['vehicle_number'] }}</td>
                        <td>{{ $row['vehicle_name'] }}</td>
                        <td>{{ $row['purchase_unit'] ?? '-' }}</td>
                        <td class="text-end">{{ number_format($row['price'], 2) }}</td>
                        <td class="text-center">
                            @if($row['availability_in_so'])
                                <span class="badge bg-success">YES</span>
                            @else
                                <span class="badge bg-secondary">NO</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['availability_in_po'])
                                <span class="badge bg-success">YES</span>
                            @else
                                <span class="badge bg-secondary">NO</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('nas-freights.import.vehicles.import') }}">
            @csrf
            <input type="hidden" name="file_path" value="{{ $storedPath }}">
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fa fa-check me-1"></i> Confirm Import ({{ $total }} rows)
            </button>
            <a href="{{ route('nas-freights.import.vehicles') }}" class="btn btn-secondary btn-sm ms-2">
                <i class="fa fa-times me-1"></i> Cancel
            </a>
        </form>
    </div>
</div>
@endisset

@endsection

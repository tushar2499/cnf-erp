@extends('nas-trading.layouts.app')
@section('title', 'Chevron Lines Data Import')

@push('styles')
<style>
.preview-card { background:#fff; border:1px solid #dee2e6; border-radius:.4rem; margin-bottom:1.5rem; overflow:hidden; }
.preview-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.85rem; font-weight:600; display:flex; justify-content:space-between; align-items:center; }
.preview-header .badge { font-size:.8rem; background:#1a6b60; }
.preview-table { font-size:.78rem; }
.preview-table thead th { background:#1a6b60; color:#fff; padding:.35rem .5rem; white-space:nowrap; }
.preview-table tbody td { padding:.28rem .5rem; vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold"><i class="fa fa-file-import me-2 text-success"></i>Chevron Lines Data Import — Preview</h5>
</div>

<div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
    <i class="fa fa-exclamation-triangle"></i>
    Review data below. Click <strong>Confirm & Import</strong> to insert into the database.
    Employees will sync to <strong>NAS Trading</strong>, <strong>NAS Freights</strong>, and <strong>Chevron Lines</strong>. Existing records updated by code.
</div>

{{-- Employees --}}
<div class="preview-card">
    <div class="preview-header">
        <span><i class="fa fa-users me-2"></i>Employees</span>
        <span class="badge">{{ count($employees) }} records</span>
    </div>
    <div style="max-height:350px;overflow-y:auto">
        <table class="table table-bordered table-hover mb-0 preview-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $i => $emp)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $emp['code'] }}</td>
                    <td>{{ $emp['name'] }}</td>
                    <td>{{ $emp['designation'] }}</td>
                    <td><span class="badge bg-secondary">{{ $emp['department'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Expense Heads --}}
<div class="preview-card">
    <div class="preview-header">
        <span><i class="fa fa-list me-2"></i>Expense Heads</span>
        <span class="badge">{{ count($expenseHeads) }} records</span>
    </div>
    <div style="max-height:400px;overflow-y:auto">
        <table class="table table-bordered table-hover mb-0 preview-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Expense Name</th>
                    <th>Type</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseHeads as $i => $head)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $head['name'] }}</td>
                    <td>
                        @if($head['type'] === 'Internal')
                            <span class="badge bg-primary">Internal</span>
                        @elseif($head['type'] === 'External')
                            <span class="badge bg-warning text-dark">External</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $head['category'] ?: '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Confirm button --}}
<div class="d-flex gap-3 align-items-center mb-4">
    <button id="btnImport" class="btn btn-success px-4">
        <i class="fa fa-check-circle me-2"></i>Confirm & Import
    </button>
    <span id="importStatus" class="text-muted" style="font-size:.85rem"></span>
</div>
@endsection

@push('scripts')
<script>
$('#btnImport').on('click', function () {
    var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Importing...');
    $('#importStatus').text('');

    $.ajax({
        url: '{{ route('nas-trading.import.chevron') }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
    })
    .done(function (r) {
        Swal.fire({ icon: 'success', title: 'Import Complete', text: r.message });
        $btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i>Confirm & Import');
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: 'Import Failed', text: xhr.responseJSON?.message || 'Unknown error.' });
        $btn.prop('disabled', false).html('<i class="fa fa-check-circle me-2"></i>Confirm & Import');
    });
});
</script>
@endpush

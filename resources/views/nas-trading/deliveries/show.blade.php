@extends('nas-trading.layouts.app')
@section('title', 'Delivery — ' . $delivery->delivery_no)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.info-header { background:#1a6b60; color:#fff; padding:.45rem 1rem; font-size:.8rem; font-weight:700; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.72rem; color:#6c757d; text-transform:uppercase; }
.info-value { font-size:.85rem; font-weight:600; }
.dt-sm th, .dt-sm td { font-size:.78rem; padding:.35rem .5rem; vertical-align:middle; }
.dt-sm th { background:#f8f9fa; font-weight:700; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0"><i class="fa fa-truck me-2 text-info"></i> {{ $delivery->delivery_no }}</h4>
        @php $badge = ['Pending' => 'secondary', 'Dispatched' => 'warning', 'Delivered' => 'success'] @endphp
        <span class="badge bg-{{ $badge[$delivery->delivery_status] ?? 'secondary' }} text-{{ $delivery->delivery_status === 'Dispatched' ? 'dark' : 'white' }}">{{ $delivery->delivery_status }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($delivery->delivery_status === 'Pending')
        <button class="btn btn-sm btn-outline-warning" id="btnDispatch"><i class="fa fa-truck me-1"></i>Dispatch</button>
        @endif
        @if($delivery->delivery_status === 'Dispatched')
        <button class="btn btn-sm btn-outline-success" id="btnDeliver"><i class="fa fa-check-circle me-1"></i>Mark Delivered</button>
        @endif
        <a href="{{ route('nas-trading.deliveries.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-info-circle me-2"></i> Delivery Information</div>
            <div class="info-body">
                <div class="row g-2">
                    @php $fields = [
                        ['Delivery No', $delivery->delivery_no],
                        ['Bill No', $delivery->bill_no ?? '-'],
                        ['LC No', $delivery->lc_no ?? '-'],
                        ['Customer', $delivery->customer_name ?? '-'],
                        ['Delivery Date', $delivery->delivery_date?->format('d-M-Y') ?? '-'],
                        ['Vehicle No', $delivery->vehicle_no ?? '-'],
                        ['Driver', $delivery->driver_name ?? '-'],
                        ['Driver Phone', $delivery->driver_phone ?? '-'],
                    ] @endphp
                    @foreach($fields as [$label, $value])
                    <div class="col-md-3 col-6">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{{ $value }}</div>
                    </div>
                    @endforeach
                    <div class="col-12">
                        <div class="info-label">Delivery Address</div>
                        <div class="info-value">{{ $delivery->delivery_address }}</div>
                    </div>
                    @if($delivery->note)
                    <div class="col-12">
                        <div class="info-label">Note</div>
                        <div class="info-value">{{ $delivery->note }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-header"><i class="fa fa-boxes me-2"></i> Delivery Items</div>
            <table class="table table-bordered dt-sm mb-0">
                <thead><tr><th>#</th><th>Product Name</th><th>HS Code</th><th>Qty</th><th>Unit</th></tr></thead>
                <tbody>
                    @forelse($delivery->deliveryItems as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->hs_code }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->unit }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-history me-2"></i> Status Timeline</div>
            <div class="info-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success">Created</span>
                    <span style="font-size:.8rem">{{ $delivery->created_at?->format('d-M-Y H:i') }}</span>
                </div>
                @if($delivery->delivery_status !== 'Pending')
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark">Dispatched</span>
                    <span style="font-size:.8rem">{{ $delivery->delivery_date?->format('d-M-Y') }}</span>
                </div>
                @endif
                @if($delivery->delivery_status === 'Delivered')
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success">Delivered</span>
                    <span style="font-size:.8rem">{{ $delivery->updated_at?->format('d-M-Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function statusUpdate(url, msg) {
    Swal.fire({ title: msg, icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes' })
    .then(res => {
        if (res.isConfirmed) {
            $.post(url, { _token: '{{ csrf_token() }}' })
            .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()))
            .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
        }
    });
}
$('#btnDispatch').on('click', () => statusUpdate('{{ route('nas-trading.deliveries.dispatch', $delivery->id) }}', 'Mark as Dispatched?'));
$('#btnDeliver').on('click',  () => statusUpdate('{{ route('nas-trading.deliveries.deliver',  $delivery->id) }}', 'Mark as Delivered?'));
</script>
@endpush

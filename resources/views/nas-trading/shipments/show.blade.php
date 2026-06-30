@extends('nas-trading.layouts.app')
@section('title', 'Shipment — ' . $shipment->shipment_no)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.info-header { background:#1a6b60; color:#fff; padding:.45rem 1rem; font-size:.8rem; font-weight:700; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.72rem; color:#6c757d; text-transform:uppercase; letter-spacing:.03em; }
.info-value { font-size:.85rem; font-weight:600; color:#212529; }
.dt-sm th, .dt-sm td { font-size:.78rem; padding:.35rem .5rem; vertical-align:middle; }
.dt-sm th { background:#f8f9fa; font-weight:700; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-ship me-2 text-info"></i> {{ $shipment->shipment_no }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-trading.shipments.edit', $shipment->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i>Edit</a>
        <a href="{{ route('nas-trading.shipments.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-info-circle me-2"></i> Shipment Information</div>
            <div class="info-body">
                <div class="row g-2">
                    @php $fields = [
                        ['LC No (System)', $shipment->lc_no ?? '-'],
                        ['Customer', $shipment->customer_name ?? '-'],
                        ['Vessel', $shipment->vessel ?? '-'],
                        ['Shipping Mode', $shipment->shipping_mode ?? '-'],
                        ['BL Number', $shipment->bl_number ?? '-'],
                        ['BL Date', $shipment->bl_date?->format('d-M-Y') ?? '-'],
                        ['BL Qty / Unit', ($shipment->bl_qty ?? '-') . ' ' . ($shipment->unit ?? '')],
                        ['Bill of Entry', $shipment->bill_of_entry ?? '-'],
                        ['BE Date', $shipment->be_date?->format('d-M-Y') ?? '-'],
                        ['Arrival Date', $shipment->arrival_date?->format('d-M-Y') ?? '-'],
                        ['Freight Value', $shipment->freight_value ? number_format($shipment->freight_value, 2) : '-'],
                        ['CNF Value', $shipment->cnf_value ? number_format($shipment->cnf_value, 2) : '-'],
                        ['Duty Amount', $shipment->duty_amount ? number_format($shipment->duty_amount, 2) : '-'],
                        ['Duty Pay Date', $shipment->duty_pay_date?->format('d-M-Y') ?? '-'],
                        ['GRN Branch', $shipment->grn_branch ?? '-'],
                        ['Status', $shipment->shipment_status ?? '-'],
                    ] @endphp
                    @foreach($fields as [$label, $value])
                    <div class="col-md-3 col-6">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
                @if($shipment->remarks)
                <div class="mt-2 pt-2 border-top">
                    <div class="info-label">Remarks</div>
                    <div style="font-size:.82rem">{{ $shipment->remarks }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Duty Items --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-percent me-2"></i> Duty / Line Items</div>
            <div style="overflow-x:auto">
                <table class="table table-bordered dt-sm mb-0">
                    <thead><tr>
                        <th>#</th><th>Item</th><th>HS Code</th><th>GRN Qty</th><th>Rate</th><th>Assessable</th>
                        <th>CD%</th><th>CD Amt</th><th>SD%</th><th>SD Amt</th><th>VAT%</th><th>VAT Amt</th><th>AIT%</th><th>AIT Amt</th>
                    </tr></thead>
                    <tbody>
                        @forelse($shipment->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->item_name }}<br><small class="text-muted">{{ $item->description }}</small></td>
                            <td>{{ $item->hs_code }}</td>
                            <td>{{ $item->grn_qty }}</td>
                            <td>{{ $item->rate }}</td>
                            <td>{{ number_format($item->assessable, 2) }}</td>
                            <td>{{ $item->cd_pct }}</td><td>{{ number_format($item->cd_amt, 2) }}</td>
                            <td>{{ $item->sd_pct }}</td><td>{{ number_format($item->sd_amt, 2) }}</td>
                            <td>{{ $item->vat_pct }}</td><td>{{ number_format($item->vat_amt, 2) }}</td>
                            <td>{{ $item->ait_pct }}</td><td>{{ number_format($item->ait_amt, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="14" class="text-center text-muted">No items</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Costs --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-coins me-2"></i> Shipment Costs</div>
            <table class="table table-bordered dt-sm mb-0">
                <thead><tr><th>#</th><th>Cost Head</th><th class="text-end">Amount</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse($shipment->costs as $i => $cost)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $cost->cost_head }}</td>
                        <td class="text-end">{{ number_format($cost->amount, 2) }}</td>
                        <td>{{ $cost->remarks }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">No costs</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="info-card">
            <div class="info-header"><i class="fa fa-chart-pie me-2"></i> Summary</div>
            <div class="info-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" style="font-size:.8rem">Freight Value</td><td class="text-end fw-bold">{{ number_format($shipment->freight_value ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted" style="font-size:.8rem">CNF Value</td><td class="text-end fw-bold">{{ number_format($shipment->cnf_value ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted" style="font-size:.8rem">Duty Amount</td><td class="text-end fw-bold">{{ number_format($shipment->duty_amount ?? 0, 2) }}</td></tr>
                    @php $totalCosts = $shipment->costs->sum('amount') @endphp
                    <tr><td class="text-muted" style="font-size:.8rem">Other Costs</td><td class="text-end fw-bold">{{ number_format($totalCosts, 2) }}</td></tr>
                    <tr class="table-success"><td class="fw-bold">Grand Total</td><td class="text-end fw-bold">{{ number_format(($shipment->freight_value ?? 0) + ($shipment->cnf_value ?? 0) + ($shipment->duty_amount ?? 0) + $totalCosts, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

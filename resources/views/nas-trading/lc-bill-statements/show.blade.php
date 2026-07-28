@extends('nas-trading.layouts.app')
@section('title', 'LC Bill Statement — ' . $lcBillStatement->bill_no)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; overflow:hidden; margin-bottom:1rem; }
.info-header { background:#0c2340; color:#fff; padding:.45rem 1rem; font-size:.8rem; font-weight:700; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.72rem; color:#6c757d; text-transform:uppercase; }
.info-value { font-size:.85rem; font-weight:600; }
.stmt-table th { background:#1a6b60; color:#fff; font-size:.77rem; padding:.4rem .5rem; }
.stmt-table td { font-size:.8rem; padding:.35rem .5rem; vertical-align:middle; }

@media print {
    .top-navbar, .mobile-context-bar, .sidebar, footer, .d-print-none { display:none !important; }
    .main-content { margin-left:0 !important; padding:0.5rem !important; }
    body { background:#fff !important; font-size:9px !important; }
    .info-card { border:1px solid #ccc; border-radius:0; margin-bottom:.35rem; page-break-inside:avoid; }
    .info-header { background:#0c2340 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; padding:.3rem .6rem; font-size:.7rem; }
    .stmt-table th { background:#1a6b60 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; padding:.25rem .35rem; font-size:.65rem; }
    .stmt-table td { padding:.2rem .35rem; font-size:.65rem; }
    a[href]::after { content:none !important; }
    .container-fluid { padding:0 !important; }
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <div>
        <h4 class="mb-0"><i class="fa fa-file-alt me-2 text-success"></i> {{ $lcBillStatement->bill_no }}</h4>
        <small class="text-muted">{{ $lcBillStatement->bill_date?->format('d-M-Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        @if ($lcBillStatement->status === 'Draft')
            <a href="{{ route('nas-trading.lc-bill-statements.edit', $lcBillStatement->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-edit me-1"></i>Edit
            </a>
            <button class="btn btn-sm btn-outline-success" id="btnConfirm">
                <i class="fa fa-check me-1"></i>Confirm
            </button>
        @endif
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-print me-1"></i>View / Print
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('nas-trading.lc-bill-statements.print.cnf-dues', $lcBillStatement->id) }}" target="_blank"><i class="fa fa-file-invoice me-2 text-secondary"></i>C&amp;F Bill Statement</a></li>
                <li><a class="dropdown-item" href="{{ route('nas-trading.lc-bill-statements.print.commission-bill', $lcBillStatement->id) }}" target="_blank"><i class="fa fa-file-invoice-dollar me-2 text-secondary"></i>LC Commission Bill</a></li>
                <li><a class="dropdown-item" href="{{ route('nas-trading.lc-bill-statements.print.commission-statement', $lcBillStatement->id) }}" target="_blank"><i class="fa fa-file-alt me-2 text-secondary"></i>LC Commission Bill Statement</a></li>
                <li><a class="dropdown-item" href="{{ route('nas-trading.lc-bill-statements.print.bill-statement', $lcBillStatement->id) }}" target="_blank"><i class="fa fa-receipt me-2 text-secondary"></i>Bill Statement</a></li>
            </ul>
        </div>
        <a href="{{ route('nas-trading.lc-bill-statements.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

{{-- ======== PRINT LAYOUT ======== --}}
<div class="d-none d-print-block">
    <table style="width:100%;border-collapse:collapse;margin-bottom:.6rem;font-size:.72rem;">
        <tbody>
            <tr>
                <td style="width:25%;padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Bill No</td>
                <td style="width:25%;padding:.25rem .5rem;font-weight:600;color:#000;">{{ $lcBillStatement->bill_no }}</td>
                <td style="width:25%;padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Bill Date</td>
                <td style="width:25%;padding:.25rem .5rem;font-weight:600;color:#000;">{{ $lcBillStatement->bill_date?->format('d-M-Y') }}</td>
            </tr>
            <tr>
                <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Customer</td>
                <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $lcBillStatement->customer?->company_name ?? '-' }}</td>
                <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Status</td>
                <td style="padding:.25rem .5rem;font-weight:600;color:#000;">{{ $lcBillStatement->status }}</td>
            </tr>
            @if ($lcBillStatement->note)
            <tr>
                <td style="padding:.25rem .5rem;color:#555;font-size:.62rem;text-transform:uppercase;">Note</td>
                <td colspan="3" style="padding:.25rem .5rem;color:#000;">{{ $lcBillStatement->note }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:.72rem;">
        <thead>
            <tr>
                <th style="border:1px solid #999;padding:.25rem .4rem;width:30px;text-align:center;font-size:.65rem;font-weight:700;color:#000;">#</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">PFI No</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">LC No</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">LC Date</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;font-size:.65rem;font-weight:700;color:#000;">LC Retirement Date</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;text-align:right;font-size:.65rem;font-weight:700;color:#000;">LC RT Value (BDT)</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;text-align:right;font-size:.65rem;font-weight:700;color:#000;">Commission %</th>
                <th style="border:1px solid #999;padding:.25rem .4rem;text-align:right;font-size:.65rem;font-weight:700;color:#000;">Commission Amt (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lcBillStatement->items as $i => $item)
            <tr>
                <td style="border:1px solid #999;padding:.25rem .4rem;text-align:center;color:#000;">{{ $i + 1 }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;color:#000;">{{ $item->lc?->pfi_no ?? '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;color:#000;">{{ $item->lc?->lc_no ?? '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;color:#000;">{{ $item->lc?->lc_open_date?->format('d-M-Y') ?? '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;color:#000;">{{ $item->lc?->lc_retirement_date?->format('d-M-Y') ?? '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;text-align:right;color:#000;">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;text-align:right;color:#000;">{{ $item->lc?->lc_commission_percent ? number_format($item->lc->lc_commission_percent, 2).'%' : '-' }}</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;text-align:right;color:#000;">{{ $item->lc?->lc_commission ? number_format($item->lc->lc_commission, 2) : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="border:1px solid #999;padding:.3rem;text-align:center;color:#555;">No items</td>
            </tr>
            @endforelse
        </tbody>
        @if ($lcBillStatement->items->count())
        <tfoot>
            <tr>
                <td colspan="7" style="border:1px solid #999;padding:.25rem .4rem;text-align:right;font-weight:700;color:#000;">Total Commission</td>
                <td style="border:1px solid #999;padding:.25rem .4rem;text-align:right;font-weight:700;color:#000;">
                    {{ number_format($lcBillStatement->items->sum(fn($i) => $i->lc?->lc_commission ?? 0), 2) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
{{-- ======== END PRINT LAYOUT ======== --}}

{{-- ======== SCREEN LAYOUT ======== --}}
<div class="d-print-none">
    <div class="info-card">
        <div class="info-header"><i class="fa fa-info-circle me-2"></i> Statement Information</div>
        <div class="info-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="info-label">Bill No</div>
                    <div class="info-value">{{ $lcBillStatement->bill_no }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Bill Date</div>
                    <div class="info-value">{{ $lcBillStatement->bill_date?->format('d-M-Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Customer</div>
                    <div class="info-value">{{ $lcBillStatement->customer?->company_name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @php $badge = ['Draft' => 'secondary', 'Confirmed' => 'success', 'Paid' => 'primary'] @endphp
                        <span class="badge bg-{{ $badge[$lcBillStatement->status] ?? 'secondary' }}">{{ $lcBillStatement->status }}</span>
                    </div>
                </div>
                @if ($lcBillStatement->note)
                <div class="col-md-6">
                    <div class="info-label">Note</div>
                    <div class="info-value">{{ $lcBillStatement->note }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="info-card">
        <div class="info-header"><i class="fa fa-file-contract me-2"></i> LC Entries</div>
        <div style="overflow-x:auto">
            <table class="table table-bordered stmt-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>PFI No</th>
                        <th>LC No</th>
                        <th>LC Date</th>
                        <th>LC Retirement Date</th>
                        <th class="text-end">LC RT Value (BDT)</th>
                        <th class="text-end">Commission %</th>
                        <th class="text-end">Commission Amt (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lcBillStatement->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->lc?->pfi_no ?? '-' }}</td>
                        <td>{{ $item->lc?->lc_no ?? '-' }}</td>
                        <td>{{ $item->lc?->lc_open_date?->format('d-M-Y') ?? '-' }}</td>
                        <td>{{ $item->lc?->lc_retirement_date?->format('d-M-Y') ?? '-' }}</td>
                        <td class="text-end fw-bold">{{ $item->lc?->lc_rt_value ? number_format($item->lc->lc_rt_value, 2) : '-' }}</td>
                        <td class="text-end">{{ $item->lc?->lc_commission_percent ? number_format($item->lc->lc_commission_percent, 2).'%' : '-' }}</td>
                        <td class="text-end fw-bold">{{ $item->lc?->lc_commission ? number_format($item->lc->lc_commission, 2) : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No LC entries</td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($lcBillStatement->items->count())
                <tfoot>
                    <tr style="background:#f0f4f8">
                        <td colspan="7" class="text-end fw-bold" style="font-size:.82rem">Total Commission (BDT)</td>
                        <td class="text-end fw-bold text-success" style="font-size:.9rem">
                            {{ number_format($lcBillStatement->items->sum(fn($i) => $i->lc?->lc_commission ?? 0), 2) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
{{-- ======== END SCREEN LAYOUT ======== --}}
@endsection

@push('scripts')
<script>
$('#btnConfirm').on('click', function () {
    Swal.fire({ title: 'Confirm this statement?', text: 'Status will move to Confirmed.', icon: 'question', showCancelButton: true, confirmButtonColor: '#198754', confirmButtonText: 'Yes, Confirm' })
        .then(res => {
            if (res.isConfirmed) {
                $.post('{{ route('nas-trading.lc-bill-statements.confirm', $lcBillStatement->id) }}', { _token: '{{ csrf_token() }}' })
                    .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => location.reload()))
                    .fail(() => Swal.fire({ icon: 'error', title: 'Failed.' }));
            }
        });
});
</script>
@endpush

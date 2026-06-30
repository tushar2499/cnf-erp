@extends('nas-trading.layouts.app')
@section('title', 'LC — ' . $lc->lc_no_system)
@push('styles')
<style>
.info-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; }
.info-header { background:#0c2340; color:#fff; padding:.5rem 1rem; font-size:.82rem; font-weight:700; border-radius:.5rem .5rem 0 0; }
.info-body { padding:.75rem 1rem; }
.info-label { font-size:.75rem; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.1rem; }
.info-value { font-size:.85rem; color:#111; font-weight:500; }
.section-divider { border-top:1px solid #e5e7eb; margin:.5rem 0; }
.exp-table th { background:#1a6b60; color:#fff; font-size:.77rem; padding:.4rem .6rem; }
.exp-table td { font-size:.8rem; padding:.35rem .6rem; vertical-align:middle; }
.badge-posting { font-size:.7rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="fa fa-file-contract me-2 text-info"></i>
        {{ $lc->lc_no_system }}
        <small class="ms-2">
            @if($lc->lc_status === 'Open') <span class="badge bg-success">Open</span>
            @elseif($lc->lc_status === 'Closed') <span class="badge bg-secondary">Closed</span>
            @elseif($lc->lc_status === 'Cancelled') <span class="badge bg-danger">Cancelled</span>
            @else <span class="badge bg-warning text-dark">{{ $lc->lc_status }}</span>
            @endif
        </small>
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('nas-trading.lcs.edit', $lc->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit me-1"></i> Edit</a>
        <a href="{{ route('nas-trading.lcs.generate-bill', $lc->id) }}" class="btn btn-sm btn-success"><i class="fa fa-file-invoice-dollar me-1"></i> Generate Bill</a>
        <a href="{{ route('nas-trading.lcs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<div class="row g-3">
    {{-- Left column: LC details --}}
    <div class="col-md-8">
        {{-- Identification --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-id-card me-2"></i> Identification</div>
            <div class="info-body">
                <div class="row g-2">
                    @php $fields = [
                        'LC No (System)' => $lc->lc_no_system,
                        'LC No (Bank)'   => $lc->lc_no,
                        'PFI No'         => $lc->pfi_no,
                        'PFI Date'       => $lc->pfi_date?->format('d-M-Y'),
                        'LC Open Date'   => $lc->lc_open_date?->format('d-M-Y'),
                        'LC Expiry'      => $lc->lc_expiry_date?->format('d-M-Y'),
                        'LC Type'        => $lc->lc_type,
                        'Month'          => $lc->month,
                        'Customer'       => $lc->customer_name,
                        'Shipment From'  => $lc->shipment_from,
                        'Last Shipment'  => $lc->last_shipment_date?->format('d-M-Y'),
                        'Docs Received'  => $lc->shipping_docs_received_date?->format('d-M-Y'),
                    ] @endphp
                    @foreach($fields as $label => $val)
                    <div class="col-md-3">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{{ $val ?? '—' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Supplier --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-industry me-2"></i> Supplier & Goods</div>
            <div class="info-body">
                <div class="row g-2">
                    <div class="col-md-4"><div class="info-label">Supplier</div><div class="info-value">{{ $lc->supplier_name ?? '—' }}</div></div>
                    <div class="col-md-3"><div class="info-label">Country</div><div class="info-value">{{ $lc->supplier_country ?? '—' }}</div></div>
                    <div class="col-md-3"><div class="info-label">PO Date</div><div class="info-value">{{ $lc->customer_po_date?->format('d-M-Y') ?? '—' }}</div></div>
                    <div class="col-12"><div class="info-label">Item Description</div><div class="info-value">{{ $lc->item_description ?? '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Financials --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-dollar-sign me-2"></i> LC Financials</div>
            <div class="info-body">
                <div class="row g-2">
                    @php $finFields = [
                        'PFI Value'     => ($lc->currency ?? '') . ' ' . number_format((float)$lc->pfi_value, 4),
                        'LC OP Rate'    => $lc->lc_open_rate,
                        'Margin %'      => $lc->margin_percent ? $lc->margin_percent . '%' : '—',
                        'LC Margin Amt' => $lc->lc_margin_amt ? 'BDT ' . number_format((float)$lc->lc_margin_amt, 2) : '—',
                        'LC Open Cost'  => $lc->lc_open_cost_bdt ? 'BDT ' . number_format((float)$lc->lc_open_cost_bdt, 2) : '—',
                        'Freight Value' => $lc->freight_value,
                        'LC Value'      => $lc->lc_value,
                        'Amount BDT'    => $lc->amount_bdt ? 'BDT ' . number_format((float)$lc->amount_bdt, 2) : '—',
                        'Total LC Cost' => $lc->total_lc_cost ? 'BDT ' . number_format((float)$lc->total_lc_cost, 2) : '—',
                        'Landed Cost'   => $lc->landed_cost ? 'BDT ' . number_format((float)$lc->landed_cost, 2) : '—',
                    ] @endphp
                    @foreach($finFields as $label => $val)
                    <div class="col-md-3">
                        <div class="info-label">{{ $label }}</div>
                        <div class="info-value">{{ $val ?? '—' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        @if($lc->items->count())
        <div class="info-card">
            <div class="info-header"><i class="fa fa-boxes me-2"></i> Product Line Items</div>
            <div style="overflow-x:auto">
                <table class="table table-bordered exp-table mb-0 w-100">
                    <thead><tr><th>#</th><th>Product</th><th>Code</th><th>HS Code</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Amount</th><th>Curr.</th></tr></thead>
                    <tbody>
                        @foreach($lc->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->product_code }}</td>
                            <td>{{ $item->hs_code }}</td>
                            <td>{{ $item->qty_count }}</td>
                            <td>{{ $item->qty_unit }}</td>
                            <td>{{ number_format((float)$item->unit_price, 4) }}</td>
                            <td>{{ number_format((float)$item->line_amount, 4) }}</td>
                            <td>{{ $item->currency }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Right column: Expenses --}}
    <div class="col-md-4">
        <div class="info-card">
            <div class="info-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-receipt me-2"></i> LC Expenses</span>
                <button class="btn btn-sm btn-light py-0 px-2" style="font-size:.72rem" data-bs-toggle="modal" data-bs-target="#expenseModal">
                    <i class="fa fa-plus me-1"></i>Add
                </button>
            </div>
            <div class="info-body p-0">
                @if($lc->expenses->count())
                <table class="table table-sm exp-table mb-0 w-100">
                    <thead><tr><th>Head</th><th>Type</th><th>Amount</th><th>Date</th><th></th></tr></thead>
                    <tbody id="expenseRows">
                    @foreach($lc->expenses as $exp)
                    <tr data-id="{{ $exp->id }}">
                        <td>{{ $exp->expense_head_name ?? '—' }}</td>
                        <td><span class="badge badge-posting bg-secondary">{{ $exp->posting_type }}</span></td>
                        <td>{{ number_format((float)$exp->amount, 2) }}</td>
                        <td>{{ $exp->expense_date?->format('d-M-Y') }}</td>
                        <td>
                            <button class="btn btn-outline-danger btn-xs btn-del-exp" data-id="{{ $exp->id }}" style="padding:1px 5px;font-size:.68rem"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                <div id="noExpenses" class="text-center text-muted py-3" style="font-size:.82rem"><i class="fa fa-receipt fa-2x mb-2 d-block opacity-50"></i>No expenses yet.</div>
                @endif
            </div>
            @if($lc->expenses->count())
            <div class="info-body border-top pt-2">
                <div class="d-flex justify-content-between">
                    <strong style="font-size:.82rem">Total Expenses:</strong>
                    <strong style="font-size:.82rem">BDT {{ number_format($lc->expenses->sum('amount'), 2) }}</strong>
                </div>
            </div>
            @endif
        </div>

        {{-- Quick stats --}}
        <div class="info-card">
            <div class="info-header"><i class="fa fa-chart-bar me-2"></i> Quick Summary</div>
            <div class="info-body">
                @php
                    $totalExpenses = $lc->expenses->sum('amount');
                    $lcCost = (float)($lc->total_lc_cost ?? 0);
                @endphp
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="font-size:.82rem">PFI Value</span>
                    <span style="font-size:.82rem font-weight:600">{{ $lc->currency }} {{ number_format((float)($lc->pfi_value ?? 0), 4) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="font-size:.82rem">Amount BDT</span>
                    <span style="font-size:.82rem">BDT {{ number_format((float)($lc->amount_bdt ?? 0), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="font-size:.82rem">Total LC Cost</span>
                    <span style="font-size:.82rem">BDT {{ number_format($lcCost, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="font-size:.82rem">Total Expenses</span>
                    <span style="font-size:.82rem">BDT {{ number_format($totalExpenses, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <strong style="font-size:.85rem">Grand Total</strong>
                    <strong style="font-size:.85rem; color:#1a6b60">BDT {{ number_format($lcCost + $totalExpenses, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#0c2340; color:#fff;">
                <h6 class="modal-title"><i class="fa fa-plus me-2"></i>Add LC Expense</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <input type="hidden" name="lc_id" value="{{ $lc->id }}">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Expense Head</label>
                        <select name="expense_head_id" id="expHeadSel" class="form-select form-select-sm">
                            <option value="">Select Expense Head...</option>
                            @foreach($expenseHeads as $eh)
                            <option value="{{ $eh->id }}" data-name="{{ $eh->name }}">{{ $eh->name }} ({{ $eh->category }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Amount (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Posting Type <span class="text-danger">*</span></label>
                            <select name="posting_type" id="postingType" class="form-select form-select-sm" required>
                                <option value="Other">Other</option>
                                <option value="Insurance">Insurance</option>
                                <option value="LC Opening">LC Opening</option>
                                <option value="LC Additional">LC Additional</option>
                                <option value="Doc Retirement">Doc Retirement</option>
                                <option value="Post Shipment">Post Shipment</option>
                                <option value="CNF Expenses">CNF Expenses</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.82rem;font-weight:600">Sub-type</label>
                            <select name="posting_sub_type" id="postingSubType" class="form-select form-select-sm">
                                <option value="">N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Reference</label>
                        <input type="text" name="reference" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:.82rem;font-weight:600">Note</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveExpense"><i class="fa fa-save me-1"></i>Save Expense</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Posting sub-types
var subTypes = {
    'Doc Retirement': ['Bank Pay','IFDBC','Acceptance','LTR','STL','ATR','Margin Adjust'],
};
$('#postingType').on('change', function () {
    var opts = subTypes[$(this).val()] || [];
    var sel = $('#postingSubType').empty().append('<option value="">N/A</option>');
    opts.forEach(o => sel.append(`<option value="${o}">${o}</option>`));
});

$('#btnSaveExpense').on('click', function () {
    if (!$('[name=amount]').val() || !$('[name=posting_type]').val()) {
        Swal.fire({ icon: 'warning', title: 'Amount and Posting Type are required.' }); return;
    }
    var btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    $.ajax({
        url: '{{ route('nas-trading.lc-expenses.store') }}',
        method: 'POST',
        data: $('#expenseForm').serialize() + '&_token={{ csrf_token() }}',
    })
    .done(function (r) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: r.message, showConfirmButton: false, timer: 2000 });
        $('#expenseModal').modal('hide');
        // Add row to table
        var exp = r.expense;
        $('#noExpenses').hide();
        if (!$('#expenseRows').length) {
            // Reload page to show table
            location.reload();
        } else {
            $('#expenseRows').append(`
            <tr data-id="${exp.id}">
                <td>${exp.expense_head_name || '—'}</td>
                <td><span class="badge badge-posting bg-secondary">${exp.posting_type}</span></td>
                <td>${parseFloat(exp.amount).toFixed(2)}</td>
                <td>${exp.expense_date || ''}</td>
                <td><button class="btn btn-outline-danger btn-xs btn-del-exp" data-id="${exp.id}" style="padding:1px 5px;font-size:.68rem"><i class="fa fa-times"></i></button></td>
            </tr>`);
        }
        $('#expenseForm')[0].reset();
        $('[name=expense_date]').val('{{ now()->format('Y-m-d') }}');
    })
    .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Something went wrong.' });
    })
    .always(() => btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i>Save Expense'));
});

$(document).on('click', '.btn-del-exp', function () {
    var id = $(this).data('id'), row = $(this).closest('tr');
    Swal.fire({ title: 'Delete this expense?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes' })
        .then(res => {
            if (res.isConfirmed) {
                $.ajax({ url: '/nas-trading/lc-expenses/' + id, method: 'DELETE', data: { _token: $('meta[name="csrf-token"]').attr('content') } })
                    .done(r => { Swal.fire({ icon: 'success', title: r.message, timer: 1200, showConfirmButton: false }); row.remove(); })
                    .fail(() => Swal.fire({ icon: 'error', title: 'Delete failed.' }));
            }
        });
});
</script>
@endpush

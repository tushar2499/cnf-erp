@extends('nas-trading.layouts.app')
@section('title', 'Edit Shipment — ' . $shipment->shipment_no)
@push('styles')
<style>
.shp-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.shp-header { background:#1a6b60; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.shp-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.duty-table th, .duty-table td { font-size:.76rem; padding:.3rem .4rem; vertical-align:middle; }
.duty-table th { background:#e9ecef; white-space:nowrap; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-edit me-2 text-info"></i> Edit {{ $shipment->shipment_no }}</h4>
    <a href="{{ route('nas-trading.shipments.show', $shipment->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<form id="shpForm">
    @csrf
    @method('PUT')

    <div class="shp-card">
        <div class="shp-header"><i class="fa fa-link me-2"></i> LC Reference</div>
        <div class="shp-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">LC</label>
                    <select id="lcSelect" name="lc_id" class="form-select form-select-sm" required>
                        @if($shipment->lc_id)
                        <option value="{{ $shipment->lc_id }}" selected>{{ $shipment->lc_no ?? 'LC-' . $shipment->lc_id }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="lc_no" id="lcNo" value="{{ $shipment->lc_no }}">
                    <input type="hidden" name="pfi_no" id="pfiNo" value="{{ $shipment->pfi_no }}">
                    <input type="hidden" name="customer_id" id="customerId" value="{{ $shipment->customer_id }}">
                    <input type="hidden" name="customer_name" id="customerName" value="{{ $shipment->customer_name }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <input type="text" id="customerDisplay" class="form-control form-control-sm bg-light" readonly value="{{ $shipment->customer_name }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">PFI No</label>
                    <input type="text" class="form-control form-control-sm bg-light" readonly value="{{ $shipment->pfi_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipment No</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $shipment->shipment_no }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="shp-card">
        <div class="shp-header"><i class="fa fa-info-circle me-2"></i> Shipment Details</div>
        <div class="shp-body">
            <div class="row g-2">
                @php $txt = ['vessel','bill_of_entry','bl_number','unit','psi_no','grn_branch','remarks'];
                     $num = ['bl_qty','freight_value','cnf_value','duty_amount'];
                     $dt  = ['arrival_date','bl_date','be_date','duty_pay_date']; @endphp
                <div class="col-md-3">
                    <label class="form-label">Vessel / Flight</label>
                    <input type="text" name="vessel" class="form-control form-control-sm" value="{{ $shipment->vessel }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipping Mode</label>
                    <select name="shipping_mode" class="form-select form-select-sm">
                        @foreach(['Sea','Air','Land','Rail'] as $m)
                        <option {{ $shipment->shipping_mode === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Port of Discharge</label>
                    <select name="port_of_disc_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($ports as $p)
                        <option value="{{ $p->id }}" {{ $shipment->port_of_disc_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Arrival Date</label>
                    <input type="date" name="arrival_date" class="form-control form-control-sm" value="{{ $shipment->arrival_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">BL Number</label>
                    <input type="text" name="bl_number" class="form-control form-control-sm" value="{{ $shipment->bl_number }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">BL Date</label>
                    <input type="date" name="bl_date" class="form-control form-control-sm" value="{{ $shipment->bl_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">BL Qty</label>
                    <input type="number" name="bl_qty" class="form-control form-control-sm" step="0.001" value="{{ $shipment->bl_qty }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control form-control-sm" value="{{ $shipment->unit }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bill of Entry</label>
                    <input type="text" name="bill_of_entry" class="form-control form-control-sm" value="{{ $shipment->bill_of_entry }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">BE Date</label>
                    <input type="date" name="be_date" class="form-control form-control-sm" value="{{ $shipment->be_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Freight Value</label>
                    <input type="number" name="freight_value" class="form-control form-control-sm" step="0.01" value="{{ $shipment->freight_value }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CNF Value</label>
                    <input type="number" name="cnf_value" class="form-control form-control-sm" step="0.01" value="{{ $shipment->cnf_value }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duty Amount</label>
                    <input type="number" name="duty_amount" class="form-control form-control-sm" step="0.01" value="{{ $shipment->duty_amount }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duty Pay Date</label>
                    <input type="date" name="duty_pay_date" class="form-control form-control-sm" value="{{ $shipment->duty_pay_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI Company</label>
                    <select name="psi_company_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($psiCompanies as $p)
                        <option value="{{ $p->id }}" {{ $shipment->psi_company_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI No</label>
                    <input type="text" name="psi_no" class="form-control form-control-sm" value="{{ $shipment->psi_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CNF Agent</label>
                    <select name="cnf_agent_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($cnfAgents as $c)
                        <option value="{{ $c->id }}" {{ $shipment->cnf_agent_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transport Co.</label>
                    <select name="transport_co_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($transportCos as $t)
                        <option value="{{ $t->id }}" {{ $shipment->transport_co_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">GRN Branch</label>
                    <input type="text" name="grn_branch" class="form-control form-control-sm" value="{{ $shipment->grn_branch }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipment Status</label>
                    <select name="shipment_status" class="form-select form-select-sm">
                        @foreach(['Pending','In Transit','Arrived','Cleared','Delivered'] as $s)
                        <option {{ $shipment->shipment_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ $shipment->remarks }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="shp-card">
        <div class="shp-header"><i class="fa fa-table me-2"></i> Details</div>
        <div class="shp-body p-0">
            <ul class="nav nav-tabs px-3 pt-2">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabItems">Duty / Line Items</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabCosts">Costs</a></li>
            </ul>
            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="tabItems">
                    <div class="mb-2 text-end"><button type="button" class="btn btn-sm btn-outline-success" id="btnAddItem" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Row</button></div>
                    <div style="overflow-x:auto">
                        <table class="table table-bordered duty-table mb-0 w-100" id="dutyTable">
                            <thead><tr>
                                <th style="width:35px">#</th><th style="min-width:120px">Item</th><th style="min-width:100px">Desc</th><th style="width:85px">HS Code</th>
                                <th style="width:65px">GRN Qty</th><th style="width:70px">Rate</th><th style="width:85px">Assessable</th>
                                <th style="width:50px">CD%</th><th style="width:65px">CD Amt</th>
                                <th style="width:50px">SD%</th><th style="width:65px">SD Amt</th>
                                <th style="width:50px">VAT%</th><th style="width:65px">VAT Amt</th>
                                <th style="width:50px">AIT%</th><th style="width:65px">AIT Amt</th>
                                <th style="width:35px"></th>
                            </tr></thead>
                            <tbody id="dutyBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tabCosts">
                    <div class="mb-2 text-end"><button type="button" class="btn btn-sm btn-outline-success" id="btnAddCost" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Row</button></div>
                    <table class="table table-bordered mb-0" id="costsTable">
                        <thead><tr><th style="width:35px">#</th><th>Cost Head</th><th style="width:130px">Amount</th><th>Remarks</th><th style="width:35px"></th></tr></thead>
                        <tbody id="costsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Update Shipment</button>
        <a href="{{ route('nas-trading.shipments.show', $shipment->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var dutyRowIdx = 0, costRowIdx = 0;
var existingItems = @json($shipment->items);
var existingCosts = @json($shipment->costs);

function addDutyRow(d) {
    d = d || {};
    var i = dutyRowIdx++;
    var pcts = ['cd','rd','sd','vat','ait'];
    var pcols = pcts.map(p =>
        `<td><input type="number" name="items[${i}][${p}_pct]" class="form-control form-control-sm duty-pct" data-pct="${p}" data-row="${i}" value="${d[p+'_pct']||''}" step="0.01" min="0"></td>
         <td><input type="number" name="items[${i}][${p}_amt]" class="form-control form-control-sm" id="${p}_amt_${i}" value="${d[p+'_amt']||''}" step="0.01" readonly></td>`
    ).join('');
    $('#dutyBody').append(`<tr>
        <td class="text-center row-num">${dutyRowIdx}</td>
        <td><input type="text" name="items[${i}][item_name]" class="form-control form-control-sm" value="${d.item_name||''}"></td>
        <td><input type="text" name="items[${i}][description]" class="form-control form-control-sm" value="${d.description||''}"></td>
        <td><input type="text" name="items[${i}][hs_code]" class="form-control form-control-sm" value="${d.hs_code||''}"></td>
        <td><input type="number" name="items[${i}][grn_qty]" class="form-control form-control-sm duty-qty" data-row="${i}" value="${d.grn_qty||''}" step="0.001"></td>
        <td><input type="number" name="items[${i}][rate]" class="form-control form-control-sm duty-rate" data-row="${i}" value="${d.rate||''}" step="0.0001"></td>
        <td><input type="number" name="items[${i}][assessable]" class="form-control form-control-sm duty-assessable" data-row="${i}" id="assessable_${i}" value="${d.assessable||''}" step="0.01"></td>
        ${pcols}
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-duty p-0" style="width:22px;height:22px"><i class="fa fa-times" style="font-size:.6rem"></i></button></td>
    </tr>`);
}

function calcDuty(row) {
    var assessable = parseFloat($(`#assessable_${row}`).val()) || 0;
    ['cd','rd','sd','vat','ait'].forEach(p => {
        var pct = parseFloat($(`[name="items[${row}][${p}_pct]"]`).val()) || 0;
        $(`#${p}_amt_${row}`).val((assessable * pct / 100).toFixed(2));
    });
}

function addCostRow(d) {
    d = d || {};
    var i = costRowIdx++;
    $('#costsBody').append(`<tr>
        <td class="text-center row-num">${costRowIdx}</td>
        <td><input type="text" name="costs[${i}][cost_head]" class="form-control form-control-sm" value="${d.cost_head||''}"></td>
        <td><input type="number" name="costs[${i}][amount]" class="form-control form-control-sm" value="${d.amount||''}" step="0.01"></td>
        <td><input type="text" name="costs[${i}][remarks]" class="form-control form-control-sm" value="${d.remarks||''}"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-cost p-0" style="width:22px;height:22px"><i class="fa fa-times" style="font-size:.6rem"></i></button></td>
    </tr>`);
}

$(function () {
    $('#lcSelect').select2({
        width: '100%', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.shipments.search-lc') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#lcNo').val(d.lc_no||''); $('#pfiNo').val(d.pfi_no||'');
        $('#customerId').val(d.customer_id||''); $('#customerName').val(d.customer_name||'');
        $('#customerDisplay').val(d.customer_name||'');
    });

    $(document).on('input', '.duty-qty, .duty-rate', function () {
        var row = $(this).data('row');
        $(`#assessable_${row}`).val(((parseFloat($(`[name="items[${row}][grn_qty]"]`).val())||0) * (parseFloat($(`[name="items[${row}][rate]"]`).val())||0)).toFixed(2));
        calcDuty(row);
    });
    $(document).on('input', '.duty-pct, .duty-assessable', function () { calcDuty($(this).data('row')); });

    $('#btnAddItem').on('click', () => addDutyRow());
    $(document).on('click', '.btn-remove-duty', function () { $(this).closest('tr').remove(); $('#dutyBody tr').each((i,tr) => $(tr).find('.row-num').text(i+1)); });
    $('#btnAddCost').on('click', () => addCostRow());
    $(document).on('click', '.btn-remove-cost', function () { $(this).closest('tr').remove(); $('#costsBody tr').each((i,tr) => $(tr).find('.row-num').text(i+1)); });

    existingItems.forEach(d => addDutyRow(d));
    if (!existingItems.length) addDutyRow();
    existingCosts.forEach(d => addCostRow(d));
    if (!existingCosts.length) addCostRow();

    $('#shpForm').on('submit', function (e) {
        e.preventDefault();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url: '{{ route('nas-trading.shipments.update', $shipment->id) }}', method: 'POST', data: $(this).serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Update Shipment'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error' }); });
    });
});
</script>
@endpush

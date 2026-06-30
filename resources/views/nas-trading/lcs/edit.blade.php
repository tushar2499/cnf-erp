@extends('nas-trading.layouts.app')
@section('title', 'Edit LC — ' . $lc->lc_no_system)
@push('styles')
<style>
.lc-card { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; margin-bottom:1rem; overflow:hidden; }
.lc-section-header { background:#1a6b60; color:#fff; padding:.5rem 1rem; font-size:.8rem; font-weight:700; }
.lc-section-body { padding:1rem; }
.form-label { font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.2rem; }
.form-control, .form-select { font-size:.82rem; }
.items-table th { background:#e9ecef; font-size:.77rem; padding:.4rem .5rem; }
.items-table td { padding:.3rem .5rem; vertical-align:middle; }
.items-table .form-control-sm, .items-table .form-select-sm { font-size:.78rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-edit me-2 text-info"></i> Edit {{ $lc->lc_no_system }}</h4>
    <a href="{{ route('nas-trading.lcs.show', $lc->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<form id="lcForm">
    @csrf
    @method('PUT')
    {{-- Reuse identical sections from create, pre-filled --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-id-card me-2"></i> Section 1 — Identification</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">LC No (System)</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $lc->lc_no_system }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select id="customerSelect" class="form-select form-select-sm" name="customer_id" required>
                        @if($lc->customer_id)
                        <option value="{{ $lc->customer_id }}" selected>{{ $lc->customer_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="customer_name" id="customerName" value="{{ $lc->customer_name }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PFI No <span class="text-danger">*</span></label>
                    <input type="text" name="pfi_no" class="form-control form-control-sm" value="{{ $lc->pfi_no }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">PFI Date</label>
                    <input type="date" name="pfi_date" class="form-control form-control-sm" value="{{ $lc->pfi_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC No (Bank)</label>
                    <input type="text" name="lc_no" class="form-control form-control-sm" value="{{ $lc->lc_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Open Date</label>
                    <input type="date" name="lc_open_date" class="form-control form-control-sm" value="{{ $lc->lc_open_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Expiry Date</label>
                    <input type="date" name="lc_expiry_date" class="form-control form-control-sm" value="{{ $lc->lc_expiry_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Type</label>
                    <select name="lc_type" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach(['TT/LCA','Sight','DF'] as $t)
                        <option value="{{ $t }}" {{ $lc->lc_type === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">LC Status</label>
                    <select name="lc_status" class="form-select form-select-sm">
                        @foreach(['Open','Closed','Cancelled','Amended'] as $s)
                        <option value="{{ $s }}" {{ $lc->lc_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="text" name="month" class="form-control form-control-sm" value="{{ $lc->month }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipment From</label>
                    <input type="text" name="shipment_from" class="form-control form-control-sm" value="{{ $lc->shipment_from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Shipment Date</label>
                    <input type="date" name="last_shipment_date" class="form-control form-control-sm" value="{{ $lc->last_shipment_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shipping Docs Received</label>
                    <input type="date" name="shipping_docs_received_date" class="form-control form-control-sm" value="{{ $lc->shipping_docs_received_date?->format('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Sections 2-8 with pre-filled values --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-industry me-2"></i> Section 2 — Supplier & Goods</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <select id="supplierSelect" class="form-select form-select-sm" name="supplier_id">
                        @if($lc->supplier_id)
                        <option value="{{ $lc->supplier_id }}" selected>{{ $lc->supplier_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="supplier_name" id="supplierName" value="{{ $lc->supplier_name }}">
                    <input type="hidden" name="supplier_country" id="supplierCountry" value="{{ $lc->supplier_country }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <input type="text" name="supplier_country" id="supplierCountryDisplay" class="form-control form-control-sm bg-light" readonly value="{{ $lc->supplier_country }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Importer</label>
                    <select name="importer_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($importers as $imp)
                        <option value="{{ $imp->id }}" {{ $lc->importer_id == $imp->id ? 'selected' : '' }}>{{ $imp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Customer PO Date</label>
                    <input type="date" name="customer_po_date" class="form-control form-control-sm" value="{{ $lc->customer_po_date?->format('Y-m-d') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Item Description</label>
                    <textarea name="item_description" class="form-control form-control-sm" rows="2">{{ $lc->item_description }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-dollar-sign me-2"></i> Section 3 — LC Financials</div>
        <div class="lc-section-body">
            <div class="row g-2">
                @php $fin = [
                    ['pfi_value','PFI Value','0.0001'],['lc_open_rate','LC OP Rate','0.0001'],
                    ['margin_percent','Margin %','0.0001'],['lc_margin_amt','LC Margin Amt','0.01'],
                    ['lc_open_cost_bdt','LC Opening Cost BDT','0.01'],['freight_value','Freight Value','0.0001'],
                    ['lc_value','LC Value (calc)','0.0001'],['amount_bdt','Amount BDT (calc)','0.01'],
                    ['total_lc_cost','Total LC Cost','0.01'],['landed_cost','Landed Cost','0.01'],
                ] @endphp
                <div class="col-md-2">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select form-select-sm">
                        @foreach(['USD','EUR','GBP','CNY','BDT'] as $c)
                        <option value="{{ $c }}" {{ $lc->currency === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                @foreach($fin as [$name,$label,$step])
                <div class="col-md-2">
                    <label class="form-label">{{ $label }}</label>
                    <input type="number" name="{{ $name }}" id="{{ Str::camel($name) }}" class="form-control form-control-sm {{ in_array($name,['lc_value','amount_bdt','lc_margin_amt']) ? 'bg-light' : '' }}" {{ in_array($name,['lc_value','amount_bdt','lc_margin_amt']) ? 'readonly' : '' }} step="{{ $step }}" value="{{ $lc->$name }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Remaining sections 4-8 abbreviated for edit --}}
    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-file-alt me-2"></i> Sections 4-7 — Posting & Financial Details</div>
        <div class="lc-section-body">
            <div class="row g-2">
                @php $misc = [
                    'doc_rt_rate','lc_rt_value','lc_charge_posting',
                    'advance_received_bdt','advance_date','advance_posting',
                    'rest_amount_bdt','rest_amount_date','rest_amount_posting','total_received_bdt',
                    'lc_closing_bill','lc_closing_bill_date',
                    'duty_advance','duty_advance_date','duty_advance_posting',
                    'bill_of_entry_no','bill_of_entry_date','customs_duty','customs_duty_posting',
                    'cnf_party','cnf_total_cost','cnf_cost_posting',
                    'payable_receivable','received_amount','received_date',
                    'vat_return','vat_return_date','vat_return_posting',
                    'income_tax','bank_statement_amt','lc_commission','lc_commission_date',
                    'sales_amount','sales_posting','coss_amount','coss_posting',
                ] @endphp
                @foreach($misc as $fname)
                @php $isDate = Str::contains($fname, ['_date']); $isNum = !$isDate && !Str::contains($fname, ['posting','party','no','status','types','mode','note']); @endphp
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.75rem">{{ ucwords(str_replace('_',' ',$fname)) }}</label>
                    <input type="{{ $isDate ? 'date' : ($isNum ? 'number' : 'text') }}" name="{{ $fname }}" class="form-control form-control-sm" value="{{ $isDate ? $lc->{$fname}?->format('Y-m-d') : $lc->{$fname} }}" {{ $isNum ? 'step=0.01' : '' }}>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="lc-card">
        <div class="lc-section-header"><i class="fa fa-university me-2"></i> Section 8 — Bank & Documents</div>
        <div class="lc-section-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Opening Bank</label>
                    <select name="opening_bank_id" class="form-select form-select-sm">
                        <option value="">Select Bank...</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ $lc->opening_bank_id == $bank->id ? 'selected' : '' }}>{{ $bank->name }}{{ $bank->branch ? ' - '.$bank->branch : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Port of Destination</label>
                    <select name="port_of_dest_id" class="form-select form-select-sm">
                        <option value="">Select Port...</option>
                        @foreach($ports as $port)
                        <option value="{{ $port->id }}" {{ $lc->port_of_dest_id == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI Company</label>
                    <select name="psi_company_id" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @foreach($psiCompanies as $psi)
                        <option value="{{ $psi->id }}" {{ $lc->psi_company_id == $psi->id ? 'selected' : '' }}>{{ $psi->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Doc Status</label>
                    <select name="doc_status" class="form-select form-select-sm">
                        @foreach(['Pending','Received','Complete'] as $s)
                        <option value="{{ $s }}" {{ $lc->doc_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Origin</label>
                    <input type="text" name="country_of_origin" class="form-control form-control-sm" value="{{ $lc->country_of_origin }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Mode</label>
                    <input type="text" name="payment_mode" class="form-control form-control-sm" value="{{ $lc->payment_mode }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Insurance Amt</label>
                    <input type="number" name="insurance_amt" class="form-control form-control-sm" step="0.01" value="{{ $lc->insurance_amt }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cover Note</label>
                    <input type="text" name="cover_note" class="form-control form-control-sm" value="{{ $lc->cover_note }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PSI No</label>
                    <input type="text" name="psi_no" class="form-control form-control-sm" value="{{ $lc->psi_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comm. Currency</label>
                    <input type="text" name="comm_currency" class="form-control form-control-sm" value="{{ $lc->comm_currency }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comm. Amount</label>
                    <input type="number" name="comm_amount" class="form-control form-control-sm" step="0.01" value="{{ $lc->comm_amount }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ $lc->remarks }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- LC Line Items --}}
    <div class="lc-card">
        <div class="lc-section-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-boxes me-2"></i> Product Line Items</span>
            <button type="button" class="btn btn-sm btn-light py-0 px-2" id="btnAddItem" style="font-size:.75rem"><i class="fa fa-plus me-1"></i>Add Row</button>
        </div>
        <div class="lc-section-body p-0">
            <div style="overflow-x:auto">
                <table class="table table-bordered items-table mb-0 w-100" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="min-width:180px">Product</th>
                            <th style="width:90px">Code</th>
                            <th style="width:100px">HS Code</th>
                            <th style="width:80px">Qty</th>
                            <th style="width:70px">Unit</th>
                            <th style="width:80px">Weight</th>
                            <th style="width:70px">W.Unit</th>
                            <th style="width:100px">Unit Price</th>
                            <th style="width:100px">Amount</th>
                            <th style="width:60px">Curr.</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-success px-5" id="btnSave"><i class="fa fa-save me-1"></i> Update LC</button>
        <a href="{{ route('nas-trading.lcs.show', $lc->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var itemRowIdx = 0;
var existingItems = @json($lc->items);

function addItemRow(data) {
    data = data || {};
    var idx = itemRowIdx++;
    var optHtml = data.item_id ? `<option value="${data.item_id}" selected>${data.product_name || ''}</option>` : '';
    var html = `<tr>
        <td class="text-center row-num">${idx + 1}</td>
        <td>
            <select class="form-select form-select-sm" name="items[${idx}][item_id]" data-row="${idx}">${optHtml}</select>
            <input type="hidden" name="items[${idx}][product_name]" class="item-name" value="${data.product_name || ''}">
        </td>
        <td><input type="text" class="form-control form-control-sm item-code" name="items[${idx}][product_code]" value="${data.product_code || ''}" readonly></td>
        <td><input type="text" class="form-control form-control-sm" name="items[${idx}][hs_code]" value="${data.hs_code || ''}"></td>
        <td><input type="number" class="form-control form-control-sm item-qty" name="items[${idx}][qty_count]" value="${data.qty_count || ''}" step="0.0001" min="0"></td>
        <td><input type="text" class="form-control form-control-sm item-unit" name="items[${idx}][qty_unit]" value="${data.qty_unit || ''}"></td>
        <td><input type="number" class="form-control form-control-sm" name="items[${idx}][weight]" value="${data.weight || ''}" step="0.0001" min="0"></td>
        <td><input type="text" class="form-control form-control-sm" name="items[${idx}][weight_unit]" value="${data.weight_unit || ''}"></td>
        <td><input type="number" class="form-control form-control-sm item-uprice" name="items[${idx}][unit_price]" value="${data.unit_price || ''}" step="0.0001" min="0"></td>
        <td><input type="number" class="form-control form-control-sm item-amount" name="items[${idx}][line_amount]" value="${data.line_amount || ''}" step="0.0001" readonly></td>
        <td><select class="form-select form-select-sm" name="items[${idx}][currency]">${['USD','EUR','GBP','CNY','BDT'].map(c => `<option value="${c}" ${(data.currency||'USD')===c?'selected':''}>${c}</option>`).join('')}</select></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-0" style="width:24px;height:24px"><i class="fa fa-times" style="font-size:.65rem"></i></button></td>
    </tr>`;
    $('#itemsBody').append(html);
    initSelect2(idx, data.item_id);
}

function initSelect2(idx, initialId) {
    $(`[name="items[${idx}][item_id]"]`).select2({
        width: '100%', placeholder: 'Search item...', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.items.search') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', function (e) {
        var d = e.params.data, row = $(this).closest('tr');
        row.find('.item-name').val(d.text.split(' | ')[1] || d.text);
        row.find('.item-code').val(d.code||'');
        row.find('[name*=hs_code]').val(d.hs_code||'');
        row.find('.item-unit').val(d.unit||'');
    });
}

$(function () {
    $('#customerSelect').select2({
        width: '100%', allowClear: true, minimumInputLength: 0,
        ajax: { url: '{{ route('nas-trading.lcs.search-customers') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', e => $('#customerName').val(e.params.data.text.split(' | ')[1] || e.params.data.text));

    $('#supplierSelect').select2({
        width: '100%', allowClear: true, minimumInputLength: 1,
        ajax: { url: '{{ route('nas-trading.lcs.search-suppliers') }}', dataType: 'json', delay: 300, data: p => ({q: p.term}), processResults: d => ({results: d}) }
    }).on('select2:select', function (e) {
        $('#supplierName').val(e.params.data.text.split(' | ')[1] || e.params.data.text);
        $('#supplierCountry, #supplierCountryDisplay').val(e.params.data.country || '');
    });

    $(document).on('input', '.item-qty, .item-uprice', function () {
        var row = $(this).closest('tr');
        row.find('.item-amount').val(((parseFloat(row.find('.item-qty').val())||0) * (parseFloat(row.find('.item-uprice').val())||0)).toFixed(4));
    });

    $('#btnAddItem').on('click', () => addItemRow());
    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('tr').remove();
        $('#itemsBody tr').each((i, tr) => $(tr).find('.row-num').text(i + 1));
    });

    // Pre-fill existing items
    existingItems.forEach(item => addItemRow(item));
    if (!existingItems.length) addItemRow();

    $('#lcForm').on('submit', function (e) {
        e.preventDefault();
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        $.ajax({ url: '{{ route('nas-trading.lcs.update', $lc->id) }}', method: 'POST', data: $('#lcForm').serialize() })
        .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(() => { if (r.redirect) window.location.href = r.redirect; }))
        .fail(xhr => { $('#btnSave').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Update LC'); Swal.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Error' }); });
    });
});
</script>
@endpush

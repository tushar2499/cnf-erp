@extends('chevron.layouts.app')

@section('title', isset($receipt) ? 'Edit Money Receipt' : 'New Money Receipt')

@push('styles')
<style>
    #billsTable th, #billsTable td { font-size: .78rem; vertical-align: middle; white-space: nowrap; }
    #billsTable .alloc-amount { min-width: 130px; }
    #billsTable .invalid-feedback { white-space: normal; }
    #balanceCheck.is-ok { color: var(--bs-success); }
    #balanceCheck.is-bad { color: var(--bs-danger); }
    .btn-check:focus-visible + .btn { outline: 2px solid var(--bs-primary); outline-offset: 2px; }
</style>
@endpush

@section('content')
@php
    $selectedType   = old('receipt_type', $receipt->receipt_type ?? '');
    $partyIdValue   = old('party_id', $receipt->party_id ?? '');
    $partyNameValue = old('party_name', $receipt->party_name ?? '');
    $advanceMode    = old('advance_mode', ! empty($existingJobAllocations) ? 'job' : 'general');
@endphp

<div class="page-header">
    <h4>
        <i class="fa fa-money-bill-wave me-2 text-primary"></i>
        {{ isset($receipt) ? 'Edit Money Receipt — ' . $receipt->receipt_no : 'New Money Receipt' }}
    </h4>
    <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form method="POST"
      action="{{ isset($receipt) ? route('chevron.cnf.money-receipts.update', $receipt->id) : route('chevron.cnf.money-receipts.store') }}"
      id="mrForm" novalidate>
    @csrf
    @if(isset($receipt)) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header fw-bold"><i class="fa fa-info-circle me-2"></i>Receipt Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="receiptDate">Money Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receiptDate" class="form-control form-control-sm @error('receipt_date') is-invalid @enderror"
                           value="{{ old('receipt_date', isset($receipt) ? $receipt->receipt_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="partySelect">Party Name <span class="text-danger">*</span></label>
                    <select id="partySelect" name="party_id" class="form-select form-select-sm @error('party_name') is-invalid @enderror" style="width:100%">
                        @if($partyNameValue !== '')
                            <option value="{{ $partyIdValue }}" selected>{{ $partyNameValue }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="party_name" id="partyName" value="{{ $partyNameValue }}">
                    <div class="small mt-1 d-none" id="advanceChip">
                        <i class="fa fa-wallet text-primary me-1"></i>Advance balance
                        <strong class="ms-1" id="advanceBalanceText">0.00</strong>
                        <a href="#" id="advanceStatementLink" target="_blank" rel="noopener" class="ms-2">Statement</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="payType">Payment Method <span class="text-danger">*</span></label>
                    <select name="pay_type" id="payType" class="form-select form-select-sm @error('pay_type') is-invalid @enderror" required>
                        <option value="">--Select Payment Method--</option>
                        @foreach($payTypes as $pt)
                            <option value="{{ $pt }}" {{ old('pay_type', $receipt->pay_type ?? '') == $pt ? 'selected' : '' }}>{{ $pt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <span class="form-label d-block" id="receiptTypeLabel">Receipt For <span class="text-danger">*</span></span>
                    <div class="btn-group w-100" role="group" aria-labelledby="receiptTypeLabel" id="receiptTypeGroup" tabindex="-1">
                        @foreach($receiptTypes as $rt)
                            <input type="radio" class="btn-check" name="receipt_type" id="receiptType{{ $rt }}" value="{{ $rt }}" autocomplete="off" {{ $selectedType === $rt ? 'checked' : '' }}>
                            <label class="btn btn-sm btn-outline-primary" for="receiptType{{ $rt }}">{{ $rt }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-4 d-none" id="advanceModeCol">
                    <span class="form-label d-block" id="advanceModeLabel">Advance For</span>
                    <div class="btn-group w-100" role="group" aria-labelledby="advanceModeLabel" id="advanceModeGroup">
                        <input type="radio" class="btn-check" name="advance_mode" id="advanceModeGeneral" value="general" autocomplete="off" {{ $advanceMode === 'general' ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary" for="advanceModeGeneral">General (no job)</label>
                        <input type="radio" class="btn-check" name="advance_mode" id="advanceModeJob" value="job" autocomplete="off" {{ $advanceMode === 'job' ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary" for="advanceModeJob">Job-wise</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="totalAmountDisplay">Total Collected</label>
                    <input type="text" id="totalAmountDisplay" class="form-control form-control-sm text-end bg-light fw-bold" readonly value="0.00">
                </div>

                {{-- Job-wise advance rows (shown before Description) --}}
                <div class="col-12 d-none" id="advanceJobs">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center fw-bold">
                            <span><i class="fa fa-briefcase me-2"></i>Job-wise Advance</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddJob">
                                <i class="fa fa-plus me-1"></i> Add Job
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="jobsTable" tabindex="-1">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job No</th>
                                            <th style="width:200px" class="text-end">Advance Amount</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="jobsBody"></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th class="text-end">Assigned to jobs</th>
                                            <th class="text-end" id="jobTotal">0.00</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th class="text-end">General advance (no job)</th>
                                            <th class="text-end" id="jobUnassigned">0.00</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bill-wise collection (shown before Description) --}}
                <div class="col-12 d-none" id="billPanel">
                <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 fw-bold">
            <span><i class="fa fa-file-invoice me-2"></i>Outstanding Bills</span>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted fw-normal" id="billCount" aria-live="polite"></small>
                <label for="billFilter" class="visually-hidden">Filter bills by bill number or job number</label>
                <input type="search" id="billFilter" class="form-control form-control-sm" style="width:240px" placeholder="Filter by bill no / job no" autocomplete="off">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="billsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px" class="text-center">
                                <input type="checkbox" class="form-check-input" id="billCheckAll" aria-label="Select all bills">
                            </th>
                            <th>Bill No</th>
                            <th>Bill Date</th>
                            <th>Job No</th>
                            <th class="text-end">Bill Amount</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Balance</th>
                            <th style="width:170px" class="text-end">Collect Now</th>
                        </tr>
                    </thead>
                    <tbody id="billsBody"></tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">Selected</th>
                            <th class="text-end" id="selectedBalance">0.00</th>
                            <th class="text-end" id="allocTotal">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
                </div>
                </div>

                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea name="description" id="description" class="form-control form-control-sm" rows="2" placeholder="Optional remarks...">{{ old('description', $receipt->description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Rows --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center fw-bold">
            <span><i class="fa fa-list me-2"></i>Payment Details</span>
            <button type="button" class="btn btn-sm btn-danger" id="btnAddRow">
                <i class="fa fa-plus me-1"></i> Add
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="itemsTable" tabindex="-1">
                    <thead class="table-light">
                        <tr>
                            <th style="width:200px">Type</th>
                            <th style="width:190px">Acc No</th>
                            <th>Cheque/Card Holder</th>
                            <th style="width:160px">Cheque/Card No</th>
                            <th style="width:130px">Amount</th>
                            <th style="width:150px">Cheque Date</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-none fw-semibold" id="balanceCheck" aria-live="polite"></div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSave">
            <i class="fa fa-save me-1"></i> {{ isset($receipt) ? 'Update' : 'Save' }}
        </button>
        <a href="{{ route('chevron.cnf.money-receipts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
var accounts       = @json($accounts);
var rowPayTypes    = @json($rowPayTypes);
var existing       = @json($existingItems);
var pendingAllocs  = {};
@json($existingAllocations).forEach(function (a) { pendingAllocs[a.bill_id] = a.amount; });
var existingJobs   = @json($existingJobAllocations);
var serverErrors   = @json($errors->getMessages());
var receiptId      = @json($receipt->id ?? null);

var adjustmentLabel = @json(\App\Models\Chevron\ChevronMoneyReceipt::ADVANCE_ADJUSTMENT);

var billsRequest     = 0;
var jobRowSeq        = 0;
var advanceRequest   = 0;
var advanceAvailable = 0;

function esc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function number_fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function currentType() {
    return $('input[name="receipt_type"]:checked').val() || '';
}

function currentAdvanceMode() {
    return $('input[name="advance_mode"]:checked').val() || 'general';
}

function accountOptions(selected) {
    var html = '<option value="">--Select Acc No--</option>';
    accounts.forEach(function (a) {
        var sel = (selected && selected == a.id) ? 'selected' : '';
        html += '<option value="' + a.id + '" data-no="' + esc(a.account_no) + '" ' + sel + '>' + esc(a.account_no) + (a.account_name ? ' — ' + esc(a.account_name) : '') + '</option>';
    });
    return html;
}

function rowTypeChoices(selected) {
    var choices = rowPayTypes.slice();
    if ((currentType() === 'Bill' && advanceAvailable > 0) || selected === adjustmentLabel) {
        choices.push(adjustmentLabel);
    }
    return choices;
}

function typeOptions(selected) {
    var html = '<option value="">--Select Payment--</option>';
    rowTypeChoices(selected).forEach(function (t) {
        html += '<option value="' + esc(t) + '"' + (t === selected ? ' selected' : '') + '>' + esc(t) + '</option>';
    });
    return html;
}

function applyRowState($tr) {
    var isAdjustment = $tr.find('.row-type').val() === adjustmentLabel;
    var $details = $tr.find('.row-account, .row-holder, .row-cardno, .row-chequedate');

    if (isAdjustment) {
        $details.val('').prop('disabled', true);
        $tr.find('.row-account-no').val('');
    } else {
        $details.prop('disabled', false);
    }
}

function refreshRowTypes() {
    $('#itemsBody .row-type').each(function () {
        var current = $(this).val();
        var allowed = rowTypeChoices(current === adjustmentLabel && currentType() === 'Bill' ? current : '');
        var keep = allowed.indexOf(current) !== -1 ? current : '';
        $(this).html(typeOptions(keep)).val(keep);
        applyRowState($(this).closest('tr'));
    });
    syncPayMethod();
}

function adjustmentTotal() {
    var total = 0;
    $('#itemsBody tr').each(function () {
        if ($(this).find('.row-type').val() === adjustmentLabel) {
            total += parseFloat($(this).find('.row-amount').val()) || 0;
        }
    });
    return Math.round(total * 100) / 100;
}

function syncPayMethod() {
    var types = $('#itemsBody .row-type').map(function () { return $(this).val(); }).get().filter(Boolean);
    var $method = $('#payType');
    if (!types.length) { return; }

    var allAdjustments = types.every(function (t) { return t === adjustmentLabel; });
    if (allAdjustments && currentType() === 'Bill') {
        $method.val(adjustmentLabel);
    } else if ($method.val() === adjustmentLabel) {
        $method.val(types.some(function (t) { return t === adjustmentLabel; }) ? 'Mixed' : '');
    }
}

function loadAdvance() {
    var partyId = $('#partySelect').val() || '';
    var seq = ++advanceRequest;

    if (!partyId || !$('#partyName').val()) {
        advanceAvailable = 0;
        $('#advanceChip').addClass('d-none');
        refreshRowTypes();
        return;
    }

    $.get('{{ route('chevron.cnf.money-receipts.party-advance') }}', { party_id: partyId, receipt_id: receiptId || '' }).done(function (r) {
        if (seq !== advanceRequest) { return; }
        advanceAvailable = Math.max(parseFloat(r.balance) || 0, 0);
        $('#advanceBalanceText').text('BDT ' + number_fmt(advanceAvailable));
        $('#advanceStatementLink').attr('href', r.statement_url || '#');
        $('#advanceChip').toggleClass('d-none', advanceAvailable <= 0);
        refreshRowTypes();
        recalcAll();
    }).fail(function () {
        if (seq !== advanceRequest) { return; }
        advanceAvailable = 0;
        $('#advanceChip').addClass('d-none');
    });
}

function addRow(data) {
    data = data || {};
    var idx = $('#itemsBody tr').length;
    var row = '<tr>' +
        '<td><select name="items[' + idx + '][payment_type]" class="form-select form-select-sm row-type" aria-label="Payment type" required>' + typeOptions(data.payment_type) + '</select></td>' +
        '<td>' +
            '<select name="items[' + idx + '][account_id]" class="form-select form-select-sm row-account" aria-label="Account number">' + accountOptions(data.account_id) + '</select>' +
            '<input type="hidden" name="items[' + idx + '][account_no]" class="row-account-no" value="' + esc(data.account_no) + '">' +
        '</td>' +
        '<td><input type="text" name="items[' + idx + '][cheque_card_holder]" class="form-control form-control-sm row-holder" aria-label="Cheque or card holder" value="' + esc(data.cheque_card_holder) + '" placeholder="Holder name..."></td>' +
        '<td><input type="text" name="items[' + idx + '][cheque_card_no]" class="form-control form-control-sm row-cardno" aria-label="Cheque or card number" value="' + esc(data.cheque_card_no) + '" placeholder="No..."></td>' +
        '<td><input type="number" name="items[' + idx + '][amount]" class="form-control form-control-sm text-end row-amount" aria-label="Amount" value="' + esc(data.amount) + '" min="0" step="0.01" required placeholder="0.00"></td>' +
        '<td><input type="date" name="items[' + idx + '][cheque_date]" class="form-control form-control-sm row-chequedate" aria-label="Cheque date" value="' + esc(data.cheque_date) + '"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" aria-label="Remove payment row"><i class="fa fa-times"></i></button></td>' +
        '</tr>';
    $('#itemsBody').append(row);
    reIndexRows();
    applyRowState($('#itemsBody tr').last());
}

function reIndexRows() {
    $('#itemsBody tr').each(function (i) {
        $(this).find('[name]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/items\[\d+\]/, 'items[' + i + ']'));
        });
    });
}

function collectedTotal() {
    var total = 0;
    $('.row-amount').each(function () { total += parseFloat($(this).val()) || 0; });
    return Math.round(total * 100) / 100;
}

function allocatedTotal() {
    var total = 0;
    $('#billsBody .alloc-amount:not(:disabled)').each(function () { total += parseFloat($(this).val()) || 0; });
    return Math.round(total * 100) / 100;
}

function recalcAll() {
    var collected = collectedTotal();
    $('#totalAmountDisplay').val(number_fmt(collected));

    var selectedBalance = 0;
    $('#billsBody tr[data-bill-id]').each(function () {
        if ($(this).find('.bill-check').is(':checked')) {
            selectedBalance += parseFloat($(this).data('balance')) || 0;
        }
    });
    var allocated = allocatedTotal();
    $('#selectedBalance').text(number_fmt(selectedBalance));
    $('#allocTotal').text(number_fmt(allocated));
    updateBillCount();

    var jobTotal = jobAssignedTotal();
    var unassigned = Math.round((collected - jobTotal) * 100) / 100;
    $('#jobTotal').text(number_fmt(jobTotal));
    $('#jobUnassigned').text(number_fmt(Math.max(unassigned, 0))).toggleClass('text-danger', unassigned < 0);

    // The footer only speaks up when something is wrong; a clean form shows nothing extra.
    var $check = $('#balanceCheck').addClass('d-none').removeClass('is-bad').empty();
    var type = currentType();
    var adjusted = adjustmentTotal();

    if (type === 'Bill' && adjusted > advanceAvailable) {
        $check.removeClass('d-none').addClass('is-bad').html('<i class="fa fa-exclamation-triangle me-1"></i>Advance adjustment ' + number_fmt(adjusted) +
            ' exceeds the available advance balance ' + number_fmt(advanceAvailable) + '.');
    } else if (type === 'Advance' && currentAdvanceMode() === 'job' && unassigned < 0) {
        $check.removeClass('d-none').addClass('is-bad').html('<i class="fa fa-exclamation-triangle me-1"></i>Job-wise advance ' + number_fmt(jobTotal) +
            ' exceeds the collected total ' + number_fmt(collected) + ' by ' + number_fmt(Math.abs(unassigned)) + '.');
    } else if (type === 'Bill' && allocated > 0) {
        var diff = Math.round((collected - allocated) * 100) / 100;
        if (diff !== 0) {
            $check.removeClass('d-none').addClass('is-bad').html('<i class="fa fa-exclamation-triangle me-1"></i>Collected ' + number_fmt(collected) +
                ' · Allocated to bills ' + number_fmt(allocated) + ' · Difference ' + number_fmt(Math.abs(diff)) + '. Both amounts must match.');
        }
    }
}

function jobAssignedTotal() {
    var total = 0;
    $('#jobsBody .job-amount:not(:disabled)').each(function () { total += parseFloat($(this).val()) || 0; });
    return Math.round(total * 100) / 100;
}

function usedJobIds($exceptRow) {
    var ids = [];
    $('#jobsBody tr.job-row').each(function () {
        if ($exceptRow && this === $exceptRow[0]) { return; }
        var id = $(this).attr('data-job-id');
        if (id) { ids.push(String(id)); }
    });
    return ids;
}

function toastErrors(messages) {
    var list = [].concat(messages).filter(function (m, i, all) { return m && all.indexOf(m) === i; });
    if (!list.length || typeof Swal === 'undefined') { return; }

    var options = {
        toast: true,
        position: 'top-end',
        icon: 'error',
        showConfirmButton: false,
        showCloseButton: true,
        timer: 4500 + (list.length - 1) * 1500,
        timerProgressBar: true
    };

    if (list.length === 1) {
        options.title = list[0];
    } else {
        options.title = 'Please fix the following:';
        options.html = '<ul class="text-start mb-0 ps-3">' + list.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
    }

    Swal.fire(options);
}

function focusField(id) {
    var el = id ? document.getElementById(id) : null;
    if (!el) { return; }
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (typeof el.focus === 'function') { el.focus({ preventScroll: true }); }
}

function setJobRowError($tr, message) {
    var $amount = $tr.find('.job-amount');
    if (message) {
        $amount.addClass('is-invalid').attr({ title: message, 'aria-invalid': 'true' });
    } else {
        $amount.removeClass('is-invalid').removeAttr('title').removeAttr('aria-invalid');
    }
}

function setJobIdentity($tr, jobId) {
    $tr.attr('data-job-id', jobId);
    $tr.find('.job-amount')
        .attr('name', 'job_allocations[' + jobId + '][amount]')
        .attr('id', 'job-amount-' + jobId);
    $tr.find('.job-id-hidden').attr('name', 'job_allocations[' + jobId + '][job_id]').val(jobId);
}

function refreshJobsEmpty() {
    var hasRows = $('#jobsBody tr.job-row').length > 0;
    $('#jobsBody tr.jobs-empty').remove();
    if (!hasRows) {
        $('#jobsBody').append('<tr class="jobs-empty"><td colspan="3" class="text-center text-muted py-3">' +
            '<i class="fa fa-info-circle me-1"></i>No job added yet. Click Add Job, or choose General advance (no job).</td></tr>');
    }
}

function addJobRow(data) {
    data = data || {};
    var seq = ++jobRowSeq;
    var $tr = $(
        '<tr class="job-row">' +
        '<td><select class="form-select form-select-sm job-select" style="width:100%" aria-label="Job number"><option value=""></option></select></td>' +
        '<td>' +
            '<input type="number" class="form-control form-control-sm text-end job-amount" id="job-row-amount-' + seq + '" min="0.01" step="0.01" placeholder="0.00" aria-label="Advance amount for this job" value="' + esc(data.amount) + '">' +
            '<input type="hidden" class="job-id-hidden">' +
        '</td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-job" aria-label="Remove job row"><i class="fa fa-times"></i></button></td>' +
        '</tr>'
    );
    $('#jobsBody tr.jobs-empty').remove();
    $('#jobsBody').append($tr);

    var $select = $tr.find('.job-select');
    if (data.job_id) {
        $select.append(new Option(data.job_no || ('Job #' + data.job_id), data.job_id, true, true));
        setJobIdentity($tr, data.job_id);
        var key = 'job_allocations.' + data.job_id + '.';
        if (serverErrors[key + 'amount'] || serverErrors[key + 'job_id']) {
            setJobRowError($tr, (serverErrors[key + 'amount'] || serverErrors[key + 'job_id'])[0]);
        }
    }

    $select.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Type a job number to search...',
        ajax: {
            url: '{{ route('chevron.cnf.money-receipts.party-jobs') }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term || '',
                    page: params.page || 1,
                    party_id: $('#partySelect').val() || '',
                    party_name: $('#partyName').val()
                };
            },
            processResults: function (d) {
                var used = usedJobIds($tr);
                return {
                    results: d.results.filter(function (r) { return used.indexOf(String(r.id)) === -1; }),
                    pagination: d.pagination
                };
            }
        },
        minimumInputLength: 0
    });

    $select.on('select2:select', function (e) {
        setJobIdentity($tr, e.params.data.id);
        setJobRowError($tr, '');
        $tr.find('.job-amount').trigger('focus');
        recalcAll();
    });

    refreshJobsEmpty();
    recalcAll();
}

function clearJobRows() {
    $('#jobsBody tr.job-row .job-select').each(function () { $(this).select2('destroy'); });
    $('#jobsBody').empty();
    refreshJobsEmpty();
}

function updateBillCount() {
    var total = $('#billsBody tr[data-bill-id]').length;
    if (!total) { $('#billCount').text(''); return; }
    var visible = $('#billsBody tr[data-bill-id]:not(.d-none)').length;
    var selected = $('#billsBody .bill-check:checked').length;
    $('#billCount').text('Showing ' + visible + ' of ' + total + ' · ' + selected + ' selected');
}

function applyBillFilter() {
    var term = $.trim($('#billFilter').val()).toLowerCase();
    var visible = 0;
    $('#billsBody tr[data-bill-id]').each(function () {
        var show = !term || ($(this).attr('data-search') || '').indexOf(term) !== -1;
        $(this).toggleClass('d-none', !show);
        if (show) { visible++; }
    });
    var total = $('#billsBody tr[data-bill-id]').length;
    $('#billNoMatch').toggleClass('d-none', !(total && visible === 0));
    updateBillCount();
}

function setRowError($tr, message) {
    var $input = $tr.find('.alloc-amount');
    if (message) {
        $input.addClass('is-invalid').attr({ title: message, 'aria-invalid': 'true' });
    } else {
        $input.removeClass('is-invalid').removeAttr('title').removeAttr('aria-invalid');
    }
}

function allocMessage($tr) {
    var $input = $tr.find('.alloc-amount');
    if ($input.prop('disabled')) { return ''; }
    var balance = parseFloat($tr.data('balance')) || 0;
    var value = parseFloat($input.val());
    var billNo = $.trim($tr.find('label').text());
    if (isNaN(value) || value <= 0) {
        return billNo + ': enter an amount greater than 0.';
    }
    if (Math.round((value - balance) * 100) / 100 > 0) {
        return billNo + ': cannot collect more than the bill balance of ' + number_fmt(balance) + '.';
    }
    return '';
}

function validateAllocRow($tr) {
    var message = allocMessage($tr);
    setRowError($tr, message);
    return message === '';
}

function syncRow($tr) {
    var on = $tr.find('.bill-check').is(':checked') && currentType() === 'Bill';
    $tr.find('.alloc-amount, .alloc-bill').prop('disabled', !on);
    if (!on) { setRowError($tr, ''); }
}

function renderBills(bills) {
    var $body = $('#billsBody').empty();
    $('#billCheckAll').prop('checked', false);

    if (!bills.length) {
        $body.html('<tr><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-inbox me-1"></i>No outstanding bills for this party.</td></tr>');
        recalcAll();
        return;
    }

    bills.forEach(function (b) {
        var id = b.id;
        var $tr = $(
            '<tr data-bill-id="' + id + '" data-balance="' + b.balance + '" data-search="' + esc(((b.bill_no || '') + ' ' + (b.job_no || '')).toLowerCase()) + '">' +
            '<td class="text-center"><input type="checkbox" class="form-check-input bill-check" id="bill-check-' + id + '" aria-label="Select bill ' + esc(b.bill_no) + '"></td>' +
            '<td><label for="bill-check-' + id + '" class="mb-0">' + esc(b.bill_no) + '</label></td>' +
            '<td>' + esc(b.bill_date || '—') + '</td>' +
            '<td>' + esc(b.job_no || '—') + '</td>' +
            '<td class="text-end">' + number_fmt(b.due_amount) + '</td>' +
            '<td class="text-end">' + number_fmt(b.received_amount) + '</td>' +
            '<td class="text-end fw-semibold">' + number_fmt(b.balance) + '</td>' +
            '<td>' +
                '<input type="number" class="form-control form-control-sm text-end alloc-amount" id="collect-' + id + '" name="allocations[' + id + '][amount]" ' +
                    'min="0.01" max="' + b.balance + '" step="0.01" placeholder="0.00" aria-label="Amount to collect for ' + esc(b.bill_no) + '" disabled>' +
                '<input type="hidden" class="alloc-bill" name="allocations[' + id + '][bill_id]" value="' + id + '" disabled>' +
            '</td>' +
            '</tr>'
        );
        $body.append($tr);

        if (pendingAllocs[id] !== undefined) {
            $tr.find('.bill-check').prop('checked', true);
            $tr.find('.alloc-amount').val(pendingAllocs[id]);
        }
        syncRow($tr);

        var key = 'allocations.' + id + '.amount';
        var keyBill = 'allocations.' + id + '.bill_id';
        if (serverErrors[key] || serverErrors[keyBill]) {
            setRowError($tr, (serverErrors[key] || serverErrors[keyBill])[0]);
        }
    });

    $body.append('<tr id="billNoMatch" class="d-none"><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-search me-1"></i>No bills match the filter.</td></tr>');

    pendingAllocs = {};
    applyBillFilter();
    recalcAll();
}

function loadBills() {
    var name = $('#partyName').val();
    var $body = $('#billsBody');

    if (!name) {
        $body.html('<tr><td colspan="8" class="text-center text-muted py-3">Select a party to see their outstanding bills.</td></tr>');
        recalcAll();
        return;
    }

    var seq = ++billsRequest;
    $body.html('<tr><td colspan="8" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading bills...</td></tr>');

    $.get('{{ route('chevron.cnf.money-receipts.party-bills') }}', {
        party_id: $('#partySelect').val() || '',
        party_name: name,
        receipt_id: receiptId || ''
    }).done(function (r) {
        if (seq === billsRequest) { renderBills(r.bills); }
    }).fail(function () {
        if (seq === billsRequest) {
            $body.html('<tr><td colspan="8" class="text-center text-danger py-3"><i class="fa fa-exclamation-triangle me-1"></i>Could not load bills. Re-select the party to retry.</td></tr>');
        }
    });
}

function updateTypeUI() {
    var type = currentType();
    var hasParty = !!$('#partyName').val();

    $('input[name="receipt_type"]').prop('disabled', !hasParty);
    $('#advanceModeCol').toggleClass('d-none', type !== 'Advance');
    $('#billPanel').toggleClass('d-none', type !== 'Bill');
    var jobMode = type === 'Advance' && currentAdvanceMode() === 'job';
    $('#advanceModeCol :input').prop('disabled', type !== 'Advance');
    if (hasParty) { $('#receiptTypeGroup').removeAttr('title'); } else { $('#receiptTypeGroup').attr('title', 'Select a party first'); }
    $('#advanceJobs').toggleClass('d-none', !jobMode);
    $('#advanceJobs :input').prop('disabled', !jobMode);
    $('#payType option').filter(function () { return this.value === adjustmentLabel; }).prop('disabled', type !== 'Bill');
    if (type !== 'Bill' && $('#payType').val() === adjustmentLabel) { $('#payType').val(''); }
    $('#billsBody tr[data-bill-id]').each(function () { syncRow($(this)); });
    refreshRowTypes();
    recalcAll();
}

function showClientErrors(errors) {
    toastErrors(errors.map(function (e) { return e.msg; }));
    focusField(errors[0].id);
}

$(function () {
    // Party
    $('#partySelect').select2({
        ajax: {
            url: '{{ route('chevron.cnf.money-receipts.search-parties') }}',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term }),
            processResults: d => ({ results: d.results }),
        },
        minimumInputLength: 2,
        placeholder: 'Enter minimum 2 characters',
        allowClear: true,
    });

    $('#partySelect').on('select2:select', function (e) {
        $('#partyName').val(e.params.data.text);
        pendingAllocs = {};
        clearJobRows();
        updateTypeUI();
        loadAdvance();
        if (currentType() === 'Bill') { loadBills(); }
    });

    $('#partySelect').on('select2:clear', function () {
        $('#partyName').val('');
        pendingAllocs = {};
        clearJobRows();
        $('#billsBody').empty();
        updateTypeUI();
        loadAdvance();
    });

    // Advance mode: general (no job) or against job(s)
    $('input[name="advance_mode"]').on('change', function () {
        updateTypeUI();
        if (currentAdvanceMode() === 'job' && $('#jobsBody tr.job-row').length === 0) { addJobRow(); }
    });

    // Job-wise advance rows
    $('#btnAddJob').on('click', function () { addJobRow(); });

    $(document).on('click', '.btn-remove-job', function () {
        var $tr = $(this).closest('tr');
        $tr.find('.job-select').select2('destroy');
        $tr.remove();
        refreshJobsEmpty();
        recalcAll();
    });

    $(document).on('input', '.job-amount', function () {
        setJobRowError($(this).closest('tr'), '');
        recalcAll();
    });

    // Bill filter (bill no / job no)
    $('#billFilter').on('input', applyBillFilter);

    // Receipt type toggle
    $('input[name="receipt_type"]').on('change', function () {
        updateTypeUI();
        if (currentType() === 'Bill') { loadBills(); }
    });

    // Bill selection
    $(document).on('change', '.bill-check', function () {
        var $tr = $(this).closest('tr');
        var $amount = $tr.find('.alloc-amount');
        syncRow($tr);
        if ($(this).is(':checked')) {
            if (!$amount.val()) { $amount.val($tr.data('balance')); }
            validateAllocRow($tr);
        } else {
            $amount.val('');
        }
        recalcAll();
    });

    $('#billCheckAll').on('change', function () {
        var on = $(this).is(':checked');
        $('#billsBody tr[data-bill-id]:not(.d-none) .bill-check').prop('checked', on).trigger('change');
    });

    $(document).on('input', '.alloc-amount', function () {
        validateAllocRow($(this).closest('tr'));
        recalcAll();
    });

    $(document).on('change', '.alloc-amount', function () {
        var message = allocMessage($(this).closest('tr'));
        if (message) { toastErrors(message); }
    });

    // Add / remove payment rows
    $('#btnAddRow').on('click', function () { addRow(); });

    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('tr').remove();
        reIndexRows();
        syncPayMethod();
        recalcAll();
    });

    $(document).on('change', '.row-type', function () {
        applyRowState($(this).closest('tr'));
        syncPayMethod();
        recalcAll();
    });

    $(document).on('input', '.row-amount', recalcAll);

    $(document).on('change', '.row-account', function () {
        var no = $(this).find(':selected').data('no') || '';
        $(this).closest('td').find('.row-account-no').val(no);
    });

    // Initial state
    if (existing.length) {
        existing.forEach(function (item) { addRow(item); });
    } else {
        addRow();
    }
    existingJobs.forEach(function (job) { addJobRow(job); });
    refreshJobsEmpty();
    updateTypeUI();
    loadAdvance();
    if (currentType() === 'Bill') { loadBills(); }

    var serverMessages = [];
    $.each(serverErrors, function (key, messages) { serverMessages = serverMessages.concat(messages); });
    toastErrors(serverMessages);

    // Submit
    $('#mrForm').on('submit', function (e) {
        var errors = [];
        var type = currentType();

        if (!$('#receiptDate').val()) { errors.push({ id: 'receiptDate', msg: 'Select the receipt date.' }); }
        if (!$('#partyName').val()) { errors.push({ id: 'partySelect', msg: 'Select a party.' }); }
        if (!$('#payType').val()) { errors.push({ id: 'payType', msg: 'Select the payment method.' }); }
        if (!type) { errors.push({ id: 'receiptTypeGroup', msg: 'Select what this receipt is for: Advance or Bill.' }); }

        if ($('#itemsBody tr').length === 0) {
            errors.push({ id: 'itemsTable', msg: 'Add at least one payment row.' });
        } else {
            $('#itemsBody tr').each(function (i) {
                var $type = $(this).find('.row-type');
                var $amount = $(this).find('.row-amount');
                if (!$type.val()) { errors.push({ id: $type.attr('id') || 'itemsTable', msg: 'Payment row ' + (i + 1) + ': select a type.' }); }
                if (!(parseFloat($amount.val()) > 0)) { errors.push({ id: $amount.attr('id') || 'itemsTable', msg: 'Payment row ' + (i + 1) + ': enter an amount greater than 0.' }); }
            });
        }

        if (type === 'Bill') {
            var $checked = $('#billsBody .bill-check:checked');
            if ($checked.length === 0) {
                errors.push({ id: 'billPanel', msg: 'Select at least one bill to collect against.' });
            }
            $checked.each(function () {
                var $tr = $(this).closest('tr');
                if (!validateAllocRow($tr)) {
                    errors.push({ id: $tr.find('.alloc-amount').attr('id'), msg: allocMessage($tr) });
                }
            });
            var diff = Math.round((collectedTotal() - allocatedTotal()) * 100) / 100;
            if ($checked.length && diff !== 0) {
                errors.push({ id: 'itemsTable', msg: 'Collected total (' + number_fmt(collectedTotal()) + ') must equal the amount allocated to bills (' + number_fmt(allocatedTotal()) + ').' });
            }
            if (adjustmentTotal() > advanceAvailable) {
                errors.push({ id: 'itemsTable', msg: 'Advance adjustment (' + number_fmt(adjustmentTotal()) + ') exceeds the available advance balance (' + number_fmt(advanceAvailable) + ').' });
            }
        }

        if (type === 'Advance' && currentAdvanceMode() === 'job') {
            var seenJobs = {};
            if ($('#jobsBody tr.job-row').length === 0) {
                errors.push({ id: 'btnAddJob', msg: 'Add at least one job, or choose General advance (no job).' });
            }
            $('#jobsBody tr.job-row').each(function (i) {
                var $tr = $(this);
                var jobId = $tr.attr('data-job-id');
                var $amount = $tr.find('.job-amount');
                var label = 'Job row ' + (i + 1);

                if (!jobId) {
                    setJobRowError($tr, 'Select a job or remove this row.');
                    errors.push({ id: $amount.attr('id'), msg: label + ': select a job or remove the row.' });
                    return;
                }
                if (seenJobs[jobId]) {
                    setJobRowError($tr, 'This job is already added.');
                    errors.push({ id: $amount.attr('id'), msg: label + ': this job is already added.' });
                    return;
                }
                seenJobs[jobId] = true;

                if (!(parseFloat($amount.val()) > 0)) {
                    setJobRowError($tr, 'Enter an amount greater than 0.');
                    errors.push({ id: $amount.attr('id'), msg: label + ': enter an amount greater than 0.' });
                }
            });

            if (jobAssignedTotal() > collectedTotal()) {
                errors.push({ id: 'jobsTable', msg: 'Job-wise advance (' + number_fmt(jobAssignedTotal()) + ') cannot exceed the collected total (' + number_fmt(collectedTotal()) + ').' });
            }
        }

        if (errors.length) {
            e.preventDefault();
            showClientErrors(errors);
            return false;
        }

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    });
});
</script>
@endpush

@extends('chevron.layouts.app')

@section('title', $expense ? 'Edit Job Expense' : 'Job Expense Entry')

@push('styles')
<style>
.exp-topbar { background: linear-gradient(135deg, #0d2626 0%, #0d6e6e 60%, #14b8a6 100%); color: #fff; padding: .55rem 1rem; display: flex; align-items: center; justify-content: space-between; margin: -1.5rem -1.5rem 1.25rem; }
.exp-topbar .title { font-size: 1rem; font-weight: 700; letter-spacing: .02em; }
.exp-topbar .btn { font-size: .8rem; }
.form-label { font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .18rem; }
.req { color: #dc2626; }
.ro-field { background: #f1f5f9 !important; color: #6b7280; }
#rowsTable th { background: #1e293b; color: #e2e8f0; font-size: .78rem; font-weight: 600; padding: .45rem .5rem; white-space: nowrap; }
#rowsTable td { padding: .25rem .4rem; vertical-align: middle; }
#rowsTable tbody tr:nth-child(even) { background: #f8fafc; }
</style>
@endpush

@section('content')

{{-- Top bar --}}
<div class="exp-topbar">
    <div class="d-flex gap-2">
        <a href="{{ route('chevron.cnf.job-expenses.index') }}" class="btn btn-sm btn-light text-dark">
            <i class="fa fa-arrow-left me-1"></i> Back To List
        </a>
    </div>
    <div class="title">Job Expense Entry @if($expense)<span class="ms-2 badge bg-light text-dark">{{ $expense->expense_no }}</span>@endif</div>
    <div></div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2">
    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form id="expenseForm" method="POST"
      action="{{ $expense ? route('chevron.cnf.job-expenses.update', $expense->id) : route('chevron.cnf.job-expenses.store') }}">
    @csrf
    @if($expense) @method('PUT') @endif

    {{-- Hidden fields --}}
    <input type="hidden" name="job_id"      id="jobId"      value="{{ old('job_id', $expense?->job_id) }}">
    <input type="hidden" name="employee_id" id="employeeId" value="{{ old('employee_id', $expense?->employee_id) }}">
    <input type="hidden" name="total_expense_amount"  id="hidTotalExp"  value="{{ old('total_expense_amount', $expense?->total_expense_amount ?? 0) }}">
    <input type="hidden" name="total_approved_amount" id="hidTotalApp"  value="{{ old('total_approved_amount', $expense?->total_approved_amount ?? 0) }}">

    {{-- Header fields --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-3">
                {{-- Left col --}}
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Job No <span class="req">*</span></label>
                            <select id="jobSelect" class="form-select form-select-sm w-100">
                                @if($expense?->job_no)
                                    <option value="{{ $expense->job_id }}" selected>{{ $expense->job_no }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="job_no" id="jobNo" value="{{ old('job_no', $expense?->job_no) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">B/E No</label>
                            <input type="text" name="be_no" id="beNo" class="form-control form-control-sm ro-field" readonly value="{{ old('be_no', $expense?->be_no) }}" placeholder="Auto-filled from job">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invoice No</label>
                            <input type="text" name="invoice_no" id="invoiceNo" class="form-control form-control-sm ro-field" readonly value="{{ old('invoice_no', $expense?->invoice_no) }}" placeholder="Auto-filled from job">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Total Expense Amount</label>
                            <input type="text" id="totalExpDisplay" class="form-control form-control-sm ro-field" readonly value="{{ number_format($expense?->total_expense_amount ?? 0, 2) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Remarks">{{ old('remarks', $expense?->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
                {{-- Right col --}}
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Employee Name <span class="req">*</span></label>
                            <select id="employeeSelect" class="form-select form-select-sm w-100">
                                @if($expense?->employee)
                                    <option value="{{ $expense->employee_id }}" selected>{{ $expense->employee->employee_id }} — {{ $expense->employee->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Date <span class="req">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ old('date', $expense?->date?->format('Y-m-d') ?? $today) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invoice Value (USD)</label>
                            <input type="text" name="invoice_value_usd" id="invoiceValueUsd" class="form-control form-control-sm ro-field" readonly value="{{ old('invoice_value_usd', $expense?->invoice_value_usd) }}" placeholder="Auto-filled from job">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Total Approved Amount</label>
                            <input type="text" id="totalAppDisplay" class="form-control form-control-sm ro-field" readonly value="{{ number_format($expense?->total_approved_amount ?? 0, 2) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">B/L NO</label>
                            <input type="text" name="bl_no" id="blNo" class="form-control form-control-sm ro-field" readonly value="{{ old('bl_no', $expense?->bl_no) }}" placeholder="Auto-filled from job">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rows table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-600 small"><i class="fa fa-list-ul me-1"></i> Expense Lines</span>
            <button type="button" id="btnAddRow" class="btn btn-sm btn-danger">
                <i class="fa fa-plus me-1"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="rowsTable" class="table table-bordered mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width:40px;">SL</th>
                            <th style="width:36px;"></th>
                            <th>Expense Head</th>
                            <th style="width:100px;">Receiptable</th>
                            <th style="width:130px;">Expense Amount</th>
                            <th style="width:130px;">Approved Amount</th>
                            <th style="width:130px;">Expense Date</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody">
                        @php
                        $existingRows = $expense?->items ?? collect();
                        if ($existingRows->isEmpty()) {
                            $existingRows = collect([null]); // one empty row on create
                        }
                        @endphp
                        @foreach($existingRows as $i => $row)
                        <tr class="expense-row">
                            <td class="text-center sl-num">{{ $i + 1 }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-remove-row p-0 px-1"><i class="fa fa-times"></i></button>
                            </td>
                            <td>
                                <select name="rows[{{ $i }}][expense_head_id]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    @foreach($expenseHeads as $head)
                                        <option value="{{ $head->id }}" {{ $row?->expense_head_id == $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="rows[{{ $i }}][receiptable]" class="form-select form-select-sm">
                                    <option value="No"  {{ ($row?->receiptable ?? 'No') === 'No'  ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ ($row?->receiptable)         === 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </td>
                            <td><input type="number" name="rows[{{ $i }}][expense_amount]"  class="form-control form-control-sm expense-amt text-end" step="0.01" min="0" value="{{ $row?->expense_amount ?? 0 }}"></td>
                            <td><input type="number" name="rows[{{ $i }}][approved_amount]" class="form-control form-control-sm approved-amt text-end" step="0.01" min="0" value="{{ $row?->approved_amount ?? 0 }}"></td>
                            <td><input type="date"   name="rows[{{ $i }}][expense_date]"    class="form-control form-control-sm" value="{{ $row?->expense_date?->format('Y-m-d') ?? $today }}"></td>
                            <td><input type="text"   name="rows[{{ $i }}][note]"             class="form-control form-control-sm" value="{{ $row?->note }}" placeholder="Note"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Submit bar --}}
    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="{{ route('chevron.cnf.job-expenses.index') }}" class="btn btn-outline-secondary btn-sm px-4">
            <i class="fa fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-sm px-5 text-white fw-600" style="background:#0d9488; border-color:#0d9488;">
            <i class="fa fa-save me-1"></i> {{ $expense ? 'Update' : 'Submit' }}
        </button>
    </div>
</form>

{{-- Row template (hidden) --}}
<template id="rowTemplate">
    <tr class="expense-row">
        <td class="text-center sl-num"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-row p-0 px-1"><i class="fa fa-times"></i></button>
        </td>
        <td>
            <select name="" class="form-select form-select-sm">
                <option value="">-- Select --</option>
                @foreach($expenseHeads as $head)
                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="" class="form-select form-select-sm">
                <option value="No">No</option>
                <option value="Yes">Yes</option>
            </select>
        </td>
        <td><input type="number" name="" class="form-control form-control-sm expense-amt text-end" step="0.01" min="0" value="0"></td>
        <td><input type="number" name="" class="form-control form-control-sm approved-amt text-end" step="0.01" min="0" value="0"></td>
        <td><input type="date" name="" class="form-control form-control-sm" value="{{ $today }}"></td>
        <td><input type="text" name="" class="form-control form-control-sm" placeholder="Note"></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
var SEARCH_JOBS      = '{{ route('chevron.cnf.job-expenses.search-jobs') }}';
var SEARCH_EMPLOYEES = '{{ route('chevron.cnf.job-expenses.search-employees') }}';
var TODAY            = '{{ $today }}';

$(function () {

    // ── Job No Select2 ──
    $('#jobSelect').select2({
        theme: 'bootstrap-5', width: '100%',
        placeholder: 'Enter minimum 3 characters',
        allowClear: true, minimumInputLength: 1,
        ajax: {
            url: SEARCH_JOBS, dataType: 'json', delay: 250,
            data: p => ({ q: p.term }),
            processResults: d => ({ results: d }),
        },
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#jobId').val(d.id);
        $('#jobNo').val(d.text);
        $('#beNo').val(d.be_no || '');
        $('#invoiceNo').val(d.invoice_no || '');
        $('#invoiceValueUsd').val(d.invoice_value_usd || '');
        $('#blNo').val(d.bl_no || '');
    }).on('select2:clear', function () {
        $('#jobId, #jobNo').val('');
        $('#beNo, #invoiceNo, #invoiceValueUsd, #blNo').val('');
    });

    // ── Employee Select2 ──
    $('#employeeSelect').select2({
        theme: 'bootstrap-5', width: '100%',
        placeholder: 'Enter employee name or code',
        allowClear: true, minimumInputLength: 1,
        ajax: {
            url: SEARCH_EMPLOYEES, dataType: 'json', delay: 250,
            data: p => ({ q: p.term }),
            processResults: d => ({ results: d }),
        },
    }).on('select2:select', function (e) {
        $('#employeeId').val(e.params.data.id);
    }).on('select2:clear', function () {
        $('#employeeId').val('');
    });

    // ── Add Row ──
    $('#btnAddRow').on('click', function () {
        var idx    = $('#rowsBody .expense-row').length;
        var tpl    = document.getElementById('rowTemplate').content.cloneNode(true);
        var $tr    = $(tpl).find('tr');

        // Fix field names with current index
        $tr.find('[name]').each(function () {
            var n = $(this).attr('name');
            if (n === '') {
                // set name based on position
            }
        });

        // Easier: build names directly
        $tr.find('select').eq(0).attr('name', 'rows[' + idx + '][expense_head_id]');
        $tr.find('select').eq(1).attr('name', 'rows[' + idx + '][receiptable]');
        $tr.find('input').eq(0).attr('name', 'rows[' + idx + '][expense_amount]');
        $tr.find('input').eq(1).attr('name', 'rows[' + idx + '][approved_amount]');
        $tr.find('input').eq(2).attr('name', 'rows[' + idx + '][expense_date]');
        $tr.find('input').eq(3).attr('name', 'rows[' + idx + '][note]');

        $('#rowsBody').append($tr);
        reindex();
    });

    // ── Remove Row ──
    $(document).on('click', '.btn-remove-row', function () {
        if ($('#rowsBody .expense-row').length <= 1) {
            Swal.fire({ icon: 'warning', title: 'At least one row required.', timer: 1500, showConfirmButton: false });
            return;
        }
        $(this).closest('tr').remove();
        reindex();
        recalcTotals();
    });

    function reindex() {
        $('#rowsBody .expense-row').each(function (i) {
            $(this).find('.sl-num').text(i + 1);
            $(this).find('select').eq(0).attr('name', 'rows[' + i + '][expense_head_id]');
            $(this).find('select').eq(1).attr('name', 'rows[' + i + '][receiptable]');
            $(this).find('input').eq(0).attr('name', 'rows[' + i + '][expense_amount]');
            $(this).find('input').eq(1).attr('name', 'rows[' + i + '][approved_amount]');
            $(this).find('input').eq(2).attr('name', 'rows[' + i + '][expense_date]');
            $(this).find('input').eq(3).attr('name', 'rows[' + i + '][note]');
        });
    }

    // ── Totals ──
    function recalcTotals() {
        var exp = 0, app = 0;
        $('#rowsBody .expense-row').each(function () {
            exp += parseFloat($(this).find('.expense-amt').val())  || 0;
            app += parseFloat($(this).find('.approved-amt').val()) || 0;
        });
        $('#totalExpDisplay').val(exp.toFixed(2));
        $('#totalAppDisplay').val(app.toFixed(2));
        $('#hidTotalExp').val(exp.toFixed(2));
        $('#hidTotalApp').val(app.toFixed(2));
    }

    $(document).on('input', '.expense-amt, .approved-amt', recalcTotals);

    reindex();
    recalcTotals();
});
</script>
@endpush

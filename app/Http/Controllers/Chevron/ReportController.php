<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Report\JobExpenseSummaryRequest;
use App\Http\Requests\Chevron\Report\JobExpenseVsBillRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Chevron\ChevronJobExpenseItem;
use App\Models\Employee;

class ReportController extends Controller
{
    public function jobExpenseSummary(JobExpenseSummaryRequest $request)
    {
        $employees = Employee::whereHas('branchAccess', fn ($q) => $q->where('branch_id', session('active_branch_id')))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $expenses = $this->hasFilters($request)
            ? $this->filteredExpenses($request)
            : collect();

        return view('chevron.reports.job-expense-summary', compact('employees', 'expenses'));
    }

    public function jobExpenseSummaryPrint(JobExpenseSummaryRequest $request)
    {
        $expenses = $this->filteredExpenses($request);

        return view('chevron.reports.job-expense-summary-print', compact('expenses'));
    }

    public function jobExpenseVsBill(JobExpenseVsBillRequest $request)
    {
        $rows = $this->hasVsBillFilters($request)
            ? $this->filteredExpenseRows($request)
            : collect();

        $billTotals = $this->billTotals($rows);

        return view('chevron.reports.job-expense-vs-bill', [
            'rows'         => $rows,
            'billTotals'   => $billTotals,
            'totalExpense' => $rows->sum('expense_amount'),
            'totalBill'    => $billTotals->sum(),
        ]);
    }

    public function jobExpenseVsBillPrint(JobExpenseVsBillRequest $request)
    {
        $rows = $this->filteredExpenseRows($request);

        $billTotals = $this->billTotals($rows);

        return view('chevron.reports.job-expense-vs-bill-print', [
            'rows'         => $rows,
            'billTotals'   => $billTotals,
            'totalExpense' => $rows->sum('expense_amount'),
            'totalBill'    => $billTotals->sum(),
        ]);
    }

    public function searchJobs(JobExpenseVsBillRequest $request)
    {
        $q = $request->get('q', '');

        $results = ChevronJob::where('status', 'Active')
            ->where(fn ($s) => $s->where('job_no', 'like', '%'.$q.'%')
                ->orWhere('party_name', 'like', '%'.$q.'%'))
            ->limit(20)
            ->select(['id', 'job_no', 'party_name'])
            ->get()
            ->map(fn ($j) => ['id' => $j->job_no, 'text' => $j->job_no]);

        return response()->json($results);
    }

    private function hasFilters(JobExpenseSummaryRequest $request): bool
    {
        return $request->filled('from_date')
            || $request->filled('to_date')
            || $request->filled('job_no')
            || $request->filled('employee_id');
    }

    private function filteredExpenses(JobExpenseSummaryRequest $request)
    {
        return ChevronJobExpense::with([
            'employee:id,name',
            'items.expenseHead:id,name',
        ])
            ->where('branch_id', session('active_branch_id'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('date', '<=', $request->to_date))
            ->when($request->filled('job_no'), fn ($q) => $q->where('job_no', 'like', '%'.$request->job_no.'%'))
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->orderBy('expense_no')
            ->get();
    }

    private function hasVsBillFilters(JobExpenseVsBillRequest $request): bool
    {
        return $request->filled('from_date')
            || $request->filled('to_date')
            || $request->filled('job_no');
    }

    private function filteredExpenseRows(JobExpenseVsBillRequest $request)
    {
        return ChevronJobExpenseItem::query()
            ->select('chevron_job_expense_items.*')
            ->join('chevron_job_expenses', 'chevron_job_expenses.id', '=', 'chevron_job_expense_items.job_expense_id')
            ->with([
                'expenseHead:id,name,expense_category_id',
                'expenseHead.expenseCategory:id,name',
                'jobExpense:id,expense_no,job_id,job_no,employee_id,date',
                'jobExpense.employee:id,name,code',
                'jobExpense.job:id,job_no,party_name',
            ])
            ->where('chevron_job_expenses.branch_id', session('active_branch_id'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('chevron_job_expense_items.expense_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('chevron_job_expense_items.expense_date', '<=', $request->to_date))
            ->when($request->filled('job_no'), fn ($q) => $q->where('chevron_job_expenses.job_no', 'like', '%'.$request->job_no.'%'))
            ->orderBy('chevron_job_expenses.job_no')
            ->orderBy('chevron_job_expense_items.expense_date')
            ->orderBy('chevron_job_expense_items.id')
            ->get();
    }

    private function billTotals($rows)
    {
        $jobIds = $rows->pluck('jobExpense.job_id')->filter()->unique()->values();

        return ChevronBill::query()
            ->where('branch_id', session('active_branch_id'))
            ->whereIn('job_id', $jobIds)
            ->selectRaw('job_id, SUM(net_payable) as total_bill')
            ->groupBy('job_id')
            ->pluck('total_bill', 'job_id');
    }
}

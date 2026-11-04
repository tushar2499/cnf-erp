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
use Yajra\DataTables\Facades\DataTables;

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
        if ($request->ajax()) {
            return $this->jobExpenseVsBillDataTable($request);
        }

        return view('chevron.reports.job-expense-vs-bill', [
            'hasFilters' => $this->hasVsBillFilters($request),
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

    private function jobExpenseVsBillDataTable(JobExpenseVsBillRequest $request)
    {
        $joined = $this->expenseRowsQuery($request)
            ->when(! $this->hasVsBillFilters($request), fn ($q) => $q->whereRaw('1 = 0'))
            ->leftJoin('chevron_jobs', 'chevron_jobs.id', '=', 'chevron_job_expenses.job_id')
            ->leftJoin('employees', 'employees.id', '=', 'chevron_job_expenses.employee_id')
            ->leftJoin('chevron_expense_heads', 'chevron_expense_heads.id', '=', 'chevron_job_expense_items.expense_head_id')
            ->leftJoin('chevron_expense_categories', 'chevron_expense_categories.id', '=', 'chevron_expense_heads.expense_category_id');

        $totalsQuery = $this->applyDataTableSearch(clone $joined, $request);

        $totalExpense = (clone $totalsQuery)->reorder()->sum('chevron_job_expense_items.expense_amount');
        $totalRows = (clone $totalsQuery)->count();

        $jobIds = (clone $totalsQuery)->reorder()
            ->select('chevron_job_expenses.job_id')
            ->distinct()
            ->pluck('chevron_job_expenses.job_id')
            ->filter();

        $totalBill = ChevronBill::query()
            ->where('branch_id', session('active_branch_id'))
            ->whereIn('job_id', $jobIds)
            ->sum('net_payable');

        $query = (clone $joined)->addSelect([
            'bill_total' => ChevronBill::query()
                ->selectRaw('COALESCE(SUM(chevron_bills.net_payable), 0)')
                ->whereColumn('chevron_bills.job_id', 'chevron_job_expenses.job_id')
                ->where('chevron_bills.branch_id', session('active_branch_id')),
        ]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('job_no', fn ($r) => $r->jobExpense?->job_no ?? '—')
            ->addColumn('party_name', fn ($r) => $r->jobExpense?->job?->party_name ?? '—')
            ->editColumn('expense_date', fn ($r) => ($r->expense_date ?? $r->jobExpense?->date)?->format('d M Y') ?? '—')
            ->addColumn('expense_no', fn ($r) => $r->jobExpense?->expense_no ?? '—')
            ->addColumn('expense_by', fn ($r) => $r->jobExpense?->employee?->name ?? '—')
            ->addColumn('expense_category', fn ($r) => $r->expenseHead?->expenseCategory?->name ?? '—')
            ->addColumn('expense_head', fn ($r) => $r->expenseHead?->name ?? '—')
            ->addColumn('receiptable_badge', fn ($r) => $r->receiptable === 'Yes'
                ? '<span class="badge-yes">Yes</span>'
                : '<span class="badge-no">No</span>')
            ->editColumn('expense_amount', fn ($r) => number_format((float) $r->expense_amount, 2))
            ->editColumn('bill_total', fn ($r) => number_format((float) $r->bill_total, 2))
            ->rawColumns(['receiptable_badge'])
            ->filterColumn('job_no', fn ($q, $k) => $q->where('chevron_job_expenses.job_no', 'like', "%{$k}%"))
            ->filterColumn('party_name', fn ($q, $k) => $q->where('chevron_jobs.party_name', 'like', "%{$k}%"))
            ->filterColumn('expense_date', fn ($q, $k) => $q->where('chevron_job_expense_items.expense_date', 'like', "%{$k}%"))
            ->filterColumn('expense_no', fn ($q, $k) => $q->where('chevron_job_expenses.expense_no', 'like', "%{$k}%"))
            ->filterColumn('expense_by', fn ($q, $k) => $q->where('employees.name', 'like', "%{$k}%"))
            ->filterColumn('expense_category', fn ($q, $k) => $q->where('chevron_expense_categories.name', 'like', "%{$k}%"))
            ->filterColumn('expense_head', fn ($q, $k) => $q->where('chevron_expense_heads.name', 'like', "%{$k}%"))
            ->filterColumn('receiptable_badge', fn ($q, $k) => $q->where('chevron_job_expense_items.receiptable', 'like', "%{$k}%"))
            ->filterColumn('expense_amount', fn ($q, $k) => $q->where('chevron_job_expense_items.expense_amount', 'like', "%{$k}%"))
            ->filterColumn('bill_total', fn ($q, $k) => $q->whereRaw(
                $this->billTotalSubquery().' LIKE ?',
                [session('active_branch_id'), "%{$k}%"]
            ))
            ->orderColumn('job_no', fn ($q, $dir) => $q->orderBy('chevron_job_expenses.job_no', $dir))
            ->orderColumn('party_name', fn ($q, $dir) => $q->orderBy('chevron_jobs.party_name', $dir))
            ->orderColumn('expense_no', fn ($q, $dir) => $q->orderBy('chevron_job_expenses.expense_no', $dir))
            ->orderColumn('expense_by', fn ($q, $dir) => $q->orderBy('employees.name', $dir))
            ->orderColumn('expense_category', fn ($q, $dir) => $q->orderBy('chevron_expense_categories.name', $dir))
            ->orderColumn('expense_head', fn ($q, $dir) => $q->orderBy('chevron_expense_heads.name', $dir))
            ->orderColumn('receiptable_badge', fn ($q, $dir) => $q->orderBy('chevron_job_expense_items.receiptable', $dir))
            ->orderColumn('bill_total', fn ($q, $dir) => $q->orderBy('bill_total', $dir))
            ->with([
                'total_expense' => number_format((float) $totalExpense, 2),
                'total_bill'    => number_format((float) $totalBill, 2),
                'difference'    => number_format((float) $totalExpense - (float) $totalBill, 2),
                'total_rows'    => $totalRows,
                'total_jobs'    => $jobIds->count(),
            ])
            ->make(true);
    }

    private function applyDataTableSearch($query, JobExpenseVsBillRequest $request)
    {
        $branchId = session('active_branch_id');
        $globalSearch = trim((string) $request->input('search.value', ''));

        if ($globalSearch !== '') {
            $like = '%'.$globalSearch.'%';

            $query->where(function ($q) use ($like, $branchId) {
                $q->where('chevron_job_expenses.job_no', 'like', $like)
                    ->orWhere('chevron_jobs.party_name', 'like', $like)
                    ->orWhere('chevron_job_expense_items.expense_date', 'like', $like)
                    ->orWhere('chevron_job_expenses.expense_no', 'like', $like)
                    ->orWhere('employees.name', 'like', $like)
                    ->orWhere('chevron_expense_categories.name', 'like', $like)
                    ->orWhere('chevron_expense_heads.name', 'like', $like)
                    ->orWhere('chevron_job_expense_items.receiptable', 'like', $like)
                    ->orWhere('chevron_job_expense_items.expense_amount', 'like', $like)
                    ->orWhereRaw($this->billTotalSubquery().' LIKE ?', [$branchId, $like]);
            });
        }

        foreach ((array) $request->input('columns', []) as $column) {
            $name = $column['name'] ?? $column['data'] ?? null;
            $keyword = trim((string) ($column['search']['value'] ?? ''));

            if (! $name || $keyword === '') {
                continue;
            }

            $like = '%'.$keyword.'%';

            match ($name) {
                'job_no'            => $query->where('chevron_job_expenses.job_no', 'like', $like),
                'party_name'        => $query->where('chevron_jobs.party_name', 'like', $like),
                'expense_date'      => $query->where('chevron_job_expense_items.expense_date', 'like', $like),
                'expense_no'        => $query->where('chevron_job_expenses.expense_no', 'like', $like),
                'expense_by'        => $query->where('employees.name', 'like', $like),
                'expense_category'  => $query->where('chevron_expense_categories.name', 'like', $like),
                'expense_head'      => $query->where('chevron_expense_heads.name', 'like', $like),
                'receiptable_badge' => $query->where('chevron_job_expense_items.receiptable', 'like', $like),
                'expense_amount'    => $query->where('chevron_job_expense_items.expense_amount', 'like', $like),
                'bill_total'        => $query->whereRaw($this->billTotalSubquery().' LIKE ?', [$branchId, $like]),
                default             => null,
            };
        }

        return $query;
    }

    private function billTotalSubquery(): string
    {
        return '(SELECT COALESCE(SUM(chevron_bills.net_payable), 0) FROM chevron_bills WHERE chevron_bills.job_id = chevron_job_expenses.job_id AND chevron_bills.branch_id = ?)';
    }

    private function expenseRowsQuery(JobExpenseVsBillRequest $request)
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
            ->when($request->filled('job_no'), fn ($q) => $q->where('chevron_job_expenses.job_no', 'like', '%'.$request->job_no.'%'));
    }

    private function filteredExpenseRows(JobExpenseVsBillRequest $request)
    {
        return $this->expenseRowsQuery($request)
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

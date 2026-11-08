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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    private const VS_BILL_VIEW = 'chevron_job_expense_vs_bill_view';

    /**
     * DataTable column name => column on the vs-bill view.
     */
    private const VS_BILL_SEARCH_COLUMNS = [
        'job_no'            => 'job_no',
        'party_name'        => 'party_name',
        'expense_date'      => 'expense_date',
        'expense_no'        => 'expense_no',
        'expense_by'        => 'expense_by',
        'expense_category'  => 'expense_category',
        'expense_head'      => 'expense_head',
        'receiptable_badge' => 'receiptable',
        'expense_amount'    => 'expense_amount',
        'bill_total'        => 'bill_total',
    ];

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
        $base = $this->vsBillViewQuery($request)
            ->when(! $this->hasVsBillFilters($request), fn ($q) => $q->whereRaw('1 = 0'));

        $totalsQuery = $this->applyDataTableSearch(clone $base, $request);

        $totalExpense = (clone $totalsQuery)->sum('expense_amount');
        $totalRows = (clone $totalsQuery)->count();

        $jobBills = (clone $totalsQuery)
            ->whereNotNull('job_id')
            ->distinct()
            ->get(['job_id', 'bill_total']);

        $totalBill = $jobBills->sum('bill_total');

        return DataTables::query($base)
            ->addIndexColumn()
            ->editColumn('job_no', fn ($r) => $r->job_no ?? '—')
            ->editColumn('party_name', fn ($r) => $r->party_name ?? '—')
            ->editColumn('expense_date', fn ($r) => $r->expense_date ? Carbon::parse($r->expense_date)->format('d M Y') : '—')
            ->editColumn('expense_no', fn ($r) => $r->expense_no ?? '—')
            ->editColumn('expense_by', fn ($r) => $r->expense_by ?? '—')
            ->editColumn('expense_category', fn ($r) => $r->expense_category ?? '—')
            ->editColumn('expense_head', fn ($r) => $r->expense_head ?? '—')
            ->addColumn('receiptable_badge', fn ($r) => $r->receiptable === 'Yes'
                ? '<span class="badge-yes">Yes</span>'
                : '<span class="badge-no">No</span>')
            ->editColumn('expense_amount', fn ($r) => number_format((float) $r->expense_amount, 2))
            ->editColumn('bill_total', fn ($r) => number_format((float) $r->bill_total, 2))
            ->rawColumns(['receiptable_badge'])
            ->filterColumn('receiptable_badge', fn ($q, $k) => $q->where('receiptable', 'like', "%{$k}%"))
            ->orderColumn('receiptable_badge', fn ($q, $dir) => $q->orderBy('receiptable', $dir))
            ->with([
                'total_expense' => number_format((float) $totalExpense, 2),
                'total_bill'    => number_format((float) $totalBill, 2),
                'difference'    => number_format((float) $totalExpense - (float) $totalBill, 2),
                'total_rows'    => $totalRows,
                'total_jobs'    => $jobBills->count(),
            ])
            ->make(true);
    }

    private function vsBillViewQuery(JobExpenseVsBillRequest $request)
    {
        return DB::table(self::VS_BILL_VIEW)
            ->where('branch_id', session('active_branch_id'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('expense_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('expense_date', '<=', $request->to_date))
            ->when($request->filled('job_no'), fn ($q) => $q->where('job_no', 'like', '%'.$request->job_no.'%'));
    }

    private function applyDataTableSearch($query, JobExpenseVsBillRequest $request)
    {
        $globalSearch = trim((string) $request->input('search.value', ''));

        if ($globalSearch !== '') {
            $like = '%'.$globalSearch.'%';

            $query->where(function ($q) use ($like) {
                foreach (self::VS_BILL_SEARCH_COLUMNS as $dbColumn) {
                    $q->orWhere($dbColumn, 'like', $like);
                }
            });
        }

        foreach ((array) $request->input('columns', []) as $column) {
            $name = $column['name'] ?? $column['data'] ?? null;
            $keyword = trim((string) ($column['search']['value'] ?? ''));
            $dbColumn = self::VS_BILL_SEARCH_COLUMNS[$name] ?? null;

            if ($dbColumn === null || $keyword === '') {
                continue;
            }

            $query->where($dbColumn, 'like', '%'.$keyword.'%');
        }

        return $query;
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

<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Report\JobBillRequest;
use App\Http\Requests\Chevron\Report\JobExpenseRequest;
use App\Http\Requests\Chevron\Report\JobExpenseSummaryRequest;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    private const EXPENSE_VIEW = 'chevron_job_expense_view';

    private const BILL_VIEW = 'chevron_job_bill_view';

    /**
     * DataTable column name => column on the job expense view.
     */
    private const EXPENSE_SEARCH_COLUMNS = [
        'branch_name'       => 'branch_name',
        'job_no'            => 'job_no',
        'party_name'        => 'party_name',
        'expense_date'      => 'expense_date',
        'expense_no'        => 'expense_no',
        'expense_by'        => 'expense_by',
        'expense_category'  => 'expense_category',
        'expense_head'      => 'expense_head',
        'receiptable_badge' => 'receiptable',
        'expense_amount'    => 'expense_amount',
    ];

    /**
     * DataTable column name => column on the job bill view.
     */
    private const BILL_SEARCH_COLUMNS = [
        'branch_name'      => 'branch_name',
        'job_no'           => 'job_no',
        'party_name'       => 'party_name',
        'bill_date'        => 'bill_date',
        'bill_no'          => 'bill_no',
        'bill_type'        => 'bill_type',
        'expense_category' => 'expense_category',
        'expense_head'     => 'expense_head',
        'bill_amount'      => 'bill_amount',
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

    public function jobExpense(JobExpenseRequest $request)
    {
        if ($request->ajax()) {
            return $this->jobExpenseDataTable($request);
        }

        return view('chevron.reports.job-expense', [
            'hasFilters' => $this->hasReportFilters($request),
        ]);
    }

    public function jobExpensePrint(JobExpenseRequest $request)
    {
        $rows = $this->jobExpenseViewQuery($request)
            ->orderBy('job_no')
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get();

        return view('chevron.reports.job-expense-print', [
            'rows'         => $rows,
            'totalExpense' => $rows->sum('expense_amount'),
            'totalJobs'    => $rows->pluck('job_id')->filter()->unique()->count(),
        ]);
    }

    public function jobBill(JobBillRequest $request)
    {
        if ($request->ajax()) {
            return $this->jobBillDataTable($request);
        }

        return view('chevron.reports.job-bill', [
            'hasFilters' => $this->hasReportFilters($request),
        ]);
    }

    public function jobBillPrint(JobBillRequest $request)
    {
        $rows = $this->jobBillViewQuery($request)
            ->orderBy('job_no')
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get();

        return view('chevron.reports.job-bill-print', [
            'rows'      => $rows,
            'totalBill' => $rows->sum('bill_amount'),
            'totalJobs' => $rows->pluck('job_id')->filter()->unique()->count(),
        ]);
    }

    public function searchJobs(JobExpenseRequest $request)
    {
        return $this->searchActiveJobs($request);
    }

    public function searchBillJobs(JobBillRequest $request)
    {
        return $this->searchActiveJobs($request);
    }

    private function searchActiveJobs(Request $request)
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

    private function hasReportFilters(Request $request): bool
    {
        return $request->filled('from_date')
            || $request->filled('to_date')
            || $request->filled('job_no');
    }

    private function jobExpenseDataTable(JobExpenseRequest $request)
    {
        $base = $this->jobExpenseViewQuery($request)
            ->when(! $this->hasReportFilters($request), fn ($q) => $q->whereRaw('1 = 0'));

        $totalsQuery = $this->applyDataTableSearch(clone $base, $request, self::EXPENSE_SEARCH_COLUMNS);

        $totalExpense = (clone $totalsQuery)->sum('expense_amount');
        $totalRows = (clone $totalsQuery)->count();
        $totalJobs = (clone $totalsQuery)->whereNotNull('job_id')->distinct()->count('job_id');

        return DataTables::query($base)
            ->addIndexColumn()
            ->editColumn('branch_name', fn ($r) => $r->branch_name ?? '—')
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
            ->rawColumns(['receiptable_badge'])
            ->filterColumn('receiptable_badge', fn ($q, $k) => $q->where('receiptable', 'like', "%{$k}%"))
            ->orderColumn('receiptable_badge', fn ($q, $dir) => $q->orderBy('receiptable', $dir))
            ->with([
                'total_expense' => number_format((float) $totalExpense, 2),
                'total_rows'    => $totalRows,
                'total_jobs'    => $totalJobs,
            ])
            ->make(true);
    }

    private function jobExpenseViewQuery(JobExpenseRequest $request)
    {
        return DB::table(self::EXPENSE_VIEW)
            ->where('branch_id', session('active_branch_id'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('expense_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('expense_date', '<=', $request->to_date))
            ->when($request->filled('job_no'), fn ($q) => $q->where('job_no', 'like', '%'.$request->job_no.'%'));
    }

    private function jobBillDataTable(JobBillRequest $request)
    {
        $base = $this->jobBillViewQuery($request)
            ->when(! $this->hasReportFilters($request), fn ($q) => $q->whereRaw('1 = 0'));

        $totalsQuery = $this->applyDataTableSearch(clone $base, $request, self::BILL_SEARCH_COLUMNS);

        $totalBill = (clone $totalsQuery)->sum('bill_amount');
        $totalRows = (clone $totalsQuery)->count();
        $totalJobs = (clone $totalsQuery)->whereNotNull('job_id')->distinct()->count('job_id');

        return DataTables::query($base)
            ->addIndexColumn()
            ->editColumn('branch_name', fn ($r) => $r->branch_name ?? '—')
            ->editColumn('job_no', fn ($r) => $r->job_no ?? '—')
            ->editColumn('party_name', fn ($r) => $r->party_name ?? '—')
            ->editColumn('bill_date', fn ($r) => $r->bill_date ? Carbon::parse($r->bill_date)->format('d M Y') : '—')
            ->editColumn('bill_no', fn ($r) => $r->bill_no ?? '—')
            ->editColumn('bill_type', fn ($r) => $r->bill_type ?? '—')
            ->editColumn('expense_category', fn ($r) => $r->expense_category ?? '—')
            ->editColumn('expense_head', fn ($r) => $r->expense_head ?? '—')
            ->editColumn('bill_amount', fn ($r) => number_format((float) $r->bill_amount, 2))
            ->with([
                'total_bill' => number_format((float) $totalBill, 2),
                'total_rows' => $totalRows,
                'total_jobs' => $totalJobs,
            ])
            ->make(true);
    }

    private function jobBillViewQuery(JobBillRequest $request)
    {
        return DB::table(self::BILL_VIEW)
            ->where('branch_id', session('active_branch_id'))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('bill_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('bill_date', '<=', $request->to_date))
            ->when($request->filled('job_no'), fn ($q) => $q->where('job_no', 'like', '%'.$request->job_no.'%'));
    }

    private function applyDataTableSearch($query, Request $request, array $searchColumns)
    {
        $globalSearch = trim((string) $request->input('search.value', ''));

        if ($globalSearch !== '') {
            $like = '%'.$globalSearch.'%';

            $query->where(function ($q) use ($like, $searchColumns) {
                foreach ($searchColumns as $dbColumn) {
                    $q->orWhere($dbColumn, 'like', $like);
                }
            });
        }

        foreach ((array) $request->input('columns', []) as $column) {
            $name = $column['name'] ?? $column['data'] ?? null;
            $keyword = trim((string) ($column['search']['value'] ?? ''));
            $dbColumn = $searchColumns[$name] ?? null;

            if ($dbColumn === null || $keyword === '') {
                continue;
            }

            $query->where($dbColumn, 'like', '%'.$keyword.'%');
        }

        return $query;
    }
}

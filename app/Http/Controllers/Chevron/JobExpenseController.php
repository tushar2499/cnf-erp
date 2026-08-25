<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JobExpenseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ChevronJobExpense::with('employee')
                ->where('branch_id', session('active_branch_id'));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('date', fn ($r) => $r->date?->format('d M Y') ?? '—')
                ->addColumn('employee_name', fn ($r) => $r->employee?->name ?? '—')
                ->addColumn('total_expense_amount_fmt', fn ($r) => number_format($r->total_expense_amount, 2))
                ->addColumn('total_approved_amount_fmt', fn ($r) => number_format($r->total_approved_amount, 2))
                ->addColumn('invoice_value_usd_fmt', fn ($r) => $r->invoice_value_usd ? number_format($r->invoice_value_usd, 2) : '—')
                ->filterColumn('employee_name', fn ($q, $k) => $q->whereHas('employee', fn ($s) => $s->where('name', 'like', "%{$k}%")))
                ->addColumn('status_badge', fn ($r) => match ($r->status) {
                    'Submitted' => '<span class="badge bg-warning text-dark">Submitted</span>',
                    'Approved'  => '<span class="badge bg-success">Approved</span>',
                    default     => '<span class="badge bg-secondary">Draft</span>',
                })
                ->addColumn('action', fn ($r) => '
                    <a href="'.route('chevron.cnf.job-expenses.edit', $r->id).'" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete"
                        data-url="'.route('chevron.cnf.job-expenses.destroy', $r->id).'"
                        data-name="'.e($r->expense_no).'"><i class="fa fa-trash"></i></button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.cnf.job-expenses.index');
    }

    public function create()
    {
        $heads = ChevronExpenseHead::where('is_active', true)
            ->whereHas('expenseCategory', fn ($q) => $q->where('is_job', true))
            ->orderBy('name')
            ->get();

        $employees = Employee::whereHas('branchAccess', fn ($q) => $q->where('branch_id', session('active_branch_id')))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('chevron.cnf.job-expenses.create', [
            'expense'          => null,
            'expenseHeads'     => $heads,
            'expenseHeadsJson' => $heads->map(fn ($h) => ['id' => $h->id, 'name' => $h->name, 'amount' => (float) $h->amount])->values(),
            'employees'        => $employees,
            'today'            => now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'job_id'                 => ['required'],
            'employee_id'            => ['required'],
            'date'                   => ['required', 'date'],
            'rows'                   => ['required', 'array', 'min:1'],
            'rows.*.expense_head_id' => ['required'],
            'rows.*.expense_date'    => ['required', 'date'],
        ]);

        $job = ChevronJob::find($request->job_id);
        if (! $job || $job->status !== 'Active') {
            return back()->withInput()->withErrors(['job_id' => 'Selected job is not active.']);
        }

        $employee = Employee::find($request->employee_id);
        if (! $employee || ! $employee->branchAccess()->where('branch_id', session('active_branch_id'))->exists()) {
            return back()->withInput()->withErrors(['employee_id' => 'Selected employee does not belong to the active branch.']);
        }

        DB::transaction(function () use ($request) {
            $expense = ChevronJobExpense::create([
                'expense_no'            => ChevronJobExpense::generateExpenseNo(),
                'branch_id'             => session('active_branch_id'),
                'job_id'                => $request->job_id,
                'job_no'                => $request->job_no,
                'employee_id'           => $request->employee_id,
                'be_no'                 => $request->be_no,
                'invoice_no'            => $request->invoice_no,
                'invoice_value_usd'     => $request->invoice_value_usd ?: null,
                'bl_no'                 => $request->bl_no,
                'date'                  => $request->date,
                'total_expense_amount'  => $request->total_expense_amount ?? 0,
                'total_approved_amount' => $request->total_approved_amount ?? 0,
                'remarks'               => $request->remarks,
                'status'                => ($request->total_approved_amount > 0) ? 'Approved' : 'Draft',
            ]);

            foreach ($request->rows as $row) {
                $expense->items()->create([
                    'expense_head_id' => $row['expense_head_id'],
                    'receiptable'     => $row['receiptable'] ?? 'No',
                    'expense_amount'  => $row['expense_amount'] ?? 0,
                    'approved_amount' => $row['approved_amount'] ?? 0,
                    'expense_date'    => $row['expense_date'],
                    'note'            => $row['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('chevron.cnf.job-expenses.index')
            ->with('success', 'Job expense created successfully.');
    }

    public function edit(ChevronJobExpense $jobExpense)
    {
        $jobExpense->load('items');

        $heads = ChevronExpenseHead::where('is_active', true)
            ->whereHas('expenseCategory', fn ($q) => $q->where('is_job', true))
            ->orderBy('name')
            ->get();

        $employees = Employee::whereHas('branchAccess', fn ($q) => $q->where('branch_id', session('active_branch_id')))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('chevron.cnf.job-expenses.create', [
            'expense'          => $jobExpense,
            'expenseHeads'     => $heads,
            'expenseHeadsJson' => $heads->map(fn ($h) => ['id' => $h->id, 'name' => $h->name, 'amount' => (float) $h->amount])->values(),
            'employees'        => $employees,
            'today'            => now()->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, ChevronJobExpense $jobExpense)
    {
        $request->validate([
            'job_id'                 => ['required'],
            'employee_id'            => ['required'],
            'date'                   => ['required', 'date'],
            'rows'                   => ['required', 'array', 'min:1'],
            'rows.*.expense_head_id' => ['required'],
            'rows.*.expense_date'    => ['required', 'date'],
        ]);

        $employee = Employee::find($request->employee_id);
        if (! $employee || ! $employee->branchAccess()->where('branch_id', session('active_branch_id'))->exists()) {
            return back()->withInput()->withErrors(['employee_id' => 'Selected employee does not belong to the active branch.']);
        }

        DB::transaction(function () use ($request, $jobExpense) {
            $jobExpense->update([
                'job_id'                => $request->job_id,
                'job_no'                => $request->job_no,
                'employee_id'           => $request->employee_id,
                'be_no'                 => $request->be_no,
                'invoice_no'            => $request->invoice_no,
                'invoice_value_usd'     => $request->invoice_value_usd ?: null,
                'bl_no'                 => $request->bl_no,
                'date'                  => $request->date,
                'total_expense_amount'  => $request->total_expense_amount ?? 0,
                'total_approved_amount' => $request->total_approved_amount ?? 0,
                'remarks'               => $request->remarks,
                'status'                => ($request->total_approved_amount > 0) ? 'Approved' : 'Draft',
            ]);

            $jobExpense->items()->delete();
            foreach ($request->rows as $row) {
                $jobExpense->items()->create([
                    'expense_head_id' => $row['expense_head_id'],
                    'receiptable'     => $row['receiptable'] ?? 'No',
                    'expense_amount'  => $row['expense_amount'] ?? 0,
                    'approved_amount' => $row['approved_amount'] ?? 0,
                    'expense_date'    => $row['expense_date'],
                    'note'            => $row['note'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Job expense updated successfully.');
    }

    public function destroy(ChevronJobExpense $jobExpense)
    {
        $jobExpense->delete();

        return response()->json(['message' => 'Expense '.$jobExpense->expense_no.' deleted.']);
    }

    public function searchJobs(Request $request)
    {
        $q = $request->get('q', '');
        $results = ChevronJob::where('status', 'Active')
            ->where(fn ($s) => $s->where('job_no', 'like', '%'.$q.'%')
                ->orWhere('party_name', 'like', '%'.$q.'%'))
            ->limit(20)
            ->select(['id', 'job_no', 'be_no', 'invoice_no', 'invoice_value_1', 'bl_no'])
            ->get()
            ->map(fn ($j) => [
                'id'                => $j->id,
                'text'              => $j->job_no,
                'be_no'             => $j->be_no,
                'invoice_no'        => $j->invoice_no,
                'invoice_value_usd' => $j->invoice_value_1,
                'bl_no'             => $j->bl_no,
            ]);

        return response()->json($results);
    }

    public function searchEmployees(Request $request)
    {
        $q = $request->get('q', '');

        $results = Employee::whereHas('branchAccess', fn ($q2) => $q2->where('branch_id', session('active_branch_id')))
            ->where('is_active', true)
            ->where(fn ($s) => $s->where('name', 'like', '%'.$q.'%')
                ->orWhere('code', 'like', '%'.$q.'%'))
            ->limit(20)
            ->get(['id', 'code', 'name'])
            ->map(fn ($e) => [
                'id'   => $e->id,
                'text' => $e->code.' — '.$e->name,
                'name' => $e->name,
            ]);

        return response()->json($results);
    }
}

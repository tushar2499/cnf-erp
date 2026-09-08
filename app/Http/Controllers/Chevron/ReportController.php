<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chevron\Report\JobExpenseSummaryRequest;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Chevron\ChevronJobExpense;

class ReportController extends Controller
{
    public function jobExpenseSummary(JobExpenseSummaryRequest $request)
    {
        $employees = ChevronEmployee::orderBy('name')->get(['id', 'name', 'employee_id']);

        $expenses = collect();

        if ($request->hasAny(['from_date', 'to_date', 'job_no', 'employee_id'])) {
            $query = ChevronJobExpense::with([
                'employee:id,name,employee_id',
                'items.expenseHead:id,name',
            ])->orderBy('expense_no');

            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }
            if ($request->filled('job_no')) {
                $query->where('job_no', 'like', '%'.$request->job_no.'%');
            }
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $expenses = $query->get();
        }

        return view('chevron.reports.job-expense-summary', compact('employees', 'expenses'));
    }

    public function jobExpenseSummaryPrint(JobExpenseSummaryRequest $request)
    {
        $query = ChevronJobExpense::with([
            'employee:id,name,employee_id',
            'items.expenseHead:id,name',
        ])->orderBy('expense_no');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }
        if ($request->filled('job_no')) {
            $query->where('job_no', 'like', '%'.$request->job_no.'%');
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $expenses = $query->get();

        return view('chevron.reports.job-expense-summary-print', compact('expenses'));
    }
}

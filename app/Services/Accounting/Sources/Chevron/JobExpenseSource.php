<?php

namespace App\Services\Accounting\Sources\Chevron;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Approved C&F job expenses, split by expense head and receiptable flag.
 */
class JobExpenseSource extends AccountingSource
{
    public function key(): string
    {
        return 'chevron.job_expense';
    }

    public function label(): string
    {
        return 'C&F Operation — Job Expense';
    }

    public function companySlug(): string
    {
        return 'chevron-lines';
    }

    public function components(): array
    {
        return [
            'receiptable'     => ['label' => 'Receiptable expense (approved)', 'match' => 'chevron_expense_head'],
            'non_receiptable' => ['label' => 'Non-receiptable expense (approved)', 'match' => 'chevron_expense_head'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $expenses = DB::table('chevron_job_expenses as e')
            ->leftJoin('employees as emp', 'emp.id', '=', 'e.employee_id')
            ->whereBetween('e.date', self::range($from, $to))
            ->where('e.status', 'Approved')
            ->orderBy('e.date')->orderBy('e.id')
            ->get(['e.*', 'emp.name as employee_name']);

        $items = DB::table('chevron_job_expense_items')
            ->whereIn('job_expense_id', $expenses->pluck('id'))
            ->get()
            ->groupBy('job_expense_id');

        return $expenses->map(function ($exp) use ($items) {
            $entry = $this->entry($exp, (string) $exp->id, $exp->date, $exp->expense_no, $exp->branch_id,
                'employee', $exp->employee_id, $exp->employee_name, "Job expense {$exp->expense_no} — job {$exp->job_no}");

            $groups = ($items[$exp->id] ?? collect())
                ->groupBy(fn ($i) => (strtolower((string) $i->receiptable) === 'yes' ? 'receiptable' : 'non_receiptable').'|'.$i->expense_head_id);

            foreach ($groups as $key => $rows) {
                [$component, $headId] = explode('|', $key);
                $entry->addPart($component, $rows->sum('approved_amount'), $headId);
            }

            return $entry;
        });
    }
}

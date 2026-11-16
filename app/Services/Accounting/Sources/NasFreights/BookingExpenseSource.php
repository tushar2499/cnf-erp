<?php

namespace App\Services\Accounting\Sources\NasFreights;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Freight import / export booking (job) expenses — approved amounts per expense head.
 */
class BookingExpenseSource extends AccountingSource
{
    public function key(): string
    {
        return 'freights.booking_expense';
    }

    public function label(): string
    {
        return 'Freight Import / Export — Job Expense';
    }

    public function companySlug(): string
    {
        return 'nas-freights';
    }

    public function components(): array
    {
        return [
            'import_expense' => ['label' => 'Import job expense (approved)', 'match' => 'freights_expense_head'],
            'export_expense' => ['label' => 'Export job expense (approved)', 'match' => 'freights_expense_head'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $expenses = DB::table('nas_freights_freight_booking_expenses as e')
            ->leftJoin('employees as emp', 'emp.id', '=', 'e.employee_id')
            ->whereBetween('e.date', self::range($from, $to))
            ->where('e.status', 'Approved')
            ->orderBy('e.date')->orderBy('e.id')
            ->get(['e.*', 'emp.name as employee_name']);

        $items = DB::table('nas_freights_freight_booking_expense_items')
            ->whereIn('freight_booking_expense_id', $expenses->pluck('id'))
            ->get()
            ->groupBy('freight_booking_expense_id');

        return $expenses->map(function ($exp) use ($items) {
            $component = $exp->booking_type === 'export' ? 'export_expense' : 'import_expense';
            $entry = $this->entry($exp, (string) $exp->id, $exp->date, $exp->expense_no, $exp->branch_id,
                'employee', $exp->employee_id, $exp->employee_name,
                ucfirst($exp->booking_type)." job expense {$exp->expense_no} — booking {$exp->booking_no}");

            foreach (($items[$exp->id] ?? collect())->groupBy('expense_head_id') as $headId => $rows) {
                $entry->addPart($component, $rows->sum('approved_amount'), $headId);
            }

            return $entry;
        });
    }
}

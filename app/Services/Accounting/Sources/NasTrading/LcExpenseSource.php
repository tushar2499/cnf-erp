<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LcExpenseSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.lc_expense';
    }

    public function label(): string
    {
        return 'LC Operation — LC Expense';
    }

    public function companySlug(): string
    {
        return 'nas-trading';
    }

    public function components(): array
    {
        return [
            'expense' => ['label' => 'LC expense', 'match' => 'trading_expense_head'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_trading_lc_expenses as e')
            ->join('nas_trading_lcs as lc', 'lc.id', '=', 'e.lc_id')
            ->whereBetween('e.expense_date', self::range($from, $to))
            ->orderBy('e.expense_date')->orderBy('e.id')
            ->get(['e.*', 'lc.lc_no', 'lc.branch_id'])
            ->map(function ($exp) {
                $posting = trim(implode(' / ', array_filter([$exp->posting_type, $exp->posting_sub_type])));

                return $this->entry($exp, (string) $exp->id, $exp->expense_date, $exp->reference ?: 'LC '.$exp->lc_no, $exp->branch_id,
                    null, null, null, "LC {$exp->lc_no} — {$exp->expense_head_name}".($posting ? " ({$posting})" : ''))
                    ->addPart('expense', $exp->amount, $exp->expense_head_id);
            });
    }
}

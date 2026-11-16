<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * LC bills paid. Rows are re-created when the LC is saved, so the reference uses the row content.
 */
class LcBillPaidSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.lc_bill_paid';
    }

    public function label(): string
    {
        return 'LC Operation — LC Bill Paid';
    }

    public function companySlug(): string
    {
        return 'nas-trading';
    }

    public function voucherType(): string
    {
        return 'payment';
    }

    public function components(): array
    {
        return [
            'paid' => ['label' => 'Bill paid'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_trading_lc_bill_paids as bp')
            ->join('nas_trading_lcs as lc', 'lc.id', '=', 'bp.lc_id')
            ->whereBetween('bp.date', self::range($from, $to))
            ->orderBy('bp.date')->orderBy('bp.id')
            ->get(['bp.*', 'lc.lc_no', 'lc.branch_id'])
            ->map(function ($bp) {
                $ref = "{$bp->lc_id}:".substr($bp->date, 0, 10).':'.number_format((float) $bp->amount, 2, '.', '');

                $entry = $this->entry($bp, $ref, $bp->date, 'LC '.$bp->lc_no, $bp->branch_id, null, null, null,
                    "LC {$bp->lc_no} bill paid".($bp->posting ? " — {$bp->posting}" : '').($bp->remarks ? " — {$bp->remarks}" : ''), (string) $bp->lc_id)
                    ->addPart('paid', $bp->amount);
                $entry->updatedAt = null;

                return $entry;
            });
    }
}

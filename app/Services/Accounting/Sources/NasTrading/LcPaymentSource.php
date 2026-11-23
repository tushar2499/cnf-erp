<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Advance / regular amounts received from the customer against an LC.
 * LC payment rows are re-created whenever the LC is saved, so the reference is
 * built from the LC and the row content instead of the row id.
 */
class LcPaymentSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.lc_payment';
    }

    public function label(): string
    {
        return 'LC Operation — LC Payment Received';
    }

    public function companySlug(): string
    {
        return 'nas-trading';
    }

    public function voucherType(): string
    {
        return 'receipt';
    }

    public function components(): array
    {
        return [
            'received' => ['label' => 'Amount received', 'match' => 'lc_payment_type'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_trading_lc_payments as p')
            ->join('nas_trading_lcs as lc', 'lc.id', '=', 'p.lc_id')
            ->whereBetween('p.date', self::range($from, $to))
            ->orderBy('p.date')->orderBy('p.id')
            ->get(['p.*', 'lc.lc_no', 'lc.branch_id', 'lc.customer_id', 'lc.customer_name', 'lc.updated_at as lc_updated_at'])
            ->map(function ($p) {
                $ref = "{$p->lc_id}:{$p->payment_type}:".substr($p->date, 0, 10).':'.number_format((float) $p->amount, 2, '.', '');

                $entry = $this->entry($p, $ref, $p->date, $p->receipt_no ?: 'LC '.$p->lc_no, $p->branch_id,
                    'customer', $p->customer_id, $p->customer_name,
                    ucfirst($p->payment_type)." received — LC {$p->lc_no} — {$p->customer_name}", (string) $p->lc_id)
                    ->addPart('received', $p->amount, $p->payment_type);
                $entry->updatedAt = null;

                return $entry;
            });
    }
}

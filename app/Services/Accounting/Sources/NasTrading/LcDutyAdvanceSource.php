<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LcDutyAdvanceSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.lc_duty_advance';
    }

    public function label(): string
    {
        return 'LC Operation — Duty Advance';
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
            'duty' => ['label' => 'Duty advance'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_trading_lc_bill_of_entry_duty_advances as d')
            ->join('nas_trading_lc_bill_of_entries as be', 'be.id', '=', 'd.bill_of_entry_id')
            ->join('nas_trading_lcs as lc', 'lc.id', '=', 'be.lc_id')
            ->whereBetween('d.date', self::range($from, $to))
            ->orderBy('d.date')->orderBy('d.id')
            ->get(['d.*', 'be.be_no', 'lc.lc_no', 'lc.branch_id'])
            ->map(fn ($d) => $this->entry($d, (string) $d->id, $d->date, 'BE '.$d->be_no, $d->branch_id, null, null, null,
                "Duty advance — LC {$d->lc_no}, BE {$d->be_no}".($d->posting ? " — {$d->posting}" : ''))
                ->addPart('duty', $d->amount));
    }
}

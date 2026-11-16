<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerBillSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.customer_bill';
    }

    public function label(): string
    {
        return 'LC Operation — Customer Bill';
    }

    public function companySlug(): string
    {
        return 'nas-trading';
    }

    public function components(): array
    {
        return [
            'line' => ['label' => 'Bill item', 'match' => 'trading_expense_head'],
            'vat'  => ['label' => 'VAT'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $bills = DB::table('nas_trading_customer_bills')
            ->whereBetween('bill_date', self::range($from, $to))
            ->where('status', '!=', 'Draft')
            ->orderBy('bill_date')->orderBy('id')
            ->get();

        $items = DB::table('nas_trading_customer_bill_items')
            ->whereIn('bill_id', $bills->pluck('id'))
            ->get()
            ->groupBy('bill_id');

        return $bills->map(function ($bill) use ($items) {
            $rate = $bill->currency && strtoupper($bill->currency) !== 'BDT' && (float) $bill->exchange_rate > 0
                ? (float) $bill->exchange_rate
                : 1.0;

            $entry = $this->entry($bill, (string) $bill->id, $bill->bill_date, $bill->bill_no, $bill->branch_id,
                'customer', $bill->customer_id, $bill->customer_name, "Customer bill {$bill->bill_no} — LC {$bill->lc_no} — {$bill->customer_name}");

            foreach (($items[$bill->id] ?? collect())->groupBy(fn ($i) => (string) $i->expense_head_id) as $headId => $rows) {
                $entry->addPart('line', $rows->sum('amount') * $rate, $headId);
            }

            return $entry->addPart('vat', (float) $bill->vat_amount * $rate);
        });
    }
}

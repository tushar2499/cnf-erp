<?php

namespace App\Services\Accounting\Sources\Chevron;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoneyReceiptSource extends AccountingSource
{
    public function key(): string
    {
        return 'chevron.money_receipt';
    }

    public function label(): string
    {
        return 'C&F Operation — Money Receipt';
    }

    public function companySlug(): string
    {
        return 'chevron-lines';
    }

    public function voucherType(): string
    {
        return 'receipt';
    }

    public function components(): array
    {
        return [
            'received' => ['label' => 'Amount received', 'match' => 'chevron_account'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $receipts = DB::table('chevron_money_receipts')
            ->whereBetween('receipt_date', self::range($from, $to))
            ->orderBy('receipt_date')->orderBy('id')
            ->get();

        $items = DB::table('chevron_money_receipt_items')
            ->whereIn('receipt_id', $receipts->pluck('id'))
            ->get()
            ->groupBy('receipt_id');

        return $receipts->map(function ($mr) use ($items) {
            $entry = $this->entry($mr, (string) $mr->id, $mr->receipt_date, $mr->receipt_no, $mr->branch_id,
                'customer', $mr->party_id, $mr->party_name, "Received from {$mr->party_name} — MR {$mr->receipt_no}");

            foreach (($items[$mr->id] ?? collect())->groupBy(fn ($i) => (string) $i->account_id) as $accountId => $rows) {
                $entry->addPart('received', $rows->sum('amount'), $accountId);
            }

            return $entry;
        });
    }
}

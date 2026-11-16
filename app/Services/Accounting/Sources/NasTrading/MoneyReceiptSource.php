<?php

namespace App\Services\Accounting\Sources\NasTrading;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoneyReceiptSource extends AccountingSource
{
    public function key(): string
    {
        return 'trading.money_receipt';
    }

    public function label(): string
    {
        return 'LC Operation — Money Receipt';
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
            'received' => ['label' => 'Amount received', 'match' => 'trading_payment_mode'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_trading_money_receipts')
            ->whereBetween('receipt_date', self::range($from, $to))
            ->orderBy('receipt_date')->orderBy('id')
            ->get()
            ->map(fn ($mr) => $this->entry($mr, (string) $mr->id, $mr->receipt_date, $mr->receipt_no, $mr->branch_id,
                'customer', $mr->customer_id, $mr->customer_name, "Received from {$mr->customer_name} against bill {$mr->bill_no} ({$mr->payment_mode})")
                ->addPart('received', $mr->amount_received, $mr->payment_mode));
    }
}

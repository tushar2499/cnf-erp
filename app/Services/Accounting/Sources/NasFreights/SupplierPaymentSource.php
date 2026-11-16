<?php

namespace App\Services\Accounting\Sources\NasFreights;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupplierPaymentSource extends AccountingSource
{
    public function key(): string
    {
        return 'freights.supplier_payment';
    }

    public function label(): string
    {
        return 'Local Logistics — Supplier Payment';
    }

    public function companySlug(): string
    {
        return 'nas-freights';
    }

    public function voucherType(): string
    {
        return 'payment';
    }

    public function components(): array
    {
        return [
            'paid' => ['label' => 'Amount paid', 'match' => 'freights_payment_mode'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_freights_supplier_payments')
            ->whereBetween('payment_date', self::range($from, $to))
            ->orderBy('payment_date')->orderBy('id')
            ->get()
            ->map(fn ($p) => $this->entry($p, (string) $p->id, $p->payment_date, $p->payment_no, $p->branch_id,
                'supplier', $p->supplier_id, $p->supplier_name, "Paid to {$p->supplier_name} against bill {$p->bill_no} ({$p->payment_mode})")
                ->addPart('paid', $p->amount_paid, $p->payment_mode));
    }
}

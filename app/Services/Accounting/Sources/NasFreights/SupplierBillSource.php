<?php

namespace App\Services\Accounting\Sources\NasFreights;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupplierBillSource extends AccountingSource
{
    public function key(): string
    {
        return 'freights.supplier_bill';
    }

    public function label(): string
    {
        return 'Local Logistics — Supplier Bill';
    }

    public function companySlug(): string
    {
        return 'nas-freights';
    }

    public function components(): array
    {
        return [
            'cost' => ['label' => 'Bill total (cover van cost)'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_freights_supplier_bills')
            ->whereBetween('bill_date', self::range($from, $to))
            ->where('status', '!=', 'Draft')
            ->orderBy('bill_date')->orderBy('id')
            ->get()
            ->map(fn ($bill) => $this->entry($bill, (string) $bill->id, $bill->bill_date, $bill->pay_order_no, $bill->branch_id,
                'supplier', $bill->supplier_id, $bill->supplier_name, "Supplier bill {$bill->pay_order_no} — {$bill->supplier_name}")
                ->addPart('cost', $bill->total_amount));
    }
}

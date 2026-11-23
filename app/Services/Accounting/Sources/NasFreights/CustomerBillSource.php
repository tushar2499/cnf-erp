<?php

namespace App\Services\Accounting\Sources\NasFreights;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerBillSource extends AccountingSource
{
    public function key(): string
    {
        return 'freights.customer_bill';
    }

    public function label(): string
    {
        return 'Local Logistics — Customer Bill';
    }

    public function companySlug(): string
    {
        return 'nas-freights';
    }

    public function components(): array
    {
        return [
            'revenue' => ['label' => 'Transport charges (bill total less VAT / TDS / AIT)'],
            'vat'     => ['label' => 'VAT'],
            'tds'     => ['label' => 'TDS'],
            'ait'     => ['label' => 'AIT'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_freights_customer_bills')
            ->whereBetween('bill_date', self::range($from, $to))
            ->where('status', '!=', 'Draft')
            ->orderBy('bill_date')->orderBy('id')
            ->get()
            ->map(function ($bill) {
                $vat = (float) $bill->vat_amount;
                $tds = (float) $bill->tds_amount;
                $ait = (float) $bill->ait_amount;

                return $this->entry($bill, (string) $bill->id, $bill->bill_date, $bill->bill_no, $bill->branch_id,
                    'customer', $bill->customer_id, $bill->customer_name, "Customer bill {$bill->bill_no} — {$bill->customer_name}")
                    ->addPart('revenue', (float) $bill->total_amount - $vat - $tds - $ait)
                    ->addPart('vat', $vat)
                    ->addPart('tds', $tds)
                    ->addPart('ait', $ait);
            });
    }
}

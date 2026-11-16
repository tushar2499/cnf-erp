<?php

namespace App\Services\Accounting\Sources\NasFreights;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Freight export booking / job bills to the customer or the overseas agent (BDT values).
 */
class ExportBookingBillSource extends AccountingSource
{
    public function key(): string
    {
        return 'freights.export_bill';
    }

    public function label(): string
    {
        return 'Freight Export — Customer / Overseas Agent Bill';
    }

    public function companySlug(): string
    {
        return 'nas-freights';
    }

    public function components(): array
    {
        return [
            'charges' => ['label' => 'Bill charges (BDT)', 'match' => 'export_bill_type'],
            'vat'     => ['label' => 'VAT (BDT)', 'match' => 'export_bill_type'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('nas_freights_freight_export_booking_bills as b')
            ->join('nas_freights_freight_export_bookings as eb', 'eb.id', '=', 'b.export_booking_id')
            ->leftJoin('nas_freights_customers as c', 'c.id', '=', 'eb.customer_id')
            ->leftJoin('nas_freights_overseas_agents as a', 'a.id', '=', 'eb.overseas_agent_id')
            ->whereBetween('b.bill_date', self::range($from, $to))
            ->orderBy('b.bill_date')->orderBy('b.id')
            ->get(['b.*', 'eb.export_booking_no', 'eb.customer_id', 'eb.overseas_agent_id', 'c.name as customer_name', 'a.name as agent_name'])
            ->map(function ($bill) {
                $isAgent = $bill->bill_type === 'Overseas Agent';
                $partyName = $isAgent ? $bill->agent_name : $bill->customer_name;

                return $this->entry($bill, (string) $bill->id, $bill->bill_date, $bill->bill_no, $bill->branch_id,
                    $isAgent ? 'agent' : 'customer', $isAgent ? $bill->overseas_agent_id : $bill->customer_id, $partyName,
                    "{$bill->bill_type} bill {$bill->bill_no} — job {$bill->export_booking_no}".($partyName ? " — {$partyName}" : ''))
                    ->addPart('charges', $bill->total_bdt_amount, $bill->bill_type)
                    ->addPart('vat', $bill->vat_amount_bdt, $bill->bill_type);
            });
    }
}

<?php

namespace App\Services\Accounting\Sources\Chevron;

use App\Services\Accounting\Sources\AccountingSource;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * C&F bills. The bill has no customer id; the customer comes from the linked job.
 */
class BillSource extends AccountingSource
{
    public function key(): string
    {
        return 'chevron.bill';
    }

    public function label(): string
    {
        return 'C&F Operation — Bill';
    }

    public function companySlug(): string
    {
        return 'chevron-lines';
    }

    public function components(): array
    {
        return [
            'line'               => ['label' => 'Bill item', 'match' => 'chevron_expense_head'],
            'commission'         => ['label' => 'Commission'],
            'less_customs_duty'  => ['label' => 'Less customs duty & tax'],
            'income_tax_cnf_com' => ['label' => 'Income tax on C&F commission'],
        ];
    }

    public function entries(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $bills = DB::table('chevron_bills as b')
            ->leftJoin('chevron_jobs as j', 'j.id', '=', 'b.job_id')
            ->leftJoin('chevron_customers as c', 'c.id', '=', 'j.customer_id')
            ->whereBetween('b.bill_date', self::range($from, $to))
            ->orderBy('b.bill_date')->orderBy('b.id')
            ->get(['b.*', 'j.customer_id', 'c.name as customer_name']);

        $items = DB::table('chevron_bill_items')
            ->whereIn('bill_id', $bills->pluck('id'))
            ->get()
            ->groupBy('bill_id');

        return $bills->map(function ($bill) use ($items) {
            $party = $bill->customer_name ?: $bill->party_name;
            $entry = $this->entry($bill, (string) $bill->id, $bill->bill_date, $bill->bill_no, $bill->branch_id,
                'customer', $bill->customer_id, $party, "{$bill->bill_type} bill {$bill->bill_no} — job {$bill->job_no} — {$party}");

            foreach (($items[$bill->id] ?? collect())->groupBy(fn ($i) => (string) $i->expense_head_id) as $headId => $rows) {
                $entry->addPart('line', $rows->sum('amount'), $headId);
            }

            return $entry
                ->addPart('commission', $bill->commission_amount)
                ->addPart('less_customs_duty', $bill->less_customs_duty_tax)
                ->addPart('income_tax_cnf_com', $bill->income_tax_cnf_com);
        });
    }
}

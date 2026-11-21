<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChevronBill extends Model
{
    protected $fillable = [
        'bill_no', 'branch_id', 'bill_type', 'bill_date', 'delivery_date',
        'job_id', 'customer_id', 'job_no', 'party_name', 'party_address',
        'goods_description', 'mate_code', 'po_no',
        'quantity', 'quantity_unit', 'quantity_remark',
        'gross_weight', 'gross_weight_unit',
        'lc_no', 'lc_ref', 'be_no', 'be_date',
        'invoice_no', 'invoice_ref', 'invoice_date',
        'bl_no', 'bl_ref', 'bl_date',
        'assessable_value', 'invoice_value_bdt', 'invoice_value',
        'currency_type', 'currency_rate', 'remarks',
        'sub_total', 'commission_on', 'commission_rate', 'commission_amount',
        'total_payable', 'less_customs_duty_tax', 'income_tax_cnf_com',
        'net_payable', 'advance_amount', 'due_amount', 'received_amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'      => 'date',
            'delivery_date'  => 'date',
            'be_date'        => 'date',
            'bl_date'        => 'date',
            'invoice_date'   => 'date',
        ];
    }

    public function job()
    {
        return $this->belongsTo(ChevronJob::class, 'job_id');
    }

    public static function billTypes(): array
    {
        return ['C&F', 'Transport', 'Advance', 'Others'];
    }

    public static function commissionOnOptions(): array
    {
        return ['ASSESSABLE', 'INVOICE VALUE', 'MINIMUM', 'MAXIMUM'];
    }

    public static function generateBillNo(): string
    {
        $last = static::lockForUpdate()->max(DB::raw('CAST(SUBSTRING(bill_no, 4) AS UNSIGNED)'));

        return 'BIL'.str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(ChevronBillItem::class, 'bill_id');
    }

    /**
     * Advance drawn from a customer's advance balance by bills of a branch (the bill's `advance_amount`),
     * optionally ignoring one bill. Bills not linked to a customer never draw from the balance.
     */
    public static function advanceApplied(int $branchId, int $customerId, ?int $exceptBillId = null): float
    {
        return round((float) static::query()
            ->where('branch_id', $branchId)
            ->where('customer_id', $customerId)
            ->when($exceptBillId, fn ($q) => $q->where('id', '!=', $exceptBillId))
            ->sum('advance_amount'), 2);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ChevronMoneyReceiptAllocation::class, 'bill_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChevronBranch::class, 'branch_id');
    }

    /**
     * Advance money collected from the customer for this bill's job, one entry per Advance receipt, oldest first.
     * Empty when the bill has no job or no customer can be found for it.
     *
     * @return Collection<int, array{receipt_id: int, receipt_no: string, receipt_date: ?Carbon, amount: float}>
     */
    public function advanceCollections(): Collection
    {
        $customerId = $this->customer_id ?: $this->job?->customer_id;

        if (! $customerId || ! $this->job_id) {
            return collect();
        }

        return ChevronMoneyReceipt::advanceCollectionsForJob((int) $this->branch_id, (int) $customerId, (int) $this->job_id);
    }

    /**
     * Money receipts collected against this bill, one entry per receipt, oldest first.
     *
     * @return Collection<int, array{receipt_id: int, receipt_no: string, receipt_date: ?Carbon, pay_type: ?string, amount: float}>
     */
    public function receiptCollections(): Collection
    {
        return $this->allocations()
            ->with('receipt:id,receipt_no,receipt_date,pay_type')
            ->get()
            ->filter(fn (ChevronMoneyReceiptAllocation $allocation) => $allocation->receipt !== null)
            ->groupBy('receipt_id')
            ->map(fn (Collection $rows) => [
                'receipt_id'   => (int) $rows->first()->receipt_id,
                'receipt_no'   => (string) $rows->first()->receipt->receipt_no,
                'receipt_date' => $rows->first()->receipt->receipt_date,
                'pay_type'     => $rows->first()->receipt->pay_type,
                'amount'       => round((float) $rows->sum('amount'), 2),
            ])
            ->sortBy(fn (array $row) => ($row['receipt_date']?->format('Y-m-d') ?? '').'-'.str_pad((string) $row['receipt_id'], 10, '0', STR_PAD_LEFT))
            ->values();
    }

    /**
     * Amount still collectable on this bill: due at billing time minus money receipts allocated to it.
     */
    protected function balance(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->due_amount - (float) $this->received_amount, 2));
    }

    /**
     * Recalculate the cached received amount from the money receipt allocations (the source of truth).
     */
    public function syncReceivedAmount(): void
    {
        $this->update(['received_amount' => round((float) $this->allocations()->sum('amount'), 2)]);
    }
}

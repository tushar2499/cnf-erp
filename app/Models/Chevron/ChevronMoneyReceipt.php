<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChevronMoneyReceipt extends Model
{
    public const TYPE_ADVANCE = 'Advance';

    public const TYPE_BILL = 'Bill';

    public const ADVANCE_ADJUSTMENT = 'Advance Adjustment';

    protected $fillable = [
        'receipt_no', 'branch_id', 'receipt_date', 'receipt_type', 'party_id', 'party_name',
        'pay_type', 'payable_amount', 'total_amount', 'description', 'status',
    ];

    protected function casts(): array
    {
        return ['receipt_date' => 'date'];
    }

    public function items()
    {
        return $this->hasMany(ChevronMoneyReceiptItem::class, 'receipt_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChevronBranch::class, 'branch_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ChevronMoneyReceiptAllocation::class, 'receipt_id');
    }

    /**
     * @return array<int, string>
     */
    public static function receiptTypes(): array
    {
        return [self::TYPE_ADVANCE, self::TYPE_BILL];
    }

    public function isEditable(): bool
    {
        return $this->status === 'Active';
    }

    public static function generateReceiptNo(): string
    {
        return DB::transaction(function () {
            $max = DB::table('chevron_money_receipts')->lockForUpdate()->max('receipt_no');
            $next = $max ? (int) substr($max, 2) + 1 : 1;

            return 'MR'.str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function payTypes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Mobile Banking', 'Card', 'Mixed', self::ADVANCE_ADJUSTMENT];
    }

    /**
     * Advance money received from a customer (Advance receipts) in a branch, optionally ignoring one receipt.
     */
    public static function advanceReceived(int $branchId, int $partyId, ?int $exceptReceiptId = null): float
    {
        return round((float) static::query()
            ->where('branch_id', $branchId)
            ->where('party_id', $partyId)
            ->where('receipt_type', self::TYPE_ADVANCE)
            ->when($exceptReceiptId, fn ($q) => $q->where('id', '!=', $exceptReceiptId))
            ->sum('total_amount'), 2);
    }

    /**
     * Advance already used to settle bills: "Advance Adjustment" payment rows of Bill receipts, plus the
     * advance applied directly on bills. Optionally ignores one receipt and/or one bill.
     */
    public static function advanceAdjusted(int $branchId, int $partyId, ?int $exceptReceiptId = null, ?int $exceptBillId = null): float
    {
        $viaReceipts = (float) ChevronMoneyReceiptItem::query()
            ->where('payment_type', self::ADVANCE_ADJUSTMENT)
            ->whereHas('receipt', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('party_id', $partyId)
                ->where('receipt_type', self::TYPE_BILL)
                ->when($exceptReceiptId, fn ($q) => $q->where('id', '!=', $exceptReceiptId)))
            ->sum('amount');

        return round($viaReceipts + ChevronBill::advanceApplied($branchId, $partyId, $exceptBillId), 2);
    }

    /**
     * Advance still available for a customer: received minus adjusted against bills.
     */
    public static function advanceBalance(int $branchId, int $partyId, ?int $exceptReceiptId = null, ?int $exceptBillId = null): float
    {
        return round(
            static::advanceReceived($branchId, $partyId, $exceptReceiptId) - static::advanceAdjusted($branchId, $partyId, $exceptReceiptId, $exceptBillId),
            2
        );
    }

    /**
     * Advance collected through Advance receipts against one job (the job-wise part of the customer's advance),
     * one entry per receipt, oldest first.
     *
     * @return Collection<int, array{receipt_id: int, receipt_no: string, receipt_date: ?Carbon, amount: float}>
     */
    public static function advanceCollectionsForJob(int $branchId, int $partyId, int $jobId): Collection
    {
        return ChevronMoneyReceiptAllocation::query()
            ->with('receipt:id,receipt_no,receipt_date')
            ->whereNull('bill_id')
            ->where('job_id', $jobId)
            ->whereHas('receipt', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->where('party_id', $partyId)
                ->where('receipt_type', self::TYPE_ADVANCE))
            ->get()
            ->groupBy('receipt_id')
            ->map(fn (Collection $rows) => [
                'receipt_id'   => (int) $rows->first()->receipt_id,
                'receipt_no'   => (string) $rows->first()->receipt->receipt_no,
                'receipt_date' => $rows->first()->receipt->receipt_date,
                'amount'       => round((float) $rows->sum('amount'), 2),
            ])
            ->sortBy(fn (array $row) => ($row['receipt_date']?->format('Y-m-d') ?? '').'-'.str_pad((string) $row['receipt_id'], 10, '0', STR_PAD_LEFT))
            ->values();
    }

    public static function rowPayTypes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Mobile Banking', 'Card'];
    }
}

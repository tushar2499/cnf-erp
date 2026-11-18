<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChevronMoneyReceiptAllocation extends Model
{
    protected $fillable = ['receipt_id', 'bill_id', 'job_id', 'amount'];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ChevronMoneyReceipt::class, 'receipt_id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ChevronBill::class, 'bill_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ChevronJob::class, 'job_id');
    }
}

<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccVoucherLine extends Model
{
    protected $fillable = [
        'voucher_id', 'account_id', 'debit', 'credit',
        'party_type', 'party_id', 'party_name', 'narration', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'debit'  => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(AccVoucher::class, 'voucher_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccAccount::class, 'account_id');
    }
}

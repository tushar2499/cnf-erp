<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccTransactionMapping extends Model
{
    protected $fillable = [
        'source_type', 'component', 'match_key',
        'debit_account_id', 'credit_account_id', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(AccAccount::class, 'debit_account_id');
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(AccAccount::class, 'credit_account_id');
    }
}

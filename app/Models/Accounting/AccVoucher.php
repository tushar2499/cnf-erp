<?php

namespace App\Models\Accounting;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccVoucher extends Model
{
    public const TYPES = [
        'journal' => 'Journal',
        'payment' => 'Payment',
        'receipt' => 'Receipt',
        'contra'  => 'Contra',
    ];

    public const PREFIXES = [
        'journal' => 'JV',
        'payment' => 'PV',
        'receipt' => 'RV',
        'contra'  => 'CV',
    ];

    protected $fillable = [
        'company_id', 'branch_id', 'voucher_no', 'voucher_type', 'voucher_date', 'narration',
        'source_type', 'source_id', 'source_ref', 'source_doc_no', 'source_updated_at',
        'is_auto', 'status', 'total_amount', 'created_by', 'cancelled_by', 'cancelled_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date'      => 'date',
            'source_updated_at' => 'datetime',
            'cancelled_at'      => 'datetime',
            'is_auto'           => 'boolean',
            'total_amount'      => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccVoucherLine::class, 'voucher_id')->orderBy('sort_order');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('acc_vouchers.status', 'posted');
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }
}

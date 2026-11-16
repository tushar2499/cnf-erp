<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccAccount extends Model
{
    public const LEVEL_MASTER = 1;

    public const LEVEL_LEDGER = 4;

    /** Code length of each level: Master 2, Sub-1 4, Sub-2 6, Ledger 10. */
    public const CODE_LENGTHS = [1 => 2, 2 => 4, 3 => 6, 4 => 10];

    public const LEVEL_NAMES = [1 => 'Master', 2 => 'Sub-1', 3 => 'Sub-2', 4 => 'Ledger'];

    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    protected $fillable = [
        'parent_id', 'code', 'name', 'level', 'type', 'nature',
        'is_group', 'is_cash_bank', 'party_type', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'level'        => 'integer',
            'is_group'     => 'boolean',
            'is_cash_bank' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function defaultNature(string $type): string
    {
        return in_array($type, ['asset', 'expense'], true) ? 'dr' : 'cr';
    }

    /**
     * Next free code under this group, e.g. 010202 -> 0102020015.
     */
    public function nextChildCode(): string
    {
        $childLevel = $this->level + 1;
        $suffixLength = self::CODE_LENGTHS[$childLevel] - strlen($this->code);

        $lastCode = self::where('parent_id', $this->id)
            ->where('code', 'like', $this->code.'%')
            ->whereRaw('LENGTH(code) = ?', [self::CODE_LENGTHS[$childLevel]])
            ->orderByDesc('code')
            ->value('code');

        $next = $lastCode ? ((int) substr($lastCode, strlen($this->code))) + 1 : 1;

        return $this->code.str_pad((string) $next, $suffixLength, '0', STR_PAD_LEFT);
    }
}

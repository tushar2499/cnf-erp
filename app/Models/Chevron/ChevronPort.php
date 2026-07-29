<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChevronPort extends Model
{
    protected $fillable = ['branch_id', 'name', 'code', 'prefix', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChevronBranch::class, 'branch_id');
    }
}

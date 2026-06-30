<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronBranch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

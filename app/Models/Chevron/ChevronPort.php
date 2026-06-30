<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronPort extends Model
{
    protected $fillable = ['name', 'code', 'prefix', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

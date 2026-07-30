<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronJobType extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

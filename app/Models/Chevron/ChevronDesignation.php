<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronDesignation extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

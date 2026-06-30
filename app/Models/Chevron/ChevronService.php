<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronService extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

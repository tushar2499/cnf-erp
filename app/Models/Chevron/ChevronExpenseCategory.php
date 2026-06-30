<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronExpenseCategory extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

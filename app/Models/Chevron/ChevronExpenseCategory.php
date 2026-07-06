<?php

namespace App\Models\Chevron;

use App\Enums\Chevron\ChevronExpenseCategoryType;
use Illuminate\Database\Eloquent\Model;

class ChevronExpenseCategory extends Model
{
    protected $fillable = ['name', 'type', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'name'        => 'string',
            'type'        => ChevronExpenseCategoryType::class,
            'description' => 'string',
            'is_active'   => 'boolean',
        ];
    }
}

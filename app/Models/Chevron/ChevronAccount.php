<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronAccount extends Model
{
    protected $fillable = [
        'account_no', 'account_name', 'bank_name', 'branch_name', 'account_type', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function accountTypes(): array
    {
        return ['Bank', 'Cash', 'Mobile Banking', 'Others'];
    }
}

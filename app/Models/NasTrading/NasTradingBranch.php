<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingBranch extends Model
{
    protected $table = 'nas_trading_branches';
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingBank extends Model
{
    protected $table = 'nas_trading_banks';

    protected $fillable = [
        'name', 'branch', 'swift_code', 'account_no', 'status',
    ];
}

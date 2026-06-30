<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingPort extends Model
{
    protected $table = 'nas_trading_ports';

    protected $fillable = [
        'name', 'country', 'type', 'status',
    ];
}

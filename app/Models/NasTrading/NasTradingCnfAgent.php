<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingCnfAgent extends Model
{
    protected $table = 'nas_trading_cnf_agents';

    protected $fillable = [
        'name', 'phone', 'address', 'status',
    ];
}

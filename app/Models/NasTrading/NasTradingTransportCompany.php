<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingTransportCompany extends Model
{
    protected $table = 'nas_trading_transport_companies';

    protected $fillable = [
        'name', 'phone', 'address', 'status',
    ];
}

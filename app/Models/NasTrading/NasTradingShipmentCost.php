<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingShipmentCost extends Model
{
    protected $table = 'nas_trading_shipment_costs';

    protected $fillable = [
        'shipment_id', 'expense_head_id', 'cost_head', 'amount', 'remarks',
    ];
}

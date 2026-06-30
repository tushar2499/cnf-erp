<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingDeliveryItem extends Model
{
    protected $table = 'nas_trading_delivery_items';

    protected $fillable = [
        'delivery_id', 'lc_item_id', 'product_name', 'hs_code', 'qty', 'unit', 'remarks',
    ];
}

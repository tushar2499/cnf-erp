<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcItem extends Model
{
    protected $table = 'nas_trading_lc_items';

    protected $fillable = [
        'lc_id', 'item_id', 'product_name', 'product_code', 'hs_code',
        'qty_count', 'qty_unit', 'weight', 'weight_unit',
        'unit_price', 'line_amount', 'currency', 'remarks',
    ];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public function item()
    {
        return $this->belongsTo(NasTradingItem::class, 'item_id');
    }
}

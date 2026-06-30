<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingShipmentItem extends Model
{
    protected $table = 'nas_trading_shipment_items';

    protected $fillable = [
        'shipment_id', 'item_name', 'description', 'hs_code', 'grn_qty', 'rate', 'assessable',
        'cd_pct', 'cd_amt', 'rd_pct', 'rd_amt', 'sd_pct', 'sd_amt',
        'vat_pct', 'vat_amt', 'ait_pct', 'ait_amt',
    ];
}

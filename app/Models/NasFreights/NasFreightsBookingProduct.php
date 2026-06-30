<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsBookingProduct extends Model
{
    protected $table = 'nas_freights_booking_products';

    protected $fillable = [
        'booking_id', 'goods_name', 'qty', 'qty_unit', 'net_weight', 'weight_unit',
    ];
}

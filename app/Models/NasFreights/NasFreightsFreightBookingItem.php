<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NasFreightsFreightBookingItem extends Model
{
    protected $table = 'nas_freights_freight_booking_items';

    protected $fillable = [
        'freight_booking_id', 'item_type', 'container_size', 'package_type',
        'hs_code', 'commodity', 'quantity', 'gross_weight', 'weight_unit',
        'volume_cbm', 'country_of_origin', 'is_dangerous_goods', 'special_handling',
    ];

    protected function casts(): array
    {
        return [
            'is_dangerous_goods' => 'boolean',
        ];
    }

    public function freightBooking(): BelongsTo
    {
        return $this->belongsTo(NasFreightsFreightBooking::class, 'freight_booking_id');
    }
}

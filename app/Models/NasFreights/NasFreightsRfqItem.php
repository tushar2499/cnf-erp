<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NasFreightsRfqItem extends Model
{
    protected $table = 'nas_freights_rfq_items';

    protected $fillable = [
        'rfq_id', 'item_type', 'container_size', 'package_type',
        'hs_code', 'commodity', 'quantity',
        'gross_weight', 'weight_unit', 'volume_cbm',
        'cargo_value', 'country_of_origin',
        'is_dangerous_goods', 'special_handling',
    ];

    protected function casts(): array
    {
        return [
            'is_dangerous_goods' => 'boolean',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(NasFreightsRfq::class, 'rfq_id');
    }

    public static function containerSizes(): array
    {
        return ['20GP', '40GP', '40HC', '40RF', '45HC'];
    }

    public static function packageTypes(): array
    {
        return ['Carton', 'Pallet', 'Drum', 'Bag', 'Crate', 'Roll', 'Bundle'];
    }

    public static function weightUnits(): array
    {
        return ['KG', 'MT'];
    }
}

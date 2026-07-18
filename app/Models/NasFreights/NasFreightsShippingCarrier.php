<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsShippingCarrier extends Model
{
    protected $table = 'nas_freights_shipping_carriers';

    protected $fillable = ['carrier_code', 'name', 'scac_code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max = DB::table('nas_freights_shipping_carriers')->lockForUpdate()->max('carrier_code');
            $next = 1;
            if ($max && preg_match('/\d+$/', $max, $m)) {
                $next = (int) $m[0] + 1;
            }

            return 'SC-'.str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name');
    }
}

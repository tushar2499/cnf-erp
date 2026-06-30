<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingDelivery extends Model
{
    protected $table = 'nas_trading_deliveries';

    protected $fillable = [
        'delivery_no', 'bill_id', 'bill_no', 'lc_id', 'lc_no',
        'customer_id', 'customer_name', 'delivery_date', 'delivery_address',
        'transport_co_id', 'vehicle_no', 'driver_name', 'driver_phone',
        'delivery_status', 'note', 'entry_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function deliveryItems()
    {
        return $this->hasMany(NasTradingDeliveryItem::class, 'delivery_id');
    }

    public static function generateDeliveryNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(delivery_no, 5) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'DLV-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

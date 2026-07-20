<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightBooking extends Model
{
    protected $table = 'nas_freights_freight_bookings';

    protected $fillable = [
        'freight_booking_no', 'branch_id', 'rfq_id', 'rfq_no',
        'customer_id', 'salesperson_id', 'overseas_agent_id', 'shipping_carrier_id',
        'booking_date', 'type', 'service_type', 'incoterms', 'currency',
        'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'vessel_name', 'voyage_no', 'bl_no',
        'etd', 'eta', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'etd'          => 'date',
            'eta'          => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(NasFreightsCustomer::class, 'customer_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(NasFreightsEmployee::class, 'salesperson_id');
    }

    public function overseasAgent(): BelongsTo
    {
        return $this->belongsTo(NasFreightsOverseasAgent::class, 'overseas_agent_id');
    }

    public function shippingCarrier(): BelongsTo
    {
        return $this->belongsTo(NasFreightsShippingCarrier::class, 'shipping_carrier_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(NasFreightsRfq::class, 'rfq_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NasFreightsFreightBookingItem::class, 'freight_booking_id');
    }

    public static function generateFreightBookingNo(): string
    {
        $prefix = 'FBK-'.now()->format('Ymd').'-';
        $last = static::lockForUpdate()
            ->where('freight_booking_no', 'like', $prefix.'%')
            ->max(DB::raw('CAST(SUBSTRING(freight_booking_no, '.(strlen($prefix) + 1).') AS UNSIGNED)'));

        return $prefix.str_pad(($last ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function statuses(): array
    {
        return ['Draft', 'Confirmed', 'In-Transit', 'Delivered', 'Cancelled'];
    }

    public static function types(): array
    {
        return ['import' => 'Import', 'export' => 'Export'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'Truck', 'Road'];
    }
}

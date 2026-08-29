<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightExportBooking extends Model
{
    protected $table = 'nas_freights_freight_export_bookings';

    protected $fillable = [
        'export_booking_no', 'branch_id',
        'customer_id', 'salesperson_id', 'overseas_agent_id', 'shipping_carrier_id',
        'booking_date', 'service_type', 'incoterms', 'currency',
        'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'vessel_name', 'voyage_no', 'export_bl_no', 'booking_note_no',
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

    public function items(): HasMany
    {
        return $this->hasMany(NasFreightsFreightExportBookingItem::class, 'export_booking_id');
    }

    public static function generateExportBookingNo(): string
    {
        $prefix = 'FEB-'.now()->format('Ymd').'-';
        $last = static::lockForUpdate()
            ->where('export_booking_no', 'like', $prefix.'%')
            ->max(DB::raw('CAST(SUBSTRING(export_booking_no, '.(strlen($prefix) + 1).') AS UNSIGNED)'));

        return $prefix.str_pad(($last ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    public static function statuses(): array
    {
        return ['Draft', 'Confirmed', 'In-Transit', 'Delivered', 'Cancelled'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'Truck', 'Road'];
    }
}

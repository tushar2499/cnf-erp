<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NasFreightsRfq extends Model
{
    protected $table = 'nas_freights_rfqs';

    protected $fillable = [
        'rfq_no', 'branch_id', 'customer_id', 'rfq_date', 'valid_until',
        'type', 'service_type', 'incoterms', 'currency',
        'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'remarks',
        'status', 'lost_reason', 'converted_booking_id',
        'salesperson_id', 'overseas_agent_id', 'shipping_carrier_id',
    ];

    protected function casts(): array
    {
        return [
            'rfq_date'    => 'date',
            'valid_until' => 'date',
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

    public function convertedBooking(): BelongsTo
    {
        return $this->belongsTo(NasFreightsBooking::class, 'converted_booking_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NasFreightsRfqItem::class, 'rfq_id');
    }

    public static function generateRfqNo(): string
    {
        $last = static::lockForUpdate()->max(
            DB::raw('CAST(SUBSTRING(rfq_no, 4) AS UNSIGNED)')
        );

        return 'RFQ'.str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function types(): array
    {
        return ['import' => 'Import', 'export' => 'Export'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'Truck', 'Road'];
    }

    public static function statuses(): array
    {
        return ['Draft', 'Pending', 'Win', 'Lose'];
    }

    public static function incoterms(): array
    {
        return ['EXW', 'FCA', 'FAS', 'FOB', 'CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'];
    }

    public static function lostReasons(): array
    {
        return ['Price', 'Transit Time', 'Relationship', 'Competitor', 'Other'];
    }

    public static function currencies(): array
    {
        return ['BDT', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'INR', 'SGD', 'AUD', 'AED'];
    }
}

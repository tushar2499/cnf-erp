<?php

namespace App\Models\NasFreights;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightBooking extends Model
{
    protected $table = 'nas_freights_freight_bookings';

    protected $fillable = [
        'freight_booking_no', 'branch_id', 'rfq_id', 'rfq_no',
        'customer_id', 'customer_invoice_no', 'customer_invoice_date', 'agent_invoice_no',
        'salesperson_id', 'overseas_agent_id', 'shipping_carrier_id',
        'booking_date', 'service_type', 'incoterms', 'currency',
        'exchange_rate', 'buy_amount', 'buy_bdt_amount', 'sell_amount', 'sell_bdt_amount',
        'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'hs_codes', 'vessel_name', 'voyage_no', 'flight_no', 'flight_date',
        'bl_no', 'mbl_mawb_no', 'mbl_mawb_date', 'hbl_hawb_no', 'hbl_hawb_date',
        'lc_no', 'cad_no', 'tt_no', 'rfq_tender_no', 'igm_no', 'delivery_order_no',
        'etd', 'eta', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'booking_date'          => 'date',
            'customer_invoice_date' => 'date',
            'flight_date'           => 'date',
            'mbl_mawb_date'         => 'date',
            'hbl_hawb_date'         => 'date',
            'hs_codes'              => 'array',
            'exchange_rate'         => 'decimal:6',
            'buy_amount'            => 'decimal:2',
            'buy_bdt_amount'        => 'decimal:2',
            'sell_amount'           => 'decimal:2',
            'sell_bdt_amount'       => 'decimal:2',
            'etd'                   => 'date',
            'eta'                   => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(NasFreightsCustomer::class, 'customer_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'salesperson_id');
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
        $prefix = 'FIB-';
        $last = static::lockForUpdate()
            ->where('freight_booking_no', 'REGEXP', '^FIB-[0-9]+$')
            ->max(DB::raw('CAST(SUBSTRING(freight_booking_no, '.(strlen($prefix) + 1).') AS UNSIGNED)'));

        return $prefix.str_pad(($last ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    public function isAir(): bool
    {
        return $this->service_type === 'Air';
    }

    public static function statuses(): array
    {
        return ['Draft', 'Confirmed', 'In-Transit', 'Delivered', 'Cancelled'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'SEA', 'Truck', 'Road', 'Handling', 'Other'];
    }
}

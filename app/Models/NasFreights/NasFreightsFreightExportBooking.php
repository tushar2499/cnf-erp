<?php

namespace App\Models\NasFreights;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightExportBooking extends Model
{
    protected $table = 'nas_freights_freight_export_bookings';

    protected $fillable = [
        'export_booking_no', 'branch_id',
        'customer_id', 'party_bill_ref_no', 'party_bill_date', 'party_invoice_no', 'party_invoice_date',
        'salesperson_id', 'overseas_agent_id', 'shipping_carrier_id',
        'booking_date', 'service_type', 'incoterms', 'currency',
        'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'hs_codes', 'vessel_name', 'voyage_no', 'export_bl_no', 'bl_date', 'booking_note_no',
        'transport_doc_type', 'transport_doc_no', 'transport_doc_date',
        'exp_no', 'exp_date', 'invoice_no', 'invoice_date', 'lc_no',
        'etd', 'eta', 'status', 'remarks', 'transport_amount',
    ];

    protected function casts(): array
    {
        return [
            'booking_date'       => 'date',
            'party_bill_date'    => 'date',
            'party_invoice_date' => 'date',
            'hs_codes'           => 'array',
            'etd'                => 'date',
            'eta'                => 'date',
            'exp_date'           => 'date',
            'invoice_date'       => 'date',
            'bl_date'            => 'date',
            'transport_doc_date' => 'date',
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

    public function items(): HasMany
    {
        return $this->hasMany(NasFreightsFreightExportBookingItem::class, 'export_booking_id');
    }

    public function transportItems(): HasMany
    {
        return $this->hasMany(NasFreightsFreightExportBookingTransportItem::class, 'export_booking_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(NasFreightsFreightExportBookingBill::class, 'export_booking_id');
    }

    public function expense(): HasOne
    {
        return $this->hasOne(NasFreightsFreightExportBookingExpense::class, 'booking_id');
    }

    public static function generateExportBookingNo(): string
    {
        $serialOffset = 9; // NASEXP(6) + YY(2) + 1-based offset

        $last = static::lockForUpdate()
            ->where('export_booking_no', 'REGEXP', '^NASEXP[0-9]{2}[0-9]+$')
            ->max(DB::raw('CAST(SUBSTRING(export_booking_no, '.$serialOffset.') AS UNSIGNED)'));

        return 'NASEXP'.now()->format('y').str_pad(($last ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    }

    public static function statuses(): array
    {
        return ['Draft', 'Confirmed', 'In-Transit', 'Delivered', 'Cancelled'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'Sea', 'Truck', 'Road', 'Handling', 'Other'];
    }
}

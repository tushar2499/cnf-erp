<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsBooking extends Model
{
    protected $table = 'nas_freights_bookings';

    protected $fillable = [
        'job_no', 'booking_prefix', 'sales_type',
        'sales_person_id', 'sales_person_name',
        'job_date', 'goods_name',
        'customer_id', 'customer_name', 'delivery_address',
        'lc_no', 'invoice_no', 'delivery_date', 'po_number',
        'cover_van_no', 'note',
        'qty', 'qty_unit', 'net_weight', 'weight_unit',
        'tds_section', 'tds_percent', 'tds_amount',
        'vat_percent', 'vat_amount',
        'ait_percent', 'ait_amount',
        'total_amount', 'discount', 'forfeited_amount',
        'status', 'delivery_status', 'entry_by', 'branch_id',
    ];

    protected $casts = [
        'job_date'      => 'date',
        'delivery_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasFreightsBookingItem::class, 'booking_id');
    }

    public function products()
    {
        return $this->hasMany(NasFreightsBookingProduct::class, 'booking_id');
    }

    public static function generateJobNo(): string
    {
        $max = static::lockForUpdate()->max(
            DB::raw("CAST(SUBSTRING(job_no, 5) AS UNSIGNED)")
        );
        return 'TMS-' . str_pad(($max ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function bookingPrefixes(): array
    {
        return ['CORPORATE_SALES', 'SPECIAL_SALES'];
    }

    public static function salesTypes(): array
    {
        return ['DISTRIBUTION', 'EXPORT', 'IMPORT', 'LOCAL'];
    }

    public static function qtyUnits(): array
    {
        return ['PCS', 'BOX', 'BAG', 'KG', 'TON', 'LITER', 'DRUM', 'CARTON'];
    }

    public static function weightUnits(): array
    {
        return ['KG', 'TON', 'MT', 'LB'];
    }

    public static function deliveryStatuses(): array
    {
        return ['Pending', 'Partially Delivered', 'Fully Delivered'];
    }
}

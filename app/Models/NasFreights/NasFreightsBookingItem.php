<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsBookingItem extends Model
{
    protected $table = 'nas_freights_booking_items';

    protected $fillable = [
        'booking_id', 'cover_van_no', 'capacity',
        'supplier_id', 'supplier_name',
        'qty', 'supplier_rate', 'customer_rate',
        'demurrage_days', 'cus_demurrage_charge', 'sup_demurrage_charge',
        'amount', 'location_from', 'location_to',
    ];

    public function booking()
    {
        return $this->belongsTo(NasFreightsBooking::class, 'booking_id');
    }
}

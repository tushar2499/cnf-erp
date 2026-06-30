<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsCustomerBillItem extends Model
{
    protected $table = 'nas_freights_customer_bill_items';

    protected $fillable = [
        'bill_id', 'booking_id', 'booking_item_id',
        'booking_date', 'delivery_date',
        'item_code', 'item_name', 'location',
        'b_qty', 'd_qty', 'due_qty', 'demurrage_day', 'demurrage_amount',
        'price', 'disc_percent', 'discount', 'ait_percent', 'line_amount',
    ];

    public function bill()
    {
        return $this->belongsTo(NasFreightsCustomerBill::class, 'bill_id');
    }

    public function booking()
    {
        return $this->belongsTo(NasFreightsBooking::class, 'booking_id');
    }

    public function bookingItem()
    {
        return $this->belongsTo(NasFreightsBookingItem::class, 'booking_item_id');
    }
}

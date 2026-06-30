<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsSupplierBillItem extends Model
{
    protected $table = 'nas_freights_supplier_bill_items';

    protected $fillable = [
        'bill_id', 'booking_id', 'booking_item_id',
        'booking_date', 'entry_date',
        'item_code', 'item_name', 'location',
        'b_qty', 'd_qty', 'due_qty',
        'price', 'demurrage_day', 'demurrage_amount',
        'line_amount', 'notes',
    ];

    public function bill()
    {
        return $this->belongsTo(NasFreightsSupplierBill::class, 'bill_id');
    }

    public function booking()
    {
        return $this->belongsTo(\App\Models\NasFreights\NasFreightsBooking::class, 'booking_id');
    }
}

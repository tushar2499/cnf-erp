<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsFreightExportBookingTransportItem extends Model
{
    protected $table = 'nas_freights_freight_export_booking_transport_items';

    protected $fillable = [
        'export_booking_id', 'cover_van_no', 'challan_no', 'capacity',
        'supplier_id', 'supplier_name',
        'qty', 'supplier_rate', 'customer_rate',
        'demurrage_days', 'cus_demurrage_charge', 'sup_demurrage_charge',
        'amount', 'location_from', 'location_to',
    ];

    public function exportBooking()
    {
        return $this->belongsTo(NasFreightsFreightExportBooking::class, 'export_booking_id');
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NasFreightsFreightExportBookingPartyInvoice extends Model
{
    protected $table = 'nas_freights_freight_export_booking_party_invoices';

    protected $fillable = ['export_booking_id', 'invoice_no', 'invoice_date', 'sort_order'];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
        ];
    }

    public function exportBooking(): BelongsTo
    {
        return $this->belongsTo(NasFreightsFreightExportBooking::class, 'export_booking_id');
    }
}

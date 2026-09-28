<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NasFreightsFreightExportBookingBillItem extends Model
{
    protected $table = 'nas_freights_freight_export_booking_bill_items';

    protected $fillable = ['bill_id', 'name', 'amount', 'amount_bdt', 'sort_order'];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'amount_bdt' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(NasFreightsFreightExportBookingBill::class, 'bill_id');
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightExportBookingBill extends Model
{
    protected $table = 'nas_freights_freight_export_booking_bills';

    protected $fillable = [
        'bill_no', 'export_booking_id', 'branch_id', 'bill_type',
        'bill_date', 'currency', 'exchange_rate',
        'total_amount', 'total_bdt_amount', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'        => 'date',
            'exchange_rate'    => 'decimal:4',
            'total_amount'     => 'decimal:2',
            'total_bdt_amount' => 'decimal:2',
        ];
    }

    public function exportBooking(): BelongsTo
    {
        return $this->belongsTo(NasFreightsFreightExportBooking::class, 'export_booking_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NasFreightsFreightExportBookingBillItem::class, 'bill_id')->orderBy('sort_order');
    }

    public static function billTypes(): array
    {
        return ['Customer', 'Overseas Agent'];
    }

    public static function statuses(): array
    {
        return ['Draft', 'Confirmed'];
    }

    public static function generateBillNo(): string
    {
        $prefix = 'FEB-BILL-'.now()->format('Y').'-';
        $last = static::lockForUpdate()
            ->where('bill_no', 'like', $prefix.'%')
            ->max(DB::raw('CAST(SUBSTRING(bill_no, '.(strlen($prefix) + 1).') AS UNSIGNED)'));

        return $prefix.str_pad(($last ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }
}

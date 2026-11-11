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
        'bill_prefix_ref', 'bill_prefix_job',
        'bill_date', 'attention', 'designation', 'currency', 'exchange_rate',
        'total_amount', 'total_bdt_amount', 'status', 'remarks',
        'vat_title', 'vat_amount', 'vat_amount_bdt',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'        => 'date',
            'exchange_rate'    => 'decimal:4',
            'total_amount'     => 'decimal:2',
            'total_bdt_amount' => 'decimal:2',
            'vat_amount'       => 'decimal:2',
            'vat_amount_bdt'   => 'decimal:2',
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

    /**
     * Booking/Job No with this bill's own Job prefix appended (just the Job No when the bill has none).
     * Null when the bill has no booking.
     */
    public function jobRefNo(): ?string
    {
        $jobNo = $this->exportBooking?->export_booking_no;

        if ($jobNo === null) {
            return null;
        }

        return $this->bill_prefix_job ? $jobNo.$this->bill_prefix_job : $jobNo;
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

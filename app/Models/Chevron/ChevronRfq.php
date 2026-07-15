<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronRfq extends Model
{
    protected $fillable = [
        'rfq_no', 'branch_id', 'customer_id', 'rfq_date', 'valid_until',
        'type', 'service_type', 'incoterms', 'currency',
        'pol_id', 'pod_id', 'place_of_receipt', 'place_of_delivery',
        'commodity_description', 'remarks',
        'status', 'lost_reason', 'converted_job_id', 'salesperson_id',
    ];

    protected function casts(): array
    {
        return [
            'rfq_date'    => 'date',
            'valid_until' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(ChevronCustomer::class, 'customer_id');
    }

    public function pol()
    {
        return $this->belongsTo(ChevronPort::class, 'pol_id');
    }

    public function pod()
    {
        return $this->belongsTo(ChevronPort::class, 'pod_id');
    }

    public function salesperson()
    {
        return $this->belongsTo(ChevronEmployee::class, 'salesperson_id');
    }

    public function convertedJob()
    {
        return $this->belongsTo(ChevronJob::class, 'converted_job_id');
    }

    public function items()
    {
        return $this->hasMany(ChevronRfqItem::class, 'rfq_id');
    }

    public static function generateRfqNo(): string
    {
        $last = static::lockForUpdate()->max(
            DB::raw('CAST(SUBSTRING(rfq_no, 4) AS UNSIGNED)')
        );

        return 'RFQ'.str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function types(): array
    {
        return ['import' => 'Import', 'export' => 'Export'];
    }

    public static function serviceTypes(): array
    {
        return ['FCL', 'LCL', 'Air', 'Truck'];
    }

    public static function statuses(): array
    {
        return ['Draft', 'Pending', 'Win', 'Lose'];
    }

    public static function incoterms(): array
    {
        return ['EXW', 'FCA', 'FAS', 'FOB', 'CFR', 'CIF', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP'];
    }

    public static function lostReasons(): array
    {
        return ['Price', 'Transit Time', 'Relationship', 'Competitor', 'Other'];
    }

    public static function currencies(): array
    {
        return ['BDT', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'INR', 'SGD', 'AUD', 'AED'];
    }
}

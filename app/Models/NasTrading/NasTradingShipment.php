<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingShipment extends Model
{
    protected $table = 'nas_trading_shipments';

    protected $fillable = [
        'shipment_no', 'lc_id', 'lc_no', 'pfi_no', 'customer_id', 'customer_name',
        'vessel', 'bl_number', 'bl_date', 'bl_qty', 'bl_unit',
        'bill_of_entry', 'be_date', 'arrival_date', 'port_of_disc_id',
        'freight_value', 'cnf_value', 'duty_amount', 'duty_pay_date',
        'shipping_mode', 'psi_company_id', 'psi_no', 'cnf_agent_id', 'transport_co_id',
        'grn_branch', 'activity_status', 'shipment_status', 'remarks', 'created_by',
    ];

    protected $casts = [
        'bl_date' => 'date', 'be_date' => 'date', 'arrival_date' => 'date', 'duty_pay_date' => 'date',
    ];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public function items()
    {
        return $this->hasMany(NasTradingShipmentItem::class, 'shipment_id');
    }

    public function costs()
    {
        return $this->hasMany(NasTradingShipmentCost::class, 'shipment_id');
    }

    public static function generateShipmentNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(shipment_no, 5) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'SHP-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

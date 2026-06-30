<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronBill extends Model
{
    protected $fillable = [
        'bill_no', 'branch_id', 'bill_type', 'bill_date', 'delivery_date',
        'job_id', 'job_no', 'party_name', 'party_address',
        'goods_description', 'mate_code', 'po_no',
        'quantity', 'quantity_unit', 'quantity_remark',
        'gross_weight', 'gross_weight_unit',
        'lc_no', 'lc_ref', 'be_no', 'be_date',
        'invoice_no', 'invoice_ref', 'invoice_date',
        'bl_no', 'bl_ref',
        'assessable_value', 'invoice_value_bdt', 'remarks',
        'sub_total', 'commission_on', 'commission_rate', 'commission_amount',
        'total_payable', 'less_customs_duty_tax', 'income_tax_cnf_com',
        'net_payable', 'advance_amount', 'due_amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'      => 'date',
            'delivery_date'  => 'date',
            'be_date'        => 'date',
            'invoice_date'   => 'date',
        ];
    }

    public static function billTypes(): array
    {
        return ['Import', 'Export', 'Re-Export', 'Freight', 'Port Service', 'Other'];
    }

    public static function commissionOnOptions(): array
    {
        return ['ASSESSABLE', 'INVOICE VALUE'];
    }

    public static function generateBillNo(): string
    {
        $last = static::lockForUpdate()->max(DB::raw("CAST(SUBSTRING(bill_no, 4) AS UNSIGNED)"));
        return 'BIL' . str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(ChevronBillItem::class, 'bill_id');
    }
}

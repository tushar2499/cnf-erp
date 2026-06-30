<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsSupplierBill extends Model
{
    protected $table = 'nas_freights_supplier_bills';

    protected $fillable = [
        'pay_order_no', 'from_date', 'to_date',
        'supplier_id', 'supplier_name',
        'bill_date', 'bill_by', 'note',
        'branch_id', 'total_amount', 'status', 'entry_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'bill_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasFreightsSupplierBillItem::class, 'bill_id');
    }

    public static function generatePayOrderNo(): string
    {
        $max = static::lockForUpdate()->max(
            DB::raw("CAST(SUBSTRING(pay_order_no, 5) AS UNSIGNED)")
        );
        return 'SPO-' . str_pad(($max ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingCustomerBill extends Model
{
    protected $table = 'nas_trading_customer_bills';

    protected $fillable = [
        'bill_no', 'lc_id', 'lc_no', 'pfi_no', 'customer_id', 'customer_name',
        'customer_address', 'bill_date', 'currency', 'exchange_rate',
        'sub_total', 'vat_pct', 'vat_amount', 'total_amount', 'status', 'note', 'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasTradingCustomerBillItem::class, 'bill_id');
    }

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public static function generateBillNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(bill_no, 6) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'BILL-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

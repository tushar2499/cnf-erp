<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingLcBillStatement extends Model
{
    protected $table = 'nas_trading_lc_bill_statements';

    protected $fillable = [
        'branch_id', 'bill_no', 'customer_id', 'bill_date',
        'status', 'note', 'enclosed', 'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasTradingLcBillStatementItem::class, 'bill_statement_id');
    }

    public function customer()
    {
        return $this->belongsTo(NasTradingCustomer::class, 'customer_id');
    }

    public static function generateBillNo(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $max = self::lockForUpdate()
                ->whereYear('created_at', $year)
                ->selectRaw('MAX(CAST(SUBSTRING_INDEX(bill_no, "/", -2) AS UNSIGNED)) as max_no')
                ->value('max_no') ?? 0;

            return 'NAS/COM/'.str_pad($max + 1, 2, '0', STR_PAD_LEFT).'/'.$year;
        });
    }
}

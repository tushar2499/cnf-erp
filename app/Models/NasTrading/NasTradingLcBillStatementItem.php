<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingLcBillStatementItem extends Model
{
    protected $table = 'nas_trading_lc_bill_statement_items';

    protected $fillable = [
        'bill_statement_id',
        'lc_id',
        'serial_number',
        'bill_no',
        'sort_order',
    ];

    protected $casts = [
        'sort_order'    => 'integer',
        'serial_number' => 'string',
    ];

    public function billStatement()
    {
        return $this->belongsTo(NasTradingLcBillStatement::class, 'bill_statement_id');
    }

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public static function generateSerialNo(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $max = self::lockForUpdate()
                ->whereYear('created_at', $year)
                ->whereNotNull('serial_number')
                ->selectRaw('MAX(CAST(SUBSTRING_INDEX(serial_number, "/", -2) AS UNSIGNED)) as max_no')
                ->value('max_no') ?? 0;

            return 'NAS/COM/'.str_pad($max + 1, 2, '0', STR_PAD_LEFT).'/'.$year;
        });
    }
}

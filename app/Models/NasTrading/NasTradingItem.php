<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingItem extends Model
{
    protected $table = 'nas_trading_items';

    protected $fillable = [
        'code', 'name', 'hs_code', 'unit', 'category', 'status',
    ];

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'ITM-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

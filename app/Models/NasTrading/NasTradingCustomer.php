<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingCustomer extends Model
{
    protected $table = 'nas_trading_customers';

    protected $fillable = [
        'code', 'company_name', 'contact_person', 'phone', 'email',
        'address', 'delivery_address', 'credit_limit', 'status',
    ];

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'CUS-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

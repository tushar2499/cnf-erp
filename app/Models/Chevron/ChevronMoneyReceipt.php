<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronMoneyReceipt extends Model
{
    protected $fillable = [
        'receipt_no', 'branch_id', 'receipt_date', 'party_id', 'party_name',
        'pay_type', 'payable_amount', 'total_amount', 'description', 'status',
    ];

    protected function casts(): array
    {
        return ['receipt_date' => 'date'];
    }

    public function items()
    {
        return $this->hasMany(ChevronMoneyReceiptItem::class, 'receipt_id');
    }

    public static function generateReceiptNo(): string
    {
        return DB::transaction(function () {
            $max = DB::table('chevron_money_receipts')->lockForUpdate()->max('receipt_no');
            $next = $max ? (int) substr($max, 2) + 1 : 1;
            return 'MR' . str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function payTypes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Mobile Banking', 'Card', 'Mixed'];
    }

    public static function rowPayTypes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Mobile Banking', 'Card'];
    }
}

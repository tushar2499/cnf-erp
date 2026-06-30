<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsMoneyReceipt extends Model
{
    protected $table = 'nas_freights_money_receipts';

    protected $fillable = [
        'branch_id', 'receipt_no', 'receipt_date', 'customer_id', 'customer_name',
        'bill_id', 'bill_no', 'bill_amount', 'amount_received',
        'payment_mode', 'reference_no', 'note', 'entry_by',
    ];

    protected $casts = ['receipt_date' => 'date'];

    public static function generateReceiptNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(receipt_no, 4) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'MR-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function paymentModes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Online'];
    }

    public function bill()
    {
        return $this->belongsTo(NasFreightsCustomerBill::class, 'bill_id');
    }
}

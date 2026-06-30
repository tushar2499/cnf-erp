<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsSupplierPayment extends Model
{
    protected $table = 'nas_freights_supplier_payments';

    protected $fillable = [
        'branch_id', 'payment_no', 'payment_date', 'supplier_id', 'supplier_name',
        'bill_id', 'bill_no', 'bill_amount', 'amount_paid',
        'payment_mode', 'reference_no', 'note', 'entry_by',
    ];

    protected $casts = ['payment_date' => 'date'];

    public static function generatePaymentNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(payment_no, 4) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'SP-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function paymentModes(): array
    {
        return ['Cash', 'Cheque', 'Bank Transfer', 'Online'];
    }

    public function bill()
    {
        return $this->belongsTo(NasFreightsSupplierBill::class, 'bill_id');
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcPayment extends Model
{
    protected $fillable = ['lc_id', 'payment_type', 'receipt_no', 'date', 'amount', 'remark'];

    protected $casts = ['date' => 'date:Y-m-d'];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcPayment extends Model
{
    protected $fillable = ['lc_id', 'payment_type', 'receipt_no', 'date', 'amount'];

    protected $casts = ['date' => 'date'];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

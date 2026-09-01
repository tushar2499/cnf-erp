<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcBillPaid extends Model
{
    protected $fillable = ['lc_id', 'date', 'posting', 'remarks', 'amount'];

    protected $casts = ['date' => 'date:Y-m-d'];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

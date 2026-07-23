<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcOtherCharge extends Model
{
    protected $table = 'nas_trading_lc_other_charges';

    protected $fillable = ['lc_id', 'name', 'amount'];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

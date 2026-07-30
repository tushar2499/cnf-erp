<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcDutyAdvance extends Model
{
    protected $table = 'nas_trading_lc_bill_of_entry_duty_advances';

    protected $fillable = [
        'bill_of_entry_id', 'amount', 'date', 'posting',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function billOfEntry()
    {
        return $this->belongsTo(NasTradingLcBillOfEntry::class, 'bill_of_entry_id');
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcBillOfEntry extends Model
{
    protected $table = 'nas_trading_lc_bill_of_entries';

    protected $fillable = [
        'lc_id', 'be_no', 'be_date', 'customs_duty', 'customs_duty_posting',
        'cnf_party', 'cnf_total_costing', 'cnf_total_posting',
    ];

    protected $casts = [
        'be_date' => 'date:Y-m-d',
    ];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public function dutyAdvances()
    {
        return $this->hasMany(NasTradingLcDutyAdvance::class, 'bill_of_entry_id');
    }
}

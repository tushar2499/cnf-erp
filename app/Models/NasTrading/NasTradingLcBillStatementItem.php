<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcBillStatementItem extends Model
{
    protected $table = 'nas_trading_lc_bill_statement_items';

    protected $fillable = [
        'bill_statement_id', 'lc_id', 'sort_order',
    ];

    public function billStatement()
    {
        return $this->belongsTo(NasTradingLcBillStatement::class, 'bill_statement_id');
    }

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcInvoiceValue extends Model
{
    protected $table = 'nas_trading_lc_invoice_values';

    protected $fillable = [
        'lc_id', 'invoice_no', 'invoice_value',
    ];
}

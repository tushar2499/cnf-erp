<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingCustomerBillItem extends Model
{
    protected $table = 'nas_trading_customer_bill_items';

    protected $fillable = [
        'bill_id', 'description', 'expense_head_id', 'qty', 'unit_price', 'amount', 'note', 'sort_order',
    ];
}

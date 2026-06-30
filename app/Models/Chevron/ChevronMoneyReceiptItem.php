<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronMoneyReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id', 'payment_type', 'account_id', 'account_no',
        'cheque_card_holder', 'cheque_card_no', 'amount', 'cheque_date',
    ];

    protected function casts(): array
    {
        return ['cheque_date' => 'date'];
    }

    public function account()
    {
        return $this->belongsTo(ChevronAccount::class, 'account_id');
    }
}

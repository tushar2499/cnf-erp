<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronBillItem extends Model
{
    protected $fillable = [
        'bill_id', 'expense_category_id', 'expense_head_id', 'amount', 'note',
    ];

    public function expenseCategory()
    {
        return $this->belongsTo(ChevronExpenseCategory::class, 'expense_category_id');
    }

    public function expenseHead()
    {
        return $this->belongsTo(ChevronExpenseHead::class, 'expense_head_id');
    }
}

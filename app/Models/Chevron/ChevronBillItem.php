<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronBillItem extends Model
{
    protected $fillable = [
        'bill_id', 'expense_category_id', 'expense_head_id', 'amount', 'note', 'rate', 'qty',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'qty'  => 'decimal:3',
        ];
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ChevronExpenseCategory::class, 'expense_category_id');
    }

    public function expenseHead()
    {
        return $this->belongsTo(ChevronExpenseHead::class, 'expense_head_id');
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcExpense extends Model
{
    protected $table = 'nas_trading_lc_expenses';

    protected $fillable = [
        'lc_id', 'expense_head_id', 'expense_head_name', 'expense_date',
        'amount', 'posting_type', 'posting_sub_type', 'reference', 'note', 'entry_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }

    public function expenseHead()
    {
        return $this->belongsTo(NasTradingExpenseHead::class, 'expense_head_id');
    }
}

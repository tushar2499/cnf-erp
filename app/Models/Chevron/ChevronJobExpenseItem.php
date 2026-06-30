<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronJobExpenseItem extends Model
{
    protected $fillable = [
        'job_expense_id', 'expense_head_id',
        'receiptable', 'expense_amount', 'approved_amount',
        'expense_date', 'note',
    ];

    protected function casts(): array
    {
        return ['expense_date' => 'date'];
    }

    public function expenseHead() { return $this->belongsTo(ChevronExpenseHead::class, 'expense_head_id'); }
}

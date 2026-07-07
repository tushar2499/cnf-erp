<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronExpenseHead extends Model
{
    protected $fillable = ['name', 'expense_category_id', 'type', 'amount', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amount'    => 'decimal:2',
        ];
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ChevronExpenseCategory::class, 'expense_category_id');
    }

    public function employees()
    {
        return $this->belongsToMany(ChevronEmployee::class, 'chevron_expense_head_employees', 'expense_head_id', 'employee_id');
    }
}

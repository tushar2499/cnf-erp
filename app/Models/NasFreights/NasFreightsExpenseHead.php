<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsExpenseHead extends Model
{
    protected $table = 'nas_freights_expense_heads';

    protected $fillable = ['name', 'type', 'expense_category_id', 'amount', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public static function types(): array
    {
        return ['Internal', 'External'];
    }

    public function expenseCategory()
    {
        return $this->belongsTo(NasFreightsExpenseCategory::class, 'expense_category_id');
    }

    public function freightBookingExpenseItems()
    {
        return $this->hasMany(NasFreightsFreightBookingExpenseItem::class, 'expense_head_id');
    }
}

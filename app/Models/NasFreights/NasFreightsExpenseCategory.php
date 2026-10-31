<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsExpenseCategory extends Model
{
    protected $table = 'nas_freights_expense_categories';

    protected $fillable = ['name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function expenseHeads()
    {
        return $this->hasMany(NasFreightsExpenseHead::class, 'expense_category_id');
    }
}

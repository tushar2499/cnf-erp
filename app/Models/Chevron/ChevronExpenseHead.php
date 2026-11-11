<?php

namespace App\Models\Chevron;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class ChevronExpenseHead extends Model
{
    protected $fillable = ['name', 'expense_category_id', 'is_bill', 'is_job', 'type', 'amount', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_bill'   => 'boolean',
            'is_job'    => 'boolean',
            'is_active' => 'boolean',
            'amount'    => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ChevronExpenseHead $head) {
            $head->applyCategoryApplicability();
        });
    }

    /**
     * Derive the bill/job flags from the linked category when they are not
     * explicitly provided (e.g. imports and API-created heads).
     */
    public function applyCategoryApplicability(): void
    {
        if ($this->is_bill !== null || $this->is_job !== null) {
            return;
        }

        $category = $this->expenseCategory;

        if (! $category || (! $category->is_bill && ! $category->is_job)) {
            return;
        }

        $this->is_bill = $category->is_bill;
        $this->is_job = $category->is_job;
    }

    public function applicabilityBadge(): string
    {
        if ($this->is_bill && $this->is_job) {
            return '<span class="badge bg-info text-white">Bill & Job</span>';
        }
        if ($this->is_bill) {
            return '<span class="badge text-white" style="background-color:#0891b2">Bill</span>';
        }
        if ($this->is_job) {
            return '<span class="badge bg-primary">Job</span>';
        }

        return '<span class="badge bg-secondary">None</span>';
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ChevronExpenseCategory::class, 'expense_category_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'chevron_expense_head_employees', 'expense_head_id', 'employee_id');
    }
}

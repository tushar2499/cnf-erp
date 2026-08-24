<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronExpenseCategory extends Model
{
    protected $fillable = ['name', 'is_bill', 'is_job', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'name'      => 'string',
            'is_bill'   => 'boolean',
            'is_job'    => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function typeBadge(): string
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
}

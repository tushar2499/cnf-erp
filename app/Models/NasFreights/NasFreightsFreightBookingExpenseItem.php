<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsFreightBookingExpenseItem extends Model
{
    protected $table = 'nas_freights_freight_booking_expense_items';

    protected $fillable = [
        'freight_booking_expense_id', 'expense_head_id',
        'receiptable', 'expense_amount', 'approved_amount',
        'expense_date', 'note',
    ];

    protected function casts(): array
    {
        return [
            'expense_date'    => 'date',
            'expense_amount'  => 'decimal:2',
            'approved_amount' => 'decimal:2',
        ];
    }

    public function expenseHead()
    {
        return $this->belongsTo(NasFreightsExpenseHead::class, 'expense_head_id');
    }
}

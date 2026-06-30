<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingExpenseHead extends Model
{
    protected $table = 'nas_trading_expense_heads';

    protected $fillable = [
        'name', 'type', 'category', 'status',
    ];

    public static function categories(): array
    {
        return [
            'TRANSPORTATION', 'CUSTOMS EXPENSE', 'CUSTOMS', 'JETTY EXPENSES',
            'PORT & JETTY', 'MISCELLENEOUS', 'GODOWN', 'CUSTOMS SECTION',
            'DELIVERY SECTION', 'EXPORT SECTION', 'D/O SECTION', 'ALL Expenses',
            'DELIVERY SIDE', 'ASSESSMENT SIDE', 'LC Cost', 'Duty', 'Shipping', 'Other',
        ];
    }
}

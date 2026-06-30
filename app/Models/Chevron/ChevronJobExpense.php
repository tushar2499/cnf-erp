<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronJobExpense extends Model
{
    protected $fillable = [
        'expense_no', 'branch_id', 'job_id', 'job_no', 'employee_id',
        'be_no', 'invoice_no', 'invoice_value_usd', 'bl_no',
        'date', 'total_expense_amount', 'total_approved_amount',
        'remarks', 'status',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function job()      { return $this->belongsTo(ChevronJob::class,      'job_id'); }
    public function employee() { return $this->belongsTo(ChevronEmployee::class, 'employee_id'); }
    public function items()    { return $this->hasMany(ChevronJobExpenseItem::class, 'job_expense_id'); }

    public static function generateExpenseNo(): string
    {
        $last = static::lockForUpdate()->max(
            DB::raw("CAST(SUBSTRING(expense_no, 4) AS UNSIGNED)")
        );
        return 'EXP' . str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsFreightBookingExpense extends Model
{
    protected $table = 'nas_freights_freight_booking_expenses';

    protected $fillable = [
        'expense_no', 'booking_type', 'booking_id', 'booking_no',
        'employee_id', 'invoice_no', 'invoice_value_usd', 'bl_no',
        'branch_id', 'date',
        'total_expense_amount', 'total_approved_amount',
        'remarks', 'status', 'entry_by',
    ];

    protected function casts(): array
    {
        return [
            'date'                  => 'date',
            'total_expense_amount'  => 'decimal:2',
            'total_approved_amount' => 'decimal:2',
            'invoice_value_usd'     => 'decimal:2',
        ];
    }

    public function items()
    {
        return $this->hasMany(NasFreightsFreightBookingExpenseItem::class, 'freight_booking_expense_id');
    }

    public function branch()
    {
        return $this->belongsTo(NasFreightsBranch::class, 'branch_id');
    }

    public function employee()
    {
        return $this->belongsTo(NasFreightsEmployee::class, 'employee_id');
    }

    public static function generateExpenseNo(): string
    {
        $last = static::lockForUpdate()->max(
            DB::raw('CAST(SUBSTRING(expense_no, 4) AS UNSIGNED)')
        );

        return 'FBE'.str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function statuses(): array
    {
        return ['Draft', 'Submitted', 'Approved'];
    }

    public function scopeForBooking($query, string $type, int $id)
    {
        return $query->where('booking_type', $type)->where('booking_id', $id);
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingEmployee extends Model
{
    protected $table = 'nas_trading_employees';

    protected $fillable = [
        'branch_id', 'designation_id', 'department_id', 'employee_prefix',
        'code', 'name', 'short_name', 'father_name', 'mother_name',
        'joining_date', 'designation', 'phone', 'email', 'address', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'is_active'    => 'boolean',
        ];
    }

    public function designation()
    {
        return $this->belongsTo(NasTradingDesignation::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(NasTradingDepartment::class, 'department_id');
    }

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'EMP-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronEmployee extends Model
{
    protected $fillable = [
        'employee_prefix', 'employee_id', 'name', 'designation_id', 'department_id',
        'joining_date', 'short_name', 'father_name', 'mother_name',
        'phone', 'email', 'address', 'current_status', 'status', 'branch_id', 'is_active',
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
        return $this->belongsTo(ChevronDesignation::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(ChevronDepartment::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(ChevronBranch::class, 'branch_id');
    }

    public static function generateEmployeeId(string $prefix): string
    {
        $last = static::where('employee_prefix', $prefix)->lockForUpdate()->max(
            \DB::raw("CAST(SUBSTRING(employee_id, " . (strlen($prefix) + 1) . ") AS UNSIGNED)")
        );
        return $prefix . str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsEmployee extends Model
{
    protected $table = 'nas_freights_employees';

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
        return $this->belongsTo(NasFreightsDesignation::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(NasFreightsDepartment::class, 'department_id');
    }

    public static function generateCode(): string
    {
        $max = static::lockForUpdate()->max('code');
        preg_match('/\d+$/', $max ?? '', $m);
        return 'EMP-' . str_pad(($m[0] ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

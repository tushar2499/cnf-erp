<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'company_type',
        'name',
        'code',
        'designation_id',
        'joining_date',
        'short_name',
        'father_name',
        'mother_name',
        'phone',
        'email',
        'address',
        'current_status',
        'type',
        'team_leader_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'joining_date' => 'date',
        ];
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function teamLeader()
    {
        return $this->belongsTo(self::class, 'team_leader_id');
    }

    public function branchAccess()
    {
        return $this->hasMany(EmployeeBranchAccess::class, 'employee_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'employee_id');
    }

    public function getCompanyTypeLabelAttribute(): string
    {
        return match ($this->company_type) {
            'chevron'      => 'Chevron Lines',
            'nas_freights' => 'NAS Freights',
            'nas_trading'  => 'NAS Trading',
            default        => $this->company_type,
        };
    }
}

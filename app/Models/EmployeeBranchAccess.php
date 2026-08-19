<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBranchAccess extends Model
{
    protected $table = 'employee_branch_access';

    protected $fillable = ['employee_id', 'company_id', 'branch_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}

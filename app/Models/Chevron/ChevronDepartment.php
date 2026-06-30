<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;

class ChevronDepartment extends Model
{
    protected $table = 'chevron_departments';

    protected $fillable = ['name', 'status'];

    public function employees()
    {
        return $this->hasMany(ChevronEmployee::class, 'department_id');
    }
}

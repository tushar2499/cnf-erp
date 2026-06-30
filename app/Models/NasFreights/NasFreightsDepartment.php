<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsDepartment extends Model
{
    protected $table = 'nas_freights_departments';

    protected $fillable = ['name', 'status'];

    public function employees()
    {
        return $this->hasMany(NasFreightsEmployee::class, 'department_id');
    }
}

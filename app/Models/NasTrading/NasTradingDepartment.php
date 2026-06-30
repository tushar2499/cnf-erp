<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingDepartment extends Model
{
    protected $table = 'nas_trading_departments';

    protected $fillable = ['name', 'status'];

    public function employees()
    {
        return $this->hasMany(NasTradingEmployee::class, 'department_id');
    }
}

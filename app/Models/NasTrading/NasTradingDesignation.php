<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingDesignation extends Model
{
    protected $table = 'nas_trading_designations';

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function employees()
    {
        return $this->hasMany(NasTradingEmployee::class, 'designation_id');
    }
}

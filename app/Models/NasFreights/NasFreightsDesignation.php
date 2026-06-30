<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsDesignation extends Model
{
    protected $table = 'nas_freights_designations';

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function employees()
    {
        return $this->hasMany(NasFreightsEmployee::class, 'designation_id');
    }
}

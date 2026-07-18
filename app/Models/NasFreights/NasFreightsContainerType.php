<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsContainerType extends Model
{
    protected $table = 'nas_freights_container_types';

    protected $fillable = ['name', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}

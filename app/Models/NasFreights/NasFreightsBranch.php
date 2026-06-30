<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsBranch extends Model
{
    protected $table = 'nas_freights_branches';
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}

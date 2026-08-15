<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'guard_name', 'company_id', 'module', 'sorting_order'];

    protected function casts(): array
    {
        return [
            'sorting_order' => 'integer',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }
}

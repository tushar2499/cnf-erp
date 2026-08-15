<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'logo', 'address', 'phone', 'email', 'is_active',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_company');
    }
}

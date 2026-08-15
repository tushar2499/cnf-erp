<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'guard_name'];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'role_company');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class);
    }
}

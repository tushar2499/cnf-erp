<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBranchAccess extends Model
{
    protected $table = 'user_branch_access';

    protected $fillable = ['user_id', 'company_id', 'branch_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

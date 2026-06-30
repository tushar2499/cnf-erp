<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingPsiCompany extends Model
{
    protected $table = 'nas_trading_psi_companies';

    protected $fillable = [
        'name', 'country', 'status',
    ];
}

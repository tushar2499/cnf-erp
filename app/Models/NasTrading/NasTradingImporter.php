<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingImporter extends Model
{
    protected $table = 'nas_trading_importers';

    protected $fillable = [
        'name', 'bin_no', 'address', 'status',
    ];
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;

class NasTradingLcRtValue extends Model
{
    protected $fillable = ['lc_id', 'date', 'note', 'amount'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function lc()
    {
        return $this->belongsTo(NasTradingLc::class, 'lc_id');
    }
}

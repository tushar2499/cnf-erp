<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;

class NasFreightsEmployee extends Model
{
    protected $table = 'nas_freights_employees';

    protected $fillable = ['branch_id', 'code', 'name', 'designation', 'phone', 'email', 'status'];

    public static function generateCode(): string
    {
        $max = static::lockForUpdate()->max('code');
        preg_match('/\d+$/', $max ?? '', $m);
        return 'EMP-' . str_pad(($m[0] ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsOverseasAgent extends Model
{
    protected $table = 'nas_freights_overseas_agents';

    protected $fillable = [
        'agent_code', 'name', 'country', 'city', 'address',
        'contact_person', 'designation', 'email', 'phone', 'mobile',
        'payment_terms', 'remarks', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max = DB::table('nas_freights_overseas_agents')->lockForUpdate()->max('agent_code');
            $next = 1;
            if ($max && preg_match('/\d+$/', $max, $m)) {
                $next = (int) $m[0] + 1;
            }

            return 'OA-'.str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }
}

<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsSupplier extends Model
{
    protected $table = 'nas_freights_suppliers';

    protected $fillable = [
        'branch_id', 'code', 'company_name', 'owner_name', 'address',
        'phone_no', 'fax', 'url', 'mobile_no', 'email',
        'contact', 'designation', 'supplier_group', 'taxscope', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $max  = DB::table('nas_freights_suppliers')->lockForUpdate()->max('code');
            $next = 1;
            if ($max && preg_match('/\d+$/', $max, $m)) {
                $next = (int) $m[0] + 1;
            }
            return 'SUP-' . str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function supplierGroups(): array
    {
        return [
            'Exporter' => 'Exporter :- External',
            'Importer' => 'Importer :- Internal',
        ];
    }

    public static function taxscopes(): array
    {
        return ['Exempted', 'Applied'];
    }
}

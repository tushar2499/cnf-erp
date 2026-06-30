<?php

namespace App\Models\NasFreights;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasFreightsCustomer extends Model
{
    protected $table = 'nas_freights_customers';

    protected $fillable = [
        'branch_id', 'id_prefix', 'customer_group', 'customer_id', 'name', 'owner_name',
        'address', 'phone', 'fax', 'mobile', 'email', 'sales_person',
        'customer_account', 'vat_id', 'identity_type', 'tin_bin_nid',
        'contact_person_details', 'country', 'division', 'district',
        'city', 'region', 'customer_id_reference', 'postal_code',
        'pay_type', 'portal_password', 'status', 'taxscope',
        'discount', 'commission', 'credit_limit', 'limit_days',
        'security_deposit', 'maturity_days', 'prefix',
    ];

    public static function customerGroups(): array
    {
        return ['CORPORATE_SALES', 'SPECIAL_SALES'];
    }

    public static function generateCustomerId(string $prefix): string
    {
        $last = static::where('id_prefix', $prefix)->lockForUpdate()->max(
            DB::raw("CAST(SUBSTRING(customer_id, " . (strlen($prefix) + 1) . ") AS UNSIGNED)")
        );
        return $prefix . str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

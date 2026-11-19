<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronCustomer extends Model
{
    protected $fillable = [
        'id_prefix', 'customer_id', 'name', 'branch_id', 'owner_name',
        'address', 'phone', 'fax', 'mobile', 'email', 'sales_person',
        'customer_account', 'vat_id', 'identity_type', 'tin_bin_nid',
        'contact_person_details', 'country', 'division', 'district',
        'city', 'region', 'customer_id_reference', 'postal_code',
        'pay_type', 'portal_password', 'status', 'taxscope',
        'discount', 'commission', 'credit_limit', 'limit_days',
        'security_deposit', 'maturity_days', 'prefix',
    ];

    public function branch()
    {
        return $this->belongsTo(ChevronBranch::class, 'branch_id');
    }

    /**
     * Recalculate the stored `advance_balance` (total advance across all branches: Advance receipts minus
     * "Advance Adjustment" rows used on Bill receipts). The column is maintained by the system only, so it is
     * deliberately not mass assignable. Call inside the transaction that changed the receipts.
     */
    public static function refreshAdvanceBalance(int $customerId): float
    {
        $received = (float) ChevronMoneyReceipt::where('party_id', $customerId)
            ->where('receipt_type', ChevronMoneyReceipt::TYPE_ADVANCE)
            ->sum('total_amount');

        $adjusted = (float) ChevronMoneyReceiptItem::where('payment_type', ChevronMoneyReceipt::ADVANCE_ADJUSTMENT)
            ->whereHas('receipt', fn ($q) => $q->where('party_id', $customerId)->where('receipt_type', ChevronMoneyReceipt::TYPE_BILL))
            ->sum('amount');

        $balance = round($received - $adjusted, 2);

        static::whereKey($customerId)->update(['advance_balance' => $balance]);

        return $balance;
    }

    public static function generateCustomerId(string $prefix): string
    {
        $last = static::where('id_prefix', $prefix)->lockForUpdate()->max(
            DB::raw('CAST(SUBSTRING(customer_id, '.(strlen($prefix) + 1).') AS UNSIGNED)')
        );

        return $prefix.str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models\NasTrading;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NasTradingLc extends Model
{
    protected $table = 'nas_trading_lcs';

    protected $fillable = [
        'lc_no_system', 'customer_id', 'customer_name', 'pfi_no', 'pfi_date',
        'lc_no', 'lc_open_date', 'lc_expiry_date', 'lc_type', 'lc_status',
        'month', 'shipment_from', 'last_shipment_date', 'shipping_docs_received_date',
        'supplier_id', 'supplier_name', 'supplier_country', 'importer_id', 'item_description', 'customer_po_date',
        'pfi_value', 'currency', 'lc_open_rate', 'margin_percent', 'lc_margin_amt',
        'lc_open_cost_bdt', 'freight_value', 'lc_value', 'amount_bdt', 'total_lc_cost', 'landed_cost',
        'doc_rt_rate', 'lc_rt_value', 'lc_charge_posting',
        'advance_received_bdt', 'advance_date', 'advance_posting',
        'rest_amount_bdt', 'rest_amount_date', 'rest_amount_posting',
        'total_received_bdt', 'lc_closing_bill', 'lc_closing_bill_date',
        'duty_advance', 'duty_advance_date', 'duty_advance_posting',
        'bill_of_entry_no', 'bill_of_entry_date', 'customs_duty', 'customs_duty_posting',
        'cnf_party', 'cnf_total_cost', 'cnf_cost_posting',
        'payable_receivable', 'received_amount', 'received_date',
        'vat_return', 'vat_return_date', 'vat_return_posting', 'income_tax',
        'bank_statement_amt', 'bank_lc_diff', 'lc_commission', 'lc_commission_date',
        'sales_amount', 'sales_posting', 'coss_amount', 'coss_posting',
        'opening_bank_id', 'port_of_dest_id', 'country_of_origin', 'payment_mode',
        'insurance_amt', 'cover_note', 'insurance_validity', 'psi_no', 'psi_company_id',
        'comm_currency', 'comm_amount', 'doc_status', 'sanction_types', 'third_party', 'remarks', 'created_by',
    ];

    protected $casts = [
        'pfi_date' => 'date', 'lc_open_date' => 'date', 'lc_expiry_date' => 'date',
        'last_shipment_date' => 'date', 'shipping_docs_received_date' => 'date',
        'customer_po_date' => 'date', 'advance_date' => 'date', 'rest_amount_date' => 'date',
        'lc_closing_bill_date' => 'date', 'duty_advance_date' => 'date',
        'bill_of_entry_date' => 'date', 'vat_return_date' => 'date',
        'received_date' => 'date', 'lc_commission_date' => 'date', 'insurance_validity' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(NasTradingLcItem::class, 'lc_id');
    }

    public function expenses()
    {
        return $this->hasMany(NasTradingLcExpense::class, 'lc_id');
    }

    public function customer()
    {
        return $this->belongsTo(NasTradingCustomer::class, 'customer_id');
    }

    public static function generateLcNo(): string
    {
        return DB::transaction(function () {
            $max = self::lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(lc_no_system, 4) AS UNSIGNED)) as max_no")
                ->value('max_no') ?? 0;
            return 'LC-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}

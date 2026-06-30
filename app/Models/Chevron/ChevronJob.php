<?php

namespace App\Models\Chevron;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChevronJob extends Model
{
    protected $fillable = [
        'job_no', 'branch_id', 'service_id', 'job_type_id', 'port_id', 'country_of_origin', 'job_date',
        'customer_id', 'party_name', 'party_address',
        'item_id', 'goods_name', 'pack_quantity', 'pack_unit',
        'copy_doc_received_date', 'original_doc_received_date', 'eta_date',
        'hbi_hawb_no', 'hbi_hawb_date',
        'gross_weight', 'gross_weight_unit', 'net_weight',
        'be_no', 'be_date', 'lc_no', 'lc_date', 'lca_no', 'lca_date', 'po_no', 'mate_code',
        'bl_no', 'bl_date', 'mbl_mawb_no', 'mbl_mawb_date', 'invoice_no', 'invoice_date',
        'lading_no', 'lading_date', 'flight_no', 'flight_date',
        'vessel_name', 'boyge_no', 'vessel_etb_agent', 'al_no', 'sailed_no',
        'arrived_date', 'common_lading_date', 'w_rent_due_date',
        'berthing', 'berthing_date', 'shed_no', 'yard_no',
        'rot_no', 'bl_weight_measurement', 'jetty_sarker_name', 'contact_no', 'unit_no',
        'port_bill_amount', 'port_bill_date', 'labour_bill_amount', 'labour_bill_date',
        'etb_date', 'shipping_charge', 'transport_name', 'transport_no', 'delivery_date', 'remarks',
        'consignee_name', 'consignee_address', 'agent_name', 'agent_address',
        'container_no', 'commodity', 'no_of_container', 'pol', 'destination', 'etd_date',
        'received_amount', 'due_amount', 'assessable_value',
        'currency_type', 'currency_rate', 'assessable_value_bdt',
        'be_efr_no',
        'pickup_charge_1',   'pickup_charge_2',
        'cnf_charge_1',      'cnf_charge_2',
        'stuffing_charge_1', 'stuffing_charge_2',
        'carrier_bill_1',    'carrier_bill_2',
        'mbl_free_1',        'mbl_free_2',
        'hbl_charge_1',      'hbl_charge_2',
        'ps_to_agent_1',     'ps_to_agent_2',
        'ps_to_b_co_1',      'ps_to_b_co_2',
        'noc_charge_1',      'noc_charge_2',
        'other_charge_1',    'other_charge_2',
        'invoice_value_1',   'invoice_value_2',
        'duty_rate',     'duty_amount',
        'ait_rate',      'ait_amount',
        'sup_tax_rate',  'sup_tax_amount',
        'vat_rate',      'vat_amount',
        'rd_rate',       'rd_amount',
        'atv_rate',      'atv_amount',
        'df_vat_rate',   'df_vat_amount',
        'total_payable_1',  'total_payable_2',
        'comm_discount_pct','comm_discount_1', 'comm_discount_2',
        'net_payable_1',    'net_payable_2',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'job_date'                  => 'date',
            'copy_doc_received_date'    => 'date',
            'original_doc_received_date'=> 'date',
            'eta_date'                  => 'date',
            'hbi_hawb_date'             => 'date',
            'be_date'                   => 'date',
            'lc_date'                   => 'date',
            'lca_date'                  => 'date',
            'bl_date'                   => 'date',
            'mbl_mawb_date'             => 'date',
            'invoice_date'              => 'date',
            'lading_date'               => 'date',
            'flight_date'               => 'date',
            'arrived_date'              => 'date',
            'common_lading_date'        => 'date',
            'w_rent_due_date'           => 'date',
            'berthing_date'             => 'date',
            'port_bill_date'            => 'date',
            'labour_bill_date'          => 'date',
            'etb_date'                  => 'date',
            'delivery_date'             => 'date',
            'etd_date'                  => 'date',
        ];
    }

    public function service()  { return $this->belongsTo(ChevronService::class,  'service_id'); }
    public function jobType()  { return $this->belongsTo(ChevronJobType::class,  'job_type_id'); }
    public function port()     { return $this->belongsTo(ChevronPort::class,     'port_id'); }
    public function customer() { return $this->belongsTo(ChevronCustomer::class, 'customer_id'); }
    public function item()     { return $this->belongsTo(ChevronItem::class,     'item_id'); }

    public static function generateJobNo(): string
    {
        $last = static::lockForUpdate()->max(
            DB::raw("CAST(SUBSTRING(job_no, 3) AS UNSIGNED)")
        );
        return 'CF' . str_pad(($last ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function currencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'INR', 'SGD', 'AUD', 'CAD', 'CHF', 'AED', 'SAR', 'KWD', 'HKD', 'MYR', 'THB'];
    }

    public static function countries(): array
    {
        return [
            'Bangladesh', 'China', 'India', 'USA', 'UK', 'Germany', 'Japan', 'South Korea',
            'Singapore', 'Hong Kong', 'Thailand', 'Malaysia', 'Indonesia', 'Vietnam', 'Pakistan',
            'Sri Lanka', 'Myanmar', 'Nepal', 'Australia', 'Canada', 'France', 'Italy',
            'Netherlands', 'Belgium', 'UAE', 'Saudi Arabia', 'Turkey', 'Brazil', 'Russia', 'Other',
        ];
    }
}

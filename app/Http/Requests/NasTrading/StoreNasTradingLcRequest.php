<?php

namespace App\Http\Requests\NasTrading;

use Illuminate\Foundation\Http\FormRequest;

class StoreNasTradingLcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Identification — required
            'customer_id' => ['required'],
            'pfi_no'      => ['required', 'string', 'max:100'],

            // Identification — optional
            'pfi_date'                    => ['nullable', 'date'],
            'lc_no'                       => ['nullable', 'string', 'max:100'],
            'lc_open_date'                => ['nullable', 'date'],
            'lc_expiry_date'              => ['nullable', 'date'],
            'lc_type'                     => ['nullable', 'in:TT/LCA,Sight,DF'],
            'lc_status'                   => ['nullable', 'in:Open,Closed,Cancelled,Amended'],
            'month'                       => ['nullable', 'string', 'max:30'],
            'shipment_from'               => ['nullable', 'string', 'max:100'],
            'last_shipment_date'          => ['nullable', 'date'],
            'shipping_docs_received_date' => ['nullable', 'date'],

            // Supplier & Goods
            'supplier_id'      => ['nullable', 'integer'],
            'supplier_country' => ['nullable', 'string', 'max:100'],
            'importer_id'      => ['nullable', 'integer'],
            'item_description' => ['nullable', 'string'],
            'customer_po_date' => ['nullable', 'date'],

            // Bank & Documents
            'opening_bank_id'      => ['nullable', 'integer'],
            'port_of_dest_id'      => ['nullable', 'integer'],
            'country_of_origin'    => ['nullable', 'string', 'max:100'],
            'payment_mode'         => ['nullable', 'string', 'max:100'],
            'insurance_amt'        => ['nullable', 'numeric', 'min:0'],
            'cover_note'           => ['nullable', 'string', 'max:100'],
            'insurance_validity'   => ['nullable', 'date'],
            'psi_no'               => ['nullable', 'string', 'max:100'],
            'psi_company_id'       => ['nullable', 'integer'],
            'comm_currency'        => ['nullable', 'string', 'max:10'],
            'comm_amount'          => ['nullable', 'numeric', 'min:0'],
            'bank_charge'          => ['nullable', 'numeric', 'min:0'],
            'lc_amendment_charge'  => ['nullable', 'numeric', 'min:0'],
            'credit_report_charge' => ['nullable', 'numeric', 'min:0'],
            'other_charges'        => ['nullable', 'numeric', 'min:0'],
            'doc_status'           => ['nullable', 'in:Pending,Received,Complete'],
            'sanction_types'       => ['nullable', 'string', 'max:255'],
            'third_party'          => ['nullable', 'string', 'max:255'],
            'remarks'              => ['nullable', 'string'],

            // LC Details
            'pfi_value'             => ['nullable', 'numeric', 'min:0'],
            'currency'              => ['nullable', 'in:USD,EUR,GBP,CNY'],
            'lc_open_rate'          => ['nullable', 'numeric', 'min:0'],
            'lc_rate_amount'        => ['nullable', 'numeric', 'min:0'],
            'margin_percent'        => ['nullable', 'numeric', 'min:0'],
            'lc_margin_amt'         => ['nullable', 'numeric', 'min:0'],
            'lc_open_cost_bdt'      => ['nullable', 'numeric', 'min:0'],
            'freight_value'         => ['nullable', 'numeric', 'min:0'],
            'lc_value'              => ['nullable', 'numeric', 'min:0'],
            'amount_bdt'            => ['nullable', 'numeric', 'min:0'],
            'total_lc_cost'         => ['nullable', 'numeric', 'min:0'],
            'landed_cost'           => ['nullable', 'numeric', 'min:0'],
            'doc_rt_rate'           => ['nullable', 'numeric', 'min:0'],
            'lc_rt_value'           => ['nullable', 'numeric', 'min:0'],
            'lc_commission_percent' => ['nullable', 'numeric', 'min:0'],
            'lc_commission_flat'    => ['nullable', 'numeric', 'min:0'],
            'lc_charge_posting'     => ['nullable', 'string', 'max:100'],

            // Payment Tracking
            'lc_closing_bill'         => ['nullable', 'numeric', 'min:0'],
            'lc_closing_bill_date'    => ['nullable', 'date'],
            'total_received_bdt'      => ['nullable', 'numeric', 'min:0'],
            'payments'                => ['nullable', 'array'],
            'payments.*.payment_type' => ['required_with:payments', 'in:advance,regular'],
            'payments.*.receipt_no'   => ['nullable', 'string', 'max:100'],
            'payments.*.date'         => ['nullable', 'date'],
            'payments.*.amount'       => ['nullable', 'numeric', 'min:0'],

            // Duty & Clearance
            'duty_advance'         => ['nullable', 'numeric', 'min:0'],
            'duty_advance_date'    => ['nullable', 'date'],
            'duty_advance_posting' => ['nullable', 'string', 'max:100'],
            'bill_of_entry_no'     => ['nullable', 'string', 'max:100'],
            'bill_of_entry_date'   => ['nullable', 'date'],
            'customs_duty'         => ['nullable', 'numeric', 'min:0'],
            'customs_duty_posting' => ['nullable', 'string', 'max:100'],
            'cnf_party'            => ['nullable', 'string', 'max:255'],
            'cnf_total_cost'       => ['nullable', 'numeric', 'min:0'],
            'cnf_cost_posting'     => ['nullable', 'string', 'max:100'],

            // VAT / Tax / Sales
            'payable_receivable'  => ['nullable', 'numeric'],
            'received_amount'     => ['nullable', 'numeric', 'min:0'],
            'received_date'       => ['nullable', 'date'],
            'vat_return'          => ['nullable', 'numeric', 'min:0'],
            'vat_return_date'     => ['nullable', 'date'],
            'vat_return_posting'  => ['nullable', 'string', 'max:100'],
            'income_tax'          => ['nullable', 'numeric', 'min:0'],
            'bank_statement_amt'  => ['nullable', 'numeric', 'min:0'],
            'bank_lc_diff'        => ['nullable', 'numeric'],
            'lc_commission'       => ['nullable', 'numeric', 'min:0'],
            'lc_commission_date'  => ['nullable', 'date'],
            'sales_amount'        => ['nullable', 'numeric', 'min:0'],
            'sales_posting'       => ['nullable', 'string', 'max:100'],
            'coss_amount'         => ['nullable', 'numeric', 'min:0'],
            'coss_posting'        => ['nullable', 'string', 'max:100'],

            // Product Items
            'items'                => ['nullable', 'array'],
            'items.*.item_id'      => ['nullable', 'integer'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.product_code' => ['nullable', 'string', 'max:50'],
            'items.*.hs_code'      => ['nullable', 'string', 'max:50'],
            'items.*.qty_count'    => ['nullable', 'numeric', 'min:0'],
            'items.*.qty_unit'     => ['nullable', 'string', 'max:20'],
            'items.*.weight'       => ['nullable', 'numeric', 'min:0'],
            'items.*.weight_unit'  => ['nullable', 'string', 'max:20'],
            'items.*.unit_price'   => ['nullable', 'numeric', 'min:0'],
            'items.*.line_amount'  => ['nullable', 'numeric', 'min:0'],
            'items.*.currency'     => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'pfi_no.required'      => 'PFI No is required.',
        ];
    }
}

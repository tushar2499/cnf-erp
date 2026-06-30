<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_lcs', function (Blueprint $table) {
            $table->id();
            $table->string('lc_no_system', 20)->unique(); // LC-000001
            // Section 1 — Identification
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('pfi_no')->nullable();
            $table->date('pfi_date')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('lc_open_date')->nullable();
            $table->date('lc_expiry_date')->nullable();
            $table->enum('lc_type', ['TT/LCA', 'Sight', 'DF'])->nullable();
            $table->enum('lc_status', ['Open', 'Closed', 'Cancelled', 'Amended'])->default('Open');
            $table->string('month', 20)->nullable();
            $table->string('shipment_from')->nullable();
            $table->date('last_shipment_date')->nullable();
            $table->date('shipping_docs_received_date')->nullable();
            // Section 2 — Supplier & Goods
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_country')->nullable();
            $table->unsignedBigInteger('importer_id')->nullable();
            $table->text('item_description')->nullable();
            $table->date('customer_po_date')->nullable();
            // Section 3 — LC Financials
            $table->decimal('pfi_value', 15, 4)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('lc_open_rate', 10, 4)->nullable();
            $table->decimal('margin_percent', 8, 4)->nullable();
            $table->decimal('lc_margin_amt', 15, 2)->nullable();
            $table->decimal('lc_open_cost_bdt', 15, 2)->nullable();
            $table->decimal('freight_value', 15, 4)->nullable();
            $table->decimal('lc_value', 15, 4)->nullable();      // calc: pfi_value + freight_value
            $table->decimal('amount_bdt', 15, 2)->nullable();    // calc: lc_value × lc_open_rate
            $table->decimal('total_lc_cost', 15, 2)->nullable();
            $table->decimal('landed_cost', 15, 2)->nullable();
            // Section 4 — Document Retirement
            $table->decimal('doc_rt_rate', 10, 4)->nullable();
            $table->decimal('lc_rt_value', 15, 2)->nullable();
            $table->string('lc_charge_posting')->nullable();
            // Section 5 — Payment Tracking
            $table->decimal('advance_received_bdt', 15, 2)->nullable();
            $table->date('advance_date')->nullable();
            $table->string('advance_posting')->nullable();
            $table->decimal('rest_amount_bdt', 15, 2)->nullable();
            $table->date('rest_amount_date')->nullable();
            $table->string('rest_amount_posting')->nullable();
            $table->decimal('total_received_bdt', 15, 2)->nullable();
            $table->decimal('lc_closing_bill', 15, 2)->nullable();
            $table->date('lc_closing_bill_date')->nullable();
            // Section 6 — Duty & Clearance
            $table->decimal('duty_advance', 15, 2)->nullable();
            $table->date('duty_advance_date')->nullable();
            $table->string('duty_advance_posting')->nullable();
            $table->string('bill_of_entry_no')->nullable();
            $table->date('bill_of_entry_date')->nullable();
            $table->decimal('customs_duty', 15, 2)->nullable();
            $table->string('customs_duty_posting')->nullable();
            $table->string('cnf_party')->nullable();
            $table->decimal('cnf_total_cost', 15, 2)->nullable();
            $table->string('cnf_cost_posting')->nullable();
            // Section 7 — VAT / Tax / Sales
            $table->decimal('payable_receivable', 15, 2)->nullable();
            $table->decimal('received_amount', 15, 2)->nullable();
            $table->date('received_date')->nullable();
            $table->decimal('vat_return', 15, 2)->nullable();
            $table->date('vat_return_date')->nullable();
            $table->string('vat_return_posting')->nullable();
            $table->decimal('income_tax', 15, 2)->nullable();
            $table->decimal('bank_statement_amt', 15, 2)->nullable();
            $table->decimal('bank_lc_diff', 15, 2)->nullable();  // calc
            $table->decimal('lc_commission', 15, 2)->nullable();
            $table->date('lc_commission_date')->nullable();
            $table->decimal('sales_amount', 15, 2)->nullable();
            $table->string('sales_posting')->nullable();
            $table->decimal('coss_amount', 15, 2)->nullable();
            $table->string('coss_posting')->nullable();
            // Section 8 — Bank & Docs
            $table->unsignedBigInteger('opening_bank_id')->nullable();
            $table->unsignedBigInteger('port_of_dest_id')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('payment_mode')->nullable();
            $table->decimal('insurance_amt', 15, 2)->nullable();
            $table->string('cover_note')->nullable();
            $table->date('insurance_validity')->nullable();
            $table->string('psi_no')->nullable();
            $table->unsignedBigInteger('psi_company_id')->nullable();
            $table->string('comm_currency', 10)->nullable();
            $table->decimal('comm_amount', 15, 2)->nullable();
            $table->enum('doc_status', ['Pending', 'Received', 'Complete'])->default('Pending');
            $table->string('sanction_types')->nullable();
            $table->string('third_party')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lcs');
    }
};

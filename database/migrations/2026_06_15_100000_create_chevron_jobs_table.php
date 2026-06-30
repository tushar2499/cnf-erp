<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->unique();

            // Header
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('job_type_id')->nullable();
            $table->unsignedBigInteger('port_id')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->date('job_date')->nullable();

            // Party
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('party_name')->nullable();
            $table->text('party_address')->nullable();

            // Goods
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('goods_name')->nullable();
            $table->decimal('pack_quantity', 15, 3)->nullable();
            $table->string('pack_unit')->nullable();

            // Document dates
            $table->date('copy_doc_received_date')->nullable();
            $table->date('original_doc_received_date')->nullable();
            $table->date('eta_date')->nullable();
            $table->string('hbi_hawb_no')->nullable();
            $table->date('hbi_hawb_date')->nullable();

            // Weight
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->string('gross_weight_unit')->nullable();
            $table->decimal('net_weight', 15, 3)->nullable();

            // Customs docs
            $table->string('be_no')->nullable();
            $table->date('be_date')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('lc_date')->nullable();
            $table->string('lca_no')->nullable();
            $table->date('lca_date')->nullable();
            $table->string('po_no')->nullable();
            $table->string('mate_code')->nullable();

            // Shipping docs
            $table->string('bl_no')->nullable();
            $table->date('bl_date')->nullable();
            $table->string('mbl_mawb_no')->nullable();
            $table->date('mbl_mawb_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();

            // Vessel / Transport
            $table->string('lading_no')->nullable();
            $table->date('lading_date')->nullable();
            $table->string('flight_no')->nullable();
            $table->date('flight_date')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('boyge_no')->nullable();
            $table->string('vessel_etb_agent')->nullable();
            $table->string('al_no')->nullable();
            $table->string('sailed_no')->nullable();
            $table->date('arrived_date')->nullable();
            $table->date('common_lading_date')->nullable();
            $table->date('w_rent_due_date')->nullable();
            $table->string('berthing')->nullable();
            $table->date('berthing_date')->nullable();
            $table->string('shed_no')->nullable();
            $table->string('yard_no')->nullable();

            // Port operations
            $table->string('rot_no')->nullable();
            $table->string('bl_weight_measurement')->nullable();
            $table->string('jetty_sarker_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('unit_no')->nullable();

            // Bills
            $table->decimal('port_bill_amount', 15, 2)->nullable();
            $table->date('port_bill_date')->nullable();
            $table->decimal('labour_bill_amount', 15, 2)->nullable();
            $table->date('labour_bill_date')->nullable();
            $table->date('etb_date')->nullable();
            $table->decimal('shipping_charge', 15, 2)->nullable();
            $table->string('transport_name')->nullable();
            $table->string('transport_no')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('remarks')->nullable();

            // Consignee & Agent
            $table->string('consignee_name')->nullable();
            $table->text('consignee_address')->nullable();
            $table->string('agent_name')->nullable();
            $table->text('agent_address')->nullable();

            // Container / Freight
            $table->text('container_no')->nullable();
            $table->string('commodity')->nullable();
            $table->string('no_of_container')->nullable();
            $table->string('pol')->nullable();
            $table->string('destination')->nullable();
            $table->date('etd_date')->nullable();

            // Financial
            $table->decimal('received_amount', 15, 2)->nullable();
            $table->decimal('due_amount', 15, 2)->nullable();
            $table->decimal('assessable_value', 15, 2)->nullable();
            $table->string('currency_type')->nullable();
            $table->decimal('currency_rate', 15, 4)->nullable();
            $table->decimal('assessable_value_bdt', 15, 2)->nullable();

            // Charges (col1 = foreign/rate, col2 = BDT amount)
            $table->string('be_efr_no')->nullable();
            $table->decimal('pickup_charge_1',    15, 2)->nullable();
            $table->decimal('pickup_charge_2',    15, 2)->nullable();
            $table->decimal('cnf_charge_1',       15, 2)->nullable();
            $table->decimal('cnf_charge_2',       15, 2)->nullable();
            $table->decimal('stuffing_charge_1',  15, 2)->nullable();
            $table->decimal('stuffing_charge_2',  15, 2)->nullable();
            $table->decimal('carrier_bill_1',     15, 2)->nullable();
            $table->decimal('carrier_bill_2',     15, 2)->nullable();
            $table->decimal('mbl_free_1',         15, 2)->nullable();
            $table->decimal('mbl_free_2',         15, 2)->nullable();
            $table->decimal('hbl_charge_1',       15, 2)->nullable();
            $table->decimal('hbl_charge_2',       15, 2)->nullable();
            $table->decimal('ps_to_agent_1',      15, 2)->nullable();
            $table->decimal('ps_to_agent_2',      15, 2)->nullable();
            $table->decimal('ps_to_b_co_1',       15, 2)->nullable();
            $table->decimal('ps_to_b_co_2',       15, 2)->nullable();
            $table->decimal('noc_charge_1',       15, 2)->nullable();
            $table->decimal('noc_charge_2',       15, 2)->nullable();
            $table->decimal('other_charge_1',     15, 2)->nullable();
            $table->decimal('other_charge_2',     15, 2)->nullable();
            $table->decimal('invoice_value_1',    15, 2)->nullable();
            $table->decimal('invoice_value_2',    15, 2)->nullable();

            // Taxes
            $table->decimal('duty_rate',     8, 2)->nullable();
            $table->decimal('duty_amount',   15, 2)->nullable();
            $table->decimal('ait_rate',      8, 2)->nullable();
            $table->decimal('ait_amount',    15, 2)->nullable();
            $table->decimal('sup_tax_rate',  8, 2)->nullable();
            $table->decimal('sup_tax_amount',15, 2)->nullable();
            $table->decimal('vat_rate',      8, 2)->nullable();
            $table->decimal('vat_amount',    15, 2)->nullable();
            $table->decimal('rd_rate',       8, 2)->nullable();
            $table->decimal('rd_amount',     15, 2)->nullable();
            $table->decimal('atv_rate',      8, 2)->nullable();
            $table->decimal('atv_amount',    15, 2)->nullable();
            $table->decimal('df_vat_rate',   8, 2)->nullable();
            $table->decimal('df_vat_amount', 15, 2)->nullable();

            // Totals
            $table->decimal('total_payable_1',  15, 2)->nullable();
            $table->decimal('total_payable_2',  15, 2)->nullable();
            $table->decimal('comm_discount_pct',8, 2)->nullable();
            $table->decimal('comm_discount_1',  15, 2)->nullable();
            $table->decimal('comm_discount_2',  15, 2)->nullable();
            $table->decimal('net_payable_1',    15, 2)->nullable();
            $table->decimal('net_payable_2',    15, 2)->nullable();

            $table->enum('status', ['Active', 'Pending', 'Closed'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chevron_jobs');
    }
};

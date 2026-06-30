<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no', 20)->unique(); // SHP-000001
            $table->unsignedBigInteger('lc_id')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('pfi_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('vessel')->nullable();
            $table->string('bl_number')->nullable();
            $table->date('bl_date')->nullable();
            $table->decimal('bl_qty', 12, 4)->nullable();
            $table->string('bl_unit', 20)->nullable();
            $table->string('bill_of_entry')->nullable();
            $table->date('be_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->unsignedBigInteger('port_of_disc_id')->nullable();
            $table->decimal('freight_value', 15, 2)->nullable();
            $table->decimal('cnf_value', 15, 2)->nullable();
            $table->decimal('duty_amount', 15, 2)->nullable();
            $table->date('duty_pay_date')->nullable();
            $table->enum('shipping_mode', ['Sea', 'Air', 'Land', 'Rail'])->default('Sea');
            $table->unsignedBigInteger('psi_company_id')->nullable();
            $table->string('psi_no')->nullable();
            $table->unsignedBigInteger('cnf_agent_id')->nullable();
            $table->unsignedBigInteger('transport_co_id')->nullable();
            $table->string('grn_branch')->nullable();
            $table->string('activity_status')->nullable();
            $table->enum('shipment_status', ['Pending', 'In Transit', 'Arrived', 'Cleared', 'Delivered'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_trading_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->string('item_name')->nullable();
            $table->text('description')->nullable();
            $table->string('hs_code', 20)->nullable();
            $table->decimal('grn_qty', 12, 4)->nullable();
            $table->decimal('rate', 15, 4)->nullable();
            $table->decimal('assessable', 15, 2)->nullable();
            $table->decimal('cd_pct', 8, 4)->nullable();
            $table->decimal('cd_amt', 15, 2)->nullable();
            $table->decimal('rd_pct', 8, 4)->nullable();
            $table->decimal('rd_amt', 15, 2)->nullable();
            $table->decimal('sd_pct', 8, 4)->nullable();
            $table->decimal('sd_amt', 15, 2)->nullable();
            $table->decimal('vat_pct', 8, 4)->nullable();
            $table->decimal('vat_amt', 15, 2)->nullable();
            $table->decimal('ait_pct', 8, 4)->nullable();
            $table->decimal('ait_amt', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('nas_trading_shipment_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->string('cost_head')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_shipment_costs');
        Schema::dropIfExists('nas_trading_shipment_items');
        Schema::dropIfExists('nas_trading_shipments');
    }
};

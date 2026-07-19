<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('freight_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('rfq_id')->nullable();
            $table->string('rfq_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->unsignedBigInteger('overseas_agent_id')->nullable();
            $table->unsignedBigInteger('shipping_carrier_id')->nullable();
            $table->date('booking_date');
            $table->string('type');
            $table->string('service_type');
            $table->string('incoterms')->nullable();
            $table->string('currency')->default('BDT');
            $table->string('pol')->nullable();
            $table->string('pod')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->text('commodity_description')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_no')->nullable();
            $table->string('bl_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('nas_freights_branches')->cascadeOnDelete();
            $table->foreign('rfq_id')->references('id')->on('nas_freights_rfqs')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('nas_freights_customers')->nullOnDelete();
            $table->foreign('salesperson_id')->references('id')->on('nas_freights_employees')->nullOnDelete();
            $table->foreign('overseas_agent_id')->references('id')->on('nas_freights_overseas_agents')->nullOnDelete();
            $table->foreign('shipping_carrier_id')->references('id')->on('nas_freights_shipping_carriers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_bookings');
    }
};

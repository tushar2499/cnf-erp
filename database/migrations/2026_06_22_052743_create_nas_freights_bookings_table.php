<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nas_freights_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 20)->unique();
            $table->string('booking_prefix', 30);
            $table->string('sales_type', 30);
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->string('sales_person_name')->nullable();
            $table->date('job_date');
            $table->string('goods_name');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('delivery_date');
            $table->string('po_number')->nullable();
            $table->string('cover_van_no')->nullable();
            $table->text('note')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('qty_unit', 30)->nullable();
            $table->decimal('net_weight', 15, 2)->default(0);
            $table->string('weight_unit', 30)->nullable();
            $table->string('tds_section')->nullable();
            $table->decimal('tds_percent', 8, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('vat_percent', 8, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('ait_percent', 8, 2)->default(0);
            $table->decimal('ait_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_freights_bookings');
    }
};

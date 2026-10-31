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
        Schema::create('nas_freights_freight_export_booking_transport_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_booking_id')->constrained('nas_freights_freight_export_bookings')->cascadeOnDelete();
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('nas_freights_suppliers')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('supplier_rate', 15, 2)->default(0);
            $table->decimal('customer_rate', 15, 2)->default(0);
            $table->integer('demurrage_days')->default(0);
            $table->decimal('cus_demurrage_charge', 15, 2)->default(0);
            $table->decimal('sup_demurrage_charge', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_export_booking_transport_items');
    }
};

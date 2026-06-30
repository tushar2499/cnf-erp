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
        Schema::create('nas_freights_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('nas_freights_bookings')->onDelete('cascade');
            $table->string('cover_van_no')->nullable();
            $table->string('capacity')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 15, 2)->default(1);
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
        Schema::dropIfExists('nas_freights_booking_items');
    }
};

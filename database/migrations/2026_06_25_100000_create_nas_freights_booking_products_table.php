<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_booking_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('nas_freights_bookings')->onDelete('cascade');
            $table->string('goods_name');
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('qty_unit')->nullable();
            $table->decimal('net_weight', 15, 2)->default(0);
            $table->string('weight_unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_booking_products');
    }
};

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
        Schema::create('nas_freights_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 50)->unique();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_class', 50)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('purchase_unit', 50)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('remarks')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->boolean('availability_in_po')->default(true);
            $table->boolean('availability_in_so')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_vehicles');
    }
};

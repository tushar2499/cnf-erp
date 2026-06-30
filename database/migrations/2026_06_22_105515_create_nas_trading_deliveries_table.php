<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_no', 20)->unique(); // DLV-000001
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->string('bill_no')->nullable();
            $table->unsignedBigInteger('lc_id')->nullable();
            $table->string('lc_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->unsignedBigInteger('transport_co_id')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->enum('delivery_status', ['Pending', 'Dispatched', 'Delivered'])->default('Pending');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('entry_by')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_trading_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('lc_item_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('hs_code', 20)->nullable();
            $table->decimal('qty', 12, 4)->nullable();
            $table->string('unit', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_delivery_items');
        Schema::dropIfExists('nas_trading_deliveries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_lc_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lc_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_code', 30)->nullable();
            $table->string('hs_code', 20)->nullable();
            $table->decimal('qty_count', 12, 4)->nullable();
            $table->string('qty_unit', 20)->nullable();
            $table->decimal('weight', 12, 4)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->decimal('line_amount', 15, 4)->nullable(); // calc: qty × unit_price
            $table->string('currency', 10)->default('USD');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_items');
    }
};

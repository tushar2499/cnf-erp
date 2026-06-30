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
        Schema::create('chevron_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name')->nullable();
            $table->string('supplier')->nullable();
            $table->string('purchase_unit');
            $table->text('description')->nullable();
            $table->string('remarks')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->decimal('item_price', 15, 2)->default(0);
            $table->boolean('availability_in_po')->default(true);
            $table->boolean('availability_in_so')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chevron_items');
    }
};

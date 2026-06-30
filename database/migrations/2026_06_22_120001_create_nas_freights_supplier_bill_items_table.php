<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_supplier_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('booking_item_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('entry_date')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('b_qty', 15, 2)->default(0);
            $table->decimal('d_qty', 15, 2)->default(0);
            $table->decimal('due_qty', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('demurrage_day', 15, 2)->default(0);
            $table->decimal('demurrage_amount', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('nas_freights_supplier_bills')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_supplier_bill_items');
    }
};

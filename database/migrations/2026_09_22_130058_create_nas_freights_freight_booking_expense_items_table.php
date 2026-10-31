<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freight_booking_expense_id');
            $table->unsignedBigInteger('expense_head_id');
            $table->date('expense_date')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('freight_booking_expense_id', 'nf_fbe_items_fbe_id_foreign')
                ->references('id')
                ->on('nas_freights_freight_booking_expenses')
                ->cascadeOnDelete();

            $table->foreign('expense_head_id', 'nf_fbe_items_eh_id_foreign')
                ->references('id')
                ->on('nas_freights_expense_heads')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_booking_expense_items');
    }
};

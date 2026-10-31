<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_export_booking_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('amount_bdt', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('bill_id', 'nf_feb_bill_items_bill_fk')
                ->references('id')
                ->on('nas_freights_freight_export_booking_bills')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_export_booking_bill_items');
    }
};

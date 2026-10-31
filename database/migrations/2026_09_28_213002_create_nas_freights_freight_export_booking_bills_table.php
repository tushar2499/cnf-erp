<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->unsignedBigInteger('export_booking_id');
            $table->unsignedBigInteger('branch_id');
            $table->enum('bill_type', ['Customer', 'Overseas Agent']);
            $table->date('bill_date');
            $table->string('currency', 10)->default('BDT');
            $table->decimal('exchange_rate', 12, 4)->default(1);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_bdt_amount', 15, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('export_booking_id', 'nf_feb_bills_booking_fk')
                ->references('id')
                ->on('nas_freights_freight_export_bookings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_export_booking_bills');
    }
};

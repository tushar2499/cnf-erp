<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('rfq_date');
            $table->date('valid_until')->nullable();
            $table->enum('type', ['import', 'export'])->default('import');
            $table->enum('service_type', ['FCL', 'LCL', 'Air', 'Truck'])->default('FCL');
            $table->string('incoterms')->nullable();
            $table->string('currency')->default('BDT');
            $table->string('pol')->nullable();
            $table->string('pod')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->text('commodity_description')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Draft', 'Pending', 'Win', 'Lose'])->default('Draft');
            $table->string('lost_reason')->nullable();
            $table->unsignedBigInteger('converted_booking_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('nas_freights_customers')->nullOnDelete();
            $table->foreign('converted_booking_id')->references('id')->on('nas_freights_bookings')->nullOnDelete();
            $table->foreign('salesperson_id')->references('id')->on('nas_freights_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_rfqs');
    }
};

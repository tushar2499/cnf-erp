<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_customer_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no', 20)->unique();
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('bill_date');
            $table->string('delivery_no', 20)->nullable();
            $table->string('delivery_type', 50)->nullable();
            $table->decimal('tds_percent', 8, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->string('bill_type', 50)->nullable();
            $table->string('bill_by')->nullable();
            $table->text('note')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->string('entry_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_customer_bills');
    }
};

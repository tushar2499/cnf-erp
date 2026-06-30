<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->date('receipt_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->string('bill_no')->nullable();
            $table->decimal('bill_amount', 14, 2)->default(0);
            $table->decimal('amount_received', 14, 2)->default(0);
            $table->enum('payment_mode', ['Cash', 'Cheque', 'Bank Transfer', 'Online'])->default('Cash');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->string('entry_by')->nullable();
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('nas_freights_customer_bills')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_money_receipts');
    }
};

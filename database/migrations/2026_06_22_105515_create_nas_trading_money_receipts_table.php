<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20)->unique(); // MR-000001
            $table->date('receipt_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->string('bill_no')->nullable();
            $table->decimal('bill_amount', 15, 2)->default(0);
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->enum('payment_mode', ['Cash', 'Bank Transfer', 'Cheque', 'Mobile Banking'])->default('Bank Transfer');
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_money_receipts');
    }
};

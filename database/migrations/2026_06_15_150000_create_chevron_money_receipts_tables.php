<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chevron_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20)->unique();
            $table->date('receipt_date');
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name', 255);
            $table->string('pay_type', 50);
            $table->decimal('payable_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Submitted', 'Approved'])->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_money_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('chevron_money_receipts')->cascadeOnDelete();
            $table->string('payment_type', 50);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account_no', 100)->nullable();
            $table->string('cheque_card_holder', 255)->nullable();
            $table->string('cheque_card_no', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('cheque_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chevron_money_receipt_items');
        Schema::dropIfExists('chevron_money_receipts');
    }
};

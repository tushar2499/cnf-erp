<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_customer_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no', 20)->unique(); // BILL-000001
            $table->unsignedBigInteger('lc_id')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('pfi_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('bill_date');
            $table->string('currency', 10)->default('BDT');
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('vat_pct', 8, 4)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Confirmed', 'Paid'])->default('Draft');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_trading_customer_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('description');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->decimal('qty', 12, 4)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_customer_bill_items');
        Schema::dropIfExists('nas_trading_customer_bills');
    }
};

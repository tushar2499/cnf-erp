<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_trading_lc_bill_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bill_no', 30)->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('bill_date');
            $table->enum('status', ['Draft', 'Confirmed', 'Paid'])->default('Draft');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_bill_statements');
    }
};

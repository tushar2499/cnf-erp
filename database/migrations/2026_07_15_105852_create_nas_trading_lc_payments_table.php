<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nas_trading_lc_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lc_id');
            $table->enum('payment_type', ['advance', 'regular'])->default('advance');
            $table->string('receipt_no')->nullable();
            $table->date('date')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('lc_id')->references('id')->on('nas_trading_lcs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_payments');
    }
};

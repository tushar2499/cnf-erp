<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_trading_lc_bill_statement_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_statement_id');
            $table->unsignedBigInteger('lc_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('bill_statement_id')->references('id')->on('nas_trading_lc_bill_statements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_bill_statement_items');
    }
};

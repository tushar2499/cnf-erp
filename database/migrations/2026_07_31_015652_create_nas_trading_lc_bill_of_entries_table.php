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
        Schema::create('nas_trading_lc_bill_of_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lc_id');
            $table->foreign('lc_id')->references('id')->on('nas_trading_lcs')->onDelete('cascade');
            $table->string('be_no');
            $table->date('be_date');
            $table->decimal('customs_duty', 15, 2)->nullable();
            $table->string('customs_duty_posting')->nullable();
            $table->string('cnf_party')->nullable();
            $table->decimal('cnf_total_costing', 15, 2)->nullable();
            $table->string('cnf_total_posting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_bill_of_entries');
    }
};

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
        Schema::create('nas_trading_lc_duty_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_of_entry_id');
            $table->foreign('bill_of_entry_id')->references('id')->on('nas_trading_lc_bill_of_entries')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('posting')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_duty_advances');
    }
};

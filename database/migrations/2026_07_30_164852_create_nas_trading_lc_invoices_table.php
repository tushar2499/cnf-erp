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
        Schema::create('nas_trading_lc_invoice_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('nas_trading_lcs')->cascadeOnDelete();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value', 15, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_invoice_values');
    }
};

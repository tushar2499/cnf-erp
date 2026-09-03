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
        Schema::create('nas_trading_lc_bill_paids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('nas_trading_lcs')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('posting')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('amount', 15, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_bill_paids');
    }
};

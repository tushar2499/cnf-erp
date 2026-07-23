<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_trading_lc_other_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('nas_trading_lcs')->cascadeOnDelete();
            $table->string('name', 255);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_other_charges');
    }
};

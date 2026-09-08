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
        Schema::table('nas_trading_lc_rt_values', function (Blueprint $table) {
            $table->date('date')->nullable()->after('lc_id');
            $table->string('note')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_trading_lc_rt_values', function (Blueprint $table) {
            $table->dropColumn(['date', 'note']);
        });
    }
};

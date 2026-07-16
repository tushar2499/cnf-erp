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
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->decimal('lc_commission_percent', 10, 4)->nullable()->after('lc_rt_value');
            $table->decimal('lc_commission_flat', 15, 2)->nullable()->after('lc_commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn(['lc_commission_percent', 'lc_commission_flat']);
        });
    }
};

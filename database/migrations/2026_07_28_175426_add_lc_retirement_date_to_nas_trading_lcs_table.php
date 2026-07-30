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
            $table->date('lc_retirement_date')->nullable()->after('doc_rt_rate');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn('lc_retirement_date');
        });
    }
};

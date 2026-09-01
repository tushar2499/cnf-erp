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
            $table->decimal('bill_paid_total', 15, 4)->nullable()->default(0)->after('lc_closing_bill_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn('bill_paid_total');
        });
    }
};

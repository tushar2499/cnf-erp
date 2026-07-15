<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->decimal('lc_rate_amount', 15, 2)->nullable()->after('lc_open_rate');
            $table->decimal('bank_charge', 15, 2)->nullable()->after('comm_amount');
            $table->decimal('lc_amendment_charge', 15, 2)->nullable()->after('bank_charge');
            $table->decimal('credit_report_charge', 15, 2)->nullable()->after('lc_amendment_charge');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn(['lc_rate_amount', 'bank_charge', 'lc_amendment_charge', 'credit_report_charge']);
        });
    }
};

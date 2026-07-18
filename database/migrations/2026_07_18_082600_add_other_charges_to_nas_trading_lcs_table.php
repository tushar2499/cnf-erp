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
            $table->decimal('other_charges', 15, 2)->nullable()->after('credit_report_charge');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn('other_charges');
        });
    }
};

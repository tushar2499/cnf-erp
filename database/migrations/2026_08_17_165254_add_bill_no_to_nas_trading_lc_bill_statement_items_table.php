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
        Schema::table('nas_trading_lc_bill_statement_items', function (Blueprint $table) {
            $table->string('bill_no')->nullable()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lc_bill_statement_items', function (Blueprint $table) {
            $table->dropColumn('bill_no');
        });
    }
};

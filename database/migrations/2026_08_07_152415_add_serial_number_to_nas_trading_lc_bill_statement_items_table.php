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
            $table->string('serial_number')->nullable()->after('lc_id');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lc_bill_statement_items', function (Blueprint $table) {
            $table->dropColumn('serial_number');
        });
    }
};

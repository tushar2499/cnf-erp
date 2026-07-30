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
        Schema::table('nas_trading_lc_bill_statements', function (Blueprint $table) {
            $table->text('enclosed')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_lc_bill_statements', function (Blueprint $table) {
            $table->dropColumn('enclosed');
        });
    }
};

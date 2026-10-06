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
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 6)->nullable()->after('currency');
            $table->decimal('buy_amount', 15, 2)->nullable()->after('exchange_rate');
            $table->decimal('buy_bdt_amount', 15, 2)->nullable()->after('buy_amount');
            $table->decimal('sell_amount', 15, 2)->nullable()->after('buy_bdt_amount');
            $table->decimal('sell_bdt_amount', 15, 2)->nullable()->after('sell_amount');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'buy_amount', 'buy_bdt_amount', 'sell_amount', 'sell_bdt_amount']);
        });
    }
};

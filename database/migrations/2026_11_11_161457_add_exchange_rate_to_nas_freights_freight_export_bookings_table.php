<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->decimal('exchange_rate', 14, 4)->nullable()->after('currency');
        });

        DB::table('nas_freights_freight_export_bookings')
            ->where('currency', 'BDT')
            ->update(['exchange_rate' => 1]);
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });
    }
};

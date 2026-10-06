<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->json('hs_codes')->nullable()->after('commodity_description');
        });

        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->unsignedInteger('package_qty')->nullable()->after('package_type');
        });

        // HS code moved from cargo rows to the booking; carry existing values over.
        DB::table('nas_freights_freight_booking_items')
            ->whereNotNull('hs_code')
            ->where('hs_code', '!=', '')
            ->orderBy('id')
            ->get(['freight_booking_id', 'hs_code'])
            ->groupBy('freight_booking_id')
            ->each(function ($rows, $bookingId) {
                $codes = $rows->pluck('hs_code')
                    ->map(fn ($code) => trim($code))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                DB::table('nas_freights_freight_bookings')
                    ->where('id', $bookingId)
                    ->update(['hs_codes' => json_encode($codes)]);
            });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->dropColumn('package_qty');
        });

        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn('hs_codes');
        });
    }
};

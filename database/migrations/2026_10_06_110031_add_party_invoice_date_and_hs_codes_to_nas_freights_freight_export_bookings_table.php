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
            $table->date('party_invoice_date')->nullable()->after('party_invoice_no');
            $table->json('hs_codes')->nullable()->after('commodity_description');
        });

        // HS code moved from cargo rows to the booking; carry existing values over.
        DB::table('nas_freights_freight_export_booking_items')
            ->whereNotNull('hs_code')
            ->where('hs_code', '!=', '')
            ->orderBy('id')
            ->get(['export_booking_id', 'hs_code'])
            ->groupBy('export_booking_id')
            ->each(function ($rows, $bookingId) {
                $codes = $rows->pluck('hs_code')
                    ->map(fn ($code) => trim($code))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                DB::table('nas_freights_freight_export_bookings')
                    ->where('id', $bookingId)
                    ->update(['hs_codes' => json_encode($codes)]);
            });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['party_invoice_date', 'hs_codes']);
        });
    }
};

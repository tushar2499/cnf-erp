<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_export_booking_party_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('invoice_no');
            $table->date('invoice_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('export_booking_id', 'febpi_export_booking_foreign')
                ->references('id')->on('nas_freights_freight_export_bookings')->cascadeOnDelete();
        });

        // Carry the single party invoice no/date over from the booking row.
        DB::table('nas_freights_freight_export_bookings')
            ->whereNotNull('party_invoice_no')
            ->where('party_invoice_no', '!=', '')
            ->orderBy('id')
            ->get(['id', 'party_invoice_no', 'party_invoice_date'])
            ->each(function ($booking) {
                DB::table('nas_freights_freight_export_booking_party_invoices')->insert([
                    'export_booking_id' => $booking->id,
                    'invoice_no'        => trim($booking->party_invoice_no),
                    'invoice_date'      => $booking->party_invoice_date,
                    'sort_order'        => 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            });

        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['party_invoice_no', 'party_invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->string('party_invoice_no')->nullable()->after('party_bill_ref_no');
            $table->date('party_invoice_date')->nullable()->after('party_invoice_no');
        });

        // Only the first invoice per booking fits back into the single columns.
        DB::table('nas_freights_freight_export_booking_party_invoices')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->unique('export_booking_id')
            ->each(function ($invoice) {
                DB::table('nas_freights_freight_export_bookings')
                    ->where('id', $invoice->export_booking_id)
                    ->update([
                        'party_invoice_no'   => $invoice->invoice_no,
                        'party_invoice_date' => $invoice->invoice_date,
                    ]);
            });

        Schema::dropIfExists('nas_freights_freight_export_booking_party_invoices');
    }
};

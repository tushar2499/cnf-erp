<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->string('exp_no')->nullable()->after('booking_note_no');
            $table->date('exp_date')->nullable()->after('exp_no');
            $table->string('invoice_no')->nullable()->after('exp_date');
            $table->date('invoice_date')->nullable()->after('invoice_no');
            $table->string('lc_no')->nullable()->after('invoice_date');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['exp_no', 'exp_date', 'invoice_no', 'invoice_date', 'lc_no']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->string('flight_no')->nullable()->after('voyage_no');
            $table->date('flight_date')->nullable()->after('flight_no');
            $table->string('mbl_mawb_no')->nullable()->after('bl_no');
            $table->date('mbl_mawb_date')->nullable()->after('mbl_mawb_no');
            $table->string('hbl_hawb_no')->nullable()->after('mbl_mawb_date');
            $table->date('hbl_hawb_date')->nullable()->after('hbl_hawb_no');
            $table->string('lc_no')->nullable()->after('hbl_hawb_date');
            $table->string('cad_no')->nullable()->after('lc_no');
            $table->string('tt_no')->nullable()->after('cad_no');
            $table->string('rfq_tender_no')->nullable()->after('tt_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'flight_no', 'flight_date',
                'mbl_mawb_no', 'mbl_mawb_date',
                'hbl_hawb_no', 'hbl_hawb_date',
                'lc_no', 'cad_no', 'tt_no', 'rfq_tender_no',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->string('hawb_hbl_no')->nullable()->after('bl_date');
            $table->date('hawb_hbl_date')->nullable()->after('hawb_hbl_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['hawb_hbl_no', 'hawb_hbl_date']);
        });
    }
};

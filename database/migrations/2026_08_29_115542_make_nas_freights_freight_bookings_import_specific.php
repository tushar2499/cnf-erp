<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('igm_no')->nullable()->after('bl_no');
            $table->string('delivery_order_no')->nullable()->after('igm_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->string('type')->after('booking_date');
            $table->dropColumn(['igm_no', 'delivery_order_no']);
        });
    }
};

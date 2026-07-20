<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_rfqs', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_freight_booking_id')
                ->nullable()
                ->after('converted_booking_id');

            $table->foreign('converted_freight_booking_id')
                ->references('id')->on('nas_freights_freight_bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_rfqs', function (Blueprint $table) {
            $table->dropForeign(['converted_freight_booking_id']);
            $table->dropColumn('converted_freight_booking_id');
        });
    }
};

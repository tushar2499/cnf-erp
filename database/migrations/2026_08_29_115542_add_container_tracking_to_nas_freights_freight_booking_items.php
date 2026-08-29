<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->string('container_no')->nullable()->after('container_size');
            $table->string('seal_no')->nullable()->after('container_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->dropColumn(['container_no', 'seal_no']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nas_freights_booking_items', function (Blueprint $table) {
            $table->string('challan_no')->nullable()->after('cover_van_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_freights_booking_items', function (Blueprint $table) {
            $table->dropColumn('challan_no');
        });
    }
};

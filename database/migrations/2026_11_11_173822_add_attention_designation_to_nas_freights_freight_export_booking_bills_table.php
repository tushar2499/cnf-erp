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
        Schema::table('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->string('attention', 150)->nullable()->after('bill_date');
            $table->string('designation', 150)->nullable()->after('attention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->dropColumn(['attention', 'designation']);
        });
    }
};

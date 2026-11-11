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
            $table->string('bill_prefix_ref', 100)->nullable()->after('bill_type');
            $table->string('bill_prefix_job', 100)->nullable()->after('bill_prefix_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->dropColumn(['bill_prefix_ref', 'bill_prefix_job']);
        });
    }
};

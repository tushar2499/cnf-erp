<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->date('bl_date')->nullable()->after('export_bl_no');
            $table->string('transport_doc_type')->nullable()->after('bl_date');
            $table->string('transport_doc_no')->nullable()->after('transport_doc_type');
            $table->date('transport_doc_date')->nullable()->after('transport_doc_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->dropColumn(['bl_date', 'transport_doc_type', 'transport_doc_no', 'transport_doc_date']);
        });
    }
};

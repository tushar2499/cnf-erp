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
            $table->string('vat_title')->nullable()->after('remarks');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('vat_title');
            $table->decimal('vat_amount_bdt', 15, 2)->default(0)->after('vat_amount');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->dropColumn(['vat_title', 'vat_amount', 'vat_amount_bdt']);
        });
    }
};

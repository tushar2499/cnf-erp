<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->decimal('vat_percent', 8, 2)->default(0)->after('tds_amount');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('vat_percent');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->dropColumn(['vat_percent', 'vat_amount']);
        });
    }
};

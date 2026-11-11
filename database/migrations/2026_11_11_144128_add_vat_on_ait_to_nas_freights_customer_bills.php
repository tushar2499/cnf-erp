<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->boolean('vat_on_ait')->default(false)->after('vat_amount');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->dropColumn('vat_on_ait');
        });
    }
};

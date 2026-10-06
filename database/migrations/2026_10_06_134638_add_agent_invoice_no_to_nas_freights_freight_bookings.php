<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->string('agent_invoice_no')->nullable()->after('customer_invoice_date');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn('agent_invoice_no');
        });
    }
};

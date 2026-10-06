<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->string('customer_invoice_no')->nullable()->after('customer_id');
            $table->date('customer_invoice_date')->nullable()->after('customer_invoice_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_invoice_no', 'customer_invoice_date']);
        });
    }
};

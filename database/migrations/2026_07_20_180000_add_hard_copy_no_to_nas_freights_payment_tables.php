<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_money_receipts', function (Blueprint $table) {
            $table->string('hard_copy_no')->nullable()->after('receipt_no');
        });

        Schema::table('nas_freights_supplier_payments', function (Blueprint $table) {
            $table->string('hard_copy_no')->nullable()->after('payment_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_money_receipts', function (Blueprint $table) {
            $table->dropColumn('hard_copy_no');
        });

        Schema::table('nas_freights_supplier_payments', function (Blueprint $table) {
            $table->dropColumn('hard_copy_no');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->decimal('ait_percent', 8, 2)->default(0)->after('tds_amount');
            $table->decimal('ait_amount', 15, 2)->default(0)->after('ait_percent');
        });

        Schema::table('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->decimal('ait_amount', 15, 2)->default(0)->after('ait_percent');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('ait_amount');
            $table->decimal('tds_amount', 15, 2)->default(0)->after('vat_amount');
        });

        // AIT used to live only on the rows. Copy it up to the new master field so the
        // edit page shows the bill's AIT % unchanged.
        DB::statement('UPDATE nas_freights_customer_bills SET ait_percent = COALESCE((SELECT MAX(ait_percent) FROM nas_freights_customer_bill_items WHERE nas_freights_customer_bill_items.bill_id = nas_freights_customer_bills.id), 0)');
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->dropColumn(['ait_amount', 'vat_amount', 'tds_amount']);
        });

        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->dropColumn(['ait_percent', 'ait_amount']);
        });
    }
};

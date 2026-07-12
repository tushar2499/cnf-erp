<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->date('bl_date')->nullable()->after('bl_ref');
            $table->decimal('invoice_value', 15, 2)->nullable()->after('invoice_value_bdt');
            $table->string('currency_type', 10)->nullable()->after('invoice_value');
            $table->decimal('currency_rate', 12, 4)->nullable()->after('currency_type');
        });

        Schema::table('chevron_bill_items', function (Blueprint $table) {
            $table->decimal('rate', 15, 2)->nullable()->after('note');
            $table->decimal('qty', 15, 3)->nullable()->after('rate');
        });
    }

    public function down(): void
    {
        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->dropColumn(['bl_date', 'invoice_value', 'currency_type', 'currency_rate']);
        });

        Schema::table('chevron_bill_items', function (Blueprint $table) {
            $table->dropColumn(['rate', 'qty']);
        });
    }
};

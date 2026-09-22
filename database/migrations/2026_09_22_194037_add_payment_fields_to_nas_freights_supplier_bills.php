<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_supplier_bills', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('pay_order_no');
            $table->date('payment_date')->nullable()->after('bill_date');
            $table->string('money_receipt_no')->nullable()->after('payment_date');
            $table->date('money_receipt_date')->nullable()->after('money_receipt_no');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_supplier_bills', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'payment_date', 'money_receipt_no', 'money_receipt_date']);
        });
    }
};

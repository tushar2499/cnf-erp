<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->string('bill_no', 100)->change();
            $table->string('delivery_no', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->string('bill_no', 20)->change();
            $table->string('delivery_no', 20)->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->string('print_format', 30)->default('general')->after('bill_by');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bills', function (Blueprint $table) {
            $table->dropColumn('print_format');
        });
    }
};

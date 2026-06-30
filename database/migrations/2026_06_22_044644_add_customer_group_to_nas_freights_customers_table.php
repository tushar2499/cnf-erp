<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customers', function (Blueprint $table) {
            $table->string('customer_group', 50)->nullable()->after('id_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customers', function (Blueprint $table) {
            $table->dropColumn('customer_group');
        });
    }
};

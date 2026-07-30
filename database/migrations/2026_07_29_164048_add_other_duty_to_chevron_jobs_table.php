<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chevron_jobs', function (Blueprint $table) {
            $table->decimal('other_rate', 8, 2)->nullable()->after('df_vat_amount');
            $table->decimal('other_amount', 15, 2)->nullable()->after('other_rate');
        });
    }

    public function down(): void
    {
        Schema::table('chevron_jobs', function (Blueprint $table) {
            $table->dropColumn(['other_rate', 'other_amount']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_expense_categories', function (Blueprint $table) {
            $table->boolean('is_bill')->default(true)->after('name');
            $table->boolean('is_job')->default(false)->after('is_bill');
        });

        DB::statement("UPDATE chevron_expense_categories SET is_bill = 1, is_job = 0 WHERE type = 'bill'");
        DB::statement("UPDATE chevron_expense_categories SET is_bill = 0, is_job = 1 WHERE type = 'job'");

        Schema::table('chevron_expense_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('chevron_expense_categories', function (Blueprint $table) {
            $table->enum('type', ['bill', 'job'])->default('bill')->after('name');
        });

        DB::statement("UPDATE chevron_expense_categories SET type = 'bill' WHERE is_bill = 1 AND is_job = 0");
        DB::statement("UPDATE chevron_expense_categories SET type = 'job' WHERE is_bill = 0 AND is_job = 1");
        DB::statement("UPDATE chevron_expense_categories SET type = 'bill' WHERE is_bill = 1 AND is_job = 1");

        Schema::table('chevron_expense_categories', function (Blueprint $table) {
            $table->dropColumn(['is_bill', 'is_job']);
        });
    }
};

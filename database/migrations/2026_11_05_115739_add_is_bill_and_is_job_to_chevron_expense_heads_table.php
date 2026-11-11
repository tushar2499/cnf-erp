<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_expense_heads', function (Blueprint $table) {
            $table->boolean('is_bill')->default(true)->after('expense_category_id');
            $table->boolean('is_job')->default(false)->after('is_bill');
        });

        DB::statement('
            UPDATE chevron_expense_heads AS h
            INNER JOIN chevron_expense_categories AS c ON c.id = h.expense_category_id
            SET h.is_bill = c.is_bill, h.is_job = c.is_job
        ');
    }

    public function down(): void
    {
        Schema::table('chevron_expense_heads', function (Blueprint $table) {
            $table->dropColumn(['is_bill', 'is_job']);
        });
    }
};

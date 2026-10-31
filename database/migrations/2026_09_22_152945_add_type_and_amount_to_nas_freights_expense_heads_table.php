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
        Schema::table('nas_freights_expense_heads', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
            $table->decimal('amount', 15, 2)->nullable()->after('expense_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_expense_heads', function (Blueprint $table) {
            $table->dropColumn(['type', 'amount']);
        });
    }
};

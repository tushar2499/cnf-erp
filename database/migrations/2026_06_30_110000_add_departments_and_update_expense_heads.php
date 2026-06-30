<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_trading_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::table('nas_trading_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('code');
            $table->foreign('department_id')->references('id')->on('nas_trading_departments')->nullOnDelete();
        });

        // Change category from ENUM to VARCHAR so we can store any category name
        DB::statement("ALTER TABLE nas_trading_expense_heads MODIFY COLUMN category VARCHAR(100) DEFAULT 'Other'");

        Schema::table('nas_trading_expense_heads', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('nas_trading_expense_heads', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        DB::statement("ALTER TABLE nas_trading_expense_heads MODIFY COLUMN category ENUM('LC Cost','Duty','Shipping','Other') DEFAULT 'Other'");

        Schema::table('nas_trading_employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('nas_trading_departments');
    }
};

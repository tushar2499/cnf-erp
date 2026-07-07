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
        Schema::create('chevron_expense_head_employees', function (Blueprint $table) {
            $table->foreignId('expense_head_id')->constrained('chevron_expense_heads')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('chevron_employees')->onDelete('cascade');
            $table->primary(['expense_head_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chevron_expense_head_employees');
    }
};

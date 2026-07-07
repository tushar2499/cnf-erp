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
        Schema::create('chevron_employee_customers', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('chevron_employees')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('chevron_customers')->cascadeOnDelete();
            $table->primary(['employee_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chevron_employee_customers');
    }
};

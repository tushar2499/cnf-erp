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
        Schema::create('chevron_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_prefix', 20);
            $table->string('employee_id', 30)->unique();
            $table->string('name');
            $table->foreignId('designation_id')->constrained('chevron_designations')->restrictOnDelete();
            $table->date('joining_date');
            $table->string('short_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->enum('current_status', ['Active', 'Inactive', 'Resigned', 'Terminated'])->default('Active');
            $table->foreignId('branch_id')->nullable()->constrained('chevron_branches')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chevron_employees');
    }
};

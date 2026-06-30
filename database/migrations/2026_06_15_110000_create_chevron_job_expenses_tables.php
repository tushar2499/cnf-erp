<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chevron_job_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('be_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value_usd', 15, 2)->nullable();
            $table->string('bl_no')->nullable();
            $table->date('date');
            $table->decimal('total_expense_amount', 15, 2)->default(0);
            $table->decimal('total_approved_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('chevron_job_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_expense_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->enum('receiptable', ['Yes', 'No'])->default('No');
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->default(0);
            $table->date('expense_date');
            $table->string('note')->nullable();
            $table->foreign('job_expense_id')->references('id')->on('chevron_job_expenses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chevron_job_expense_items');
        Schema::dropIfExists('chevron_job_expenses');
    }
};

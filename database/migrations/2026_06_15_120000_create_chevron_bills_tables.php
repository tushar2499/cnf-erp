<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chevron_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->string('bill_type')->nullable();
            $table->date('bill_date')->nullable();
            $table->date('delivery_date')->nullable();

            // Job reference
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();

            // Party
            $table->string('party_name')->nullable();
            $table->text('party_address')->nullable();

            // Goods
            $table->string('goods_description')->nullable();
            $table->string('mate_code')->nullable();
            $table->string('po_no')->nullable();
            $table->decimal('quantity', 15, 3)->nullable();
            $table->string('quantity_unit')->nullable()->default('KG');
            $table->string('quantity_remark')->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->string('gross_weight_unit')->nullable();

            // Document refs
            $table->string('lc_no')->nullable();
            $table->string('lc_ref')->nullable();
            $table->string('be_no')->nullable();
            $table->date('be_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('invoice_ref')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('bl_no')->nullable();
            $table->string('bl_ref')->nullable();

            // Financials
            $table->decimal('assessable_value', 15, 2)->nullable();
            $table->decimal('invoice_value_bdt', 15, 2)->nullable();
            $table->text('remarks')->nullable();

            // Totals
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->string('commission_on')->default('ASSESSABLE');
            $table->decimal('commission_rate', 8, 2)->nullable();
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('total_payable', 15, 2)->default(0);
            $table->decimal('less_customs_duty_tax', 15, 2)->default(0);
            $table->decimal('income_tax_cnf_com', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->decimal('advance_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);

            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('chevron_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->foreign('bill_id')->references('id')->on('chevron_bills')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chevron_bill_items');
        Schema::dropIfExists('chevron_bills');
    }
};

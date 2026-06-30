<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_supplier_bills', function (Blueprint $table) {
            $table->id();
            $table->string('pay_order_no', 20)->unique();
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->date('bill_date');
            $table->string('bill_by')->nullable();
            $table->text('note')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->string('entry_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_supplier_bills');
    }
};

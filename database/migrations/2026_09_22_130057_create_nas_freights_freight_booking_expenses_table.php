<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no', 20)->unique();
            $table->enum('booking_type', ['import', 'export'])->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->date('date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->unsignedBigInteger('entry_by');
            $table->timestamps();

            $table->index(['booking_type', 'booking_id'], 'nf_fbe_booking_idx');
            $table->foreign('branch_id', 'nf_fbe_branch_id_foreign')
                ->references('id')->on('nas_freights_branches')->restrictOnDelete();
            $table->foreign('entry_by', 'nf_fbe_entry_by_foreign')
                ->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_freights_freight_booking_expenses');
    }
};

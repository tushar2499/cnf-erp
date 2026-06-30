<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nas_trading_lc_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lc_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->string('expense_head_name')->nullable();
            $table->date('expense_date')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('posting_type', [
                'Insurance', 'LC Opening', 'LC Additional',
                'Doc Retirement', 'Post Shipment', 'CNF Expenses', 'Other'
            ])->default('Other');
            $table->enum('posting_sub_type', [
                'Bank Pay', 'IFDBC', 'Acceptance', 'LTR', 'STL', 'ATR', 'Margin Adjust'
            ])->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('entry_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas_trading_lc_expenses');
    }
};

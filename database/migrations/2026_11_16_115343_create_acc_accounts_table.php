<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chart of accounts shared by all companies. Four levels:
     * Master (2 digits) > Sub-1 (4) > Sub-2 (6) > Ledger (10).
     * Only ledgers (is_group = false) can be posted to.
     */
    public function up(): void
    {
        Schema::create('acc_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('acc_accounts')->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('level');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->enum('nature', ['dr', 'cr']);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_cash_bank')->default(false);
            $table->enum('party_type', ['customer', 'supplier'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_accounts');
    }
};

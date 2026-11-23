<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Debit / credit heads for each source document amount part.
         * match_key narrows a rule (expense head id, payment mode, bank account...);
         * an empty match_key is the default rule for that part.
         */
        Schema::create('acc_transaction_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 60);
            $table->string('component', 60);
            $table->string('match_key', 60)->default('');
            $table->foreignId('debit_account_id')->constrained('acc_accounts')->restrictOnDelete();
            $table->foreignId('credit_account_id')->constrained('acc_accounts')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['source_type', 'component', 'match_key'], 'acc_mappings_rule_unique');
        });

        Schema::create('acc_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('voucher_no', 30);
            $table->enum('voucher_type', ['journal', 'payment', 'receipt', 'contra']);
            $table->date('voucher_date');
            $table->text('narration')->nullable();
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            // Set only while posted, so a cancelled document can be posted again.
            $table->string('source_ref', 120)->nullable();
            $table->string('source_doc_no', 100)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->enum('status', ['posted', 'cancelled'])->default('posted');
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'voucher_no']);
            $table->unique(['company_id', 'source_ref']);
            $table->index(['company_id', 'voucher_date']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('acc_voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('acc_vouchers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('acc_accounts')->restrictOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('party_type', 20)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable();
            $table->string('narration')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['account_id', 'voucher_id']);
            $table->index(['party_type', 'party_id']);
        });

        Schema::create('acc_voucher_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('voucher_type', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_no')->default(0);

            $table->unique(['company_id', 'voucher_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_voucher_sequences');
        Schema::dropIfExists('acc_voucher_lines');
        Schema::dropIfExists('acc_vouchers');
        Schema::dropIfExists('acc_transaction_mappings');
    }
};

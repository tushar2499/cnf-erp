<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_money_receipts', function (Blueprint $table) {
            $table->string('receipt_type', 10)->nullable()->after('receipt_date');
        });

        Schema::create('chevron_money_receipt_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('chevron_money_receipts')->cascadeOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained('chevron_bills')->restrictOnDelete();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->decimal('received_amount', 15, 2)->default(0)->after('due_amount');
        });
    }

    public function down(): void
    {
        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->dropColumn('received_amount');
        });

        Schema::dropIfExists('chevron_money_receipt_allocations');

        Schema::table('chevron_money_receipts', function (Blueprint $table) {
            $table->dropColumn('receipt_type');
        });
    }
};

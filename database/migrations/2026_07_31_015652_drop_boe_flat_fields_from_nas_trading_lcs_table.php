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
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->dropColumn([
                'duty_advance', 'duty_advance_date', 'duty_advance_posting',
                'bill_of_entry_no', 'bill_of_entry_date',
                'customs_duty', 'customs_duty_posting',
                'cnf_party', 'cnf_total_cost', 'cnf_cost_posting',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas_trading_lcs', function (Blueprint $table) {
            $table->decimal('duty_advance', 15, 2)->nullable();
            $table->date('duty_advance_date')->nullable();
            $table->string('duty_advance_posting')->nullable();
            $table->string('bill_of_entry_no')->nullable();
            $table->date('bill_of_entry_date')->nullable();
            $table->decimal('customs_duty', 15, 2)->nullable();
            $table->string('customs_duty_posting')->nullable();
            $table->string('cnf_party')->nullable();
            $table->decimal('cnf_total_cost', 15, 2)->nullable();
            $table->string('cnf_cost_posting')->nullable();
        });
    }
};

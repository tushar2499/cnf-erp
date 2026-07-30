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
        Schema::rename('nas_trading_lc_duty_advances', 'nas_trading_lc_bill_of_entry_duty_advances');
    }

    public function down(): void
    {
        Schema::rename('nas_trading_lc_bill_of_entry_duty_advances', 'nas_trading_lc_duty_advances');
    }
};

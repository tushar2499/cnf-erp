<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->decimal('demurrage_day', 15, 2)->default(0)->after('due_qty');
            $table->decimal('demurrage_amount', 15, 2)->default(0)->after('demurrage_day');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->dropColumn(['demurrage_day', 'demurrage_amount']);
        });
    }
};

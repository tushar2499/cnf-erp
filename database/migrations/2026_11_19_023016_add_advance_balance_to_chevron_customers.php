<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_customers', function (Blueprint $table) {
            $table->decimal('advance_balance', 15, 2)->default(0)->after('security_deposit');
        });

        // Backfill from the receipts already on file: advance received minus advance adjusted against bills.
        DB::statement("
            UPDATE chevron_customers SET advance_balance = ROUND(
                COALESCE((
                    SELECT SUM(r.total_amount) FROM chevron_money_receipts r
                    WHERE r.party_id = chevron_customers.id AND r.receipt_type = 'Advance'
                ), 0)
                - COALESCE((
                    SELECT SUM(i.amount) FROM chevron_money_receipt_items i
                    JOIN chevron_money_receipts r ON r.id = i.receipt_id
                    WHERE r.party_id = chevron_customers.id AND r.receipt_type = 'Bill' AND i.payment_type = 'Advance Adjustment'
                ), 0)
            , 2)
        ");
    }

    public function down(): void
    {
        Schema::table('chevron_customers', function (Blueprint $table) {
            $table->dropColumn('advance_balance');
        });
    }
};

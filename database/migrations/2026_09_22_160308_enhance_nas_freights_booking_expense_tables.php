<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Expense header table ────────────────────────────────────────────
        Schema::table('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            // booking_no stored alongside booking_id for display
            $table->string('booking_no', 100)->nullable()->after('booking_id');
            // employee who submitted the expense
            $table->unsignedBigInteger('employee_id')->nullable()->after('booking_no');
            $table->foreign('employee_id', 'nf_fbe_emp_id_foreign')
                ->references('id')->on('nas_freights_employees')->restrictOnDelete();
            // auto-filled from booking
            $table->string('invoice_no', 100)->nullable()->after('employee_id');
            $table->decimal('invoice_value_usd', 15, 2)->nullable()->after('invoice_no');
            $table->string('bl_no', 100)->nullable()->after('invoice_value_usd');
            // separate expense / approved totals
            $table->decimal('total_expense_amount', 15, 2)->default(0)->after('bl_no');
            $table->decimal('total_approved_amount', 15, 2)->default(0)->after('total_expense_amount');
            // make entry_by nullable
            $table->unsignedBigInteger('entry_by')->nullable()->change();
        });

        // drop old total_amount column (no data)
        Schema::table('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        // change status from enum to varchar for flexibility
        DB::statement("ALTER TABLE nas_freights_freight_booking_expenses
            MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Draft'");

        // ── Expense items table ─────────────────────────────────────────────
        Schema::table('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->string('receiptable', 3)->default('No')->after('expense_head_id');
            $table->decimal('expense_amount', 15, 2)->default(0)->after('receiptable');
            $table->decimal('approved_amount', 15, 2)->default(0)->after('expense_amount');
        });

        // drop old amount column (no data)
        Schema::table('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }

    public function down(): void
    {
        Schema::table('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->dropColumn(['receiptable', 'expense_amount', 'approved_amount']);
            $table->decimal('amount', 15, 2)->after('expense_head_id');
        });

        DB::statement("ALTER TABLE nas_freights_freight_booking_expenses
            MODIFY COLUMN status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft'");

        Schema::table('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->dropForeign('nf_fbe_emp_id_foreign');
            $table->dropColumn(['booking_no', 'employee_id', 'invoice_no', 'invoice_value_usd', 'bl_no', 'total_expense_amount', 'total_approved_amount']);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('entry_by')->nullable(false)->change();
        });
    }
};

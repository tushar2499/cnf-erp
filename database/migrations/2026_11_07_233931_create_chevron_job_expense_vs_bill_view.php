<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VIEW = 'chevron_job_expense_vs_bill_view';

    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.self::VIEW);

        DB::statement('
            CREATE VIEW '.self::VIEW.' AS
            SELECT
                i.id                AS id,
                je.id               AS job_expense_id,
                je.branch_id        AS branch_id,
                je.job_id           AS job_id,
                je.job_no           AS job_no,
                j.party_name        AS party_name,
                je.expense_no       AS expense_no,
                i.expense_date      AS expense_date,
                e.name              AS expense_by,
                c.name              AS expense_category,
                h.name              AS expense_head,
                i.receiptable       AS receiptable,
                i.expense_amount    AS expense_amount,
                COALESCE((
                    SELECT SUM(b.net_payable)
                    FROM chevron_bills b
                    WHERE b.job_id = je.job_id
                      AND b.branch_id = je.branch_id
                ), 0)               AS bill_total
            FROM chevron_job_expense_items i
            INNER JOIN chevron_job_expenses je ON je.id = i.job_expense_id
            LEFT JOIN chevron_jobs j ON j.id = je.job_id
            LEFT JOIN employees e ON e.id = je.employee_id
            LEFT JOIN chevron_expense_heads h ON h.id = i.expense_head_id
            LEFT JOIN chevron_expense_categories c ON c.id = h.expense_category_id
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.self::VIEW);
    }
};

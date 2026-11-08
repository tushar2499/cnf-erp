<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VIEW = 'chevron_job_bill_view';

    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.self::VIEW);

        DB::statement('
            CREATE VIEW '.self::VIEW.' AS
            SELECT
                i.id                AS id,
                b.id                AS bill_id,
                b.branch_id         AS branch_id,
                br.name             AS branch_name,
                b.job_id            AS job_id,
                b.job_no            AS job_no,
                b.party_name        AS party_name,
                b.bill_no           AS bill_no,
                b.bill_date         AS bill_date,
                b.bill_type         AS bill_type,
                c.name              AS expense_category,
                h.name              AS expense_head,
                i.amount            AS bill_amount
            FROM chevron_bill_items i
            INNER JOIN chevron_bills b ON b.id = i.bill_id
            LEFT JOIN chevron_branches br ON br.id = b.branch_id
            LEFT JOIN chevron_expense_heads h ON h.id = i.expense_head_id
            LEFT JOIN chevron_expense_categories c ON c.id = i.expense_category_id
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.self::VIEW);
    }
};

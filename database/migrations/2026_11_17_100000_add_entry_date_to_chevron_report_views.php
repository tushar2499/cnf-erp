<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EXPENSE_VIEW = 'chevron_job_expense_view';

    private const BILL_VIEW = 'chevron_job_bill_view';

    public function up(): void
    {
        $this->createExpenseView(withEntryDate: true);
        $this->createBillView(withEntryDate: true);
    }

    public function down(): void
    {
        $this->createExpenseView(withEntryDate: false);
        $this->createBillView(withEntryDate: false);
    }

    private function securityClause(): string
    {
        return DB::getDriverName() === 'mysql' ? 'SQL SECURITY INVOKER ' : '';
    }

    private function createExpenseView(bool $withEntryDate): void
    {
        $entryDate = $withEntryDate
            ? "DATE(je.created_at)   AS entry_date,\n                "
            : '';

        DB::statement('DROP VIEW IF EXISTS '.self::EXPENSE_VIEW);

        DB::statement('
            CREATE '.$this->securityClause().'VIEW '.self::EXPENSE_VIEW.' AS
            SELECT
                i.id                AS id,
                je.id               AS job_expense_id,
                je.branch_id        AS branch_id,
                br.name             AS branch_name,
                je.job_id           AS job_id,
                je.job_no           AS job_no,
                j.party_name        AS party_name,
                je.expense_no       AS expense_no,
                i.expense_date      AS expense_date,
                '.$entryDate.'e.name              AS expense_by,
                c.name              AS expense_category,
                h.name              AS expense_head,
                i.receiptable       AS receiptable,
                i.expense_amount    AS expense_amount
            FROM chevron_job_expense_items i
            INNER JOIN chevron_job_expenses je ON je.id = i.job_expense_id
            LEFT JOIN chevron_branches br ON br.id = je.branch_id
            LEFT JOIN chevron_jobs j ON j.id = je.job_id
            LEFT JOIN employees e ON e.id = je.employee_id
            LEFT JOIN chevron_expense_heads h ON h.id = i.expense_head_id
            LEFT JOIN chevron_expense_categories c ON c.id = h.expense_category_id
        ');
    }

    private function createBillView(bool $withEntryDate): void
    {
        $entryDate = $withEntryDate
            ? "DATE(b.created_at)    AS entry_date,\n                "
            : '';

        DB::statement('DROP VIEW IF EXISTS '.self::BILL_VIEW);

        DB::statement('
            CREATE '.$this->securityClause().'VIEW '.self::BILL_VIEW.' AS
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
                '.$entryDate.'b.bill_type         AS bill_type,
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
};

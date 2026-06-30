<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: expand enum to include both values
        DB::statement("ALTER TABLE chevron_bills MODIFY COLUMN status ENUM('Draft','Active','Submitted','Approved') NOT NULL DEFAULT 'Active'");
        // Step 2: migrate existing rows
        DB::statement("UPDATE chevron_bills SET status = 'Active' WHERE status = 'Draft'");
        // Step 3: remove old value
        DB::statement("ALTER TABLE chevron_bills MODIFY COLUMN status ENUM('Active','Submitted','Approved') NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chevron_bills MODIFY COLUMN status ENUM('Draft','Submitted','Approved') NOT NULL DEFAULT 'Draft'");
    }
};

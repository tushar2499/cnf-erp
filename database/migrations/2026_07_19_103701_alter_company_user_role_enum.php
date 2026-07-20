<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE company_user MODIFY COLUMN role ENUM('admin','manager','staff','user') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE company_user MODIFY COLUMN role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff'");
    }
};

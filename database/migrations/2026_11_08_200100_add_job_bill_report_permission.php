<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSION = 'cnf.report.job-bill';

    private const COMPANY_ID = 1;

    public function up(): void
    {
        $exists = DB::table('permissions')
            ->where('name', self::PERMISSION)
            ->where('company_id', self::COMPANY_ID)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('permissions')->insert([
            'name'          => self::PERMISSION,
            'guard_name'    => 'web',
            'company_id'    => self::COMPANY_ID,
            'module'        => 'Reports',
            'sorting_order' => 142,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', self::PERMISSION)
            ->where('company_id', self::COMPANY_ID)
            ->delete();
    }
};

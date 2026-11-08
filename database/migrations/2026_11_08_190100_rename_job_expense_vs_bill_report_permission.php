<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_PERMISSION = 'cnf.report.job-expense-vs-bill';

    private const NEW_PERMISSION = 'cnf.report.job-expense';

    private const COMPANY_ID = 1;

    public function up(): void
    {
        $this->renamePermission(self::OLD_PERMISSION, self::NEW_PERMISSION);
    }

    public function down(): void
    {
        $this->renamePermission(self::NEW_PERMISSION, self::OLD_PERMISSION);
    }

    /**
     * Rename a permission in place so existing role assignments are kept.
     * If the target name already exists (seeder ran first), move the role
     * links onto it and drop the old row.
     */
    private function renamePermission(string $from, string $to): void
    {
        $old = DB::table('permissions')
            ->where('name', $from)
            ->where('company_id', self::COMPANY_ID)
            ->first();

        if (! $old) {
            return;
        }

        $existing = DB::table('permissions')
            ->where('name', $to)
            ->where('company_id', self::COMPANY_ID)
            ->first();

        if (! $existing) {
            DB::table('permissions')->where('id', $old->id)->update(['name' => $to]);

            return;
        }

        $alreadyLinkedRoleIds = DB::table('role_has_permissions')
            ->where('permission_id', $existing->id)
            ->pluck('role_id');

        DB::table('role_has_permissions')
            ->where('permission_id', $old->id)
            ->whereNotIn('role_id', $alreadyLinkedRoleIds)
            ->update(['permission_id' => $existing->id]);

        DB::table('permissions')->where('id', $old->id)->delete();
    }
};

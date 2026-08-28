<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropEmployeeForeignKey();

        // Cross-match chevron employee id → unified employees id by code (employee_id)
        $map = DB::table('chevron_employees')
            ->join('employees', 'employees.code', '=', 'chevron_employees.employee_id')
            ->pluck('employees.id', 'chevron_employees.id')
            ->all();

        $mappedIds = array_values($map);

        // Remap pivot rows that have a matching unified employee
        if ($map) {
            foreach ($map as $chevronId => $employeeId) {
                DB::table('chevron_expense_head_employees')
                    ->where('employee_id', $chevronId)
                    ->update(['employee_id' => $employeeId]);
            }
        }

        // Keep only pivot rows that matched a unified employee
        if (! empty($mappedIds)) {
            DB::table('chevron_expense_head_employees')
                ->whereNotIn('employee_id', $mappedIds)
                ->delete();
        }

        Schema::table('chevron_expense_head_employees', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chevron_expense_head_employees', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        // Re-map back by matching code where possible
        $map = DB::table('chevron_employees')
            ->join('employees', 'employees.code', '=', 'chevron_employees.employee_id')
            ->pluck('chevron_employees.id', 'employees.id')
            ->all();

        foreach ($map as $employeeId => $chevronId) {
            DB::table('chevron_expense_head_employees')
                ->where('employee_id', $employeeId)
                ->update(['employee_id' => $chevronId]);
        }

        Schema::table('chevron_expense_head_employees', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('chevron_employees')
                ->onDelete('cascade');
        });
    }

    private function dropEmployeeForeignKey(): void
    {
        $name = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            ['chevron_expense_head_employees', 'employee_id']
        );

        if ($name) {
            Schema::table('chevron_expense_head_employees', function (Blueprint $table) use ($name) {
                $table->dropForeign($name->CONSTRAINT_NAME);
            });
        }
    }
};

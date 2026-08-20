<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $chevronEmployees = DB::table('chevron_employees')->get();

        foreach ($chevronEmployees as $emp) {
            DB::table('employees')->insert([
                'name'           => $emp->name,
                'code'           => $emp->employee_id,
                'designation_id' => $emp->designation_id,
                'joining_date'   => $emp->joining_date,
                'short_name'     => $emp->short_name,
                'father_name'    => $emp->father_name,
                'mother_name'    => $emp->mother_name,
                'phone'          => $emp->phone,
                'email'          => $emp->email,
                'address'        => $emp->address,
                'current_status' => $emp->current_status,
                'type'           => $emp->type,
                'is_active'      => $emp->is_active,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Remove only employees that have no company-specific data (imported rows)
        // Identify by code matching chevron_employees.employee_id
        $codes = DB::table('chevron_employees')->pluck('employee_id');
        DB::table('employees')->whereIn('code', $codes)->delete();
    }
};

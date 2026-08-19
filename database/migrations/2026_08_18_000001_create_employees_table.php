<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->enum('company_type', ['chevron', 'nas_freights', 'nas_trading']);
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        // Populate employees from chevron_employees
        foreach (DB::table('chevron_employees')->get() as $emp) {
            DB::table('employees')->insert([
                'company_type' => 'chevron',
                'name'         => $emp->name,
                'code'         => $emp->employee_id,
                'is_active'    => $emp->is_active,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Populate employees from nas_freights_employees
        foreach (DB::table('nas_freights_employees')->get() as $emp) {
            DB::table('employees')->insert([
                'company_type' => 'nas_freights',
                'name'         => $emp->name,
                'code'         => $emp->code,
                'is_active'    => $emp->is_active ?? true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // Populate employees from nas_trading_employees
        foreach (DB::table('nas_trading_employees')->get() as $emp) {
            DB::table('employees')->insert([
                'company_type' => 'nas_trading',
                'name'         => $emp->name,
                'code'         => $emp->code,
                'is_active'    => $emp->is_active ?? true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── 1. DESIGNATION TABLES ──────────────────────────────────────────────
        Schema::create('nas_freights_designations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_trading_designations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add department table to Chevron (same structure as nas_trading_departments)
        Schema::create('chevron_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        // Add department table to NAS Freights
        Schema::create('nas_freights_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        // ── 2. SEED SAME DATA INTO ALL DESIGNATION TABLES ─────────────────────
        $designations = DB::table('chevron_designations')->get();
        $desigRows = $designations->map(fn($d) => [
            'id'         => $d->id,
            'name'       => $d->name,
            'is_active'  => $d->is_active,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('nas_freights_designations')->insert($desigRows);
        DB::table('nas_trading_designations')->insert($desigRows);

        // ── 3. SEED SAME DATA INTO ALL DEPARTMENT TABLES ──────────────────────
        $departments = DB::table('nas_trading_departments')->get();
        $deptRows = $departments->map(fn($d) => [
            'id'         => $d->id,
            'name'       => $d->name,
            'status'     => $d->status,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('chevron_departments')->insert($deptRows);
        DB::table('nas_freights_departments')->insert($deptRows);

        // ── 4. ADD COLUMNS TO chevron_employees ───────────────────────────────
        Schema::table('chevron_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('designation_id');
            $table->string('phone', 30)->nullable()->after('mother_name');
            $table->string('email', 150)->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            // 'status' alias — chevron uses current_status, add status for consistency
            $table->string('status', 20)->nullable()->after('current_status');
        });

        // ── 5. ADD COLUMNS TO nas_freights_employees ──────────────────────────
        Schema::table('nas_freights_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('branch_id');
            $table->unsignedBigInteger('department_id')->nullable()->after('designation_id');
            $table->string('employee_prefix', 20)->nullable()->after('department_id');
            $table->string('short_name', 50)->nullable()->after('name');
            $table->string('father_name', 100)->nullable()->after('short_name');
            $table->string('mother_name', 100)->nullable()->after('father_name');
            $table->date('joining_date')->nullable()->after('mother_name');
            $table->text('address')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('status');
        });

        // ── 6. ADD COLUMNS TO nas_trading_employees ───────────────────────────
        Schema::table('nas_trading_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('designation_id');
            $table->string('employee_prefix', 20)->nullable()->after('branch_id');
            $table->string('short_name', 50)->nullable()->after('name');
            $table->string('father_name', 100)->nullable()->after('short_name');
            $table->string('mother_name', 100)->nullable()->after('father_name');
            // rename join_date → joining_date
            $table->renameColumn('join_date', 'joining_date');
            $table->boolean('is_active')->default(true)->after('status');
        });

        // ── 7. POPULATE designation_id from free-text designation field ────────
        // Build name→id map from chevron_designations (same ids across all companies now)
        $desigMap = DB::table('chevron_designations')
            ->pluck('id', 'name')
            ->all();

        // NAS Freights employees
        $nfEmps = DB::table('nas_freights_employees')->get(['id', 'designation']);
        foreach ($nfEmps as $emp) {
            $did = $desigMap[$emp->designation] ?? null;
            if ($did) {
                DB::table('nas_freights_employees')
                    ->where('id', $emp->id)
                    ->update(['designation_id' => $did]);
            }
        }

        // NAS Trading employees
        $ntEmps = DB::table('nas_trading_employees')->get(['id', 'designation']);
        foreach ($ntEmps as $emp) {
            $did = $desigMap[$emp->designation] ?? null;
            if ($did) {
                DB::table('nas_trading_employees')
                    ->where('id', $emp->id)
                    ->update(['designation_id' => $did]);
            }
        }

        // Sync chevron current_status → status
        DB::table('chevron_employees')->update([
            'status' => DB::raw('current_status'),
        ]);
    }

    public function down(): void
    {
        // Remove added columns
        Schema::table('chevron_employees', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'phone', 'email', 'address', 'status']);
        });
        Schema::table('nas_freights_employees', function (Blueprint $table) {
            $table->dropColumn(['designation_id', 'department_id', 'employee_prefix',
                'short_name', 'father_name', 'mother_name', 'joining_date', 'address', 'is_active']);
        });
        Schema::table('nas_trading_employees', function (Blueprint $table) {
            $table->renameColumn('joining_date', 'join_date');
            $table->dropColumn(['designation_id', 'branch_id', 'employee_prefix',
                'short_name', 'father_name', 'mother_name', 'is_active']);
        });

        Schema::dropIfExists('nas_freights_designations');
        Schema::dropIfExists('nas_trading_designations');
        Schema::dropIfExists('chevron_departments');
        Schema::dropIfExists('nas_freights_departments');
    }
};

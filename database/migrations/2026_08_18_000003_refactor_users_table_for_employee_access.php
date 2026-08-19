<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('is_super');
            $table->unsignedBigInteger('role_id')->nullable()->after('employee_id');

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        // Migrate role_id from company_user → users (first non-null per user)
        $companyUserRows = DB::table('company_user')
            ->whereNotNull('role_id')
            ->orderBy('user_id')
            ->get(['user_id', 'role_id']);

        $roleByUser = [];
        foreach ($companyUserRows as $row) {
            if (! isset($roleByUser[$row->user_id])) {
                $roleByUser[$row->user_id] = $row->role_id;
            }
        }

        foreach ($roleByUser as $userId => $roleId) {
            DB::table('users')->where('id', $userId)->update(['role_id' => $roleId]);
        }

        Schema::dropIfExists('user_branch_access');
        Schema::dropIfExists('company_user');
    }

    public function down(): void
    {
        // Recreate company_user
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'manager', 'staff', 'user'])->default('staff');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'company_id']);
        });

        // Recreate user_branch_access
        Schema::create('user_branch_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();
            $table->unique(['user_id', 'company_id', 'branch_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['employee_id', 'role_id']);
        });
    }
};

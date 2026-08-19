<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Make company_type nullable — employees are no longer company-scoped
            $table->enum('company_type', ['chevron', 'nas_freights', 'nas_trading'])->nullable()->change();

            // Employee detail fields
            $table->string('designation')->nullable()->after('code');
            $table->date('joining_date')->nullable()->after('designation');
            $table->string('short_name')->nullable()->after('joining_date');
            $table->string('father_name')->nullable()->after('short_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('phone', 30)->nullable()->after('mother_name');
            $table->string('email', 150)->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            $table->enum('current_status', ['Active', 'Inactive', 'Resigned', 'Terminated'])->default('Active')->after('address');
            $table->unsignedBigInteger('team_leader_id')->nullable()->after('current_status');
            $table->foreign('team_leader_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['team_leader_id']);
            $table->dropColumn(['designation', 'joining_date', 'short_name', 'father_name', 'mother_name', 'phone', 'email', 'address', 'current_status', 'team_leader_id']);
            $table->enum('company_type', ['chevron', 'nas_freights', 'nas_trading'])->nullable(false)->change();
        });
    }
};

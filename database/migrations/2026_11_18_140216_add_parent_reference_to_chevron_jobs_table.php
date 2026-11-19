<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_jobs', function (Blueprint $table) {
            $table->foreignId('parent_job_id')
                ->nullable()
                ->after('job_no')
                ->constrained('chevron_jobs')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('partial_seq')->nullable()->after('parent_job_id');
            $table->unique(['parent_job_id', 'partial_seq'], 'chevron_jobs_parent_partial_seq_unique');
        });
    }

    public function down(): void
    {
        Schema::table('chevron_jobs', function (Blueprint $table) {
            $table->dropUnique('chevron_jobs_parent_partial_seq_unique');
            $table->dropForeign(['parent_job_id']);
            $table->dropColumn(['parent_job_id', 'partial_seq']);
        });
    }
};

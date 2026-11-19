<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jobs imported before the parent reference existed already carry "_PJ" / "_PJ1" / ... in job_no.
     * Link each one to its parent job (the job whose number is the same without the suffix).
     */
    public function up(): void
    {
        DB::table('chevron_jobs')
            ->where('job_no', 'like', '%_PJ%')
            ->whereNull('parent_job_id')
            ->orderBy('id')
            ->select(['id', 'job_no'])
            ->chunkById(500, function ($jobs) {
                foreach ($jobs as $job) {
                    if (! preg_match('/^(.+)_PJ(\d*)$/', $job->job_no, $match)) {
                        continue;
                    }

                    $parentId = DB::table('chevron_jobs')
                        ->where('job_no', $match[1])
                        ->whereNull('parent_job_id')
                        ->value('id');

                    if ($parentId === null) {
                        continue;
                    }

                    DB::table('chevron_jobs')->where('id', $job->id)->update([
                        'parent_job_id' => $parentId,
                        'partial_seq'   => $match[2] === '' ? 1 : ((int) $match[2]) + 1,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('chevron_jobs')
            ->whereNotNull('parent_job_id')
            ->update(['parent_job_id' => null, 'partial_seq' => null]);
    }
};

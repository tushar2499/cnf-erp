<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('job_id')->index();
        });

        // Link existing bills to the customer of their job, so advance used on a bill is traceable to a customer.
        DB::statement('
            UPDATE chevron_bills SET customer_id = (
                SELECT chevron_jobs.customer_id FROM chevron_jobs WHERE chevron_jobs.id = chevron_bills.job_id
            ) WHERE job_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('chevron_bills', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};

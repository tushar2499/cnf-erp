<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChevronJobTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Import', 'Export'];

        foreach ($types as $name) {
            DB::table('chevron_job_types')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'is_active' => true]
            );
        }
    }
}

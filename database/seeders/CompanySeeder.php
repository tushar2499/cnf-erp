<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name'      => 'Chevron Lines (C&F) Ltd.',
                'slug'      => 'chevron-lines',
                'type'      => 'cnf',
                'is_active' => true,
            ],
            [
                'name'      => 'NAS Freights And Logistics Ltd.',
                'slug'      => 'nas-freights',
                'type'      => 'freight',
                'is_active' => true,
            ],
            [
                'name'      => 'Nas Trading Company',
                'slug'      => 'nas-trading',
                'type'      => 'trading',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(['slug' => $company['slug']], $company);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CompanySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(ChevronExpenseSeeder::class);

        $admin = User::updateOrCreate(
            ['email' => 'admin@nasgroup.com'],
            [
                'name'      => 'System Admin',
                'password'  => bcrypt('admin1234'),
                'is_active' => true,
            ]
        );

        // Give admin access to all 3 companies
        $companies = \App\Models\Company::all();
        foreach ($companies as $company) {
            $admin->companies()->syncWithoutDetaching([
                $company->id => ['role' => 'admin', 'is_active' => true],
            ]);
        }
    }
}

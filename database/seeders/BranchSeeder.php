<?php

namespace Database\Seeders;

use App\Models\Chevron\ChevronBranch;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasTrading\NasTradingBranch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Head Office', 'code' => 'HO',  'address' => 'Dhaka, Bangladesh',      'phone' => '+880 2 1234567', 'is_active' => true],
            ['name' => 'Chittagong', 'code' => 'CGP', 'address' => 'Chittagong, Bangladesh',  'phone' => '+880 31 234567', 'is_active' => true],
        ];

        foreach ($branches as $b) {
            ChevronBranch::updateOrCreate(['code' => $b['code']], $b);
            NasFreightsBranch::updateOrCreate(['code' => $b['code']], $b);
            NasTradingBranch::updateOrCreate(['code' => $b['code']], $b);
        }
    }
}

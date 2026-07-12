<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChevronBillExpenseHeadSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            'CUSTOMS' => [
                'Customs Duty & Tax',
                'Welfare Fund (Import)',
                'B/L Verify Charge',
            ],
            'CUSTOMS EXPENSE' => [
                'Miscellaneous Expense',
                'Customs Handling LCL Cargo',
                '100% Examine Expense',
            ],
            'JETTY EXPENSES' => [
                'Shipping Charge',
                'NOC Charge',
                'Hyster Loading Charge',
            ],
            'PORT & JETTY' => [
                'Wharf Rent Charge',
                'Expense for 1.P & Photo Copy of Document',
                'Agency Commission',
                'Commission on Assessable Value',
            ],
        ];

        foreach ($data as $catName => $heads) {
            DB::table('chevron_expense_categories')->insertOrIgnore([
                'name'        => $catName,
                'type'        => 'bill',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $catId = DB::table('chevron_expense_categories')
                ->where('name', $catName)
                ->where('type', 'bill')
                ->value('id');

            foreach ($heads as $headName) {
                DB::table('chevron_expense_heads')->insertOrIgnore([
                    'name'                => $headName,
                    'expense_category_id' => $catId,
                    'type'                => 'External',
                    'amount'              => 0,
                    'is_active'           => true,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }
}

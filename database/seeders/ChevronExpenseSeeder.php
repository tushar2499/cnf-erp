<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChevronExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── 1. Sync categories from NAS Trading → Chevron ─────────────────────
        $ntCategories = DB::table('nas_trading_expense_heads')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter() // remove nulls/empty
            ->values();

        foreach ($ntCategories as $catName) {
            DB::table('chevron_expense_categories')->insertOrIgnore([
                'name'        => $catName,
                'description' => null,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // Build name → id map for Chevron categories
        $catMap = DB::table('chevron_expense_categories')
            ->pluck('id', 'name')
            ->all();

        // ── 2. Sync expense heads from NAS Trading → Chevron ──────────────────
        $ntHeads = DB::table('nas_trading_expense_heads')->get();

        foreach ($ntHeads as $head) {
            $catId = $catMap[$head->category] ?? null;

            DB::table('chevron_expense_heads')->insertOrIgnore([
                'name'                => $head->name,
                'expense_category_id' => $catId,
                'type'                => $head->type,
                'amount'              => null,
                'is_active'           => $head->status === 'Active' ? true : false,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        $catCount  = DB::table('chevron_expense_categories')->count();
        $headCount = DB::table('chevron_expense_heads')->count();

        $this->command->info("Chevron expense categories: {$catCount}");
        $this->command->info("Chevron expense heads: {$headCount}");
    }
}

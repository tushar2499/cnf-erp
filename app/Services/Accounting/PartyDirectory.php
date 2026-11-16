<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;

/**
 * Customers, suppliers, overseas agents and employees used as the party sub-ledger.
 * Parties live in each company's own tables, so the company decides where to look.
 */
class PartyDirectory
{
    public const TYPES = [
        'customer' => 'Customer',
        'supplier' => 'Supplier',
        'agent'    => 'Overseas Agent',
        'employee' => 'Employee',
    ];

    /** company slug => party type => [table, name column] */
    private const TABLES = [
        'chevron-lines' => [
            'customer' => ['chevron_customers', 'name'],
        ],
        'nas-freights' => [
            'customer' => ['nas_freights_customers', 'name'],
            'supplier' => ['nas_freights_suppliers', 'company_name'],
            'agent'    => ['nas_freights_overseas_agents', 'name'],
        ],
        'nas-trading' => [
            'customer' => ['nas_trading_customers', 'company_name'],
            'supplier' => ['nas_trading_suppliers', 'company_name'],
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function typesFor(string $companySlug): array
    {
        $types = array_keys(self::TABLES[$companySlug] ?? []);
        $types[] = 'employee';

        return array_intersect_key(self::TYPES, array_flip($types));
    }

    /**
     * @return list<array{id: int, text: string}>
     */
    public function search(string $companySlug, string $type, string $term = ''): array
    {
        [$table, $column] = $type === 'employee'
            ? ['employees', 'name']
            : (self::TABLES[$companySlug][$type] ?? [null, null]);

        if (! $table) {
            return [];
        }

        return DB::table($table)
            ->when($term !== '', fn ($q) => $q->where($column, 'like', "%{$term}%"))
            ->orderBy($column)
            ->limit(50)
            ->get(['id', "{$column} as name"])
            ->map(fn ($p) => ['id' => (int) $p->id, 'text' => (string) $p->name])
            ->all();
    }
}

<?php

namespace App\Services\Accounting\Sources;

use Illuminate\Support\Facades\DB;

/**
 * Values a mapping rule can be narrowed to (the "match_key").
 */
class MatchOptions
{
    public const LABELS = [
        'chevron_expense_head'  => 'Expense head',
        'freights_expense_head' => 'Expense head',
        'trading_expense_head'  => 'Expense head',
        'freights_payment_mode' => 'Payment mode',
        'trading_payment_mode'  => 'Payment mode',
        'chevron_account'       => 'Received in account',
        'export_bill_type'      => 'Bill type',
        'lc_payment_type'       => 'Payment type',
    ];

    /**
     * @return array<string, string> match_key => label
     */
    public static function options(string $type): array
    {
        return match ($type) {
            'chevron_expense_head'  => self::pluck('chevron_expense_heads', 'name'),
            'freights_expense_head' => self::pluck('nas_freights_expense_heads', 'name'),
            'trading_expense_head'  => self::pluck('nas_trading_expense_heads', 'name'),
            'freights_payment_mode' => self::same(['Cash', 'Cheque', 'Bank Transfer', 'Online']),
            'trading_payment_mode'  => self::same(['Cash', 'Bank Transfer', 'Cheque', 'Mobile Banking']),
            'chevron_account'       => DB::table('chevron_accounts')->orderBy('account_name')->get(['id', 'account_name', 'account_no'])
                ->mapWithKeys(fn ($a) => [(string) $a->id => trim($a->account_name.' ('.$a->account_no.')')])->all(),
            'export_bill_type'      => self::same(['Customer', 'Overseas Agent']),
            'lc_payment_type'       => ['advance' => 'Advance', 'regular' => 'Regular'],
            default                 => [],
        };
    }

    public static function label(string $type, string $key): string
    {
        if ($key === '') {
            return 'Any (default)';
        }

        return self::options($type)[$key] ?? "#{$key}";
    }

    /**
     * @return array<string, string>
     */
    private static function pluck(string $table, string $column): array
    {
        return DB::table($table)->orderBy($column)->pluck($column, 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])->all();
    }

    /**
     * @param  list<string>  $values
     * @return array<string, string>
     */
    private static function same(array $values): array
    {
        return array_combine($values, $values);
    }
}

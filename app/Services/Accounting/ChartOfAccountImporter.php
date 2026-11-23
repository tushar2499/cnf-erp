<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccAccount;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Reads the group chart of accounts workbook (columns Code_Master, Code2_Sub-1,
 * Code3_Sub-2, Code4_Ledger, Type with "code_name" cells) into acc_accounts.
 *
 * - Customer / supplier ledgers are collapsed into one control ledger per group;
 *   parties are tracked as a sub-ledger from the ERP masters instead.
 * - Master 06 (Contra Assets) only duplicates the cash and bank ledgers and is skipped.
 * - Rows whose codes break the hierarchy are reported and not imported.
 */
class ChartOfAccountImporter
{
    public const MASTER_TYPES = [
        '01' => 'asset',
        '02' => 'liability',
        '03' => 'equity',
        '04' => 'income',
        '05' => 'expense',
    ];

    public const SKIPPED_MASTERS = ['06' => 'Contra Assets duplicate the cash & bank ledgers'];

    /** Sub-2 groups whose ledgers are individual parties. */
    public const PARTY_GROUPS = [
        '010204' => 'customer',
        '020101' => 'supplier',
        '020106' => 'customer',
    ];

    public const CASH_BANK_GROUPS = ['010201', '010202'];

    private const HEADERS = [
        'master' => 'code_master',
        'sub1'   => 'code2_sub-1',
        'sub2'   => 'code3_sub-2',
        'ledger' => 'code4_ledger',
        'nature' => 'type',
    ];

    /** @var array<string, array{code: string, name: string, level: int, parent_code: ?string, type: string, nature: string, is_group: bool, is_cash_bank: bool, party_type: ?string, row: int}> */
    private array $accounts = [];

    /** @var list<array{row: int, severity: string, message: string}> */
    private array $issues = [];

    private int $partyLedgersCollapsed = 0;

    private int $skippedMasterRows = 0;

    /**
     * @return array{accounts: list<array<string, mixed>>, issues: list<array{row: int, severity: string, message: string}>, stats: array<string, int>}
     */
    public function parse(string $path): array
    {
        $this->accounts = [];
        $this->issues = [];
        $this->partyLedgersCollapsed = 0;
        $this->skippedMasterRows = 0;

        [$sheet, $columns] = $this->locateSheet($path);

        $highestRow = $sheet->getHighestDataRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $cells = [];
            foreach ($columns as $key => $column) {
                $value = trim((string) $sheet->getCell($column.$row)->getValue());
                $cells[$key] = $value === '_' ? '' : $value;
            }
            if (implode('', $cells) === '') {
                continue;
            }
            $this->parseRow($row, $cells);
        }

        ksort($this->accounts, SORT_STRING);

        $existing = AccAccount::whereIn('code', array_keys($this->accounts))->pluck('code')->flip();
        $accounts = array_map(fn ($a) => $a + ['exists' => isset($existing[$a['code']])], array_values($this->accounts));

        usort($this->issues, fn ($a, $b) => $a['row'] <=> $b['row']);

        return [
            'accounts' => $accounts,
            'issues'   => $this->issues,
            'stats'    => [
                'groups'                  => count(array_filter($accounts, fn ($a) => $a['is_group'])),
                'ledgers'                 => count(array_filter($accounts, fn ($a) => ! $a['is_group'])),
                'existing'                => count($existing),
                'party_ledgers_collapsed' => $this->partyLedgersCollapsed,
                'skipped_master_rows'     => $this->skippedMasterRows,
                'errors'                  => count(array_filter($this->issues, fn ($i) => $i['severity'] === 'error')),
                'warnings'                => count(array_filter($this->issues, fn ($i) => $i['severity'] === 'warning')),
            ],
        ];
    }

    /**
     * Parse and persist. Existing codes are updated, new codes created; nothing is deleted.
     *
     * @return array{created: int, updated: int, stats: array<string, int>}
     */
    public function import(string $path, ?int $userId = null): array
    {
        $result = $this->parse($path);
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($result, $userId, &$created, &$updated) {
            $ids = [];
            foreach ($result['accounts'] as $data) {
                $account = AccAccount::firstOrNew(['code' => $data['code']]);
                $account->fill([
                    'parent_id'    => $data['parent_code'] ? $ids[$data['parent_code']] : null,
                    'name'         => $data['name'],
                    'level'        => $data['level'],
                    'type'         => $data['type'],
                    'nature'       => $data['nature'],
                    'is_group'     => $data['is_group'],
                    'is_cash_bank' => $data['is_cash_bank'],
                    'party_type'   => $data['party_type'],
                ]);
                if (! $account->exists) {
                    $account->created_by = $userId;
                    $account->is_active = true;
                }
                $account->exists ? $updated++ : $created++;
                $account->save();
                $ids[$data['code']] = $account->id;
            }
        });

        return ['created' => $created, 'updated' => $updated, 'stats' => $result['stats']];
    }

    /**
     * @return array{0: Worksheet, 1: array<string, string>}
     */
    private function locateSheet(string $path): array
    {
        $spreadsheet = IOFactory::createReaderForFile($path)->setReadDataOnly(true)->load($path);

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $columns = [];
            foreach ($sheet->getRowIterator(1, 1)->current()->getCellIterator() as $cell) {
                $header = strtolower(trim((string) $cell->getValue()));
                $key = array_search($header, self::HEADERS, true);
                if ($key !== false) {
                    $columns[$key] = $cell->getColumn();
                }
            }
            if (count($columns) === count(self::HEADERS)) {
                return [$sheet, $columns];
            }
        }

        throw new RuntimeException('No sheet with headers Code_Master, Code2_Sub-1, Code3_Sub-2, Code4_Ledger, Type was found.');
    }

    /**
     * @param  array<string, string>  $cells
     */
    private function parseRow(int $row, array $cells): void
    {
        $master = $this->splitCodeName($cells['master']);
        if (! $master) {
            $this->issue($row, 'error', "Missing master code: \"{$cells['master']}\".");

            return;
        }
        if (isset(self::SKIPPED_MASTERS[$master[0]])) {
            $this->skippedMasterRows++;

            return;
        }
        $type = self::MASTER_TYPES[$master[0]] ?? null;
        if (! $type) {
            $this->issue($row, 'error', "Unknown master code {$master[0]}.");

            return;
        }

        $levels = [
            1 => $master,
            2 => $this->splitCodeName($cells['sub1']),
            3 => $this->splitCodeName($cells['sub2']),
        ];
        $ledger = $this->splitCodeName($cells['ledger']);

        $parentCode = null;
        foreach ($levels as $level => $codeName) {
            if (! $codeName) {
                if ($level === 2 && ! $levels[3] && $cells['ledger'] === '') {
                    return; // master-only row
                }
                $this->issue($row, 'error', AccAccount::LEVEL_NAMES[$level].' is missing.');

                return;
            }
            if (! $this->validCode($codeName[0], $level, $parentCode, $row)) {
                return;
            }
            $this->registerGroup($row, $codeName, $level, $parentCode, $type);
            $parentCode = $codeName[0];
        }

        $nature = in_array(strtolower($cells['nature']), ['dr', 'cr'], true)
            ? strtolower($cells['nature'])
            : AccAccount::defaultNature($type);

        if (isset(self::PARTY_GROUPS[$parentCode])) {
            $this->partyLedgersCollapsed++;
            $this->registerLedger($row, [$parentCode.'0000', $levels[3][1]], $parentCode, $type, $nature, self::PARTY_GROUPS[$parentCode]);

            return;
        }

        if (! $ledger) {
            $label = $cells['ledger'] !== '' ? "\"{$cells['ledger']}\"" : 'empty';
            $this->issue($row, 'error', "Ledger has no code ({$label}).");

            return;
        }
        if (! $this->validCode($ledger[0], AccAccount::LEVEL_LEDGER, $parentCode, $row, $ledger[1])) {
            return;
        }

        $this->registerLedger($row, $ledger, $parentCode, $type, $nature, null);
    }

    private function validCode(string $code, int $level, ?string $parentCode, int $row, string $name = ''): bool
    {
        $label = AccAccount::LEVEL_NAMES[$level];
        $expected = AccAccount::CODE_LENGTHS[$level];
        $suffix = $name !== '' ? " ({$name})" : '';

        if (strlen($code) !== $expected) {
            $this->issue($row, 'error', "{$label} code {$code}{$suffix} must be {$expected} digits.");

            return false;
        }
        if ($parentCode && ! str_starts_with($code, $parentCode)) {
            $this->issue($row, 'error', "{$label} code {$code}{$suffix} does not belong to parent {$parentCode}.");

            return false;
        }

        return true;
    }

    /**
     * @param  array{0: string, 1: string}  $codeName
     */
    private function registerGroup(int $row, array $codeName, int $level, ?string $parentCode, string $type): void
    {
        [$code, $name] = $codeName;
        if (isset($this->accounts[$code])) {
            $existing = $this->accounts[$code];
            if (strcasecmp($existing['name'], $name) !== 0 && ! $this->hasIssue($code)) {
                $this->issue($row, 'warning', "{$code} has two names: \"{$existing['name']}\" (kept) and \"{$name}\".", $code);
            }

            return;
        }

        $this->accounts[$code] = [
            'code'         => $code,
            'name'         => $name,
            'level'        => $level,
            'parent_code'  => $parentCode,
            'type'         => $type,
            'nature'       => AccAccount::defaultNature($type),
            'is_group'     => true,
            'is_cash_bank' => false,
            'party_type'   => null,
            'row'          => $row,
        ];
    }

    /**
     * @param  array{0: string, 1: string}  $codeName
     */
    private function registerLedger(int $row, array $codeName, string $parentCode, string $type, string $nature, ?string $partyType): void
    {
        [$code, $name] = $codeName;
        if (isset($this->accounts[$code])) {
            if (! $partyType) {
                $kept = $this->accounts[$code];
                $this->issue($row, 'error', "Duplicate ledger code {$code} (\"{$name}\"); kept \"{$kept['name']}\" from row {$kept['row']}.");
            }

            return;
        }

        $this->accounts[$code] = [
            'code'         => $code,
            'name'         => $name,
            'level'        => AccAccount::LEVEL_LEDGER,
            'parent_code'  => $parentCode,
            'type'         => $type,
            'nature'       => $nature,
            'is_group'     => false,
            'is_cash_bank' => in_array($parentCode, self::CASH_BANK_GROUPS, true),
            'party_type'   => $partyType,
            'row'          => $row,
        ];
    }

    /**
     * "0102010001_Cash In Hand" -> ['0102010001', 'Cash In Hand']. Stray codes glued to
     * the start of the name (e.g. "0502020026News Papers Expense") are removed.
     *
     * @return array{0: string, 1: string}|null
     */
    private function splitCodeName(string $value): ?array
    {
        if (! preg_match('/^\s*(\d+)_(.*)$/s', $value, $m)) {
            return null;
        }
        $name = preg_replace('/^\d{6,}_?/', '', trim($m[2]));
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return [$m[1], $name !== '' ? $name : $m[1]];
    }

    private function issue(int $row, string $severity, string $message, ?string $key = null): void
    {
        $this->issues[] = ['row' => $row, 'severity' => $severity, 'message' => $message] + ($key ? ['key' => $key] : []);
    }

    private function hasIssue(string $key): bool
    {
        foreach ($this->issues as $issue) {
            if (($issue['key'] ?? null) === $key) {
                return true;
            }
        }

        return false;
    }
}

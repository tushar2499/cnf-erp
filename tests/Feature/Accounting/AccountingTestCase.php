<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\AccAccount;
use App\Models\Accounting\AccTransactionMapping;
use App\Models\Company;
use App\Models\User;
use App\Services\Accounting\Sources\NasFreights;
use App\Services\Accounting\Sources\SourceRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The full migration chain is MySQL-only, so each test builds just the tables it needs:
 * users, companies, the accounting tables and two NAS Freights source tables.
 */
abstract class AccountingTestCase extends TestCase
{
    protected Company $freights;

    /** @var array<string, AccAccount> */
    protected array $acc = [];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super')->default(false);
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->nullable();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_customer_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bill_no');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->date('bill_date');
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('ait_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('Submitted');
            $table->timestamps();
        });

        Schema::create('nas_freights_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('receipt_no');
            $table->date('receipt_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('bill_no')->nullable();
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->string('payment_mode')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_11_16_115343_create_acc_accounts_table.php'))->up();
        (require database_path('migrations/2026_11_16_120303_create_acc_vouchers_and_mappings_tables.php'))->up();

        $this->app->instance(SourceRegistry::class, new SourceRegistry([
            NasFreights\CustomerBillSource::class,
            NasFreights\MoneyReceiptSource::class,
        ]));

        $this->freights = Company::create(['name' => 'NAS Freights', 'slug' => 'nas-freights', 'type' => 'freight']);
        Company::create(['name' => 'Chevron Lines', 'slug' => 'chevron-lines', 'type' => 'cnf']);

        $this->buildChart();
    }

    private function buildChart(): void
    {
        $tree = [
            ['01', 'Asset', 1, 'asset', 'dr', null], ['0102', 'Current Assets', 2, 'asset', 'dr', '01'],
            ['010201', 'Cash at Office', 3, 'asset', 'dr', '0102'], ['010202', 'Cash At Bank', 3, 'asset', 'dr', '0102'],
            ['010204', 'Accounts Receivable', 3, 'asset', 'dr', '0102'],
            ['02', 'Liability', 1, 'liability', 'cr', null], ['0201', 'Current Liabilities', 2, 'liability', 'cr', '02'],
            ['020107', 'Vat Payable', 3, 'liability', 'cr', '0201'],
            ['03', 'Equity Capital', 1, 'equity', 'cr', null], ['0303', 'Owners Equity', 2, 'equity', 'cr', '03'],
            ['030302', 'Share Capital', 3, 'equity', 'cr', '0303'],
            ['04', 'Income', 1, 'income', 'cr', null], ['0401', 'Direct Income', 2, 'income', 'cr', '04'],
            ['040101', 'Sales Accounts', 3, 'income', 'cr', '0401'],
            ['05', 'Expense', 1, 'expense', 'dr', null], ['0502', 'Indirect Expenses', 2, 'expense', 'dr', '05'],
            ['050203', 'Administrative Expenses', 3, 'expense', 'dr', '0502'],
        ];
        $ids = [];
        foreach ($tree as [$code, $name, $level, $type, $nature, $parent]) {
            $ids[$code] = AccAccount::create(['code' => $code, 'name' => $name, 'level' => $level, 'type' => $type, 'nature' => $nature,
                'is_group'                           => true, 'parent_id' => $parent ? $ids[$parent] : null])->id;
        }

        $ledgers = [
            'cash'     => ['0102010001', 'Cash In Hand', '010201', 'asset', 'dr', ['is_cash_bank' => true]],
            'bank'     => ['0102020001', 'MBL Tejgaon', '010202', 'asset', 'dr', ['is_cash_bank' => true]],
            'ar'       => ['0102040000', 'Accounts Receivable', '010204', 'asset', 'dr', ['party_type' => 'customer']],
            'vat'      => ['0201070001', 'Vat Payable Acc', '020107', 'liability', 'cr', []],
            'capital'  => ['0303020001', 'Share Capital', '030302', 'equity', 'cr', []],
            'sales'    => ['0401010001', 'Sales A/c', '040101', 'income', 'cr', []],
            'rent'     => ['0502030042', 'Office Rent', '050203', 'expense', 'dr', []],
        ];
        foreach ($ledgers as $key => [$code, $name, $parent, $type, $nature, $extra]) {
            $this->acc[$key] = AccAccount::create(['code' => $code, 'name' => $name, 'level' => 4, 'type' => $type, 'nature' => $nature,
                'is_group'                                => false, 'parent_id' => $ids[$parent]] + $extra);
        }
    }

    protected function map(string $source, string $component, string $debit, string $credit, string $matchKey = ''): AccTransactionMapping
    {
        return AccTransactionMapping::create([
            'source_type'      => $source, 'component' => $component, 'match_key' => $matchKey,
            'debit_account_id' => $this->acc[$debit]->id, 'credit_account_id' => $this->acc[$credit]->id,
        ]);
    }

    protected function customerBill(array $overrides = []): int
    {
        return DB::table('nas_freights_customer_bills')->insertGetId($overrides + [
            'branch_id'  => 1, 'bill_no' => 'CB-0001', 'customer_id' => 7, 'customer_name' => 'ABC Garments',
            'bill_date'  => '2026-10-05', 'vat_amount' => 2500, 'total_amount' => 52500, 'status' => 'Submitted',
            'created_at' => '2026-10-05 10:00:00', 'updated_at' => '2026-10-05 10:00:00',
        ]);
    }

    protected function moneyReceipt(array $overrides = []): int
    {
        return DB::table('nas_freights_money_receipts')->insertGetId($overrides + [
            'branch_id'     => 1, 'receipt_no' => 'MR-0001', 'receipt_date' => '2026-10-09', 'customer_id' => 7,
            'customer_name' => 'ABC Garments', 'bill_no' => 'CB-0001', 'amount_received' => 52500, 'payment_mode' => 'Bank Transfer',
            'created_at'    => '2026-10-09 10:00:00', 'updated_at' => '2026-10-09 10:00:00',
        ]);
    }

    protected function superUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_super' => true])->save();

        return $user;
    }
}

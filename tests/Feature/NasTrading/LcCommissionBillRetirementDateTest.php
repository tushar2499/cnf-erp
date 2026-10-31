<?php

namespace Tests\Feature\NasTrading;

use App\Models\NasTrading\NasTradingCustomer;
use App\Models\NasTrading\NasTradingLc;
use App\Models\NasTrading\NasTradingLcBillStatement;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LcCommissionBillRetirementDateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_super')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        $user = User::create([
            'name'     => 'Test Admin',
            'username' => 'testadmin',
            'email'    => 'admin@test.local',
            'password' => 'secret',
            'is_super' => true,
        ]);

        $this->actingAs($user);

        view()->share('activeBranch', (object) ['id' => 1, 'name' => 'Main', 'code' => 'M']);
        view()->share('activeCompany', (object) ['id' => 1, 'slug' => 'nas-trading', 'name' => 'NAS Trading', 'type' => 'trading']);

        Schema::create('nas_trading_customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('company_name');
            $table->text('address')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_trading_lcs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('lc_no_system', 20)->unique();
            $table->string('pfi_no')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('lc_open_date')->nullable();
            $table->decimal('lc_rt_value', 15, 2)->nullable();
            $table->date('lc_retirement_date')->nullable();
            $table->decimal('lc_commission_percent', 10, 4)->nullable();
            $table->decimal('lc_commission_flat', 15, 2)->nullable();
            $table->decimal('invoice_value', 15, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('nas_trading_lc_rt_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lc_id')->constrained('nas_trading_lcs')->onDelete('cascade');
            $table->date('date');
            $table->string('note')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('nas_trading_lc_bill_statements', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no', 30)->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('bill_date');
            $table->enum('status', ['Draft', 'Confirmed', 'Paid'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('nas_trading_lc_bill_statement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_statement_id')->constrained('nas_trading_lc_bill_statements')->onDelete('cascade');
            $table->unsignedBigInteger('lc_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('bill_no')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    #[Test]
    public function commission_bill_uses_first_rt_value_date_when_lc_retirement_date_is_null(): void
    {
        $lc = $this->makeLc();
        $lc->rtValues()->create(['lc_id' => $lc->id, 'date' => '2026-09-02', 'amount' => 299.85, 'note' => 'first']);
        $lc->rtValues()->create(['lc_id' => $lc->id, 'date' => '2026-09-10', 'amount' => 400.00, 'note' => 'second']);

        $content = $this->renderCommissionBillAll($lc);

        $this->assertStringContainsString('02.09.2026', $content);
        $this->assertStringNotContainsString('10.09.2026', $content);
    }

    #[Test]
    public function commission_bill_prefers_first_rt_value_date_over_lc_retirement_date(): void
    {
        $lc = $this->makeLc(['lc_retirement_date' => '2026-09-01']);
        $lc->rtValues()->create(['lc_id' => $lc->id, 'date' => '2026-09-05', 'amount' => 500.00]);

        $content = $this->renderCommissionBillAll($lc);

        $this->assertStringContainsString('05.09.2026', $content);
        $this->assertStringNotContainsString('01.09.2026', $content);
    }

    #[Test]
    public function commission_bill_falls_back_to_lc_retirement_date_when_no_rt_values(): void
    {
        $lc = $this->makeLc(['lc_retirement_date' => '2026-08-15']);
        $lc->lc_rt_value = 60000.00;
        $lc->save();

        $content = $this->renderCommissionBillAll($lc);

        $this->assertStringContainsString('15.08.2026', $content);
        $this->assertStringContainsString('60,000.00', $content);
    }

    #[Test]
    public function single_commission_bill_prints_first_rt_value_date(): void
    {
        $lc = $this->makeLc();
        $lc->rtValues()->create(['lc_id' => $lc->id, 'date' => '2026-09-03', 'amount' => 299.85]);

        [$statement, $item] = $this->makeStatementWithLc($lc);
        $statement->load(['customer']);
        $item->load(['lc.rtValues']);

        $content = view('nas-trading.lc-bill-statements.prints.commission-bill', ['lcBillStatement' => $statement, 'item' => $item])->render();

        $this->assertStringContainsString('03.09.2026', $content);
    }

    #[Test]
    public function statement_view_uses_first_rt_value_date_as_retirement_date(): void
    {
        $lc = $this->makeLc();
        $lc->rtValues()->create(['lc_id' => $lc->id, 'date' => '2026-09-02', 'amount' => 299.85]);

        [$statement, $item] = $this->makeStatementWithLc($lc);
        $statement->load(['customer', 'items.lc.rtValues']);

        $content = view('nas-trading.lc-bill-statements.show', ['lcBillStatement' => $statement])->render();

        $this->assertStringContainsString('02-Sep-2026', $content);
    }

    private function makeLc(array $attributes = []): NasTradingLc
    {
        return NasTradingLc::create(array_merge([
            'lc_no_system'           => 'LC-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id'            => $this->makeCustomer()->id,
            'lc_no'                  => 'TEST-LC-001',
            'pfi_no'                 => 'PFI-001',
            'lc_open_date'           => '2026-08-05',
            'lc_rt_value'            => 299.85,
            'lc_commission_percent'  => 0.5,
            'lc_commission_flat'     => 149.93,
        ], $attributes));
    }

    private function makeCustomer(): NasTradingCustomer
    {
        return NasTradingCustomer::create([
            'code'         => 'CUS-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'company_name' => 'Test Importer Ltd',
            'address'      => 'Dhaka, Bangladesh',
            'status'       => 'Active',
        ]);
    }

    private function makeStatementWithLc(NasTradingLc $lc): array
    {
        $statement = NasTradingLcBillStatement::create([
            'bill_no'     => 'LC/COM/'.str_pad((string) random_int(1, 99), 2, '0', STR_PAD_LEFT).'/'.date('Y'),
            'customer_id' => $lc->customer_id,
            'bill_date'   => '2026-09-08',
            'status'      => 'Draft',
        ]);

        $item = $statement->items()->create([
            'lc_id'         => $lc->id,
            'serial_number' => (string) random_int(1000, 9999),
            'bill_no'       => 'BN-001',
            'sort_order'    => 1,
        ]);

        return [$statement, $item];
    }

    private function renderCommissionBillAll(NasTradingLc $lc): string
    {
        [$statement, $item] = $this->makeStatementWithLc($lc);
        $statement->load(['customer', 'items.lc.rtValues']);

        return view('nas-trading.lc-bill-statements.prints.commission-bill-all', ['lcBillStatement' => $statement])->render();
    }
}

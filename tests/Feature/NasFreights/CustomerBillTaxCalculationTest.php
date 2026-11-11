<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBillTaxCalculationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->actingAs($this->superUser());
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super')->default(false);
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('nas_freights_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_customer_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bill_no')->unique();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('bill_date')->nullable();
            $table->string('delivery_no')->nullable();
            $table->string('delivery_type')->nullable();
            $table->decimal('tds_percent', 8, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('ait_percent', 8, 2)->default(0);
            $table->decimal('ait_amount', 15, 2)->default(0);
            $table->decimal('vat_percent', 8, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->boolean('vat_on_ait')->default(false);
            $table->string('bill_type')->nullable();
            $table->string('bill_by')->nullable();
            $table->string('print_format', 30)->default('general');
            $table->text('note')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->string('entry_by')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('booking_item_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('b_qty', 15, 2)->default(0);
            $table->decimal('d_qty', 15, 2)->default(0);
            $table->decimal('due_qty', 15, 2)->default(0);
            $table->decimal('demurrage_day', 15, 2)->default(0);
            $table->decimal('demurrage_amount', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('disc_percent', 8, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('ait_percent', 8, 2)->default(0);
            $table->decimal('ait_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    private function superUser(): User
    {
        $user = User::create([
            'name'      => 'Super User',
            'username'  => 'super',
            'email'     => 'super@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]);
        $user->forceFill(['is_super' => true])->save();

        return $user;
    }

    /**
     * Build one posted bill item row
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function itemRow(array $overrides = []): array
    {
        return array_merge([
            'booking_id'       => '',
            'booking_item_id'  => '',
            'booking_date'     => '2026-10-01',
            'delivery_date'    => '2026-10-05',
            'item_code'        => 'VAN1',
            'item_name'        => 'VAN1',
            'location'         => 'Dhaka - Ctg',
            'b_qty'            => 2,
            'd_qty'            => 2,
            'due_qty'          => 0,
            'price'            => 100,
            'disc_percent'     => 10,
            'demurrage_day'    => 1,
            'demurrage_amount' => 50,
        ], $overrides);
    }

    /**
     * Build the posted bill payload
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billPayload(array $items, array $overrides = []): array
    {
        return array_merge([
            'from_date'     => '2026-10-01',
            'to_date'       => '2026-10-31',
            'bill_date'     => '2026-10-31',
            'delivery_type' => 'LOCAL',
            'bill_type'     => 'TRUCK',
            'tds_percent'   => 2,
            'ait_percent'   => 5,
            'vat_percent'   => 10,
            'items'         => json_encode($items),
        ], $overrides);
    }

    private function withFreightSession(): static
    {
        return $this->withSession([
            'active_company_id'        => 1,
            'active_company_slug'      => 'nas-freights',
            'active_company_name'      => 'NAS Freights',
            'active_company_type'      => 'freight',
            'nas_freights_branch_id'   => 1,
            'nas_freights_branch_name' => 'Corporate',
            'nas_freights_branch_code' => 'COR',
        ]);
    }

    public function test_store_applies_master_ait_and_vat_to_price_only(): void
    {
        $response = $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()])
        );

        $response->assertOk();

        // gross 200, discount 20, price base 180, AIT 5% = 9, VAT 10% = 18, line = 207
        $bill = NasFreightsCustomerBill::with('items')->firstOrFail();
        $item = $bill->items->first();

        $this->assertEquals(5, $item->ait_percent);
        $this->assertEquals(9, $item->ait_amount);
        $this->assertEquals(18, $item->vat_amount);
        $this->assertEquals(3.6, $item->tds_amount);
        $this->assertEquals(207, $item->line_amount);

        // demurrage (50) is not taxed; TDS 2% is on the price base (3.60) and not in the line amount
        $this->assertEquals(5, $bill->ait_percent);
        $this->assertEquals(9, $bill->ait_amount);
        $this->assertEquals(10, $bill->vat_percent);
        $this->assertEquals(18, $bill->vat_amount);
        $this->assertEquals(3.6, $bill->tds_amount);
        $this->assertEquals(207, $bill->sub_total);
        $this->assertEquals(260.6, $bill->total_amount);
    }

    public function test_store_ignores_client_sent_amounts(): void
    {
        $row = $this->itemRow([
            'ait_amount'  => 9999,
            'vat_amount'  => 9999,
            'tds_amount'  => 9999,
            'line_amount' => 9999,
            'discount'    => 9999,
        ]);

        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$row])
        )->assertOk();

        $item = NasFreightsCustomerBill::with('items')->firstOrFail()->items->first();

        $this->assertEquals(20, $item->discount);
        $this->assertEquals(9, $item->ait_amount);
        $this->assertEquals(18, $item->vat_amount);
        $this->assertEquals(3.6, $item->tds_amount);
        $this->assertEquals(207, $item->line_amount);
    }

    public function test_master_rates_override_rates_sent_on_rows(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([
                $this->itemRow(['disc_percent' => 0]),
                $this->itemRow(['disc_percent' => 0, 'item_code' => 'VAN2', 'ait_percent' => 99, 'vat_percent' => 99]),
            ])
        )->assertOk();

        $items = NasFreightsCustomerBill::with('items')->firstOrFail()->items;

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals(5, $item->ait_percent);
            $this->assertEquals(10, $item->ait_amount);
            $this->assertEquals(20, $item->vat_amount);
            $this->assertEquals(230, $item->line_amount);
        }
    }

    public function test_bill_tds_amount_is_the_sum_of_row_tds_amounts(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([
                $this->itemRow(['disc_percent' => 0, 'price' => 33.33, 'd_qty' => 1]),
                $this->itemRow(['disc_percent' => 0, 'price' => 33.33, 'd_qty' => 1, 'item_code' => 'VAN2']),
            ], ['tds_percent' => 5])
        )->assertOk();

        $bill = NasFreightsCustomerBill::with('items')->firstOrFail();

        $this->assertEquals(1.67, $bill->items->first()->tds_amount);
        $this->assertEquals(3.34, $bill->tds_amount);
    }

    public function test_zero_rates_leave_line_amount_at_price_base(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()], ['ait_percent' => 0, 'vat_percent' => 0, 'tds_percent' => 0])
        )->assertOk();

        $bill = NasFreightsCustomerBill::with('items')->firstOrFail();

        $this->assertEquals(180, $bill->items->first()->line_amount);
        $this->assertEquals(180, $bill->sub_total);
        $this->assertEquals(0, $bill->ait_amount);
        $this->assertEquals(0, $bill->vat_amount);
        $this->assertEquals(230, $bill->total_amount);
    }

    public function test_store_rejects_negative_rates(): void
    {
        foreach (['ait_percent', 'vat_percent', 'tds_percent'] as $field) {
            $this->withFreightSession()->postJson(
                route('nas-freights.customer-bills.store'),
                $this->billPayload([$this->itemRow()], [$field => -1])
            )->assertStatus(422)->assertJsonValidationErrors($field);
        }

        $this->assertSame(0, NasFreightsCustomerBill::count());
    }

    public function test_update_recalculates_rows_and_totals_from_new_master_rates(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        $this->withFreightSession()->putJson(
            route('nas-freights.customer-bills.update', $bill->id),
            $this->billPayload([$this->itemRow()], [
                'ait_percent' => 3,
                'vat_percent' => 15,
                'tds_percent' => 0,
            ])
        )->assertOk();

        // price base 180: AIT 3% = 5.40, VAT 15% = 27, line = 212.40
        $bill->refresh()->load('items');

        $this->assertCount(1, $bill->items);
        $this->assertEquals(5.4, $bill->items->first()->ait_amount);
        $this->assertEquals(27, $bill->items->first()->vat_amount);
        $this->assertEquals(212.4, $bill->items->first()->line_amount);
        $this->assertEquals(3, $bill->ait_percent);
        $this->assertEquals(5.4, $bill->ait_amount);
        $this->assertEquals(15, $bill->vat_percent);
        $this->assertEquals(27, $bill->vat_amount);
        $this->assertEquals(212.4, $bill->sub_total);
        $this->assertEquals(262.4, $bill->total_amount);
    }

    public function test_vat_on_ait_defaults_to_no_and_vat_stays_on_price_base(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        $this->assertFalse($bill->vat_on_ait);
        $this->assertEquals(18, $bill->vat_amount);
    }

    public function test_store_with_vat_on_ait_charges_vat_on_price_base_plus_ait(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()], ['vat_on_ait' => 1])
        )->assertOk();

        // price base 180, AIT 5% = 9, VAT 10% of (180 + 9) = 18.90, line = 207.90
        $bill = NasFreightsCustomerBill::with('items')->firstOrFail();

        $this->assertTrue($bill->vat_on_ait);
        $this->assertEquals(9, $bill->ait_amount);
        $this->assertEquals(18.9, $bill->items->first()->vat_amount);
        $this->assertEquals(207.9, $bill->items->first()->line_amount);
        $this->assertEquals(18.9, $bill->vat_amount);
        $this->assertEquals(207.9, $bill->sub_total);
        // + demurrage 50 + TDS 3.60
        $this->assertEquals(261.5, $bill->total_amount);
    }

    public function test_vat_on_ait_with_zero_ait_matches_price_base_vat(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()], ['vat_on_ait' => 1, 'ait_percent' => 0])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        $this->assertTrue($bill->vat_on_ait);
        $this->assertEquals(18, $bill->vat_amount);
    }

    public function test_update_can_switch_vat_on_ait_and_recalculates(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        $this->withFreightSession()->putJson(
            route('nas-freights.customer-bills.update', $bill->id),
            $this->billPayload([$this->itemRow()], ['vat_on_ait' => 1])
        )->assertOk();

        $bill->refresh();
        $this->assertTrue($bill->vat_on_ait);
        $this->assertEquals(18.9, $bill->vat_amount);

        $this->withFreightSession()->putJson(
            route('nas-freights.customer-bills.update', $bill->id),
            $this->billPayload([$this->itemRow()], ['vat_on_ait' => 0])
        )->assertOk();

        $bill->refresh();
        $this->assertFalse($bill->vat_on_ait);
        $this->assertEquals(18, $bill->vat_amount);
    }

    public function test_store_rejects_invalid_vat_on_ait(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([$this->itemRow()], ['vat_on_ait' => 'maybe'])
        )->assertStatus(422)->assertJsonValidationErrors('vat_on_ait');

        $this->assertSame(0, NasFreightsCustomerBill::count());
    }

    public function test_store_requires_at_least_one_item(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload([])
        )->assertStatus(422);
    }
}

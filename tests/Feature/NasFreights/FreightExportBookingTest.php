<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreightExportBookingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::connection()->getPdo()->sqliteCreateFunction(
            'REGEXP',
            fn ($pattern, $value) => (int) preg_match('/'.$pattern.'/', (string) $value),
            2
        );

        $this->createSchema();
        $this->seedUserAndCompany();
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
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('customer_id')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_freights_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_overseas_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('agent_code')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('carrier_code')->nullable();
            $table->string('scac_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_container_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_package_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_no')->unique();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->date('rfq_date');
            $table->date('valid_until')->nullable();
            $table->string('type');
            $table->string('service_type')->nullable();
            $table->string('incoterms')->nullable();
            $table->string('currency')->nullable();
            $table->string('pol')->nullable();
            $table->string('pod')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->text('commodity_description')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Draft');
            $table->string('lost_reason')->nullable();
            $table->unsignedBigInteger('converted_freight_booking_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->unsignedBigInteger('overseas_agent_id')->nullable();
            $table->unsignedBigInteger('shipping_carrier_id')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_rfq_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->string('item_type');
            $table->string('container_size')->nullable();
            $table->string('package_type')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('commodity')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->string('weight_unit')->default('KG');
            $table->decimal('volume_cbm', 10, 3)->nullable();
            $table->decimal('cargo_value', 14, 2)->nullable();
            $table->string('country_of_origin')->nullable();
            $table->boolean('is_dangerous_goods')->default(false);
            $table->string('special_handling')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('export_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('rfq_id')->nullable();
            $table->string('rfq_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('party_bill_ref_no')->nullable();
            $table->date('party_bill_date')->nullable();
            $table->string('party_invoice_no')->nullable();
            $table->date('party_invoice_date')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->unsignedBigInteger('overseas_agent_id')->nullable();
            $table->unsignedBigInteger('shipping_carrier_id')->nullable();
            $table->date('booking_date');
            $table->string('service_type');
            $table->string('incoterms')->nullable();
            $table->string('currency')->default('BDT');
            $table->string('pol')->nullable();
            $table->string('pod')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->text('commodity_description')->nullable();
            $table->json('hs_codes')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_no')->nullable();
            $table->string('export_bl_no')->nullable();
            $table->string('booking_note_no')->nullable();
            $table->date('bl_date')->nullable();
            $table->string('exp_no')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->decimal('transport_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('item_type');
            $table->string('container_size')->nullable();
            $table->string('container_no')->nullable();
            $table->string('seal_no')->nullable();
            $table->string('package_type')->nullable();
            $table->unsignedInteger('package_qty')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('commodity')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->string('weight_unit')->default('KG');
            $table->decimal('volume_cbm', 10, 3)->nullable();
            $table->string('country_of_origin')->nullable();
            $table->boolean('is_dangerous_goods')->default(false);
            $table->string('special_handling')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_booking_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->unsignedBigInteger('export_booking_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bill_type');
            $table->date('bill_date')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('exchange_rate', 14, 4)->default(1);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('total_bdt_amount', 14, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_booking_transport_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 12, 2)->default(0);
            $table->decimal('supplier_rate', 12, 2)->default(0);
            $table->decimal('customer_rate', 12, 2)->default(0);
            $table->unsignedInteger('demurrage_days')->default(0);
            $table->decimal('cus_demurrage_charge', 12, 2)->default(0);
            $table->decimal('sup_demurrage_charge', 12, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->nullable();
            $table->string('booking_type')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('booking_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value_usd', 14, 2)->nullable();
            $table->string('bl_no')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('date')->nullable();
            $table->decimal('total_expense_amount', 14, 2)->default(0);
            $table->decimal('total_approved_amount', 14, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('entry_by')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freight_booking_expense_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->string('receiptable')->nullable();
            $table->decimal('expense_amount', 14, 2)->default(0);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->date('expense_date')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    private function seedUserAndCompany(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('cnf');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $user = User::create([
            'name'      => 'Super User',
            'username'  => 'super',
            'email'     => 'super@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]);
        $user->forceFill(['is_super' => true])->save();

        $this->actingAs($user);
    }

    private function sessionWithBranch(): array
    {
        return [
            'active_company_id'        => 1,
            'active_company_slug'      => 'nas-freights',
            'active_company_name'      => 'NAS Freights',
            'active_company_type'      => 'freight',
            'nas_freights_branch_id'   => 1,
            'nas_freights_branch_name' => 'Corporate',
            'nas_freights_branch_code' => 'COR',
        ];
    }

    private function createCustomer(): int
    {
        return DB::table('nas_freights_customers')->insertGetId([
            'customer_id' => 'CUS-0001',
            'name'        => 'ABC Exports',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function createBooking(array $attributes = []): NasFreightsFreightExportBooking
    {
        return NasFreightsFreightExportBooking::create(array_merge([
            'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'FCL',
        ], $attributes));
    }

    public function test_store_creates_export_booking_with_bl_and_booking_note(): void
    {
        $this->withSession($this->sessionWithBranch());

        $response = $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date'       => '2026-08-29',
            'service_type'       => 'FCL',
            'customer_id'        => $this->createCustomer(),
            'party_bill_ref_no'  => 'PB-0001',
            'party_bill_date'    => '2026-08-20',
            'party_invoice_no'   => 'PI-0001',
            'party_invoice_date' => '2026-08-22',
            'pol'                => 'Chittagong',
            'pod'                => 'Hamburg',
            'export_bl_no'       => 'CHT-456',
            'booking_note_no'    => 'BN-7788',
            'etd'                => '2026-09-05',
            'eta'                => '2026-09-25',
            'status'             => 'Confirmed',
            'items'              => [[
                'item_type'         => 'container',
                'container_size'    => '40HC',
                'container_no'      => 'MAEU9988776',
                'seal_no'           => 'SEAL5544',
                'quantity'          => 1,
                'gross_weight'      => 9800,
                'weight_unit'       => 'KG',
                'country_of_origin' => 'BD',
            ]],
        ]);

        $response->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $booking = NasFreightsFreightExportBooking::first();
        $this->assertNotNull($booking);
        $this->assertStringStartsWith('FEB-', $booking->export_booking_no);
        $this->assertSame('CHT-456', $booking->export_bl_no);
        $this->assertSame('BN-7788', $booking->booking_note_no);
        $this->assertSame('Confirmed', $booking->status);
        $this->assertSame(1, $booking->branch_id);
        $this->assertSame('PB-0001', $booking->party_bill_ref_no);
        $this->assertSame('2026-08-20', $booking->party_bill_date->toDateString());
        $this->assertSame('PI-0001', $booking->party_invoice_no);
        $this->assertSame('2026-08-22', $booking->party_invoice_date->toDateString());
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('MAEU9988776', $booking->items()->first()->container_no);
        $this->assertSame('SEAL5544', $booking->items()->first()->seal_no);
        $this->assertSame('BD', $booking->items()->first()->country_of_origin);
    }

    public function test_store_saves_multiple_hs_codes_trimmed_and_deduplicated(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'hs_codes'     => ['  6109.10 ', '', '6109.10', '4203.21'],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $this->assertSame(['6109.10', '4203.21'], NasFreightsFreightExportBooking::first()->hs_codes);
    }

    public function test_store_saves_null_hs_codes_when_all_inputs_blank(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'hs_codes'     => ['', '  '],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $this->assertNull(NasFreightsFreightExportBooking::first()->hs_codes);
    }

    public function test_store_ignores_item_level_hs_code_and_commodity(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'items'        => [[
                'item_type' => 'package',
                'hs_code'   => '610910',
                'commodity' => 'GARMENTS',
                'quantity'  => 2,
            ]],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $item = NasFreightsFreightExportBooking::first()->items()->first();
        $this->assertNull($item->hs_code);
        $this->assertNull($item->commodity);
    }

    public function test_store_rejects_invalid_party_dates_and_hs_codes(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date'       => '2026-08-29',
            'service_type'       => 'FCL',
            'customer_id'        => $this->createCustomer(),
            'party_bill_date'    => 'not-a-date',
            'party_invoice_date' => 'also-bad',
            'hs_codes'           => [str_repeat('9', 51)],
        ])->assertSessionHasErrors(['party_bill_date', 'party_invoice_date', 'hs_codes.0']);

        $this->assertSame(0, NasFreightsFreightExportBooking::count());
    }

    public function test_export_booking_numbers_increment(): void
    {
        $first = NasFreightsFreightExportBooking::create([
            'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'LCL',
        ]);
        $second = NasFreightsFreightExportBooking::create([
            'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'LCL',
        ]);

        $this->assertNotSame($first->export_booking_no, $second->export_booking_no);
        $this->assertStringStartsWith('FEB-', $second->export_booking_no);
    }

    public function test_update_modifies_export_booking_and_rewrites_items(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'booking_note_no' => 'OLD-BN',
            'hs_codes'        => ['1111.11'],
        ]);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'customer_id'        => $this->createCustomer(),
            'booking_date'       => '2026-08-29',
            'service_type'       => 'FCL',
            'booking_note_no'    => 'NEW-BN',
            'party_invoice_date' => '2026-09-01',
            'hs_codes'           => ['2222.22', '3333.33'],
            'items'              => [[
                'item_type'    => 'package',
                'package_type' => 'Carton',
                'quantity'     => 8,
            ]],
        ])->assertRedirect();

        $booking = $booking->fresh();
        $this->assertSame('NEW-BN', $booking->booking_note_no);
        $this->assertSame('2026-09-01', $booking->party_invoice_date->toDateString());
        $this->assertSame(['2222.22', '3333.33'], $booking->hs_codes);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('Carton', $booking->items()->first()->package_type);
    }

    public function test_update_clears_hs_codes_when_none_submitted(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['1111.11']]);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
        ])->assertRedirect();

        $this->assertNull($booking->fresh()->hs_codes);
    }

    public function test_edit_page_shows_party_dates_hs_codes_and_no_item_hs_code_or_commodity(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'party_bill_ref_no'  => 'PB-0001',
            'party_bill_date'    => '2026-08-20',
            'party_invoice_no'   => 'PI-0001',
            'party_invoice_date' => '2026-08-22',
            'hs_codes'           => ['6109.10', '4203.21'],
        ]);

        $response = $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id));

        $response->assertOk()
            ->assertSeeInOrder(['Party Bill Ref No', 'Party Bill Ref Date', 'Party Invoice No', 'Party Invoice Date'])
            ->assertSee('value="2026-08-20"', false)
            ->assertSee('value="2026-08-22"', false)
            ->assertSeeInOrder(['Commodity Description', 'HS Code'])
            ->assertDontSee('Add More')
            ->assertSee('id="hsCodeList"', false)
            ->assertSee('hs-code-action', false)
            ->assertSee('name="hs_codes[]"', false)
            ->assertSee('["6109.10","4203.21"]', false)
            ->assertDontSee('[hs_code]', false)
            ->assertDontSee('[commodity]', false);
    }

    public function test_store_saves_package_qty_and_unit_for_container_rows(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'items'        => [[
                'item_type'      => 'container',
                'container_size' => '40GP',
                'container_no'   => 'MAEU9988776',
                'quantity'       => 1,
                'package_qty'    => 500,
                'package_unit'   => 'Carton',
            ]],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $item = NasFreightsFreightExportBooking::first()->items()->first();
        $this->assertSame('40GP', $item->container_size);
        $this->assertSame(1, $item->quantity);
        $this->assertSame(500, $item->package_qty);
        $this->assertSame('Carton', $item->package_type);
    }

    public function test_store_ignores_package_qty_for_package_rows_and_keeps_package_type(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'LCL',
            'customer_id'  => $this->createCustomer(),
            'items'        => [[
                'item_type'    => 'package',
                'package_type' => 'Pallet',
                'quantity'     => 12,
                'package_qty'  => 999,
                'package_unit' => 'Carton',
            ]],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $item = NasFreightsFreightExportBooking::first()->items()->first();
        $this->assertSame('Pallet', $item->package_type);
        $this->assertSame(12, $item->quantity);
        $this->assertNull($item->package_qty);
    }

    public function test_store_allows_container_row_without_package_qty(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'items'        => [[
                'item_type'      => 'container',
                'container_size' => '20GP',
                'quantity'       => 1,
                'package_qty'    => '',
                'package_unit'   => '',
            ]],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $item = NasFreightsFreightExportBooking::first()->items()->first();
        $this->assertNull($item->package_qty);
        $this->assertNull($item->package_type);
    }

    public function test_store_rejects_invalid_package_qty(): void
    {
        $this->withSession($this->sessionWithBranch());

        $customerId = $this->createCustomer();

        foreach ([0, -5, 'abc', 1.5] as $bad) {
            $this->post(route('nas-freights.freight-export-bookings.store'), [
                'booking_date' => '2026-08-29',
                'service_type' => 'FCL',
                'customer_id'  => $customerId,
                'items'        => [['item_type' => 'container', 'package_qty' => $bad]],
            ])->assertSessionHasErrors('items.0.package_qty');
        }

        $this->assertSame(0, NasFreightsFreightExportBooking::count());
    }

    public function test_update_rewrites_package_qty_and_unit(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create(['item_type' => 'container', 'package_qty' => 100, 'package_type' => 'Box']);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'items'        => [[
                'item_type'    => 'container',
                'package_qty'  => 250,
                'package_unit' => 'Carton',
            ]],
        ])->assertRedirect();

        $item = $booking->items()->first();
        $this->assertSame(250, $item->package_qty);
        $this->assertSame('Carton', $item->package_type);
    }

    public function test_edit_page_prefills_package_qty_and_unit_and_renders_columns(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create([
            'item_type'      => 'container',
            'container_size' => '40GP',
            'package_qty'    => 500,
            'package_type'   => 'Carton',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSeeInOrder(['Container Size / Pkg', 'Pkg Qty', 'Pkg Unit', 'Container No'])
            ->assertSee('name="items[0][package_qty]"', false)
            ->assertSee('name="items[0][package_unit]"', false)
            ->assertSee('"package_qty":500', false)
            ->assertSee('"package_unit":"Carton"', false)
            ->assertSee('"package_type":null', false);
    }

    public function test_show_page_displays_package_qty_and_unit_for_container_rows(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create([
            'item_type'      => 'container',
            'container_size' => '40GP',
            'package_qty'    => 1500,
            'package_type'   => 'Carton',
        ]);
        $booking->items()->create(['item_type' => 'package', 'package_type' => 'Pallet', 'quantity' => 4]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSeeInOrder(['Pkg Qty', 'Pkg Unit', '1,500', 'Carton'])
            ->assertDontSee('Pkg Qty / Unit');
    }

    public function test_edit_page_shows_update_button_above_bills_expense_and_transport(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['transport_amount' => 5000]);

        DB::table('nas_freights_freight_export_booking_transport_items')->insert([
            'export_booking_id' => $booking->id,
            'cover_van_no'      => 'DHK-METRO-1234',
            'amount'            => 5000,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $headId = DB::table('nas_freights_expense_heads')->insertGetId([
            'name' => 'Port Handling', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $expenseId = DB::table('nas_freights_freight_booking_expenses')->insertGetId([
            'booking_type'         => 'export', 'booking_id' => $booking->id,
            'total_expense_amount' => 1200, 'total_approved_amount' => 1000,
            'created_at'           => now(), 'updated_at' => now(),
        ]);
        DB::table('nas_freights_freight_booking_expense_items')->insert([
            'freight_booking_expense_id' => $expenseId, 'expense_head_id' => $headId,
            'receiptable'                => 'Yes', 'expense_amount' => 1200, 'approved_amount' => 1000,
            'created_at'                 => now(), 'updated_at' => now(),
        ]);

        $response = $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Cancel', 'fa fa-save me-1"></i> Update', '</form>',
                'Bills', 'Expense Details', 'Port Handling',
                'Cover Van / Transport Details', 'DHK-METRO-1234',
            ], false);

        $html = $response->getContent();
        $formEnd = strpos($html, '</form>', strpos($html, 'name="booking_date"'));
        $this->assertLessThan($formEnd, strpos($html, 'fa fa-save me-1"></i> Update'));
        $this->assertGreaterThan($formEnd, strpos($html, 'Expense Details'));
        $this->assertGreaterThan($formEnd, strpos($html, 'Cover Van / Transport Details'));
        $this->assertGreaterThan($formEnd, strpos($html, 'id="bookingBillsSection"'));
    }

    public function test_edit_page_says_no_bill_created_when_booking_has_no_bills(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('No bill created yet')
            ->assertSee('Create Bill')
            ->assertSee(route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $booking->id]), false)
            ->assertDontSee('bills created');
    }

    public function test_edit_page_lists_existing_bill_and_marks_missing_type_not_created(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0001',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-09-10',
            'currency'         => 'USD',
            'exchange_rate'    => 120,
            'total_amount'     => 100,
            'total_bdt_amount' => 12000,
            'status'           => 'Confirmed',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('1 of 2 bills created')
            ->assertDontSee('No bill created yet')
            ->assertSee('FEB-BILL-2026-0001')
            ->assertSee('10 Sep 2026')
            ->assertSee('12,000.00')
            ->assertSee('100.00 USD')
            ->assertSee('Confirmed')
            ->assertSee('Not created')
            ->assertSee(route('nas-freights.freight-export-booking-bills.show', $bill->id), false)
            ->assertSee(route('nas-freights.freight-export-booking-bills.print', $bill->id), false)
            ->assertSee(route('nas-freights.freight-export-booking-bills.edit', $bill->id), false)
            ->assertSee('Create Bill');
    }

    public function test_edit_page_hides_create_bill_when_both_bills_exist(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        foreach (['Customer' => 'FEB-BILL-2026-0001', 'Overseas Agent' => 'FEB-BILL-2026-0002'] as $type => $no) {
            $booking->bills()->create([
                'bill_no'  => $no, 'bill_type' => $type, 'bill_date' => '2026-09-10',
                'currency' => 'BDT', 'total_amount' => 500, 'total_bdt_amount' => 500, 'status' => 'Draft',
            ]);
        }

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('2 of 2 bills created')
            ->assertSee('FEB-BILL-2026-0001')
            ->assertSee('FEB-BILL-2026-0002')
            ->assertDontSee('Not created')
            ->assertDontSee('Create Bill');
    }

    public function test_create_page_does_not_show_bills_expense_or_transport_sections(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->get(route('nas-freights.freight-export-bookings.create'))
            ->assertOk()
            ->assertDontSee('id="bookingBillsSection"', false)
            ->assertDontSee('Expense Details')
            ->assertDontSee('Cover Van / Transport Details');
    }

    public function test_pages_use_booking_job_wording(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['booking_note_no' => 'BN-1']);

        $this->get(route('nas-freights.freight-export-bookings.index'))
            ->assertOk()
            ->assertSee('Freight Export Booking/Jobs')
            ->assertSee('New Booking/Job')
            ->assertSee('Booking/Job No');

        $this->get(route('nas-freights.freight-export-bookings.create'))
            ->assertOk()
            ->assertSee('Freight Export Booking/Job Entry')
            ->assertSee('Booking/Job Information')
            ->assertSee('Booking/Job No')
            ->assertSee('Booking/Job Date')
            ->assertSee('Booking Note No');

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('Freight Export Booking/Job')
            ->assertSee('Booking/Job Information')
            ->assertSee('Booking/Job No')
            ->assertSee('Booking/Job Date')
            ->assertSee('Booking Note No');

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('Freight Export Booking/Job Entry');
    }

    public function test_store_saves_shipping_and_trade_document_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'bl_date'      => '2026-09-01',
            'exp_no'       => 'EXP-2026-0001',
            'exp_date'     => '2026-09-02',
            'invoice_no'   => 'INV-2026-0001',
            'invoice_date' => '2026-09-03',
            'lc_no'        => 'LC-2026-0001',
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $booking = NasFreightsFreightExportBooking::first();
        $this->assertSame('2026-09-01', $booking->bl_date->toDateString());
        $this->assertSame('EXP-2026-0001', $booking->exp_no);
        $this->assertSame('2026-09-02', $booking->exp_date->toDateString());
        $this->assertSame('INV-2026-0001', $booking->invoice_no);
        $this->assertSame('2026-09-03', $booking->invoice_date->toDateString());
        $this->assertSame('LC-2026-0001', $booking->lc_no);
    }

    public function test_update_saves_and_clears_shipping_and_trade_document_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['exp_no' => 'OLD-EXP', 'lc_no' => 'OLD-LC']);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'exp_no'       => 'NEW-EXP',
            'lc_no'        => '',
        ])->assertRedirect();

        $booking = $booking->fresh();
        $this->assertSame('NEW-EXP', $booking->exp_no);
        $this->assertNull($booking->lc_no);
    }

    public function test_store_rejects_invalid_document_dates(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'bl_date'      => 'nope',
            'exp_date'     => 'nope',
            'invoice_date' => 'nope',
        ])->assertSessionHasErrors(['bl_date', 'exp_date', 'invoice_date']);
    }

    public function test_edit_page_prefills_document_fields_without_group_headings(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'bl_date'      => '2026-09-01',
            'exp_no'       => 'EXP-2026-0001',
            'invoice_no'   => 'INV-2026-0001',
            'invoice_date' => '2026-09-03',
            'lc_no'        => 'LC-2026-0001',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Booking/Job No', 'Customer (Exporter)', 'Party Bill Ref No', 'Shipping Carrier',
                'Vessel Name', 'Commodity Description', 'HS Code', 'Export B/L No', 'EXP No', 'Remarks',
            ], false)
            ->assertDontSee('fb-group-title', false)
            ->assertSee('value="EXP-2026-0001"', false)
            ->assertSee('value="INV-2026-0001"', false)
            ->assertSee('value="LC-2026-0001"', false)
            ->assertSee('value="2026-09-01"', false)
            ->assertSee('value="2026-09-03"', false)
            ->assertSee('<label class="fb-label" for="fbBookingDate">', false)
            ->assertSee('id="fbBookingDate" name="booking_date"', false);
    }

    private function seedExpenseAndTransport(NasFreightsFreightExportBooking $booking): void
    {
        DB::table('nas_freights_freight_export_booking_transport_items')->insert([
            'export_booking_id' => $booking->id,
            'cover_van_no'      => 'DHK-METRO-1234',
            'amount'            => 5000,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $headId = DB::table('nas_freights_expense_heads')->insertGetId([
            'name' => 'Port Handling', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $expenseId = DB::table('nas_freights_freight_booking_expenses')->insertGetId([
            'booking_type'         => 'export', 'booking_id' => $booking->id,
            'total_expense_amount' => 1200, 'total_approved_amount' => 1000,
            'created_at'           => now(), 'updated_at' => now(),
        ]);
        DB::table('nas_freights_freight_booking_expense_items')->insert([
            'freight_booking_expense_id' => $expenseId, 'expense_head_id' => $headId,
            'receiptable'                => 'Yes', 'expense_amount' => 1200, 'approved_amount' => 1000,
            'created_at'                 => now(), 'updated_at' => now(),
        ]);
    }

    public function test_edit_page_tables_carry_responsive_card_markup(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['transport_amount' => 5000]);
        $booking->bills()->create([
            'bill_no'  => 'FEB-BILL-2026-0001', 'bill_type' => 'Customer', 'bill_date' => '2026-09-10',
            'currency' => 'BDT', 'total_amount' => 500, 'total_bdt_amount' => 500, 'status' => 'Draft',
        ]);
        $this->seedExpenseAndTransport($booking);

        $response = $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))->assertOk();

        $response
            ->assertSee('sub-table-wrap sub-table-wrap--bills', false)
            ->assertSee('sub-table-wrap sub-table-wrap--expense', false)
            ->assertSee('sub-table-wrap sub-table-wrap--transport', false)
            ->assertSee('data-label="Bill No"', false)
            ->assertSee('data-label="Expense Amount"', false)
            ->assertSee('data-label="Cover Van No"', false)
            ->assertSee('data-label="Total Transport Amount"', false)
            ->assertSee('@container (max-width: 1080px)', false)
            ->assertDontSee('min-width:900px', false)
            ->assertDontSee('min-width:700px', false);

        $this->assertSame(1, substr_count($response->getContent(), '.sub-table-wrap {'));
    }

    public function test_show_page_renders_expense_and_transport_through_shared_partials(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['transport_amount' => 5000]);
        $this->seedExpenseAndTransport($booking);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('Expense Details')
            ->assertSee('Port Handling')
            ->assertSee('Cover Van / Transport Details')
            ->assertSee('DHK-METRO-1234')
            ->assertSee('sub-table-wrap sub-table-wrap--expense', false)
            ->assertSee('sub-table-wrap sub-table-wrap--transport', false)
            ->assertSee('id="bookingBillsSection"', false)
            ->assertSeeInOrder(['id="bookingBillsSection"', 'Expense Details', 'Cover Van / Transport Details'], false);
    }

    public function test_show_page_follows_create_page_field_order(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'party_bill_ref_no'  => 'PB-77',
            'party_invoice_date' => '2026-09-04',
            'bl_date'            => '2026-09-01',
            'exp_no'             => 'EXP-2026-0001',
            'invoice_no'         => 'INV-2026-0001',
            'lc_no'              => 'LC-2026-0001',
            'remarks'            => 'Handle with care',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Booking/Job No', 'Booking/Job Date', 'Service Type/Mode', 'Currency', 'Incoterms', 'Status',
                'Customer (Exporter)', 'Overseas Agent / Consignee', 'Salesperson',
                'Party Bill Ref No', 'Party Bill Ref Date', 'Party Invoice No', 'Party Invoice Date',
                'Shipping Carrier', 'Port of Loading (POL)', 'Port of Discharge (POD)', 'Place of Receipt',
                'Vessel Name', 'Voyage No', 'ETD / Flight Date', 'ETA',
                'Commodity Description', 'HS Code',
                'Export B/L No', 'B/L Date', 'Booking Note No', 'LC No',
                'EXP No', 'EXP Date', 'Invoice No', 'Invoice Date', 'Remarks',
                'Cargo / Shipment Details',
            ])
            ->assertSee('PB-77')
            ->assertSee('04 Sep 2026')
            ->assertSee('01 Sep 2026')
            ->assertSee('EXP-2026-0001')
            ->assertSee('INV-2026-0001')
            ->assertSee('LC-2026-0001')
            ->assertSee('Handle with care');
    }

    public function test_show_page_cargo_columns_follow_create_page_order(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create([
            'item_type'    => 'container', 'container_size' => '40GP', 'container_no' => 'MSCU1234567',
            'seal_no'      => 'SL1', 'package_qty' => 10, 'package_type' => 'Carton', 'quantity' => 2,
            'gross_weight' => 1500, 'weight_unit' => 'KG', 'volume_cbm' => 3.5,
        ]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Item Type', 'Qty', 'Container Size / Pkg', 'Pkg Qty', 'Pkg Unit', 'Container No',
                'Seal No', 'Weight', 'Unit', 'CBM', 'Country of Origin', 'DG', 'Special Handling',
                '40GP', 'Carton', 'MSCU1234567', 'SL1', '1,500.00', 'KG', '3.500',
            ]);
    }

    public function test_show_page_says_no_bill_created_when_booking_has_no_bills(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('No bill created yet')
            ->assertSee('Create Bill')
            ->assertSee(route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $booking->id]), false)
            ->assertDontSee('bills created');
    }

    public function test_show_page_lists_existing_bill_like_edit_page(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0001',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-09-10',
            'currency'         => 'USD',
            'exchange_rate'    => 120,
            'total_amount'     => 100,
            'total_bdt_amount' => 12000,
            'status'           => 'Confirmed',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('1 of 2 bills created')
            ->assertSee('FEB-BILL-2026-0001')
            ->assertSee('10 Sep 2026')
            ->assertSee('12,000.00')
            ->assertSee('100.00 USD')
            ->assertSee('Not created')
            ->assertSee(route('nas-freights.freight-export-booking-bills.show', $bill->id), false)
            ->assertSee(route('nas-freights.freight-export-booking-bills.print', $bill->id), false)
            ->assertSee(route('nas-freights.freight-export-booking-bills.edit', $bill->id), false);
    }

    public function test_create_page_renders_hs_code_and_party_fields_without_item_hs_code_or_commodity(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->get(route('nas-freights.freight-export-bookings.create'))
            ->assertOk()
            ->assertSee('Party Bill Ref Date')
            ->assertSee('Party Invoice Date')
            ->assertSee('id="hsCodeList"', false)
            ->assertDontSee('Add More')
            ->assertDontSee('[hs_code]', false)
            ->assertDontSee('[commodity]', false);
    }

    public function test_show_page_lists_hs_codes_without_cargo_hs_and_commodity_columns(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['6109.10', '4203.21']]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('6109.10')
            ->assertSee('4203.21')
            ->assertDontSee('<th>HS Code</th>', false)
            ->assertDontSee('<th>Commodity</th>', false);
    }

    public function test_destroy_deletes_export_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = NasFreightsFreightExportBooking::create([
            'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'Air',
        ]);

        $this->deleteJson(route('nas-freights.freight-export-bookings.destroy', $booking->id))
            ->assertOk()
            ->assertJsonPath('message', 'Freight Export Booking/Job '.$booking->export_booking_no.' deleted.');

        $this->assertDatabaseCount('nas_freights_freight_export_bookings', 0);
    }
}

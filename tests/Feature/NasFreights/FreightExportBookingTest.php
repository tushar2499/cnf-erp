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
            $table->text('address')->nullable();
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

        Schema::create('nas_freights_freight_export_booking_party_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('invoice_no');
            $table->date('invoice_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
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
            $table->string('bill_prefix_ref', 100)->nullable();
            $table->string('bill_prefix_job', 100)->nullable();
            $table->date('bill_date')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('exchange_rate', 14, 4)->default(1);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('total_bdt_amount', 14, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_booking_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('name');
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('amount_bdt', 14, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
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

        Schema::create('nas_freights_bill_item_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
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

    /**
     * @return array<string, string>
     */
    private function partyBillFields(): array
    {
        return [
            'party_bill_ref_no' => 'PB-DEFAULT',
            'party_bill_date'   => '2026-08-20',
        ];
    }

    public function test_store_requires_party_bill_ref_no_and_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
        ])->assertSessionHasErrors([
            'party_bill_ref_no' => 'The Party Bill Ref No field is required.',
            'party_bill_date'   => 'The Party Bill Ref Date field is required.',
        ]);

        $this->assertSame(0, NasFreightsFreightExportBooking::count());
    }

    public function test_store_rejects_blank_party_bill_ref_no_and_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => $this->createCustomer(),
            'party_bill_ref_no' => '   ',
            'party_bill_date'   => '',
        ])->assertSessionHasErrors(['party_bill_ref_no', 'party_bill_date']);

        $this->assertSame(0, NasFreightsFreightExportBooking::count());
    }

    public function test_update_requires_party_bill_ref_no_and_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'party_bill_ref_no' => 'PB-OLD',
            'party_bill_date'   => '2026-08-01',
        ]);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
        ])->assertSessionHasErrors(['party_bill_ref_no', 'party_bill_date']);

        $this->assertSame('PB-OLD', $booking->fresh()->party_bill_ref_no);
    }

    public function test_bill_print_shows_party_bill_ref_and_date_in_header_not_in_shipment_table(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'party_bill_ref_no' => '122122',
            'party_bill_date'   => '2026-11-09',
        ]);
        $booking->partyInvoices()->create(['invoice_no' => '222222221', 'invoice_date' => '2026-11-09']);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0002',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('BILL REF NO: 122122', false)
            ->assertSee('DATE: 09.11.2026', false)
            ->assertDontSee('BILL REF NO: FEB-BILL-2026-0002', false)
            ->assertDontSee('DATE: 08.11.2026', false)
            ->assertDontSee('PARTY BILL REF NO')
            ->assertDontSee('PARTY BILL DATE')
            ->assertSee('PARTY INVOICE NO')
            ->assertSee('PARTY INVOICE DATE');
    }

    public function test_bill_print_shows_cargo_totals_under_commodity_with_weight_in_kgs(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['commodity_description' => 'MEDICINE']);
        $booking->items()->create([
            'item_type'    => 'container',
            'quantity'     => 1,
            'package_qty'  => 12,
            'package_type' => 'Carton',
            'gross_weight' => 9800,
            'weight_unit'  => 'KG',
            'volume_cbm'   => 33.5,
        ]);
        $booking->items()->create([
            'item_type'    => 'package',
            'quantity'     => 10,
            'package_type' => 'carton',
            'gross_weight' => 1.2,
            'weight_unit'  => 'MT',
            'volume_cbm'   => 2.25,
        ]);
        $booking->items()->create([
            'item_type'    => 'package',
            'quantity'     => 5,
            'package_type' => 'Bag',
        ]);
        $booking->items()->create([
            'item_type'    => 'container',
            'quantity'     => 2,
            'package_type' => 'Crate',
        ]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0004',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSeeInOrder(['COMMODITY', 'MEDICINE', 'QUANTITY', '22 CARTON', '5 BAG', '2 CRATE', 'GROSS WEIGHT', '11,000 KGS', 'CBM', '36'], false)
            ->assertDontSee('11,000.00')
            ->assertDontSee('35.750');
    }

    public function test_bill_print_quantity_uses_package_qty_else_quantity_per_package_type(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create(['item_type' => 'container', 'quantity' => 1, 'package_qty' => 12, 'package_type' => 'Carton']);
        $booking->items()->create(['item_type' => 'container', 'quantity' => 1, 'package_qty' => 8, 'package_type' => 'Carton']);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0006',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $html = $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/QUANTITY<\/td>\s*<td class="sep">:<\/td>\s*<td>\s*20 CARTON\s*<\/td>/', $html);
        $this->assertStringNotContainsString('1 CARTON', $html);
    }

    public function test_bill_print_does_not_show_overseas_agent_section_under_ref_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $agentId = DB::table('nas_freights_overseas_agents')->insertGetId([
            'name'       => 'DEREK OFORI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $booking = $this->createBooking(['export_booking_no' => 'EXP-9001', 'overseas_agent_id' => $agentId]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0007',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('REF NO: EXP-9001', false)
            ->assertDontSee('Overseas Agent:');
    }

    public function test_bill_print_hides_cargo_totals_when_booking_has_no_cargo_items(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['commodity_description' => 'MEDICINE']);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0005',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('COMMODITY')
            ->assertDontSee('QUANTITY')
            ->assertDontSee('GROSS WEIGHT')
            ->assertDontSee('KGS')
            ->assertDontSee('CBM');
    }

    public function test_bill_print_falls_back_to_bill_no_and_date_when_booking_has_no_party_bill(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0003',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('BILL REF NO: FEB-BILL-2026-0003', false)
            ->assertSee('DATE: 08.11.2026', false);
    }

    public function test_bill_print_address_keeps_line_breaks_and_wraps_parts_as_units_and_escapes_html(): void
    {
        $this->withSession($this->sessionWithBranch());

        $customerId = DB::table('nas_freights_customers')->insertGetId([
            'customer_id' => 'CUS-0002',
            'name'        => 'Renata PLC (Mirpur)',
            'address'     => "Plot-01, Milk Vita Road, Section-07\nMirpur, Dhaka-1216, <b>Bangladesh</b>.",
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $booking = $this->createBooking(['customer_id' => $customerId]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0013',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $html = $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            '<div class="to-address"><span class="addr-part">Plot-01</span>, <span class="addr-part">Milk Vita Road</span>, <span class="addr-part">Section-07</span>'
            .'<br><span class="addr-part">Mirpur</span>, <span class="addr-part">Dhaka-1216</span>, <span class="addr-part">&lt;b&gt;Bangladesh&lt;/b&gt;.</span></div>',
            $html
        );
        $this->assertStringNotContainsString('<b>Bangladesh</b>', $html);
    }

    public function test_bill_print_omits_address_block_when_party_has_no_address(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['customer_id' => $this->createCustomer()]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0014',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('ABC Exports')
            ->assertDontSee('<div class="to-address">', false);
    }

    /**
     * @return array<int, string>
     */
    private function listedBillJobNos(): array
    {
        $column = fn (string $name) => [
            'data'       => $name,
            'name'       => $name,
            'searchable' => 'false',
            'orderable'  => 'false',
        ];

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('nas-freights.freight-export-booking-bills.index', [
                'draw'    => 1,
                'start'   => 0,
                'length'  => 15,
                'columns' => [
                    $column('DT_RowIndex'),
                    $column('booking_no'),
                ],
            ]))
            ->assertOk();

        return collect($response->json('data'))->pluck('booking_no')->sort()->values()->all();
    }

    public function test_bill_list_shows_booking_job_no_with_bill_prefix_job(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['export_booking_no' => 'NASEXP26007']);
        foreach ([
            ['bill_no' => 'FEB-BILL-2026-0020', 'bill_prefix_job' => null],
            ['bill_no' => 'FEB-BILL-2026-0021', 'bill_prefix_job' => 'JOB-5'],
        ] as $row) {
            $booking->bills()->create($row + [
                'branch_id'        => 1,
                'bill_type'        => 'Customer',
                'bill_date'        => '2026-11-08',
                'currency'         => 'BDT',
                'total_amount'     => 500,
                'total_bdt_amount' => 500,
                'status'           => 'Draft',
            ]);
        }

        $this->assertSame(['NASEXP26007', 'NASEXP26007JOB-5'], $this->listedBillJobNos());
    }

    public function test_bill_print_shows_sb_no_above_mawb_bl_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['invoice_no' => 'SB-2026-0001', 'export_bl_no' => 'BL-555']);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0008',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSeeInOrder(['S.B. NO', 'SB-2026-0001', 'MAWB / B/L NO.', 'BL-555'], false)
            ->assertDontSee('INV NO');
    }

    public function test_bill_print_labels_footer_ref_as_job_ref_no_with_booking_job_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['export_booking_no' => 'NASEXP26007']);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0009',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('JOB REF NO: NASEXP26007', false);
    }

    public function test_bill_print_appends_bill_prefix_ref_and_job_to_booking_values(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'export_booking_no' => 'NASEXP26007',
            'party_bill_ref_no' => '122122',
        ]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0010',
            'bill_type'        => 'Customer',
            'bill_prefix_ref'  => 'PB-77',
            'bill_prefix_job'  => 'JOB-5',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('BILL REF NO: 122122PB-77', false)
            ->assertSee('JOB REF NO: NASEXP26007JOB-5', false);
    }

    public function test_bill_print_prefix_ref_and_job_apply_independently(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'export_booking_no' => 'NASEXP26007',
            'party_bill_ref_no' => '122122',
        ]);
        $onlyJob = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0011',
            'bill_type'        => 'Customer',
            'bill_prefix_job'  => 'JOB-5',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);
        $onlyRef = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0012',
            'bill_type'        => 'Customer',
            'bill_prefix_ref'  => 'PB-77',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $onlyJob->id))
            ->assertOk()
            ->assertSee('BILL REF NO: 122122</span>', false)
            ->assertSee('JOB REF NO: NASEXP26007JOB-5</span>', false);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $onlyRef->id))
            ->assertOk()
            ->assertSee('BILL REF NO: 122122PB-77</span>', false)
            ->assertSee('JOB REF NO: NASEXP26007</span>', false);
    }

    public function test_create_and_edit_pages_mark_party_bill_fields_required(): void
    {
        $this->withSession($this->sessionWithBranch());
        $booking = $this->createBooking([
            'party_bill_ref_no' => 'PB-OLD',
            'party_bill_date'   => '2026-08-01',
        ]);

        foreach ([
            route('nas-freights.freight-export-bookings.create'),
            route('nas-freights.freight-export-bookings.edit', $booking->id),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/for="fbPartyBillRefNo">Party Bill Ref No <span class="text-danger">\*<\/span>/', $html);
            $this->assertMatchesRegularExpression('/for="fbPartyBillRefDate">Party Bill Ref Date <span class="text-danger">\*<\/span>/', $html);
            $this->assertMatchesRegularExpression('/id="fbPartyBillRefNo"[^>]*\srequired/', $html);
            $this->assertMatchesRegularExpression('/id="fbPartyBillRefDate"[^>]*\srequired/', $html);
        }
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
            'party_invoices'     => [['no' => 'PI-0001', 'date' => '2026-08-22']],
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
        $this->assertSame(1, $booking->partyInvoices()->count());
        $this->assertSame('PI-0001', $booking->partyInvoices()->first()->invoice_no);
        $this->assertSame('2026-08-22', $booking->partyInvoices()->first()->invoice_date->toDateString());
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('MAEU9988776', $booking->items()->first()->container_no);
        $this->assertSame('SEAL5544', $booking->items()->first()->seal_no);
        $this->assertSame('BD', $booking->items()->first()->country_of_origin);
    }

    /**
     * @return array<int, string>
     */
    private function listedBookingNos(string $direction): array
    {
        $column = fn (string $name, bool $orderable) => [
            'data'       => $name,
            'name'       => $name,
            'searchable' => 'false',
            'orderable'  => $orderable ? 'true' : 'false',
        ];

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('nas-freights.freight-export-bookings.index', [
                'draw'    => 1,
                'start'   => 0,
                'length'  => 15,
                'columns' => [
                    $column('DT_RowIndex', false),
                    $column('action', false),
                    $column('export_booking_no', true),
                ],
                'order' => [['column' => 2, 'dir' => $direction]],
            ]))
            ->assertOk();

        return collect($response->json('data'))->pluck('export_booking_no')->all();
    }

    public function test_index_orders_by_job_number_numerically_with_mixed_formats(): void
    {
        $this->withSession($this->sessionWithBranch());

        $descending = [
            'NASEXP261000',
            'NASEXP26101_PJ1',
            'NASEXP26101_PJ',
            'NASEXP26101',
            'NAS EXP26017',
            'NASEXP26010',
            'NASEXP26009',
            'NAS/TB/RENATA/06/2026',
            'FEB-20260914-0001',
            '1122233',
        ];

        foreach (collect($descending)->shuffle()->all() as $no) {
            $this->createBooking(['export_booking_no' => $no]);
        }

        $this->assertSame($descending, $this->listedBookingNos('desc'));
        $this->assertSame(array_reverse($descending), $this->listedBookingNos('asc'));
    }

    public function test_store_uses_manual_booking_no_when_provided(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'export_booking_no' => 'MANUAL-001',
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => $this->createCustomer(),
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $this->assertSame('MANUAL-001', NasFreightsFreightExportBooking::first()->export_booking_no);
    }

    public function test_store_auto_generates_booking_no_when_blank(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'export_booking_no' => '',
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => $this->createCustomer(),
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $this->assertMatchesRegularExpression('/^NASEXP\d{2}\d+$/', NasFreightsFreightExportBooking::first()->export_booking_no);
    }

    public function test_store_rejects_duplicate_manual_booking_no(): void
    {
        $this->withSession($this->sessionWithBranch());
        $this->createBooking(['export_booking_no' => 'DUP-1']);

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'export_booking_no' => 'DUP-1',
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => $this->createCustomer(),
        ])->assertSessionHasErrors('export_booking_no');
    }

    public function test_store_saves_multiple_hs_codes_trimmed_and_deduplicated(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
            'booking_date'       => '2026-08-29',
            'service_type'       => 'FCL',
            'customer_id'        => $this->createCustomer(),
            'party_bill_date'    => 'not-a-date',
            'party_invoices'     => [['no' => 'PI-1', 'date' => 'also-bad']],
            'hs_codes'           => [str_repeat('9', 51)],
        ])->assertSessionHasErrors(['party_bill_date', 'party_invoices.0.date', 'hs_codes.0']);

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
        $booking->partyInvoices()->create(['invoice_no' => 'OLD-PI']);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            ...$this->partyBillFields(),
            'customer_id'        => $this->createCustomer(),
            'booking_date'       => '2026-08-29',
            'service_type'       => 'FCL',
            'booking_note_no'    => 'NEW-BN',
            'party_invoices'     => [['no' => 'NEW-PI', 'date' => '2026-09-01']],
            'hs_codes'           => ['2222.22', '3333.33'],
            'items'              => [[
                'item_type'    => 'package',
                'package_type' => 'Carton',
                'quantity'     => 8,
            ]],
        ])->assertRedirect();

        $booking = $booking->fresh();
        $this->assertSame('NEW-BN', $booking->booking_note_no);
        $this->assertSame(['NEW-PI'], $booking->partyInvoices()->pluck('invoice_no')->all());
        $this->assertSame('2026-09-01', $booking->partyInvoices()->first()->invoice_date->toDateString());
        $this->assertSame(['2222.22', '3333.33'], $booking->hs_codes);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('Carton', $booking->items()->first()->package_type);
    }

    public function test_update_clears_hs_codes_when_none_submitted(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['1111.11']]);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            ...$this->partyBillFields(),
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
            'party_bill_ref_no' => 'PB-0001',
            'party_bill_date'   => '2026-08-20',
            'hs_codes'          => ['6109.10', '4203.21'],
        ]);
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0001', 'invoice_date' => '2026-08-22']);
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0002', 'invoice_date' => '2026-08-23', 'sort_order' => 1]);

        $response = $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id));

        $response->assertOk()
            ->assertSeeInOrder(['Party Bill Ref No', 'Party Bill Ref Date', 'Party Invoice No', 'Party Invoice Date'])
            ->assertSee('value="2026-08-20"', false)
            ->assertSee('id="partyInvoiceList"', false)
            ->assertSee('name="party_invoices[0][no]"', false)
            ->assertSee('[{"no":"PI-0001","date":"2026-08-22"},{"no":"PI-0002","date":"2026-08-23"}]', false)
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
                ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
            ->assertSee('1 bill created')
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

    public function test_booking_pages_label_invoice_fields_as_sb_no_and_sb_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'invoice_no'   => 'SB-2026-0001',
            'invoice_date' => '2026-09-03',
        ]);

        foreach ([
            route('nas-freights.freight-export-bookings.create'),
            route('nas-freights.freight-export-bookings.edit', $booking->id),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('for="fbInvoiceNo">S.B. No</label>', $html);
            $this->assertStringContainsString('for="fbInvoiceDate">S.B. Date</label>', $html);
            $this->assertStringNotContainsString('for="fbInvoiceNo">Invoice No', $html);
            $this->assertStringNotContainsString('for="fbInvoiceDate">Invoice Date', $html);
        }

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('S.B. No')
            ->assertSee('S.B. Date')
            ->assertSee('SB-2026-0001')
            ->assertSee('03 Sep 2026')
            ->assertDontSee('<div class="info-label">Invoice No</div>', false)
            ->assertDontSee('<div class="info-label">Invoice Date</div>', false);
    }

    public function test_store_reports_invalid_sb_date_with_sb_label(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'customer_id'  => $this->createCustomer(),
            'invoice_date' => 'nope',
        ])->assertSessionHasErrors(['invoice_date' => 'The S.B. Date field must be a valid date.']);
    }

    public function test_edit_page_keeps_create_bill_when_both_bill_types_exist(): void
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
            ->assertSee('2 bills created')
            ->assertSee('FEB-BILL-2026-0001')
            ->assertSee('FEB-BILL-2026-0002')
            ->assertDontSee('Not created')
            ->assertSee('Create Bill');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function multipleCustomerBillRows(): array
    {
        $row = fn (string $no, string $type, string $date, float $amount, array $extra = []) => array_merge([
            'bill_no'  => $no, 'bill_type' => $type, 'bill_date' => $date,
            'currency' => 'BDT', 'total_amount' => $amount, 'total_bdt_amount' => $amount, 'status' => 'Draft',
        ], $extra);

        return [
            $row('FEB-BILL-2026-0001', 'Customer', '2026-09-10', 500),
            $row('FEB-BILL-2026-0002', 'Customer', '2026-09-11', 750, ['bill_prefix_ref' => 'PB-77', 'bill_prefix_job' => 'EXP-0005']),
            $row('FEB-BILL-2026-0003', 'Customer', '2026-09-12', 900, ['bill_prefix_ref' => 'PB-88']),
            $row('FEB-BILL-2026-0004', 'Overseas Agent', '2026-09-13', 300),
        ];
    }

    public function test_edit_and_show_pages_list_every_customer_bill_of_a_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $ids = [];
        foreach ($this->multipleCustomerBillRows() as $attributes) {
            $ids[] = $booking->bills()->create($attributes)->id;
        }

        foreach (['edit', 'show'] as $page) {
            $response = $this->get(route('nas-freights.freight-export-bookings.'.$page, $booking->id))
                ->assertOk()
                ->assertSee('4 bills created')
                ->assertDontSee('Not created')
                ->assertSee('Create Bill')
                ->assertSeeInOrder([
                    'FEB-BILL-2026-0001', 'FEB-BILL-2026-0002', 'FEB-BILL-2026-0003', 'FEB-BILL-2026-0004',
                ])
                ->assertSee('Bill Ref: PB-77 · Job No: EXP-0005')
                ->assertSee('Bill Ref: PB-88')
                ->assertSee('500.00')
                ->assertSee('750.00')
                ->assertSee('900.00');

            foreach ($ids as $id) {
                $response->assertSee(route('nas-freights.freight-export-booking-bills.show', $id), false)
                    ->assertSee(route('nas-freights.freight-export-booking-bills.print', $id), false)
                    ->assertSee(route('nas-freights.freight-export-booking-bills.edit', $id), false);
            }
        }
    }

    public function test_pages_marks_missing_customer_type_not_created_when_only_agent_bill_exists(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->bills()->create([
            'bill_no'  => 'FEB-BILL-2026-0009', 'bill_type' => 'Overseas Agent', 'bill_date' => '2026-09-10',
            'currency' => 'USD', 'total_amount' => 10, 'total_bdt_amount' => 1200, 'status' => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('1 bill created')
            ->assertDontSee('1 bills created')
            ->assertSee('Not created')
            ->assertSee('FEB-BILL-2026-0009');
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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
            ...$this->partyBillFields(),
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

    public function test_bill_show_page_lists_the_job_expense(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'  => 'FEB-BILL-2026-0001', 'bill_type' => 'Customer', 'bill_date' => '2026-09-10',
            'currency' => 'BDT', 'total_amount' => 500, 'total_bdt_amount' => 500, 'status' => 'Draft',
        ]);
        $this->seedExpenseAndTransport($booking);

        $this->get(route('nas-freights.freight-export-booking-bills.show', $bill->id))
            ->assertOk()
            ->assertSeeInOrder(['Bill Items', 'Expense Details', 'Total: 1,200.00', 'Approved: 1,000.00', 'Port Handling'])
            ->assertSee('sub-table-wrap sub-table-wrap--expense', false)
            ->assertSee('data-label="Expense Amount"', false)
            ->assertDontSee('Edit Expense')
            ->assertDontSee(route('nas-freights.freight-export-bookings.expense.edit', $booking->id), false);
    }

    public function test_bill_show_page_says_no_expense_when_job_has_none(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'  => 'FEB-BILL-2026-0002', 'bill_type' => 'Customer', 'bill_date' => '2026-09-10',
            'currency' => 'BDT', 'total_amount' => 500, 'total_bdt_amount' => 500, 'status' => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.show', $bill->id))
            ->assertOk()
            ->assertSee('Expense Details')
            ->assertSee('No expense added yet.')
            ->assertDontSee('Add Expense');
    }

    public function test_bill_create_and_edit_pages_show_the_job_expense(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $bill = $booking->bills()->create([
            'bill_no'  => 'FEB-BILL-2026-0003', 'bill_type' => 'Customer', 'bill_date' => '2026-09-10',
            'currency' => 'BDT', 'total_amount' => 500, 'total_bdt_amount' => 500, 'status' => 'Draft',
        ]);
        $this->seedExpenseAndTransport($booking);

        foreach ([
            route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $booking->id]),
            route('nas-freights.freight-export-booking-bills.edit', $bill->id),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('id="bookingExpenseWrap"', false)
                ->assertSeeInOrder(['Job Expense', 'Port Handling', 'Receipt', '1,200.00', '1,000.00', 'Total'])
                ->assertDontSee(route('nas-freights.freight-export-bookings.expense.edit', $booking->id), false)
                ->assertDontSee('Edit Expense');
        }
    }

    public function test_bill_create_page_says_no_expense_and_hides_panel_without_a_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();

        $this->get(route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $booking->id]))
            ->assertOk()
            ->assertSee('Job Expense')
            ->assertSee('No expense added yet for this booking/job.')
            ->assertDontSee('Add Expense')
            ->assertDontSee(route('nas-freights.freight-export-bookings.expense.edit', $booking->id), false);

        $this->get(route('nas-freights.freight-export-booking-bills.create'))
            ->assertOk()
            ->assertSee('id="bookingExpenseWrap"', false)
            ->assertDontSee('No expense added yet')
            ->assertDontSee('Port Handling');
    }

    public function test_show_page_follows_create_page_field_order(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'party_bill_ref_no' => 'PB-77',
            'bl_date'           => '2026-09-01',
            'exp_no'            => 'EXP-2026-0001',
            'invoice_no'        => 'INV-2026-0001',
            'lc_no'             => 'LC-2026-0001',
            'remarks'           => 'Handle with care',
        ]);
        $booking->partyInvoices()->create(['invoice_no' => 'PI-77', 'invoice_date' => '2026-09-04']);

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
                'EXP No', 'EXP Date', 'S.B. No', 'S.B. Date', 'Remarks',
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
            ->assertSee('1 bill created')
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

    public function test_store_saves_multiple_party_invoices_in_order_and_skips_blank_rows(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'booking_date'   => '2026-08-29',
            'service_type'   => 'FCL',
            'customer_id'    => $this->createCustomer(),
            'party_invoices' => [
                ['no' => ' PI-0001 ', 'date' => '2026-08-22'],
                ['no' => '', 'date' => ''],
                ['no' => 'PI-0002', 'date' => ''],
                ['no' => 'PI-0001', 'date' => '2026-08-22'],
                ['no' => 'PI-0003', 'date' => '2026-08-25'],
            ],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $invoices = NasFreightsFreightExportBooking::first()->partyInvoices;

        $this->assertSame(['PI-0001', 'PI-0002', 'PI-0003'], $invoices->pluck('invoice_no')->all());
        $this->assertSame([0, 1, 2], $invoices->pluck('sort_order')->all());
        $this->assertSame('2026-08-22', $invoices[0]->invoice_date->toDateString());
        $this->assertNull($invoices[1]->invoice_date);
        $this->assertSame('2026-08-25', $invoices[2]->invoice_date->toDateString());
    }

    public function test_store_without_party_invoices_creates_none(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'booking_date'   => '2026-08-29',
            'service_type'   => 'FCL',
            'customer_id'    => $this->createCustomer(),
            'party_invoices' => [['no' => '', 'date' => '']],
        ])->assertRedirect(route('nas-freights.freight-export-bookings.index'));

        $this->assertDatabaseCount('nas_freights_freight_export_booking_party_invoices', 0);
    }

    public function test_store_rejects_party_invoice_date_without_invoice_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-export-bookings.store'), [
            ...$this->partyBillFields(),
            'booking_date'   => '2026-08-29',
            'service_type'   => 'FCL',
            'customer_id'    => $this->createCustomer(),
            'party_invoices' => [
                ['no' => 'PI-1', 'date' => '2026-08-22'],
                ['no' => '', 'date' => '2026-08-23'],
            ],
        ])->assertSessionHasErrors(['party_invoices.1.no']);

        $this->assertSame(['party_invoices.1.no'], session('errors')->keys());

        $this->assertSame(0, NasFreightsFreightExportBooking::count());
    }

    public function test_update_replaces_party_invoices_and_clears_them_when_none_submitted(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->partyInvoices()->create(['invoice_no' => 'OLD-1', 'invoice_date' => '2026-01-01']);
        $booking->partyInvoices()->create(['invoice_no' => 'OLD-2', 'sort_order' => 1]);

        $payload = [
            ...$this->partyBillFields(),
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
        ];

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), $payload + [
            'party_invoices' => [['no' => 'NEW-1', 'date' => '2026-02-02'], ['no' => 'NEW-2', 'date' => '']],
        ])->assertRedirect();

        $this->assertSame(['NEW-1', 'NEW-2'], $booking->partyInvoices()->pluck('invoice_no')->all());

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), $payload)->assertRedirect();

        $this->assertSame(0, $booking->partyInvoices()->count());
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<int, array<string, mixed>>
     */
    private function listedPartyInvoiceRows(array $extra = []): array
    {
        $column = fn (string $name, string $search = '') => [
            'data'       => $name,
            'name'       => $name,
            'searchable' => 'true',
            'orderable'  => 'false',
            'search'     => ['value' => $search, 'regex' => 'false'],
        ];

        return $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('nas-freights.freight-export-bookings.index', [
                'draw'    => 1,
                'start'   => 0,
                'length'  => 15,
                'columns' => [
                    $column('party_invoice_no', $extra['no'] ?? ''),
                    $column('party_invoice_date', $extra['date'] ?? ''),
                ],
                'order'  => [['column' => 0, 'dir' => 'asc']],
                'search' => ['value' => $extra['global'] ?? '', 'regex' => 'false'],
            ]))
            ->assertOk()
            ->json('data');
    }

    public function test_index_lists_and_searches_all_party_invoice_numbers_and_dates(): void
    {
        $this->withSession($this->sessionWithBranch());

        $first = $this->createBooking();
        $first->partyInvoices()->create(['invoice_no' => 'ALPHA-1', 'invoice_date' => '2026-08-22']);
        $first->partyInvoices()->create(['invoice_no' => 'BETA-2', 'sort_order' => 1]);
        $second = $this->createBooking();
        $second->partyInvoices()->create(['invoice_no' => 'GAMMA-3', 'invoice_date' => '2026-09-30']);
        $this->createBooking();

        $rows = collect($this->listedPartyInvoiceRows());
        $this->assertEqualsCanonicalizing(
            ["\u{2014}", 'ALPHA-1, BETA-2', 'GAMMA-3'],
            $rows->pluck('party_invoice_no')->all()
        );
        $this->assertEqualsCanonicalizing(
            ["\u{2014}", '22 Aug 2026, '."\u{2014}", '30 Sep 2026'],
            $rows->pluck('party_invoice_date')->all()
        );

        $this->assertSame(['ALPHA-1, BETA-2'], collect($this->listedPartyInvoiceRows(['no' => 'BETA']))->pluck('party_invoice_no')->all());
        $this->assertSame(['GAMMA-3'], collect($this->listedPartyInvoiceRows(['date' => '2026-09']))->pluck('party_invoice_no')->all());
        $this->assertSame(['GAMMA-3'], collect($this->listedPartyInvoiceRows(['global' => 'GAMMA']))->pluck('party_invoice_no')->all());
    }

    public function test_show_page_lists_every_party_invoice_with_its_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0001', 'invoice_date' => '2026-08-22']);
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0002', 'invoice_date' => '2026-08-23', 'sort_order' => 1]);

        $this->get(route('nas-freights.freight-export-bookings.show', $booking->id))
            ->assertOk()
            ->assertSeeInOrder(['Party Invoice No', 'Party Invoice Date', 'PI-0001', '22 Aug 2026', 'PI-0002', '23 Aug 2026']);
    }

    public function test_bill_print_lists_all_party_invoice_numbers_and_dates(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0001', 'invoice_date' => '2026-08-22']);
        $booking->partyInvoices()->create(['invoice_no' => 'PI-0002', 'invoice_date' => '2026-08-23', 'sort_order' => 1]);
        $bill = $booking->bills()->create([
            'bill_no'          => 'FEB-BILL-2026-0003',
            'bill_type'        => 'Customer',
            'bill_date'        => '2026-11-08',
            'currency'         => 'BDT',
            'total_amount'     => 500,
            'total_bdt_amount' => 500,
            'status'           => 'Draft',
        ]);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('PI-0001, PI-0002')
            ->assertSee('22.08.2026, 23.08.2026');
    }

    public function test_create_page_renders_repeatable_party_invoice_rows(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->get(route('nas-freights.freight-export-bookings.create'))
            ->assertOk()
            ->assertSee('id="partyInvoiceList"', false)
            ->assertSee('id="partyInvoiceTemplate"', false)
            ->assertSee('name="party_invoices[0][no]"', false)
            ->assertSee('name="party_invoices[0][date]"', false)
            ->assertDontSee('name="party_invoice_no"', false)
            ->assertDontSee('name="party_invoice_date"', false);
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

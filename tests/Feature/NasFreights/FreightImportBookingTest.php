<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightBooking;
use App\Models\NasFreights\NasFreightsRfq;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreightImportBookingTest extends TestCase
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

        Schema::create('nas_freights_freight_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('freight_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('rfq_id')->nullable();
            $table->string('rfq_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_invoice_no')->nullable();
            $table->date('customer_invoice_date')->nullable();
            $table->string('agent_invoice_no')->nullable();
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
            $table->string('flight_no')->nullable();
            $table->date('flight_date')->nullable();
            $table->string('bl_no')->nullable();
            $table->string('mbl_mawb_no')->nullable();
            $table->date('mbl_mawb_date')->nullable();
            $table->string('hbl_hawb_no')->nullable();
            $table->date('hbl_hawb_date')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('cad_no')->nullable();
            $table->string('tt_no')->nullable();
            $table->string('rfq_tender_no')->nullable();
            $table->string('igm_no')->nullable();
            $table->string('delivery_order_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->decimal('buy_amount', 15, 2)->nullable();
            $table->decimal('buy_bdt_amount', 15, 2)->nullable();
            $table->decimal('sell_amount', 15, 2)->nullable();
            $table->decimal('sell_bdt_amount', 15, 2)->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freight_booking_id');
            $table->string('item_type');
            $table->string('container_size')->nullable();
            $table->string('container_no')->nullable();
            $table->string('seal_no')->nullable();
            $table->string('package_type')->nullable();
            $table->unsignedInteger('package_qty')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('commodity')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('chargeable_weight', 10, 3)->nullable();
            $table->string('weight_unit')->default('KG');
            $table->decimal('volume_cbm', 10, 3)->nullable();
            $table->string('country_of_origin')->nullable();
            $table->boolean('is_dangerous_goods')->default(false);
            $table->string('special_handling')->nullable();
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

    public function test_store_creates_import_booking_with_container_tracking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $response = $this->post(route('nas-freights.freight-import-bookings.store'), [
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => $this->createCustomer(),
            'pol'               => 'Singapore',
            'pod'               => 'Chittagong',
            'bl_no'             => 'SING-123',
            'etd'               => '2026-08-20',
            'eta'               => '2026-09-05',
            'status'            => 'In-Transit',
            'items'             => [[
                'item_type'      => 'container',
                'container_size' => '40HC',
                'container_no'   => 'MSCU1234567',
                'seal_no'        => 'SL889900',
                'hs_code'        => '848190',
                'commodity'      => 'VALVES',
                'quantity'       => 1,
                'gross_weight'   => 12500.5,
                'weight_unit'    => 'KG',
            ]],
        ]);

        $response->assertRedirect(route('nas-freights.freight-import-bookings.index'));

        $booking = NasFreightsFreightBooking::first();
        $this->assertNotNull($booking);
        $this->assertStringStartsWith('FIB-', $booking->freight_booking_no);
        $this->assertSame('SING-123', $booking->bl_no);
        $this->assertSame(1, $booking->branch_id);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('MSCU1234567', $booking->items()->first()->container_no);
        $this->assertSame('SL889900', $booking->items()->first()->seal_no);
    }

    public function test_freight_booking_numbers_increment(): void
    {
        $first = NasFreightsFreightBooking::create([
            'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            'branch_id'          => 1,
            'booking_date'       => now()->toDateString(),
            'service_type'       => 'LCL',
        ]);
        $second = NasFreightsFreightBooking::create([
            'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            'branch_id'          => 1,
            'booking_date'       => now()->toDateString(),
            'service_type'       => 'LCL',
        ]);

        $this->assertNotSame($first->freight_booking_no, $second->freight_booking_no);
        $this->assertStringStartsWith('FIB-', $second->freight_booking_no);
    }

    public function test_update_modifies_import_booking_and_rewrites_items(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = NasFreightsFreightBooking::create([
            'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            'branch_id'          => 1,
            'booking_date'       => now()->toDateString(),
            'service_type'       => 'FCL',
            'bl_no'              => 'OLD-BL',
        ]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), [
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'bl_no'        => 'NEW-BL',
            'items'        => [[
                'item_type'    => 'package',
                'package_type' => 'Carton',
                'quantity'     => 5,
            ]],
        ])->assertRedirect();

        $this->assertSame('NEW-BL', $booking->fresh()->bl_no);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('Carton', $booking->items()->first()->package_type);
        $this->assertSame(5, $booking->items()->first()->quantity);
    }

    public function test_export_rfq_cannot_convert_to_import_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $rfq = NasFreightsRfq::create([
            'rfq_no'       => 'RFQ000001',
            'branch_id'    => 1,
            'rfq_date'     => now()->toDateString(),
            'type'         => 'export',
            'service_type' => 'FCL',
            'status'       => 'Win',
        ]);

        $this->post(route('nas-freights.rfqs.convert-freight-booking', $rfq->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
        $this->assertNull($rfq->fresh()->converted_freight_booking_id);
    }

    public function test_import_rfq_converts_to_freight_import_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $rfq = NasFreightsRfq::create([
            'rfq_no'       => 'RFQ000002',
            'branch_id'    => 1,
            'rfq_date'     => now()->toDateString(),
            'type'         => 'import',
            'service_type' => 'FCL',
            'currency'     => 'BDT',
            'pol'          => 'Shanghai',
            'pod'          => 'Chittagong',
            'status'       => 'Win',
        ]);

        $rfq->items()->create([
            'item_type'      => 'container',
            'container_size' => '40HC',
            'commodity'      => 'MACHINERY',
            'quantity'       => 1,
            'gross_weight'   => 8000,
            'weight_unit'    => 'KG',
        ]);

        $this->post(route('nas-freights.rfqs.convert-freight-booking', $rfq->id))
            ->assertRedirect(route('nas-freights.freight-import-bookings.show', 1))
            ->assertSessionHas('success');

        $booking = NasFreightsFreightBooking::first();
        $this->assertNotNull($booking);
        $this->assertStringStartsWith('FIB-', $booking->freight_booking_no);
        $this->assertSame('RFQ000002', $booking->rfq_no);
        $this->assertSame('40HC', $booking->items()->first()->container_size);
        $this->assertSame(1, $rfq->fresh()->converted_freight_booking_id);
    }

    private function createCustomer(): int
    {
        return DB::table('nas_freights_customers')->where('customer_id', 'CUS-0001')->value('id')
            ?? DB::table('nas_freights_customers')->insertGetId([
                'customer_id' => 'CUS-0001',
                'name'        => 'ABC Imports',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
    }

    private function createBooking(array $attributes = []): NasFreightsFreightBooking
    {
        return NasFreightsFreightBooking::create(array_merge([
            'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            'branch_id'          => 1,
            'booking_date'       => now()->toDateString(),
            'service_type'       => 'FCL',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_id'  => $this->createCustomer(),
            'booking_date' => '2026-10-06',
            'service_type' => 'FCL',
        ], $overrides);
    }

    public function test_store_saves_multiple_hs_codes_trimmed_and_deduplicated(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'hs_codes' => ['  6109.10 ', '', '6109.10', '4203.21'],
        ]))->assertRedirect(route('nas-freights.freight-import-bookings.index'));

        $this->assertSame(['6109.10', '4203.21'], NasFreightsFreightBooking::first()->hs_codes);
    }

    public function test_store_saves_null_hs_codes_when_all_inputs_blank(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'hs_codes' => ['', '  '],
        ]))->assertRedirect();

        $this->assertNull(NasFreightsFreightBooking::first()->hs_codes);
    }

    public function test_store_ignores_item_level_hs_code_and_commodity(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'items' => [[
                'item_type' => 'container',
                'hs_code'   => '848190',
                'commodity' => 'VALVES',
                'quantity'  => 1,
            ]],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertNull($item->hs_code);
        $this->assertNull($item->commodity);
    }

    public function test_store_rejects_invalid_hs_codes_and_package_qty(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'hs_codes' => [str_repeat('9', 51)],
        ]))->assertSessionHasErrors('hs_codes.0');

        foreach (['0', '-5', 'abc', '1.5'] as $bad) {
            $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
                'items' => [['item_type' => 'container', 'package_qty' => $bad]],
            ]))->assertSessionHasErrors('items.0.package_qty');
        }

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }

    public function test_update_rewrites_and_clears_hs_codes(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['1111.11']]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload([
            'hs_codes' => ['2222.22', '3333.33'],
        ]))->assertRedirect();
        $this->assertSame(['2222.22', '3333.33'], $booking->fresh()->hs_codes);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload())
            ->assertRedirect();
        $this->assertNull($booking->fresh()->hs_codes);
    }

    public function test_store_ignores_removed_place_igm_and_do_inputs(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'place_of_receipt'  => 'Shanghai',
            'place_of_delivery' => 'Dhaka',
            'igm_no'            => '2026-001234',
            'delivery_order_no' => 'DO-8899',
        ]))->assertRedirect();

        $booking = NasFreightsFreightBooking::first();
        $this->assertNull($booking->place_of_receipt);
        $this->assertNull($booking->place_of_delivery);
        $this->assertNull($booking->igm_no);
        $this->assertNull($booking->delivery_order_no);
    }

    public function test_update_keeps_existing_place_igm_and_do_values(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'place_of_receipt'  => 'Shanghai',
            'place_of_delivery' => 'Dhaka',
            'igm_no'            => 'OLD-IGM',
            'delivery_order_no' => 'OLD-DO',
        ]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload([
            'pol' => 'Singapore',
        ]))->assertRedirect();

        $booking->refresh();
        $this->assertSame('Singapore', $booking->pol);
        $this->assertSame('Shanghai', $booking->place_of_receipt);
        $this->assertSame('Dhaka', $booking->place_of_delivery);
        $this->assertSame('OLD-IGM', $booking->igm_no);
        $this->assertSame('OLD-DO', $booking->delivery_order_no);
    }

    public function test_edit_page_has_no_place_igm_or_do_inputs(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['igm_no' => 'OLD-IGM']);

        $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))
            ->assertOk()
            ->assertDontSee('name="place_of_receipt"', false)
            ->assertDontSee('name="place_of_delivery"', false)
            ->assertDontSee('name="igm_no"', false)
            ->assertDontSee('name="delivery_order_no"', false)
            ->assertSee('name="pol"', false)
            ->assertSee('name="bl_no"', false);
    }

    /**
     * @return array<string, string>
     */
    private function documentRefs(string $suffix = '1'): array
    {
        return [
            'flight_no'     => 'BG-147-'.$suffix,
            'flight_date'   => '2026-10-01',
            'mbl_mawb_no'   => 'MAWB-'.$suffix,
            'mbl_mawb_date' => '2026-10-02',
            'hbl_hawb_no'   => 'HAWB-'.$suffix,
            'hbl_hawb_date' => '2026-10-03',
            'lc_no'         => 'LC-'.$suffix,
            'cad_no'        => 'CAD-'.$suffix,
            'tt_no'         => 'TT-'.$suffix,
            'rfq_tender_no' => 'TND-'.$suffix,
        ];
    }

    public function test_store_saves_flight_and_document_reference_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload($this->documentRefs()))
            ->assertRedirect(route('nas-freights.freight-import-bookings.index'));

        $booking = NasFreightsFreightBooking::first();
        $this->assertSame('BG-147-1', $booking->flight_no);
        $this->assertSame('2026-10-01', $booking->flight_date->format('Y-m-d'));
        $this->assertSame('MAWB-1', $booking->mbl_mawb_no);
        $this->assertSame('2026-10-02', $booking->mbl_mawb_date->format('Y-m-d'));
        $this->assertSame('HAWB-1', $booking->hbl_hawb_no);
        $this->assertSame('2026-10-03', $booking->hbl_hawb_date->format('Y-m-d'));
        $this->assertSame('LC-1', $booking->lc_no);
        $this->assertSame('CAD-1', $booking->cad_no);
        $this->assertSame('TT-1', $booking->tt_no);
        $this->assertSame('TND-1', $booking->rfq_tender_no);
    }

    public function test_store_saves_null_for_blank_flight_and_document_reference_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $blank = array_fill_keys(array_keys($this->documentRefs()), '');

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload($blank))
            ->assertRedirect();

        $booking = NasFreightsFreightBooking::first();
        foreach (array_keys($blank) as $column) {
            $this->assertNull($booking->{$column}, $column.' should be null');
        }
    }

    public function test_store_rejects_invalid_flight_and_document_reference_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $invalid = [];
        foreach (['flight_date', 'mbl_mawb_date', 'hbl_hawb_date'] as $dateField) {
            $invalid[$dateField] = 'not-a-date';
        }
        foreach (['flight_no', 'mbl_mawb_no', 'hbl_hawb_no', 'lc_no', 'cad_no', 'tt_no', 'rfq_tender_no'] as $textField) {
            $invalid[$textField] = str_repeat('X', 256);
        }

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload($invalid))
            ->assertSessionHasErrors(array_keys($invalid));

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }

    public function test_update_rewrites_and_clears_flight_and_document_reference_fields(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking($this->documentRefs('old'));

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload($this->documentRefs('new')))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame('BG-147-new', $booking->flight_no);
        $this->assertSame('TND-new', $booking->rfq_tender_no);
        $this->assertSame('HAWB-new', $booking->hbl_hawb_no);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload())
            ->assertRedirect();

        $booking->refresh();
        foreach (array_keys($this->documentRefs()) as $column) {
            $this->assertNull($booking->{$column}, $column.' should be cleared');
        }
    }

    public function test_edit_page_renders_flight_and_document_reference_inputs(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking($this->documentRefs());

        $response = $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))->assertOk();

        foreach ($this->documentRefs() as $column => $value) {
            $response->assertSee('name="'.$column.'"', false)
                ->assertSee('value="'.$value.'"', false);
        }

        $response->assertSee('MB/L / MAWB No')
            ->assertSee('HBL/HAWB No')
            ->assertSee('RFQ/Tender No');
    }

    public function test_create_page_groups_related_booking_fields_into_sections(): void
    {
        $this->withSession($this->sessionWithBranch());

        $response = $this->get(route('nas-freights.freight-import-bookings.create'))->assertOk();

        $response->assertSeeInOrder([
            'aria-label="Job &amp; Parties"', 'name="booking_date"', 'name="service_type"', 'name="status"',
            'name="customer_id"', 'name="salesperson_id"', 'name="overseas_agent_id"', 'name="shipping_carrier_id"',
            'aria-label="Routing &amp; Schedule"', 'name="pol"', 'name="pod"', 'name="etd"', 'name="eta"',
            'name="vessel_name"', 'name="voyage_no"', 'name="flight_no"', 'name="flight_date"',
            'aria-label="Documents &amp; References"', 'name="mbl_mawb_no"', 'name="mbl_mawb_date"', 'name="hbl_hawb_no"',
            'name="hbl_hawb_date"', 'name="bl_no"', 'name="rfq_tender_no"',
            'aria-label="Commercial &amp; Payment"', 'name="customer_invoice_no"', 'name="customer_invoice_date"',
            'name="agent_invoice_no"', 'name="currency"', 'name="incoterms"', 'name="lc_no"', 'name="cad_no"', 'name="tt_no"',
            'aria-label="Cargo Description"', 'name="commodity_description"', 'id="hsCodeList"', 'name="remarks"',
        ], false);

        $labelledInputs = [
            'fbBookingNo', 'fbBookingDate', 'fbServiceType', 'fbStatus', 'customerSelect', 'salespersonSelect',
            'overseasAgentSelect', 'shippingCarrierSelect', 'fbPol', 'fbPod', 'fbEtd', 'fbEta', 'fbIncoterms',
            'fbCurrency', 'fbRemarks',
        ];

        foreach ($labelledInputs as $id) {
            $response->assertSee('for="'.$id.'"', false)
                ->assertSee('id="'.$id.'"', false);
        }

        $response->assertDontSee('From RFQ')
            ->assertDontSee('<legend', false);
    }

    public function test_edit_page_shows_source_rfq_next_to_rfq_tender_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['rfq_no' => 'RFQ-SRC-1', 'rfq_tender_no' => 'TND-9']);

        $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSeeInOrder([
                'aria-label="Documents &amp; References"', 'name="rfq_tender_no"', 'for="fbFromRfq"', 'value="RFQ-SRC-1"',
                'aria-label="Commercial &amp; Payment"',
            ], false);
    }

    public function test_show_page_lists_flight_and_document_references_only_when_present(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking($this->documentRefs());
        $this->get(route('nas-freights.freight-import-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('Flight &amp; Document References', false)
            ->assertSee('BG-147-1')
            ->assertSee('01 Oct 2026')
            ->assertSee('MAWB-1')
            ->assertSee('HAWB-1')
            ->assertSee('LC-1')
            ->assertSee('CAD-1')
            ->assertSee('TT-1')
            ->assertSee('TND-1');

        $empty = $this->createBooking();
        $this->get(route('nas-freights.freight-import-bookings.show', $empty->id))
            ->assertOk()
            ->assertDontSee('Flight &amp; Document References', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function weightRow(): array
    {
        return [
            'item_type'         => 'container',
            'quantity'          => 1,
            'net_weight'        => 900.5,
            'gross_weight'      => 1000.25,
            'chargeable_weight' => 1200,
            'weight_unit'       => 'KG',
        ];
    }

    public function test_non_air_booking_saves_net_and_gross_weight_and_drops_chargeable(): void
    {
        $this->withSession($this->sessionWithBranch());

        foreach (['FCL', 'LCL', 'SEA', 'Truck', 'Road', 'Handling', 'Other'] as $mode) {
            NasFreightsFreightBooking::query()->delete();

            $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
                'service_type' => $mode,
                'items'        => [$this->weightRow()],
            ]))->assertRedirect();

            $item = NasFreightsFreightBooking::first()->items()->first();
            $this->assertEquals(900.5, $item->net_weight, $mode);
            $this->assertEquals(1000.25, $item->gross_weight, $mode);
            $this->assertNull($item->chargeable_weight, $mode);
        }
    }

    public function test_air_booking_saves_gross_and_chargeable_weight_and_drops_net(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'service_type' => 'Air',
            'items'        => [$this->weightRow()],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertNull($item->net_weight);
        $this->assertEquals(1000.25, $item->gross_weight);
        $this->assertEquals(1200, $item->chargeable_weight);
    }

    public function test_switching_mode_on_update_drops_the_weight_that_no_longer_applies(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['service_type' => 'Air']);
        $booking->items()->create(['item_type' => 'container', 'gross_weight' => 500, 'chargeable_weight' => 700]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload([
            'service_type' => 'FCL',
            'items'        => [$this->weightRow()],
        ]))->assertRedirect();

        $item = $booking->items()->first();
        $this->assertEquals(900.5, $item->net_weight);
        $this->assertNull($item->chargeable_weight);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload([
            'service_type' => 'Air',
            'items'        => [$this->weightRow()],
        ]))->assertRedirect();

        $item = $booking->items()->first();
        $this->assertNull($item->net_weight);
        $this->assertEquals(1200, $item->chargeable_weight);
    }

    public function test_blank_weights_are_saved_as_null(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'items' => [['item_type' => 'package', 'net_weight' => '', 'gross_weight' => '', 'chargeable_weight' => '']],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertNull($item->net_weight);
        $this->assertNull($item->gross_weight);
        $this->assertNull($item->chargeable_weight);
    }

    public function test_store_rejects_invalid_weights(): void
    {
        $this->withSession($this->sessionWithBranch());

        foreach (['net_weight', 'gross_weight', 'chargeable_weight'] as $field) {
            foreach (['-1', 'abc', '10000000'] as $bad) {
                $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
                    'items' => [['item_type' => 'container', $field => $bad]],
                ]))->assertSessionHasErrors('items.0.'.$field);
            }
        }

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }

    public function test_edit_page_renders_weight_columns_and_prefills_values(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['service_type' => 'Air']);
        $booking->items()->create(['item_type' => 'container', 'gross_weight' => 1000, 'chargeable_weight' => 1200]);

        $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('id="fbServiceType"', false)
            ->assertSee('data-weight-col="net"', false)
            ->assertSee('data-weight-col="chargeable"', false)
            ->assertSee('name="items[0][net_weight]"', false)
            ->assertSee('name="items[0][gross_weight]"', false)
            ->assertSee('name="items[0][chargeable_weight]"', false)
            ->assertSee('Net Weight')
            ->assertSee('Gross Weight')
            ->assertSee('Chargeable Weight')
            ->assertSee('"chargeable_weight":', false)
            ->assertDontSee('weightModeHint', false);
    }

    public function test_edit_page_cargo_table_is_responsive_and_labelled(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking();
        $booking->items()->create(['item_type' => 'container', 'gross_weight' => 1000]);

        $response = $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))->assertOk();

        $response->assertSee('class="cargo-scroll"', false)
            ->assertSee('@container cargo', false)
            ->assertDontSee('min-width:1520px', false)
            ->assertDontSee('style="font-size:.72rem;"', false);

        foreach (['Item Type', 'Qty', 'Container Size', 'Pkg Qty', 'Pkg Unit', 'Container No', 'Seal No', 'Net Weight', 'Gross Weight', 'Chargeable Weight', 'Wt Unit', 'CBM', 'Country of Origin', 'DG', 'Special Handling'] as $label) {
            $response->assertSee('data-label="'.$label.'"', false);
        }

        foreach (['Item type', 'Quantity', 'Container size', 'Package type', 'Container number', 'Seal number', 'Volume in CBM', 'Country of origin', 'Dangerous goods', 'Special handling', 'Remove cargo row'] as $ariaLabel) {
            $response->assertSee('aria-label="'.$ariaLabel.'"', false);
        }
    }

    public function test_show_page_columns_follow_shipment_mode(): void
    {
        $this->withSession($this->sessionWithBranch());

        $air = $this->createBooking(['service_type' => 'Air']);
        $air->items()->create(['item_type' => 'container', 'gross_weight' => 1000, 'chargeable_weight' => 1234.5]);

        $this->get(route('nas-freights.freight-import-bookings.show', $air->id))
            ->assertOk()
            ->assertSee('Chargeable Weight')
            ->assertDontSee('Net Weight')
            ->assertSee('1,234.50 KG');

        $sea = $this->createBooking(['service_type' => 'FCL']);
        $sea->items()->create(['item_type' => 'container', 'net_weight' => 888, 'gross_weight' => 1000]);

        $this->get(route('nas-freights.freight-import-bookings.show', $sea->id))
            ->assertOk()
            ->assertSee('Net Weight')
            ->assertDontSee('Chargeable Weight')
            ->assertSee('888.00 KG');
    }

    public function test_store_saves_customer_invoice_no_and_date(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'customer_invoice_no'   => 'INV-2026-0001',
            'customer_invoice_date' => '2026-10-01',
            'agent_invoice_no'      => 'AGT-2026-0009',
        ]))->assertRedirect(route('nas-freights.freight-import-bookings.index'));

        $booking = NasFreightsFreightBooking::first();
        $this->assertSame('INV-2026-0001', $booking->customer_invoice_no);
        $this->assertSame('AGT-2026-0009', $booking->agent_invoice_no);
        $this->assertSame('2026-10-01', $booking->customer_invoice_date->format('Y-m-d'));
    }

    public function test_store_saves_null_customer_invoice_when_blank(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'customer_invoice_no'   => '',
            'customer_invoice_date' => '',
            'agent_invoice_no'      => '',
        ]))->assertRedirect();

        $booking = NasFreightsFreightBooking::first();
        $this->assertNull($booking->agent_invoice_no);
        $this->assertNull($booking->customer_invoice_no);
        $this->assertNull($booking->customer_invoice_date);
    }

    public function test_store_rejects_invalid_customer_invoice_date_and_long_no(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'customer_invoice_no'   => str_repeat('A', 256),
            'customer_invoice_date' => 'not-a-date',
            'agent_invoice_no'      => str_repeat('B', 256),
        ]))->assertSessionHasErrors(['customer_invoice_no', 'customer_invoice_date', 'agent_invoice_no']);

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }

    public function test_update_rewrites_and_clears_customer_invoice(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'customer_invoice_no'   => 'OLD-INV',
            'customer_invoice_date' => '2026-09-01',
            'agent_invoice_no'      => 'OLD-AGT',
        ]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload([
            'customer_invoice_no'   => 'NEW-INV',
            'customer_invoice_date' => '2026-10-02',
            'agent_invoice_no'      => 'NEW-AGT',
        ]))->assertRedirect();

        $booking->refresh();
        $this->assertSame('NEW-INV', $booking->customer_invoice_no);
        $this->assertSame('NEW-AGT', $booking->agent_invoice_no);
        $this->assertSame('2026-10-02', $booking->customer_invoice_date->format('Y-m-d'));

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), $this->payload())
            ->assertRedirect();

        $booking->refresh();
        $this->assertNull($booking->customer_invoice_no);
        $this->assertNull($booking->customer_invoice_date);
        $this->assertNull($booking->agent_invoice_no);
    }

    public function test_edit_and_show_pages_render_customer_invoice(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking([
            'customer_invoice_no'   => 'INV-7788',
            'customer_invoice_date' => '2026-10-01',
            'agent_invoice_no'      => 'AGT-5566',
        ]);

        $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('name="customer_invoice_no"', false)
            ->assertSee('value="INV-7788"', false)
            ->assertSee('name="customer_invoice_date"', false)
            ->assertSee('value="2026-10-01"', false)
            ->assertSee('name="agent_invoice_no"', false)
            ->assertSee('value="AGT-5566"', false);

        $this->get(route('nas-freights.freight-import-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('Customer Invoice No')
            ->assertSee('INV-7788')
            ->assertSee('Customer Invoice Date')
            ->assertSee('01 Oct 2026')
            ->assertSee('Agent Invoice No')
            ->assertSee('AGT-5566');
    }

    public function test_store_saves_package_qty_and_unit_for_container_rows(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'items' => [[
                'item_type'      => 'container',
                'container_size' => '40HC',
                'package_qty'    => 500,
                'package_unit'   => 'Carton',
                'quantity'       => 1,
            ]],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertSame('40HC', $item->container_size);
        $this->assertSame(500, $item->package_qty);
        $this->assertSame('Carton', $item->package_type);
    }

    public function test_store_ignores_package_qty_for_package_rows_and_keeps_package_type(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'items' => [[
                'item_type'    => 'package',
                'package_type' => 'Pallet',
                'package_qty'  => 999,
                'package_unit' => 'Carton',
                'quantity'     => 3,
            ]],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertNull($item->package_qty);
        $this->assertSame('Pallet', $item->package_type);
    }

    public function test_store_allows_container_row_without_package_qty(): void
    {
        $this->withSession($this->sessionWithBranch());

        $this->post(route('nas-freights.freight-import-bookings.store'), $this->payload([
            'items' => [[
                'item_type'    => 'container',
                'package_qty'  => '',
                'package_unit' => '',
            ]],
        ]))->assertRedirect();

        $item = NasFreightsFreightBooking::first()->items()->first();
        $this->assertNull($item->package_qty);
        $this->assertNull($item->package_type);
    }

    public function test_edit_page_shows_hs_codes_and_package_columns_without_item_hs_code_or_commodity(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['6109.10', '4203.21']]);
        $booking->items()->create([
            'item_type'      => 'container',
            'container_size' => '40HC',
            'package_qty'    => 500,
            'package_type'   => 'Carton',
            'hs_code'        => 'LEGACY-HS',
            'commodity'      => 'LEGACY-COMMODITY',
        ]);

        $this->get(route('nas-freights.freight-import-bookings.edit', $booking->id))
            ->assertOk()
            ->assertSee('name="hs_codes[]"', false)
            ->assertSee('["6109.10","4203.21"]', false)
            ->assertSee('name="items[0][package_qty]"', false)
            ->assertSee('name="items[0][package_unit]"', false)
            ->assertSee('"package_qty":500', false)
            ->assertSee('"package_unit":"Carton"', false)
            ->assertDontSee('name="items[0][hs_code]"', false)
            ->assertDontSee('name="items[0][commodity]"', false)
            ->assertDontSee('LEGACY-HS')
            ->assertDontSee('LEGACY-COMMODITY');
    }

    public function test_show_page_lists_hs_codes_and_package_columns(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = $this->createBooking(['hs_codes' => ['6109.10', '4203.21']]);
        $booking->items()->create([
            'item_type'      => 'container',
            'container_size' => '40HC',
            'package_qty'    => 500,
            'package_type'   => 'Carton',
        ]);

        $this->get(route('nas-freights.freight-import-bookings.show', $booking->id))
            ->assertOk()
            ->assertSee('6109.10')
            ->assertSee('4203.21')
            ->assertSee('Pkg Qty')
            ->assertSee('Pkg Unit')
            ->assertSee('Carton');
    }

    public function test_import_rfq_conversion_carries_item_hs_codes_to_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $rfq = NasFreightsRfq::create([
            'rfq_no'       => 'RFQ000003',
            'branch_id'    => 1,
            'rfq_date'     => now()->toDateString(),
            'type'         => 'import',
            'service_type' => 'FCL',
            'currency'     => 'BDT',
            'status'       => 'Win',
        ]);
        $rfq->items()->create(['item_type' => 'container', 'hs_code' => '8481.90', 'quantity' => 1]);
        $rfq->items()->create(['item_type' => 'container', 'hs_code' => ' 8481.90 ', 'quantity' => 1]);
        $rfq->items()->create(['item_type' => 'package', 'hs_code' => '7318.15', 'quantity' => 2]);
        $rfq->items()->create(['item_type' => 'package', 'quantity' => 1]);

        $this->post(route('nas-freights.rfqs.convert-freight-booking', $rfq->id))->assertRedirect();

        $this->assertSame(['8481.90', '7318.15'], NasFreightsFreightBooking::first()->hs_codes);
    }

    public function test_destroy_deletes_import_booking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $booking = NasFreightsFreightBooking::create([
            'freight_booking_no' => NasFreightsFreightBooking::generateFreightBookingNo(),
            'branch_id'          => 1,
            'booking_date'       => now()->toDateString(),
            'service_type'       => 'Air',
        ]);

        $this->deleteJson(route('nas-freights.freight-import-bookings.destroy', $booking->id))
            ->assertOk()
            ->assertJsonPath('message', 'Freight Import Booking/Job '.$booking->freight_booking_no.' deleted.');

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }
}

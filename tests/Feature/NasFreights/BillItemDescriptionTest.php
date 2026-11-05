<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsBillItemDescription;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BillItemDescriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->actingAsSuperUser();
        $this->withSession($this->sessionWithBranch());
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

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('cnf');
            $table->boolean('is_active')->default(true);
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

        Schema::create('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('export_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('party_bill_ref_no')->nullable();
            $table->unsignedBigInteger('overseas_agent_id')->nullable();
            $table->unsignedBigInteger('shipping_carrier_id')->nullable();
            $table->date('booking_date');
            $table->string('service_type');
            $table->string('currency')->default('BDT');
            $table->json('hs_codes')->nullable();
            $table->string('status')->default('Draft');
            $table->timestamps();
        });

        Schema::create('nas_freights_freight_export_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('item_type');
            $table->unsignedInteger('quantity')->default(1);
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
            $table->string('vat_title')->nullable();
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('vat_amount_bdt', 14, 2)->default(0);
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

        Schema::create('nas_freights_bill_item_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function actingAsSuperUser(): void
    {
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

    /**
     * @return array<string, int|string>
     */
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBooking(array $attributes = []): NasFreightsFreightExportBooking
    {
        return NasFreightsFreightExportBooking::create(array_merge([
            'export_booking_no' => 'EXP-0001',
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'FCL',
        ], $attributes));
    }

    private function createDescription(string $name, bool $active = true, int $sort = 0): NasFreightsBillItemDescription
    {
        return NasFreightsBillItemDescription::create([
            'name'       => $name,
            'sort_order' => $sort,
            'is_active'  => $active,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function billPayload(int $bookingId, array $itemNames, array $overrides = []): array
    {
        return array_merge([
            'export_booking_id' => $bookingId,
            'bill_type'         => 'Customer',
            'bill_date'         => '2026-11-05',
            'currency'          => 'BDT',
            'exchange_rate'     => 1,
            'items'             => collect($itemNames)
                ->map(fn ($name) => ['name' => $name, 'amount' => 100])
                ->values()
                ->all(),
        ], $overrides);
    }

    private function storeBill(int $bookingId, array $overrides = []): TestResponse
    {
        return $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($bookingId, ['Air Freight'], $overrides)
        );
    }

    // ── Settings CRUD ──────────────────────────────────────────────

    public function test_index_lists_descriptions_for_datatable(): void
    {
        $this->createDescription('Air Freight');
        $this->createDescription('C&F Charges');

        $response = $this->get(
            route('nas-freights.settings.bill-item-descriptions.index'),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['name' => 'Air Freight'])
            ->assertJsonFragment(['name' => 'C&amp;F Charges']);
    }

    public function test_store_creates_description_and_trims_name(): void
    {
        $this->post(route('nas-freights.settings.bill-item-descriptions.store'), [
            'name'       => '  Terminal Handling  ',
            'sort_order' => 3,
        ])->assertOk();

        $this->assertDatabaseHas('nas_freights_bill_item_descriptions', [
            'name'       => 'Terminal Handling',
            'sort_order' => 3,
            'is_active'  => 1,
        ]);
    }

    public function test_store_rejects_duplicate_and_blank_name(): void
    {
        $this->createDescription('Air Freight');

        $this->postJson(route('nas-freights.settings.bill-item-descriptions.store'), ['name' => 'Air Freight'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson(route('nas-freights.settings.bill-item-descriptions.store'), ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_changes_description_and_allows_keeping_own_name(): void
    {
        $row = $this->createDescription('Air Freight');

        $this->putJson(route('nas-freights.settings.bill-item-descriptions.update', $row->id), [
            'name'      => 'Air Freight',
            'is_active' => 0,
        ])->assertOk();

        $this->putJson(route('nas-freights.settings.bill-item-descriptions.update', $row->id), [
            'name' => 'Sea Freight',
        ])->assertOk();

        $this->assertDatabaseHas('nas_freights_bill_item_descriptions', ['id' => $row->id, 'name' => 'Sea Freight']);
    }

    public function test_update_rejects_name_used_by_another_row(): void
    {
        $this->createDescription('Air Freight');
        $other = $this->createDescription('Sea Freight');

        $this->putJson(route('nas-freights.settings.bill-item-descriptions.update', $other->id), ['name' => 'Air Freight'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_destroy_deletes_description(): void
    {
        $row = $this->createDescription('Air Freight');

        $this->deleteJson(route('nas-freights.settings.bill-item-descriptions.destroy', $row->id))->assertOk();

        $this->assertDatabaseMissing('nas_freights_bill_item_descriptions', ['id' => $row->id]);
    }

    public function test_settings_require_permission(): void
    {
        $row = $this->createDescription('Air Freight');
        $this->actingAs(User::create([
            'name'      => 'No Perms',
            'username'  => 'noperms',
            'email'     => 'noperms@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]));

        $this->get(route('nas-freights.settings.bill-item-descriptions.index'))->assertForbidden();
        $this->post(route('nas-freights.settings.bill-item-descriptions.store'), ['name' => 'X'])->assertForbidden();
        $this->put(route('nas-freights.settings.bill-item-descriptions.update', $row->id), ['name' => 'Y'])->assertForbidden();
        $this->delete(route('nas-freights.settings.bill-item-descriptions.destroy', $row->id))->assertForbidden();
    }

    // ── Bill form ──────────────────────────────────────────────────

    public function test_bill_create_page_exposes_only_active_descriptions_in_order(): void
    {
        $this->createDescription('Zebra Charge', true, 2);
        $this->createDescription('Air Freight', true, 1);
        $this->createDescription('Hidden Charge', false, 0);

        $response = $this->get(route('nas-freights.freight-export-booking-bills.create'));

        $response->assertOk()
            ->assertViewHas('descriptions', fn ($d) => $d->all() === ['Air Freight', 'Zebra Charge'])
            ->assertViewHas('canAddDescription', true)
            ->assertSee('descriptionOptions', false)
            ->assertSee(route('nas-freights.settings.bill-item-descriptions.store'), false);
    }

    public function test_bill_create_page_works_with_no_saved_descriptions(): void
    {
        $this->get(route('nas-freights.freight-export-booking-bills.create'))
            ->assertOk()
            ->assertViewHas('descriptions', fn ($d) => $d->isEmpty());
    }

    public function test_duplicate_description_returns_friendly_message(): void
    {
        $this->createDescription('Air Freight');

        $this->postJson(route('nas-freights.settings.bill-item-descriptions.store'), ['name' => 'Air Freight'])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'This description already exists. If it is not in the list, it may be inactive.');
    }

    public function test_settings_reject_sort_order_above_column_limit(): void
    {
        $this->postJson(route('nas-freights.settings.bill-item-descriptions.store'), ['name' => 'Big Sort', 'sort_order' => 70000])
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort_order');
    }

    public function test_bill_store_saves_free_text_not_in_the_list(): void
    {
        $this->createDescription('Air Freight');
        $booking = $this->createBooking();

        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, ['Air Freight', 'Custom Handling Fee'])
        )->assertRedirect(route('nas-freights.freight-export-booking-bills.index'));

        $bill = NasFreightsFreightExportBookingBill::firstOrFail();
        $this->assertSame(['Air Freight', 'Custom Handling Fee'], $bill->items->pluck('name')->all());
    }

    public function test_bill_store_still_requires_item_description(): void
    {
        $booking = $this->createBooking();

        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, [''])
        )->assertSessionHasErrors('items.0.name');

        $this->assertSame(0, NasFreightsFreightExportBookingBill::count());
    }

    public function test_bill_store_rejects_duplicate_descriptions_ignoring_case_and_spaces(): void
    {
        $booking = $this->createBooking();

        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, ['Air Freight', 'air freight  '])
        )->assertSessionHasErrors('items.1.name');

        $this->assertSame(0, NasFreightsFreightExportBookingBill::count());
        $this->assertSame(0, DB::table('nas_freights_freight_export_booking_bill_items')->count());
    }

    public function test_bill_store_allows_distinct_descriptions(): void
    {
        $booking = $this->createBooking();

        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, ['Air Freight', 'Air Freight Surcharge'])
        )->assertSessionHasNoErrors();

        $this->assertSame(2, DB::table('nas_freights_freight_export_booking_bill_items')->count());
    }

    public function test_bill_update_rejects_duplicate_descriptions_and_keeps_old_items(): void
    {
        $booking = $this->createBooking();
        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, ['Air Freight', 'Handling'])
        );
        $bill = NasFreightsFreightExportBookingBill::firstOrFail();

        $this->put(
            route('nas-freights.freight-export-booking-bills.update', $bill->id),
            $this->billPayload($booking->id, ['Handling', 'HANDLING'])
        )->assertSessionHasErrors('items.1.name');

        $this->assertSame(['Air Freight', 'Handling'], $bill->fresh()->items->pluck('name')->all());
    }

    public function test_bill_form_ships_duplicate_description_check(): void
    {
        $this->get(route('nas-freights.freight-export-booking-bills.create'))
            ->assertOk()
            ->assertSee('validateDuplicateDescriptions', false)
            ->assertSee('desc-error', false);
    }

    // ── Customer bill prefix (2nd+ Customer bill of a booking/job) ──

    public function test_first_customer_bill_keeps_standard_number_without_prefix(): void
    {
        $booking = $this->createBooking();

        $this->storeBill($booking->id)->assertSessionHasNoErrors();

        $this->assertStringStartsWith('FEB-BILL-', NasFreightsFreightExportBookingBill::firstOrFail()->bill_no);
    }

    public function test_second_customer_bill_requires_a_prefix(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id)->assertSessionHasErrors('bill_prefix');

        $this->assertSame(1, NasFreightsFreightExportBookingBill::count());
    }

    public function test_second_customer_bill_with_typed_job_no_prefix(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id, ['bill_prefix_job' => 'JOB-5'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('nas_freights_freight_export_booking_bills', ['bill_no' => 'JOB-5-2', 'bill_type' => 'Customer']);
    }

    public function test_third_customer_bill_with_typed_bill_ref_prefix_continues_the_running_number(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);
        $this->storeBill($booking->id, ['bill_prefix_job' => 'JOB-5']);

        $this->storeBill($booking->id, ['bill_prefix_ref' => '  PB-77  '])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('nas_freights_freight_export_booking_bills', ['bill_no' => 'PB-77-3']);
    }

    public function test_typed_bill_ref_and_job_no_are_combined(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id, ['bill_prefix_ref' => 'PB-77', 'bill_prefix_job' => 'JOB-5'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('nas_freights_freight_export_booking_bills', ['bill_no' => 'PB-77-JOB-5-2']);
    }

    public function test_booking_values_are_never_used_automatically_as_prefix(): void
    {
        $booking = $this->createBooking(['export_booking_no' => 'EXP-0005', 'party_bill_ref_no' => 'PB-77']);
        $this->storeBill($booking->id);

        $this->storeBill($booking->id)->assertSessionHasErrors('bill_prefix');

        $this->assertSame(1, NasFreightsFreightExportBookingBill::count());
    }

    public function test_blank_prefix_inputs_are_rejected(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id, ['bill_prefix_ref' => '   ', 'bill_prefix_job' => ''])->assertSessionHasErrors('bill_prefix');

        $this->assertSame(1, NasFreightsFreightExportBookingBill::count());
    }

    public function test_overlong_prefix_is_rejected(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id, ['bill_prefix_ref' => str_repeat('A', 101)])->assertSessionHasErrors('bill_prefix_ref');

        $this->assertSame(1, NasFreightsFreightExportBookingBill::count());
    }

    public function test_prefixed_number_skips_a_number_that_is_already_taken(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);
        DB::table('nas_freights_freight_export_booking_bills')->insert([
            'bill_no'    => 'JOB-5-2', 'export_booking_id' => 99, 'bill_type' => 'Overseas Agent',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->storeBill($booking->id, ['bill_prefix_job' => 'JOB-5'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('nas_freights_freight_export_booking_bills', ['bill_no' => 'JOB-5-3']);
    }

    public function test_overseas_agent_bill_stays_limited_to_one_per_booking_and_ignores_prefix(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);

        $this->storeBill($booking->id, ['bill_type' => 'Overseas Agent', 'currency' => 'USD', 'exchange_rate' => 120, 'bill_prefix_job' => 'JOB-5'])
            ->assertSessionHasNoErrors();
        $agentBill = NasFreightsFreightExportBookingBill::where('bill_type', 'Overseas Agent')->firstOrFail();
        $this->assertStringStartsWith('FEB-BILL-', $agentBill->bill_no);

        $this->storeBill($booking->id, ['bill_type' => 'Overseas Agent', 'currency' => 'USD', 'exchange_rate' => 120])
            ->assertSessionHasErrors('bill_type');
        $this->assertSame(1, NasFreightsFreightExportBookingBill::where('bill_type', 'Overseas Agent')->count());
    }

    public function test_create_page_shows_typed_prefix_inputs_without_auto_values(): void
    {
        $booking = $this->createBooking(['export_booking_no' => 'EXP-0005', 'party_bill_ref_no' => 'PB-77']);
        $this->storeBill($booking->id);

        $this->get(route('nas-freights.freight-export-booking-bills.create', ['booking_id' => $booking->id]))
            ->assertOk()
            ->assertViewHas('existingBillTypes', [])
            ->assertSee('id="bookingPrefixData"', false)
            ->assertSee('data-customer-bills="1"', false)
            ->assertSee('id="prefixPanel"', false)
            ->assertSee('name="bill_prefix_ref"', false)
            ->assertSee('name="bill_prefix_job"', false)
            ->assertDontSee('data-bill-ref', false)
            ->assertDontSee('data-job-no', false)
            ->assertDontSee('type="radio"', false);
    }

    public function test_editing_a_customer_bill_keeps_its_number_and_allows_other_customer_bills(): void
    {
        $booking = $this->createBooking();
        $this->storeBill($booking->id);
        $this->storeBill($booking->id, ['bill_prefix_job' => 'JOB-5']);
        $second = NasFreightsFreightExportBookingBill::where('bill_no', 'JOB-5-2')->firstOrFail();

        $this->put(
            route('nas-freights.freight-export-booking-bills.update', $second->id),
            $this->billPayload($booking->id, ['Handling'])
        )->assertSessionHasNoErrors();

        $this->assertSame('JOB-5-2', $second->fresh()->bill_no);
        $this->assertSame(['Handling'], $second->fresh()->items->pluck('name')->all());
    }

    public function test_bill_edit_keeps_saved_name_that_is_no_longer_in_settings(): void
    {
        $this->createDescription('Air Freight');
        $booking = $this->createBooking();

        $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($booking->id, ['Legacy Removed Charge'])
        );

        $bill = NasFreightsFreightExportBookingBill::firstOrFail();

        $this->get(route('nas-freights.freight-export-booking-bills.edit', $bill->id))
            ->assertOk()
            ->assertViewHas('descriptions', fn ($d) => $d->all() === ['Air Freight'])
            ->assertSee('Legacy Removed Charge');

        $this->assertSame(1, DB::table('nas_freights_freight_export_booking_bill_items')->count());
    }
}

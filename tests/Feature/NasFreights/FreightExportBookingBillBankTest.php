<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsBank;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FreightExportBookingBillBankTest extends TestCase
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

        Schema::create('nas_freights_banks', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('account_type', 50);
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->string('routing_number', 50)->nullable();
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

        Schema::create('nas_freights_freight_export_booking_party_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
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
            $table->string('attention', 150)->nullable();
            $table->string('designation', 150)->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('nas_freights_banks')->nullOnDelete();
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

        Schema::create('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('booking_type')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->decimal('total_expense_amount', 14, 2)->default(0);
            $table->decimal('total_approved_amount', 14, 2)->default(0);
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

    private function createBooking(): NasFreightsFreightExportBooking
    {
        return NasFreightsFreightExportBooking::create([
            'export_booking_no' => 'EXP-0001',
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'FCL',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billPayload(int $bookingId, array $overrides = []): array
    {
        return array_merge([
            'export_booking_id' => $bookingId,
            'bill_type'         => 'Customer',
            'bill_date'         => '2026-11-12',
            'currency'          => 'BDT',
            'exchange_rate'     => 1,
            'items'             => [['name' => 'Air Freight', 'amount' => 100]],
        ], $overrides);
    }

    private function storeBill(int $bookingId, array $overrides = []): TestResponse
    {
        return $this->post(
            route('nas-freights.freight-export-booking-bills.store'),
            $this->billPayload($bookingId, $overrides)
        );
    }

    private function createBill(?int $bankId = null): NasFreightsFreightExportBookingBill
    {
        $booking = $this->createBooking();

        return NasFreightsFreightExportBookingBill::create([
            'bill_no'           => 'FEB-BILL-2026-0001',
            'export_booking_id' => $booking->id,
            'branch_id'         => 1,
            'bill_type'         => 'Customer',
            'bill_date'         => '2026-11-12',
            'bank_id'           => $bankId,
            'currency'          => 'BDT',
            'exchange_rate'     => 1,
        ]);
    }

    // ── Create form ────────────────────────────────────────────────

    public function test_create_page_offers_an_optional_bank_field_with_active_banks_only(): void
    {
        NasFreightsBank::factory()->create(['bank_name' => 'Zeta Bank', 'account_number' => '1']);
        NasFreightsBank::factory()->create(['bank_name' => 'Alpha Bank', 'account_number' => '2']);
        NasFreightsBank::factory()->inactive()->create(['bank_name' => 'Hidden Bank', 'account_number' => '3']);

        $this->get(route('nas-freights.freight-export-booking-bills.create'))
            ->assertOk()
            ->assertViewHas('banks', fn ($banks) => $banks->pluck('bank_name')->all() === ['Alpha Bank', 'Zeta Bank'])
            ->assertSee('name="bank_id"', false)
            ->assertSee('<option value="">No bank</option>', false)
            ->assertSee('Alpha Bank')
            ->assertDontSee('Hidden Bank');
    }

    public function test_create_page_works_with_no_banks_and_points_to_settings(): void
    {
        $this->get(route('nas-freights.freight-export-booking-bills.create'))
            ->assertOk()
            ->assertViewHas('banks', fn ($banks) => $banks->isEmpty())
            ->assertSee('No banks yet.')
            ->assertSee(route('nas-freights.settings.banks.index'), false);
    }

    // ── Store ──────────────────────────────────────────────────────

    public function test_store_saves_the_chosen_bank(): void
    {
        $bank = NasFreightsBank::factory()->create();
        $booking = $this->createBooking();

        $this->storeBill($booking->id, ['bank_id' => $bank->id])->assertSessionHasNoErrors();

        $this->assertSame($bank->id, NasFreightsFreightExportBookingBill::firstOrFail()->bank_id);
    }

    public function test_store_without_a_bank_saves_null(): void
    {
        $booking = $this->createBooking();

        $this->storeBill($booking->id)->assertSessionHasNoErrors();

        $this->assertNull(NasFreightsFreightExportBookingBill::firstOrFail()->bank_id);
    }

    public function test_store_treats_a_blank_bank_as_no_bank(): void
    {
        $booking = $this->createBooking();

        $this->storeBill($booking->id, ['bank_id' => ''])->assertSessionHasNoErrors();

        $this->assertNull(NasFreightsFreightExportBookingBill::firstOrFail()->bank_id);
    }

    public function test_store_rejects_unknown_and_inactive_banks(): void
    {
        $inactive = NasFreightsBank::factory()->inactive()->create();
        $booking = $this->createBooking();

        $this->storeBill($booking->id, ['bank_id' => 9999])->assertSessionHasErrors('bank_id');
        $this->storeBill($booking->id, ['bank_id' => $inactive->id])->assertSessionHasErrors('bank_id');
        $this->storeBill($booking->id, ['bank_id' => 'abc'])->assertSessionHasErrors('bank_id');

        $this->assertSame(0, NasFreightsFreightExportBookingBill::count());
    }

    // ── Update / edit ──────────────────────────────────────────────

    public function test_update_sets_changes_and_clears_the_bank(): void
    {
        $first = NasFreightsBank::factory()->create();
        $second = NasFreightsBank::factory()->create();
        $bill = $this->createBill();
        $url = route('nas-freights.freight-export-booking-bills.update', $bill->id);

        $this->put($url, $this->billPayload($bill->export_booking_id, ['bank_id' => $first->id]))->assertSessionHasNoErrors();
        $this->assertSame($first->id, $bill->fresh()->bank_id);

        $this->put($url, $this->billPayload($bill->export_booking_id, ['bank_id' => $second->id]))->assertSessionHasNoErrors();
        $this->assertSame($second->id, $bill->fresh()->bank_id);

        $this->put($url, $this->billPayload($bill->export_booking_id, ['bank_id' => '']))->assertSessionHasNoErrors();
        $this->assertNull($bill->fresh()->bank_id);
    }

    public function test_update_keeps_an_inactive_bank_already_on_the_bill_but_rejects_other_inactive_banks(): void
    {
        $saved = NasFreightsBank::factory()->create();
        $bill = $this->createBill($saved->id);
        $saved->update(['is_active' => false]);
        $otherInactive = NasFreightsBank::factory()->inactive()->create();
        $url = route('nas-freights.freight-export-booking-bills.update', $bill->id);

        $this->put($url, $this->billPayload($bill->export_booking_id, ['bank_id' => $saved->id]))->assertSessionHasNoErrors();
        $this->assertSame($saved->id, $bill->fresh()->bank_id);

        $this->put($url, $this->billPayload($bill->export_booking_id, ['bank_id' => $otherInactive->id]))
            ->assertSessionHasErrors('bank_id');
        $this->assertSame($saved->id, $bill->fresh()->bank_id);
    }

    public function test_edit_page_preselects_the_saved_bank_even_when_it_is_inactive(): void
    {
        $bank = NasFreightsBank::factory()->create(['bank_name' => 'Sonali Bank']);
        $bill = $this->createBill($bank->id);
        $bank->update(['is_active' => false]);

        $this->get(route('nas-freights.freight-export-booking-bills.edit', $bill->id))
            ->assertOk()
            ->assertViewHas('banks', fn ($banks) => $banks->pluck('id')->all() === [$bank->id])
            ->assertSee('value="'.$bank->id.'" selected', false)
            ->assertSee('[Inactive]');
    }

    public function test_deleting_a_bank_leaves_the_bill_without_a_bank(): void
    {
        $bank = NasFreightsBank::factory()->create();
        $bill = $this->createBill($bank->id);

        $bank->delete();

        $this->assertNull($bill->fresh()->bank_id);
        $this->assertDatabaseHas('nas_freights_freight_export_booking_bills', ['id' => $bill->id]);
    }

    // ── Show / print ───────────────────────────────────────────────

    public function test_show_page_displays_the_bank(): void
    {
        $bank = NasFreightsBank::factory()->create([
            'bank_name'      => 'Islami Bank Bangladesh Ltd.',
            'bank_branch'    => 'Motijheel',
            'account_number' => '1234567890123',
            'routing_number' => '125261234',
        ]);
        $bill = $this->createBill($bank->id);

        $this->get(route('nas-freights.freight-export-booking-bills.show', $bill->id))
            ->assertOk()
            ->assertSee('Islami Bank Bangladesh Ltd.')
            ->assertSee('Motijheel')
            ->assertSee('1234567890123')
            ->assertSee('Routing 125261234');
    }

    public function test_show_page_hides_the_bank_row_when_none_is_set(): void
    {
        $bill = $this->createBill();

        $this->get(route('nas-freights.freight-export-booking-bills.show', $bill->id))
            ->assertOk()
            ->assertDontSee('<div class="info-label">Bank</div>', false);
    }

    public function test_print_shows_the_bank_details_block(): void
    {
        $bank = NasFreightsBank::factory()->create([
            'account_name'   => 'NAS Freights Ltd.',
            'account_number' => '1234567890123',
            'account_type'   => 'Current',
            'bank_name'      => 'Islami Bank Bangladesh Ltd.',
            'bank_branch'    => 'Motijheel',
            'routing_number' => '125261234',
        ]);
        $bill = $this->createBill($bank->id);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('BANK DETAILS')
            ->assertSee('NAS Freights Ltd.')
            ->assertSee('1234567890123')
            ->assertSee('Current')
            ->assertSee('Islami Bank Bangladesh Ltd.')
            ->assertSee('Motijheel')
            ->assertSee('125261234');
    }

    public function test_print_places_the_bank_details_after_the_authorized_signature_with_a_gap(): void
    {
        $bill = $this->createBill(NasFreightsBank::factory()->create()->id);

        $response = $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))->assertOk();
        $html = $response->getContent();

        $this->assertGreaterThan(strpos($html, 'Authorized Signature'), strpos($html, 'BANK DETAILS'));
        $this->assertGreaterThan(strpos($html, 'NAS FREIGHTS AND LOGISTICS LTD.'), strpos($html, 'BANK DETAILS'));
        $this->assertMatchesRegularExpression('/table\.bank-block\s*\{[^}]*margin-top:\s*20mm/', $html);
    }

    public function test_print_omits_empty_optional_bank_lines(): void
    {
        $bank = NasFreightsBank::factory()->create(['bank_branch' => null, 'routing_number' => null]);
        $bill = $this->createBill($bank->id);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertSee('BANK DETAILS')
            ->assertDontSee('BRANCH')
            ->assertDontSee('ROUTING NO');
    }

    public function test_print_has_no_bank_block_without_a_bank(): void
    {
        $bill = $this->createBill();

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertDontSee('BANK DETAILS');
    }

    public function test_print_escapes_html_in_bank_values(): void
    {
        $bank = NasFreightsBank::factory()->create(['bank_name' => '<script>alert(1)</script>']);
        $bill = $this->createBill($bank->id);

        $this->get(route('nas-freights.freight-export-booking-bills.print', $bill->id))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }
}

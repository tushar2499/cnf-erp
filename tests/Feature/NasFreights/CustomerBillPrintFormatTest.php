<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsCustomerBill;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBillPrintFormatTest extends TestCase
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

        Schema::create('nas_freights_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->nullable();
            $table->string('po_number')->nullable();
            $table->string('goods_name')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_booking_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
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

    /**
     * Build the posted bill payload
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billPayload(array $overrides = []): array
    {
        return array_merge([
            'from_date'     => '2026-10-01',
            'to_date'       => '2026-10-31',
            'bill_date'     => '2026-10-31',
            'delivery_type' => 'LOCAL',
            'bill_type'     => 'TRUCK',
            'items'         => json_encode([$this->itemRow()]),
        ], $overrides);
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
            'b_qty'            => 1,
            'd_qty'            => 1,
            'due_qty'          => 0,
            'price'            => 100,
            'demurrage_day'    => 0,
            'demurrage_amount' => 0,
        ], $overrides);
    }

    /**
     * Store a Renata bill and return the saved model
     *
     * @param  array<string, mixed>  $overrides
     */
    private function storeRenataBill(array $overrides = []): NasFreightsCustomerBill
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(array_merge(['print_format' => 'renata'], $overrides))
        )->assertOk();

        return NasFreightsCustomerBill::firstOrFail();
    }

    private function makeBill(?string $printFormat = 'general'): NasFreightsCustomerBill
    {
        $bill = NasFreightsCustomerBill::create([
            'bill_no'          => 'NAS-L-COR-LC-2026-0000001',
            'branch_id'        => 1,
            'customer_name'    => 'Test Customer',
            'customer_address' => 'Dhaka',
            'bill_date'        => '2026-10-31',
            'status'           => 'Draft',
        ]);

        // Written directly so a legacy / unknown slug can be stored without model validation
        $bill->newQuery()->whereKey($bill->id)->update(['print_format' => $printFormat]);

        return $bill->refresh();
    }

    public function test_print_formats_include_general_and_renata(): void
    {
        $formats = NasFreightsCustomerBill::printFormats();

        $this->assertSame('General', $formats['general']);
        $this->assertSame('Renata', $formats['renata']);
        $this->assertArrayHasKey(NasFreightsCustomerBill::DEFAULT_PRINT_FORMAT, $formats);
    }

    public function test_every_registered_format_has_a_print_view(): void
    {
        foreach (array_keys(NasFreightsCustomerBill::printFormats()) as $slug) {
            $this->assertTrue(
                view()->exists('nas-freights.customer-bills.print-'.$slug),
                "Missing print view for format [{$slug}]"
            );
        }
    }

    public function test_store_saves_selected_print_format(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(['print_format' => 'renata'])
        )->assertOk();

        $this->assertSame('renata', NasFreightsCustomerBill::firstOrFail()->print_format);
    }

    public function test_store_defaults_to_general_when_print_format_is_omitted_or_blank(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload()
        )->assertOk();

        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(['print_format' => ''])
        )->assertOk();

        $this->assertSame(
            ['general', 'general'],
            NasFreightsCustomerBill::orderBy('id')->pluck('print_format')->all()
        );
    }

    public function test_store_rejects_unknown_print_format(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(['print_format' => 'bogus'])
        )->assertStatus(422)->assertJsonValidationErrors('print_format');

        $this->assertSame(0, NasFreightsCustomerBill::count());
    }

    public function test_update_changes_print_format(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(['print_format' => 'general'])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        $this->withFreightSession()->putJson(
            route('nas-freights.customer-bills.update', $bill->id),
            $this->billPayload(['print_format' => 'renata'])
        )->assertOk();

        $this->assertSame('renata', $bill->refresh()->print_format);
    }

    public function test_update_rejects_unknown_print_format(): void
    {
        $bill = $this->makeBill('general');

        $this->withFreightSession()->putJson(
            route('nas-freights.customer-bills.update', $bill->id),
            $this->billPayload(['print_format' => 'bogus'])
        )->assertStatus(422)->assertJsonValidationErrors('print_format');

        $this->assertSame('general', $bill->refresh()->print_format);
    }

    public function test_print_route_renders_view_for_each_format(): void
    {
        foreach (['general', 'renata'] as $slug) {
            NasFreightsCustomerBill::query()->delete();
            $bill = $this->makeBill($slug);

            $this->withFreightSession()
                ->get(route('nas-freights.customer-bills.print', $bill->id))
                ->assertOk()
                ->assertViewIs('nas-freights.customer-bills.print-'.$slug);
        }
    }

    public function test_show_page_displays_print_format_and_tax_breakdown(): void
    {
        $this->withFreightSession()->postJson(
            route('nas-freights.customer-bills.store'),
            $this->billPayload(['print_format' => 'renata', 'ait_percent' => 5, 'vat_percent' => 10, 'tds_percent' => 2])
        )->assertOk();

        $bill = NasFreightsCustomerBill::firstOrFail();

        // price base 100: AIT 5.00, VAT 10.00, TDS 2.00, line 115.00
        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.show', $bill->id))
            ->assertOk()
            ->assertSeeInOrder(['Print Format', 'Renata'])
            ->assertSeeInOrder(['AIT %', '5.00', 'AIT Amount', '5.00'])
            ->assertSeeInOrder(['VAT %', '10.00', 'VAT Amount', '10.00'])
            ->assertSeeInOrder(['TDS %', '2.00', 'TDS Amount', '2.00'])
            ->assertSeeInOrder(['AIT Amt', 'VAT Amt', 'TDS Amt', 'Line Amount', '115.00']);
    }

    public function test_renata_print_follows_transport_invoice_layout(): void
    {
        DB::table('nas_freights_bookings')->insert([
            'id' => 1, 'job_no' => 'J-1', 'po_number' => 'PO-77', 'delivery_date' => '2026-10-05',
        ]);
        DB::table('nas_freights_booking_items')->insert([
            'id'       => 1, 'booking_id' => 1, 'cover_van_no' => 'VAN1', 'challan_no' => 'CH-9',
            'capacity' => '5 Ton', 'location_from' => 'Dhaka', 'location_to' => 'Chattogram',
        ]);

        $bill = $this->storeRenataBill([
            'ait_percent'   => 5,
            'vat_percent'   => 10,
            'tds_percent'   => 2,
            'customer_name' => 'Renata PLC',
            'items'         => json_encode([$this->itemRow(['booking_id' => 1, 'booking_item_id' => 1])]),
        ]);

        // price base 100: AIT 5.00, VAT 10.00, line 115.00; TDS 2.00 -> total 117.00.
        // Printed Rate = base + AIT = 105.00; no separate AIT column or Total AIT row.
        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $bill->id))
            ->assertOk()
            ->assertViewIs('nas-freights.customer-bills.print-renata')
            ->assertSee('TRANSPORT INVOICE')
            ->assertSeeInOrder(['SI', 'Date', 'Challan No', 'Vehicle No', 'Capacity (Ton)', 'From', 'To (Destination)', 'Rate (BDT)', 'VAT 10%', 'Net Payable'])
            ->assertSeeInOrder(['05/10/2026', 'CH-9', 'VAN1', '5 Ton', 'Dhaka', 'Chattogram', '105.00', '10.00', '115.00'])
            ->assertSeeInOrder(['Billing Summary', 'Total Charge', '105.00', 'Total VAT', '10.00', 'TDS (2%)', '2.00', 'Grand Payable', '117.00'])
            ->assertDontSee('AIT 5%')
            ->assertDontSee('Total AIT')
            ->assertSee('BDT One Hundred Seventeen Taka Only')
            ->assertSee('Renata PLC')
            ->assertSee('PO-77')
            ->assertSee("October'26")
            ->assertSeeInOrder(['Bank Information', 'Account Name:', 'Account Number:', 'Bank Name:', 'Routing Number:', 'Branch Name:'])
            ->assertSeeInOrder(['Prepared By', 'Checked By'])
            ->assertDontSee('Approved By')
            ->assertDontSee('Transport Provider')
            ->assertDontSee('1F4E78', false);
    }

    public function test_renata_print_reserves_company_pad_space_like_general(): void
    {
        $renata = $this->storeRenataBill();

        $renataHtml = $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $renata->id))
            ->assertOk()
            ->getContent();

        $generalHtml = $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $this->makeGeneralBill()->id))
            ->assertOk()
            ->getContent();

        // Top/bottom space for the pre-printed company pad must match the General format
        $this->assertStringContainsString('size: A4 portrait;', $generalHtml);
        $this->assertStringContainsString('margin: 51mm 0 28mm 0;', $generalHtml);
        $this->assertStringContainsString('size: A4 portrait;', $renataHtml);
        $this->assertStringContainsString('margin: 51mm 0 28mm 0;', $renataHtml);
        $this->assertStringNotContainsString('landscape', $renataHtml);

        // Per-page footer, rows and tail the pagination script depends on
        $this->assertStringContainsString('class="item-row"', $renataHtml);
        $this->assertStringContainsString('class="tail"', $renataHtml);
        $this->assertStringContainsString('final-page-footer', $renataHtml);
        $this->assertStringContainsString('Print Date:', $renataHtml);
    }

    private function makeGeneralBill(): NasFreightsCustomerBill
    {
        NasFreightsCustomerBill::query()->delete();

        return $this->makeBill('general');
    }

    public function test_renata_print_shows_account_officer_name_over_prepared_and_checked_lines(): void
    {
        $bill = $this->storeRenataBill(['bill_by' => 'Rahim Uddin']);

        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $bill->id))
            ->assertOk()
            ->assertSeeInOrder([
                '<div class="sig-name">Rahim Uddin</div>',
                '<div class="sig-line">Prepared By</div>',
                '<div class="sig-name">Rahim Uddin</div>',
                '<div class="sig-line">Checked By</div>',
            ], false)
            ->assertDontSee('<div class="sig-line">Account Officer</div>', false);
    }

    public function test_renata_print_splits_manual_row_location_into_from_and_to(): void
    {
        $bill = $this->storeRenataBill();

        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $bill->id))
            ->assertOk()
            ->assertSeeInOrder(['<td>VAN1</td>', '<td class="c"></td>', '<td>Dhaka</td>', '<td>Ctg</td>'], false);
    }

    public function test_renata_print_shows_demurrage_and_tds_rows_only_when_present(): void
    {
        $plain = $this->storeRenataBill(['tds_percent' => 0]);

        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $plain->id))
            ->assertOk()
            ->assertDontSee('Demurrage')
            ->assertDontSee('TDS (');

        NasFreightsCustomerBill::query()->delete();

        $withExtras = $this->storeRenataBill([
            'tds_percent' => 2,
            'items'       => json_encode([$this->itemRow(['demurrage_day' => 1, 'demurrage_amount' => 50])]),
        ]);

        // line 100 + demurrage 50 + TDS 2 = 152
        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $withExtras->id))
            ->assertOk()
            ->assertSeeInOrder(['Demurrage', '50.00', 'TDS (2%)', '2.00', 'Grand Payable', '152.00']);
    }

    public function test_general_print_is_unchanged_by_renata_layout(): void
    {
        $bill = $this->makeBill('general');

        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $bill->id))
            ->assertOk()
            ->assertViewIs('nas-freights.customer-bills.print-general')
            ->assertSee('TRANSPORT BILL')
            ->assertDontSee('TRANSPORT INVOICE');
    }

    public function test_print_route_falls_back_to_general_for_unknown_format(): void
    {
        $bill = $this->makeBill('retired-format');

        $this->withFreightSession()
            ->get(route('nas-freights.customer-bills.print', $bill->id))
            ->assertOk()
            ->assertViewIs('nas-freights.customer-bills.print-general');
    }
}

<?php

namespace Tests\Feature\NasFreights;

use App\Models\Company;
use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\NasFreights\NasFreightsBookingProduct;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingPrintTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
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
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
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
            $table->string('booking_prefix')->nullable();
            $table->string('sales_type')->nullable();
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->string('sales_person_name')->nullable();
            $table->date('job_date')->nullable();
            $table->string('goods_name')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('po_number')->nullable();
            $table->string('cover_van_no')->nullable();
            $table->text('note')->nullable();
            $table->string('tds_section')->nullable();
            $table->decimal('tds_percent', 8, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('vat_percent', 8, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('ait_percent', 8, 2)->default(0);
            $table->decimal('ait_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('forfeited_amount', 15, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->string('delivery_status')->nullable();
            $table->string('entry_by')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 15, 2)->default(1);
            $table->decimal('supplier_rate', 15, 2)->default(0);
            $table->decimal('customer_rate', 15, 2)->default(0);
            $table->decimal('demurrage_days', 15, 2)->default(0);
            $table->decimal('cus_demurrage_charge', 15, 2)->default(0);
            $table->decimal('sup_demurrage_charge', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_booking_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('goods_name')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('qty_unit')->nullable();
            $table->decimal('net_weight', 15, 2)->default(0);
            $table->string('weight_unit')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->timestamps();
        });
    }

    private function actingAsSuper(): void
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
     * Build a booking with goods and one trip
     *
     * @param  array<string, mixed>  $overrides
     */
    private function makeBooking(array $overrides = []): NasFreightsBooking
    {
        $booking = NasFreightsBooking::create(array_merge([
            'job_no'            => 'TMS-COR-2026-0000001',
            'booking_prefix'    => 'CORPORATE_SALES',
            'sales_type'        => 'LOCAL',
            'sales_person_name' => 'Karim Ali',
            'job_date'          => '2026-10-01',
            'goods_name'        => 'Cement',
            'customer_name'     => 'Renata PLC',
            'delivery_address'  => 'Mirpur, Dhaka',
            'lc_no'             => 'LC-55',
            'po_number'         => 'PO-77',
            'delivery_date'     => '2026-10-05',
            'note'              => 'Handle with care',
            'vat_percent'       => 10,
            'vat_amount'        => 10,
            'total_amount'      => 110,
            'status'            => 'Approved',
            'entry_by'          => 'Rahim Uddin',
            'branch_id'         => 1,
        ], $overrides));

        NasFreightsBookingProduct::create([
            'booking_id'  => $booking->id,
            'goods_name'  => 'Cement',
            'qty'         => 500,
            'qty_unit'    => 'BAG',
            'net_weight'  => 25,
            'weight_unit' => 'TON',
        ]);

        NasFreightsBookingItem::create([
            'booking_id'           => $booking->id,
            'cover_van_no'         => 'DHAKA-METRO-11-2233',
            'challan_no'           => 'CH-9',
            'capacity'             => '5 Ton',
            'supplier_name'        => 'Speedy Carriers',
            'qty'                  => 1,
            'supplier_rate'        => 777,
            'customer_rate'        => 100,
            'demurrage_days'       => 2,
            'cus_demurrage_charge' => 40,
            'sup_demurrage_charge' => 555,
            'amount'               => 100,
            'location_from'        => 'Dhaka',
            'location_to'          => 'Chattogram',
        ]);

        return $booking;
    }

    public function test_print_renders_booking_details_trips_and_totals(): void
    {
        $this->actingAsSuper();
        Company::create(['name' => 'NAS Freights And Logistics Ltd.', 'slug' => 'nas-freights', 'type' => 'freight']);
        $booking = $this->makeBooking();

        $this->withFreightSession()
            ->get(route('nas-freights.bookings.print', $booking->id))
            ->assertOk()
            ->assertViewIs('nas-freights.bookings.print')
            ->assertSee('Transport Booking')
            ->assertSee('NAS Freights And Logistics Ltd.')
            ->assertSeeInOrder(['Job No:', 'TMS-COR-2026-0000001', 'Job Date:', '01/10/2026', 'Delivery Date:', '05/10/2026'])
            ->assertSeeInOrder(['Customer,', 'Renata PLC', 'Mirpur, Dhaka', 'LC No:', 'LC-55', 'PO No:', 'PO-77'])
            ->assertSee('CORPORATE SALES')
            ->assertSee('Karim Ali')
            ->assertSeeInOrder(['Goods Details', 'Cement', '500.00 BAG', '25.00 TON'])
            ->assertSeeInOrder(['Vehicle &amp; Trip Details', 'DHAKA-METRO-11-2233', 'CH-9', '5 Ton', 'Speedy Carriers', 'Dhaka', 'Chattogram'], false)
            ->assertSeeInOrder(['Sub Total', '100.00', 'VAT (10%)', '10.00', 'Total Amount', '110.00'])
            ->assertSee('BDT One Hundred Ten Taka Only')
            ->assertSee('Handle with care')
            ->assertSeeInOrder(['Rahim Uddin', 'Prepared By', 'Checked By', 'Authorised Signatory']);
    }

    public function test_print_hides_tax_rows_that_are_zero_and_supplier_cost_figures(): void
    {
        $this->actingAsSuper();
        $booking = $this->makeBooking(['vat_percent' => 0, 'vat_amount' => 0, 'total_amount' => 100]);

        $this->withFreightSession()
            ->get(route('nas-freights.bookings.print', $booking->id))
            ->assertOk()
            ->assertDontSee('VAT (')
            ->assertDontSee('TDS (')
            ->assertDontSee('AIT (')
            ->assertDontSee('777.00')
            ->assertDontSee('555.00');
    }

    public function test_print_falls_back_to_default_company_name_and_empty_states(): void
    {
        $this->actingAsSuper();
        $booking = $this->makeBooking(['total_amount' => 0, 'vat_amount' => 0]);
        $booking->items()->delete();
        $booking->products()->delete();

        $this->withFreightSession()
            ->get(route('nas-freights.bookings.print', $booking->id))
            ->assertOk()
            ->assertSee('NAS Freights And Logistics Ltd.')
            ->assertSee('No trips recorded')
            ->assertSee('Cement');
    }

    public function test_print_is_forbidden_without_print_permission(): void
    {
        $user = User::create([
            'name'      => 'Plain User',
            'username'  => 'plain',
            'email'     => 'plain@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]);
        $this->actingAs($user);
        $booking = $this->makeBooking();

        $this->withFreightSession()
            ->get(route('nas-freights.bookings.print', $booking->id))
            ->assertForbidden();
    }

    public function test_print_returns_not_found_for_booking_of_another_branch(): void
    {
        $this->actingAsSuper();
        $booking = $this->makeBooking(['branch_id' => 2]);

        $this->withFreightSession()
            ->get(route('nas-freights.bookings.print', $booking->id))
            ->assertNotFound();
    }

    public function test_list_print_icon_opens_print_page_not_edit_page(): void
    {
        $this->actingAsSuper();
        $booking = $this->makeBooking();

        $response = $this->withFreightSession()
            ->getJson(route('nas-freights.bookings.index', ['draw' => 1]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $action = $response->json('data.0.action');

        $this->assertStringContainsString(route('nas-freights.bookings.print', $booking->id), $action);
        $this->assertStringContainsString('target="_blank"', $action);
        $this->assertStringContainsString('fa-print', $action);
    }
}

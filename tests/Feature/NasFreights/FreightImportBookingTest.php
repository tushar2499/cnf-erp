<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightBooking;
use App\Models\NasFreights\NasFreightsRfq;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreightImportBookingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            $table->string('vessel_name')->nullable();
            $table->string('voyage_no')->nullable();
            $table->string('bl_no')->nullable();
            $table->string('igm_no')->nullable();
            $table->string('delivery_order_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
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
            'is_super'  => true,
        ]);

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

    public function test_store_creates_import_booking_with_igm_and_container_tracking(): void
    {
        $this->withSession($this->sessionWithBranch());

        $response = $this->post(route('nas-freights.freight-import-bookings.store'), [
            'booking_date'      => '2026-08-29',
            'service_type'      => 'FCL',
            'customer_id'       => null,
            'pol'               => 'Singapore',
            'pod'               => 'Chittagong',
            'bl_no'             => 'SING-123',
            'igm_no'            => '2026-001234',
            'delivery_order_no' => 'DO-8899',
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
        $this->assertSame('2026-001234', $booking->igm_no);
        $this->assertSame('DO-8899', $booking->delivery_order_no);
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
            'igm_no'             => 'OLD-IGM',
        ]);

        $this->put(route('nas-freights.freight-import-bookings.update', $booking->id), [
            'booking_date' => '2026-08-29',
            'service_type' => 'FCL',
            'igm_no'       => 'NEW-IGM',
            'items'        => [[
                'item_type'    => 'package',
                'package_type' => 'Carton',
                'commodity'    => 'BOOTS',
                'quantity'     => 5,
            ]],
        ])->assertRedirect();

        $this->assertSame('NEW-IGM', $booking->fresh()->igm_no);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('BOOTS', $booking->items()->first()->commodity);
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
            ->assertJsonPath('message', 'Freight Import Booking '.$booking->freight_booking_no.' deleted.');

        $this->assertDatabaseCount('nas_freights_freight_bookings', 0);
    }
}

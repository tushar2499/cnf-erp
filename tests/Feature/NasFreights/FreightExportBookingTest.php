<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreightExportBookingTest extends TestCase
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

        Schema::create('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('export_booking_no')->unique();
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
            $table->string('export_bl_no')->nullable();
            $table->string('booking_note_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
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

    public function test_store_creates_export_booking_with_bl_and_booking_note(): void
    {
        $this->withSession($this->sessionWithBranch());

        $response = $this->post(route('nas-freights.freight-export-bookings.store'), [
            'booking_date'     => '2026-08-29',
            'service_type'     => 'FCL',
            'customer_id'      => null,
            'pol'              => 'Chittagong',
            'pod'              => 'Hamburg',
            'export_bl_no'     => 'CHT-456',
            'booking_note_no'  => 'BN-7788',
            'etd'              => '2026-09-05',
            'eta'              => '2026-09-25',
            'status'           => 'Confirmed',
            'items'            => [[
                'item_type'         => 'container',
                'container_size'    => '40HC',
                'container_no'      => 'MAEU9988776',
                'seal_no'           => 'SEAL5544',
                'hs_code'           => '610910',
                'commodity'         => 'GARMENTS',
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
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('MAEU9988776', $booking->items()->first()->container_no);
        $this->assertSame('SEAL5544', $booking->items()->first()->seal_no);
        $this->assertSame('BD', $booking->items()->first()->country_of_origin);
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

        $booking = NasFreightsFreightExportBooking::create([
            'export_booking_no' => NasFreightsFreightExportBooking::generateExportBookingNo(),
            'branch_id'         => 1,
            'booking_date'      => now()->toDateString(),
            'service_type'      => 'FCL',
            'booking_note_no'   => 'OLD-BN',
        ]);

        $this->put(route('nas-freights.freight-export-bookings.update', $booking->id), [
            'booking_date'    => '2026-08-29',
            'service_type'    => 'FCL',
            'booking_note_no' => 'NEW-BN',
            'items'           => [[
                'item_type'    => 'package',
                'package_type' => 'Carton',
                'commodity'    => 'TEXTILES',
                'quantity'     => 8,
            ]],
        ])->assertRedirect();

        $this->assertSame('NEW-BN', $booking->fresh()->booking_note_no);
        $this->assertSame(1, $booking->items()->count());
        $this->assertSame('TEXTILES', $booking->items()->first()->commodity);
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
            ->assertJsonPath('message', 'Freight Export Booking '.$booking->export_booking_no.' deleted.');

        $this->assertDatabaseCount('nas_freights_freight_export_bookings', 0);
    }
}

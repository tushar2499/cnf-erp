<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerBillCapacityFilterTest extends TestCase
{
    private int $customerId;

    private int $bookingId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedUser();
        $this->seedBookingWithItems();
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

        Schema::create('nas_freights_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('customer_id')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('nas_freights_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('job_no')->nullable();
            $table->string('sales_type')->nullable();
            $table->date('job_date')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('tds_percent', 8, 2)->nullable();
            $table->decimal('vat_percent', 8, 2)->nullable();
            $table->decimal('ait_percent', 8, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('delivery_status')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_booking_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->decimal('customer_rate', 15, 2)->nullable();
            $table->decimal('demurrage_days')->nullable();
            $table->decimal('cus_demurrage_charge', 15, 2)->nullable();
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_customer_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('booking_item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->timestamps();
        });
    }

    private function seedUser(): void
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

    private function seedBookingWithItems(): void
    {
        $this->customerId = DB::table('nas_freights_customers')->insertGetId([
            'customer_id' => 'CUS-0001',
            'name'        => 'ABC Exports',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->bookingId = NasFreightsBooking::create([
            'job_no'          => 'TMS-COR-2026-000001',
            'branch_id'       => 1,
            'sales_type'      => 'LOCAL',
            'job_date'        => '2026-10-01',
            'customer_id'     => $this->customerId,
            'customer_name'   => 'ABC Exports',
            'delivery_date'   => '2026-10-05',
            'tds_percent'     => 0,
            'vat_percent'     => 0,
            'ait_percent'     => 0,
            'status'          => 'Draft',
            'delivery_status' => 'Pending',
        ])->id;

        foreach ([
            'VAN1' => '05 M/T',
            'VAN2' => '10 M/T',
            'VAN3' => '03-10 M/T',
            'VAN4' => '1.5-03 M/T',
            'VAN5' => null,
            'VAN6' => '01 CBM',
            'VAN7' => '03-05 M/T',
        ] as $vanNo => $capacity) {
            NasFreightsBookingItem::create([
                'booking_id'    => $this->bookingId,
                'cover_van_no'  => $vanNo,
                'capacity'      => $capacity,
                'qty'           => 10,
                'customer_rate' => 100,
            ]);
        }
    }

    /**
     * Call the load-items endpoint and return item codes in response order
     *
     * @param  string|null  $capacity  Raw capacity filter, null omits the field entirely
     * @return array<int, string>
     */
    private function loadItemCodesInOrder(?string $capacity = null): array
    {
        $payload = [
            'from_date'   => '2026-10-01',
            'to_date'     => '2026-10-31',
            'customer_id' => $this->customerId,
        ];

        if ($capacity !== null) {
            $payload['capacity'] = $capacity;
        }

        $response = $this->withSession([
            'active_company_id'        => 1,
            'active_company_slug'      => 'nas-freights',
            'active_company_name'      => 'NAS Freights',
            'active_company_type'      => 'freight',
            'nas_freights_branch_id'   => 1,
            'nas_freights_branch_name' => 'Corporate',
            'nas_freights_branch_code' => 'COR',
        ])->postJson(route('nas-freights.customer-bills.load-items'), $payload);

        $response->assertOk();

        return collect($response->json('items'))->pluck('item_code')->all();
    }

    /**
     * Call the load-items endpoint and return item codes sorted for set comparison
     *
     * @param  string|null  $capacity  Raw capacity filter, null omits the field entirely
     * @return array<int, string>
     */
    private function loadItemCodes(?string $capacity = null): array
    {
        return collect($this->loadItemCodesInOrder($capacity))->sort()->values()->all();
    }

    public function test_blank_capacity_filter_returns_all_items(): void
    {
        $this->assertSame(['VAN1', 'VAN2', 'VAN3', 'VAN4', 'VAN5', 'VAN6', 'VAN7'], $this->loadItemCodes());
        $this->assertSame(['VAN1', 'VAN2', 'VAN3', 'VAN4', 'VAN5', 'VAN6', 'VAN7'], $this->loadItemCodes(''));
        $this->assertSame(['VAN1', 'VAN2', 'VAN3', 'VAN4', 'VAN5', 'VAN6', 'VAN7'], $this->loadItemCodes('   '));
    }

    public function test_single_number_matches_exact_capacity_and_covering_ranges(): void
    {
        $this->assertSame(['VAN1', 'VAN3', 'VAN7'], $this->loadItemCodes('5'));
        $this->assertSame(['VAN1', 'VAN3', 'VAN7'], $this->loadItemCodes('05 M/T'));
        $this->assertSame(['VAN2', 'VAN3'], $this->loadItemCodes('10'));
        $this->assertSame(['VAN1', 'VAN3', 'VAN7'], $this->loadItemCodes('05'));
    }

    public function test_range_filter_matches_overlapping_ranges_only(): void
    {
        $this->assertSame(['VAN3', 'VAN4', 'VAN7'], $this->loadItemCodes('2 - 3'));
        $this->assertSame(['VAN3', 'VAN4', 'VAN7'], $this->loadItemCodes('2-3 M/T'));
        $this->assertSame(['VAN1', 'VAN3', 'VAN7'], $this->loadItemCodes('4-6'));
    }

    public function test_unit_is_respected_when_both_sides_declare_one(): void
    {
        $this->assertSame(['VAN6'], $this->loadItemCodes('1 CBM'));
        $this->assertSame([], $this->loadItemCodes('1 M/T'));
        $this->assertSame(['VAN1', 'VAN3', 'VAN7'], $this->loadItemCodes('5 MT'));
    }

    public function test_text_without_numbers_matches_by_substring(): void
    {
        $this->assertSame(['VAN6'], $this->loadItemCodes('cbm'));
    }

    public function test_already_billed_items_stay_excluded_with_capacity_filter(): void
    {
        DB::table('nas_freights_customer_bill_items')->insert([
            'bill_id'     => 1,
            'booking_id'  => $this->bookingId,
            'item_code'   => 'VAN1',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->assertSame(['VAN3', 'VAN7'], $this->loadItemCodes('5'));
    }

    public function test_items_are_returned_in_booking_date_ascending_order(): void
    {
        foreach ([['TMS-COR-2026-000002', '2026-10-05', 'VANA'], ['TMS-COR-2026-000003', '2026-10-20', 'VANB']] as [$jobNo, $jobDate, $vanNo]) {
            $bookingId = NasFreightsBooking::create([
                'job_no'          => $jobNo,
                'branch_id'       => 1,
                'sales_type'      => 'LOCAL',
                'job_date'        => $jobDate,
                'customer_id'     => $this->customerId,
                'customer_name'   => 'ABC Exports',
                'delivery_date'   => $jobDate,
                'tds_percent'     => 0,
                'vat_percent'     => 0,
                'ait_percent'     => 0,
                'status'          => 'Draft',
                'delivery_status' => 'Pending',
            ])->id;

            NasFreightsBookingItem::create([
                'booking_id'    => $bookingId,
                'cover_van_no'  => $vanNo,
                'capacity'      => '05 M/T',
                'qty'           => 5,
                'customer_rate' => 50,
            ]);
        }

        $this->assertSame(
            ['VAN1', 'VAN3', 'VAN7', 'VANA', 'VANB'],
            $this->loadItemCodesInOrder('5')
        );
        $this->assertSame(
            ['VAN1', 'VAN2', 'VAN3', 'VAN4', 'VAN5', 'VAN6', 'VAN7', 'VANA', 'VANB'],
            $this->loadItemCodesInOrder('')
        );
    }
}

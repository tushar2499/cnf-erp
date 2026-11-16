<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsBooking;
use App\Models\NasFreights\NasFreightsBookingItem;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplierBillLoadItemsOrderTest extends TestCase
{
    private const SUPPLIER_A = 11;

    private const SUPPLIER_B = 22;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedUser();
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
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->decimal('supplier_rate', 15, 2)->nullable();
            $table->decimal('customer_rate', 15, 2)->nullable();
            $table->decimal('demurrage_days')->nullable();
            $table->decimal('cus_demurrage_charge', 15, 2)->nullable();
            $table->decimal('sup_demurrage_charge', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        Schema::create('nas_freights_supplier_bill_items', function (Blueprint $table) {
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

    /**
     * Create a booking with one item per van number
     *
     * @param  array<int, string>  $vanNos
     */
    private function seedBooking(string $jobNo, string $jobDate, array $vanNos, int $supplierId = self::SUPPLIER_A): int
    {
        $bookingId = NasFreightsBooking::create([
            'job_no'          => $jobNo,
            'branch_id'       => 1,
            'sales_type'      => 'LOCAL',
            'job_date'        => $jobDate,
            'customer_name'   => 'ABC Exports',
            'delivery_date'   => $jobDate,
            'tds_percent'     => 0,
            'vat_percent'     => 0,
            'ait_percent'     => 0,
            'status'          => 'Draft',
            'delivery_status' => 'Pending',
        ])->id;

        foreach ($vanNos as $vanNo) {
            NasFreightsBookingItem::create([
                'booking_id'    => $bookingId,
                'cover_van_no'  => $vanNo,
                'supplier_id'   => $supplierId,
                'qty'           => 1,
                'supplier_rate' => 100,
            ]);
        }

        return $bookingId;
    }

    /**
     * Call the load-items endpoint and return item codes in response order
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function loadItemCodesInOrder(array $payload = []): array
    {
        $response = $this->withSession([
            'active_company_id'        => 1,
            'active_company_slug'      => 'nas-freights',
            'active_company_name'      => 'NAS Freights',
            'active_company_type'      => 'freight',
            'nas_freights_branch_id'   => 1,
            'nas_freights_branch_name' => 'Corporate',
            'nas_freights_branch_code' => 'COR',
        ])->postJson(route('nas-freights.supplier-bills.load-items'), array_merge([
            'from_date' => '2026-10-01',
            'to_date'   => '2026-10-31',
        ], $payload));

        $response->assertOk();

        return collect($response->json('items'))->pluck('item_code')->all();
    }

    public function test_items_are_returned_in_job_date_ascending_order(): void
    {
        $this->seedBooking('TMS-COR-2026-000001', '2026-10-20', ['LATE']);
        $this->seedBooking('TMS-COR-2026-000002', '2026-10-05', ['EARLY']);
        $this->seedBooking('TMS-COR-2026-000003', '2026-10-12', ['MID']);

        $this->assertSame(['EARLY', 'MID', 'LATE'], $this->loadItemCodesInOrder());
    }

    public function test_items_of_same_job_date_keep_item_id_order(): void
    {
        $this->seedBooking('TMS-COR-2026-000001', '2026-10-10', ['B1', 'B2']);
        $this->seedBooking('TMS-COR-2026-000002', '2026-10-10', ['B3']);
        $this->seedBooking('TMS-COR-2026-000003', '2026-10-01', ['A1']);

        $this->assertSame(['A1', 'B1', 'B2', 'B3'], $this->loadItemCodesInOrder());
    }

    public function test_supplier_filter_keeps_job_date_order_and_excludes_other_suppliers(): void
    {
        $this->seedBooking('TMS-COR-2026-000001', '2026-10-25', ['A-LATE']);
        $this->seedBooking('TMS-COR-2026-000002', '2026-10-15', ['B-ONLY'], self::SUPPLIER_B);
        $this->seedBooking('TMS-COR-2026-000003', '2026-10-03', ['A-EARLY']);

        $this->assertSame(
            ['A-EARLY', 'A-LATE'],
            $this->loadItemCodesInOrder(['supplier_id' => self::SUPPLIER_A])
        );
    }

    public function test_date_range_and_already_billed_items_stay_excluded(): void
    {
        $this->seedBooking('TMS-COR-2026-000001', '2026-09-30', ['BEFORE']);
        $billedBookingId = $this->seedBooking('TMS-COR-2026-000002', '2026-10-08', ['BILLED']);
        $this->seedBooking('TMS-COR-2026-000003', '2026-10-18', ['OPEN']);
        $this->seedBooking('TMS-COR-2026-000004', '2026-11-01', ['AFTER']);

        DB::table('nas_freights_supplier_bill_items')->insert([
            'bill_id'    => 1,
            'booking_id' => $billedBookingId,
            'item_code'  => 'BILLED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(['OPEN'], $this->loadItemCodesInOrder());
    }
}

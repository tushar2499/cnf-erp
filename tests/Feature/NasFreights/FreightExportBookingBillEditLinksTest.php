<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FreightExportBookingBillEditLinksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        view()->share('errors', new ViewErrorBag);

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
    }

    private function makeUser(bool $isSuper): User
    {
        $user = User::create([
            'name'      => 'Test User',
            'username'  => 'tester',
            'email'     => 'tester@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]);
        $user->forceFill(['is_super' => $isSuper])->save();

        return $user;
    }

    private function makeBill(): NasFreightsFreightExportBookingBill
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001']);
        $booking->id = 77;
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect());
        $booking->setRelation('bills', collect());
        $booking->setRelation('expense', null);

        $bill = new NasFreightsFreightExportBookingBill([
            'bill_no'       => 'BILL-0001',
            'bill_type'     => 'Customer',
            'bill_date'     => '2026-11-11',
            'currency'      => 'BDT',
            'exchange_rate' => 1,
        ]);
        $bill->id = 55;
        $bill->setRelation('exportBooking', $booking);
        $bill->setRelation('items', collect());

        return $bill;
    }

    private function renderEdit(): string
    {
        $bill = $this->makeBill();

        return view('nas-freights.freight-export-booking-bills.create', [
            'bill'              => $bill,
            'booking'           => $bill->exportBooking,
            'existingBillTypes' => [],
            'descriptions'      => collect(),
            'canAddDescription' => false,
        ])->render();
    }

    public function test_edit_page_links_to_bill_view_and_print(): void
    {
        $this->actingAs($this->makeUser(true));

        $html = $this->renderEdit();

        $this->assertStringContainsString('href="'.route('nas-freights.freight-export-booking-bills.show', 55).'"', $html);
        $this->assertStringContainsString('href="'.route('nas-freights.freight-export-booking-bills.print', 55).'"', $html);
        $this->assertMatchesRegularExpression('/<a href="[^"]+\/55\/print" target="_blank" rel="noopener"/', $html);
    }

    public function test_edit_page_hides_links_without_permission(): void
    {
        $this->actingAs($this->makeUser(false));

        $html = $this->renderEdit();

        $this->assertStringNotContainsString('href="'.route('nas-freights.freight-export-booking-bills.show', 55).'"', $html);
        $this->assertStringNotContainsString('href="'.route('nas-freights.freight-export-booking-bills.print', 55).'"', $html);
    }

    public function test_create_page_has_no_view_or_print_links(): void
    {
        $this->actingAs($this->makeUser(true));

        $html = view('nas-freights.freight-export-booking-bills.create', [
            'booking'           => null,
            'existingBillTypes' => [],
            'descriptions'      => collect(),
            'canAddDescription' => false,
        ])->render();

        $this->assertStringNotContainsString('fa-print', $html);
        $this->assertStringNotContainsString('<i class="fa fa-eye me-1"></i> View', $html);
    }
}

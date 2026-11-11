<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreightExportBookingBillBookingLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    private function makeBooking(): NasFreightsFreightExportBooking
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001']);
        $booking->id = 77;
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('bills', collect());

        return $booking;
    }

    private function renderPartial(array $data = []): string
    {
        return view('nas-freights.freight-export-booking-bills._booking-info', ['booking' => $this->makeBooking()] + $data)->render();
    }

    public function test_link_to_booking_page_opens_in_new_tab_on_form_pages(): void
    {
        $this->actingAs($this->makeUser(true));

        $html = $this->renderPartial();

        $this->assertStringContainsString(route('nas-freights.freight-export-bookings.show', 77), $html);
        $this->assertStringContainsString('Go to Booking/Job Page', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener"', $html);
    }

    public function test_link_to_booking_page_stays_in_same_tab_on_show_page(): void
    {
        $this->actingAs($this->makeUser(true));

        $html = $this->renderPartial(['openInNewTab' => false]);

        $this->assertStringContainsString(route('nas-freights.freight-export-bookings.show', 77), $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function test_link_hidden_without_booking_view_permission(): void
    {
        $this->actingAs($this->makeUser(false));

        $html = $this->renderPartial();

        $this->assertStringNotContainsString('Go to Booking/Job Page', $html);
        $this->assertStringNotContainsString(route('nas-freights.freight-export-bookings.show', 77), $html);
    }
}

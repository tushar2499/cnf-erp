<?php

namespace Tests\Feature\NasFreights;

use App\Http\Requests\NasFreights\FreightExportBookingBill\StoreFreightExportBookingBillRequest;
use App\Http\Requests\NasFreights\FreightExportBookingBill\UpdateFreightExportBookingBillRequest;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FreightExportBookingBillAttentionDesignationTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $billAttributes
     */
    private function renderPrint(array $billAttributes, string $billType = 'Customer'): string
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001']);
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect());
        $booking->setRelation('partyInvoices', collect());

        $bill = new NasFreightsFreightExportBookingBill([
            'bill_no'          => 'BILL-0001',
            'bill_type'        => $billType,
            'currency'         => 'BDT',
            'exchange_rate'    => 1,
            'total_amount'     => 100,
            'total_bdt_amount' => 100,
            'vat_amount'       => 0,
            'vat_amount_bdt'   => 0,
        ] + $billAttributes);
        $bill->setRelation('exportBooking', $booking);
        $bill->setRelation('items', collect());

        return view('nas-freights.freight-export-booking-bills.print', ['bill' => $bill])->render();
    }

    public function test_attention_and_designation_are_fillable_on_the_bill(): void
    {
        $bill = new NasFreightsFreightExportBookingBill(['attention' => 'Mr. Rahman', 'designation' => 'Manager']);

        $this->assertSame('Mr. Rahman', $bill->attention);
        $this->assertSame('Manager', $bill->designation);
    }

    public function test_print_shows_attention_and_designation_under_the_addressee(): void
    {
        $html = $this->renderPrint(['attention' => 'Mr. Rahman', 'designation' => 'Manager, Logistics']);

        $this->assertStringContainsString('<strong>ATTN: Mr. Rahman, Manager, Logistics</strong>', $html);
        $this->assertStringNotContainsString('Attention:', $html);
        $this->assertStringNotContainsString('Designation:', $html);
        $this->assertGreaterThan(strpos($html, 'To,'), strpos($html, 'ATTN:'));
        $this->assertLessThan(strpos($html, 'bill-title">'), strpos($html, 'ATTN:'));
    }

    public function test_print_shows_them_on_overseas_agent_bill_too(): void
    {
        $html = $this->renderPrint(['attention' => 'Ms. Lee', 'designation' => 'Director'], 'Overseas Agent');

        $this->assertStringContainsString('<strong>ATTN: Ms. Lee, Director</strong>', $html);
    }

    public function test_print_hides_blank_lines(): void
    {
        $html = $this->renderPrint(['attention' => null, 'designation' => null]);

        $this->assertStringNotContainsString('ATTN:', $html);
    }

    public function test_print_shows_attention_alone_without_trailing_comma(): void
    {
        $html = $this->renderPrint(['attention' => 'Mr. Rahman', 'designation' => null]);

        $this->assertStringContainsString('<strong>ATTN: Mr. Rahman</strong>', $html);
        $this->assertStringNotContainsString('Mr. Rahman,', $html);
    }

    public function test_print_shows_designation_alone_without_label_or_comma(): void
    {
        $html = $this->renderPrint(['attention' => null, 'designation' => 'Manager']);

        $this->assertStringNotContainsString('ATTN:', $html);
        $this->assertMatchesRegularExpression('/<div>\s*<strong>Manager<\/strong>\s*<\/div>/', $html);
    }

    public function test_print_escapes_html_in_attention(): void
    {
        $html = $this->renderPrint(['attention' => '<script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_store_and_update_rules_make_both_fields_optional(): void
    {
        foreach ($this->requestClasses() as $requestClass) {
            $rules = $this->attentionRules($requestClass);

            $this->assertTrue(Validator::make([], $rules)->passes());
            $this->assertTrue(Validator::make(['attention' => 'Mr. Rahman', 'designation' => null], $rules)->passes());
            $this->assertTrue(Validator::make(['attention' => null, 'designation' => 'Manager'], $rules)->passes());
        }
    }

    public function test_store_and_update_rules_cap_both_fields_at_150_characters(): void
    {
        foreach ($this->requestClasses() as $requestClass) {
            $rules = $this->attentionRules($requestClass);

            $tooLong = Validator::make(['attention' => str_repeat('a', 151), 'designation' => str_repeat('d', 151)], $rules);
            $this->assertTrue($tooLong->errors()->has('attention'));
            $this->assertTrue($tooLong->errors()->has('designation'));

            $this->assertTrue(Validator::make(['attention' => str_repeat('a', 150), 'designation' => str_repeat('d', 150)], $rules)->passes());
        }
    }

    /**
     * @return list<class-string>
     */
    private function requestClasses(): array
    {
        return [StoreFreightExportBookingBillRequest::class, UpdateFreightExportBookingBillRequest::class];
    }

    /**
     * @return array<string, mixed>
     */
    private function attentionRules(string $requestClass): array
    {
        return Arr::only((new $requestClass)->rules(), ['attention', 'designation']);
    }
}

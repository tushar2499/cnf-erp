<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use Tests\TestCase;

class FreightExportBookingBillPrintExchangeRateTest extends TestCase
{
    private function renderPrint(
        string $billType,
        string $billCurrency,
        float $billRate,
        ?string $bookingCurrency,
        ?float $bookingRate,
    ): string {
        $booking = new NasFreightsFreightExportBooking([
            'export_booking_no' => 'EXP-0001',
            'currency'          => $bookingCurrency,
            'exchange_rate'     => $bookingRate,
        ]);
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect());
        $booking->setRelation('partyInvoices', collect());

        $bill = new NasFreightsFreightExportBookingBill([
            'bill_no'          => 'BILL-0001',
            'bill_type'        => $billType,
            'currency'         => $billCurrency,
            'exchange_rate'    => $billRate,
            'total_amount'     => 100,
            'total_bdt_amount' => 100 * $billRate,
            'vat_amount'       => 0,
            'vat_amount_bdt'   => 0,
        ]);
        $bill->setRelation('exportBooking', $booking);
        $bill->setRelation('items', collect());

        return view('nas-freights.freight-export-booking-bills.print', ['bill' => $bill])->render();
    }

    public function test_booking_rate_shown_when_booking_currency_foreign_and_bill_is_bdt(): void
    {
        $html = $this->renderPrint('Customer', 'BDT', 1, 'USD', 121.5);

        $this->assertStringContainsString('Exchange Rate: 1 USD = BDT 121.50', $html);
    }

    public function test_rate_hidden_when_booking_currency_is_bdt_and_bill_is_bdt(): void
    {
        $html = $this->renderPrint('Customer', 'BDT', 1, 'BDT', 1);

        $this->assertStringNotContainsString('Exchange Rate:', $html);
    }

    public function test_rate_hidden_when_booking_has_no_currency_and_bill_is_bdt(): void
    {
        $html = $this->renderPrint('Customer', 'BDT', 1, null, null);

        $this->assertStringNotContainsString('Exchange Rate:', $html);
    }

    public function test_bill_rate_shown_when_bill_is_foreign_even_if_booking_is_bdt(): void
    {
        $html = $this->renderPrint('Customer', 'EUR', 130, 'BDT', 1);

        $this->assertStringContainsString('Exchange Rate: 1 EUR = BDT 130.00', $html);
    }

    public function test_bill_rate_wins_over_booking_rate_when_bill_is_foreign(): void
    {
        $html = $this->renderPrint('Customer', 'EUR', 130, 'USD', 121.5);

        $this->assertStringContainsString('Exchange Rate: 1 EUR = BDT 130.00', $html);
        $this->assertStringNotContainsString('1 USD = BDT', $html);
    }

    public function test_bill_rate_shown_on_overseas_agent_bill(): void
    {
        $html = $this->renderPrint('Overseas Agent', 'USD', 122, 'USD', 121.5);

        $this->assertStringContainsString('Exchange Rate: 1 USD = BDT 122.00', $html);
    }
}

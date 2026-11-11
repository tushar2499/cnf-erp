<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use Tests\TestCase;

class FreightExportBookingBillPrintExpDateTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $bookingAttributes
     */
    private function renderPrint(array $bookingAttributes): string
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001'] + $bookingAttributes);
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect());
        $booking->setRelation('partyInvoices', collect());

        $bill = new NasFreightsFreightExportBookingBill([
            'bill_no'          => 'BILL-0001',
            'bill_type'        => 'Customer',
            'currency'         => 'BDT',
            'exchange_rate'    => 1,
            'total_amount'     => 100,
            'total_bdt_amount' => 100,
            'vat_amount'       => 0,
            'vat_amount_bdt'   => 0,
        ]);
        $bill->setRelation('exportBooking', $booking);
        $bill->setRelation('items', collect());

        return view('nas-freights.freight-export-booking-bills.print', ['bill' => $bill])->render();
    }

    public function test_exp_date_shown_after_exp_no(): void
    {
        $html = $this->renderPrint(['exp_no' => 'EXP-123', 'exp_date' => '2026-11-05']);

        $this->assertStringContainsString('EXP DATE', $html);
        $this->assertStringContainsString('05.11.2026', $html);
        $this->assertGreaterThan(strpos($html, 'EXP NO'), strpos($html, 'EXP DATE'));
    }

    public function test_exp_date_hidden_when_empty(): void
    {
        $html = $this->renderPrint(['exp_no' => 'EXP-123', 'exp_date' => null]);

        $this->assertStringContainsString('EXP NO', $html);
        $this->assertStringNotContainsString('EXP DATE', $html);
    }
}

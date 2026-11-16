<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\NasFreights\NasFreightsFreightExportBookingPartyInvoice;
use Tests\TestCase;

class FreightExportBookingBillPrintHawbHblTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $bookingAttributes
     * @param  array<int, array<string, mixed>>  $partyInvoices
     */
    private function renderPrint(array $bookingAttributes, array $partyInvoices = []): string
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001'] + $bookingAttributes);
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect());
        $booking->setRelation('partyInvoices', collect($partyInvoices)->map(
            fn (array $invoice) => new NasFreightsFreightExportBookingPartyInvoice($invoice)
        ));

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

    public function test_hawb_hbl_no_and_date_shown_under_mawb_bl_no_and_its_date(): void
    {
        $html = $this->renderPrint(
            [
                'export_bl_no'  => 'MAWB-555',
                'booking_date'  => '2026-11-01',
                'hawb_hbl_no'   => 'HBL-2026-0001',
                'hawb_hbl_date' => '2026-11-07',
                'pol'           => 'DAC',
            ],
            [['invoice_no' => 'INV-77', 'invoice_date' => '2026-11-03']],
        );

        $this->assertStringContainsString('HBL-2026-0001', $html);
        $this->assertStringContainsString('07.11.2026', $html);

        $positions = [
            'MAWB / B/L NO.'  => strpos($html, 'MAWB / B/L NO.'),
            'DATE row'        => strpos($html, '<td class="k">DATE</td>'),
            'HAWB/HBL NO'     => strpos($html, 'HAWB/HBL NO'),
            'HAWB/HBL DATE'   => strpos($html, 'HAWB/HBL DATE'),
            'PORT OF LOADING' => strpos($html, 'PORT OF LOADING'),
            'INVOICE NO'      => strpos($html, 'INVOICE NO'),
        ];

        foreach ($positions as $label => $position) {
            $this->assertNotFalse($position, "{$label} missing from print");
        }

        $sorted = $positions;
        asort($sorted);
        $this->assertSame(array_keys($positions), array_keys($sorted));
    }

    public function test_hawb_hbl_rows_shown_even_without_party_invoices(): void
    {
        $html = $this->renderPrint(['hawb_hbl_no' => 'HBL-9', 'hawb_hbl_date' => '2026-01-02']);

        $this->assertStringContainsString('HBL-9', $html);
        $this->assertStringContainsString('02.01.2026', $html);
        $this->assertStringNotContainsString('INVOICE NO', $html);
    }

    public function test_hawb_hbl_date_hidden_when_empty(): void
    {
        $html = $this->renderPrint(['hawb_hbl_no' => 'HBL-9', 'hawb_hbl_date' => null]);

        $this->assertStringContainsString('HAWB/HBL NO', $html);
        $this->assertStringNotContainsString('HAWB/HBL DATE', $html);
    }

    public function test_hawb_hbl_rows_hidden_when_both_empty(): void
    {
        $html = $this->renderPrint(['hawb_hbl_no' => null, 'hawb_hbl_date' => null]);

        $this->assertStringNotContainsString('HAWB/HBL', $html);
    }
}

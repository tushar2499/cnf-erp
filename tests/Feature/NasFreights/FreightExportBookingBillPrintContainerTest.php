<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingBill;
use App\Models\NasFreights\NasFreightsFreightExportBookingItem;
use Tests\TestCase;

class FreightExportBookingBillPrintContainerTest extends TestCase
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function renderPrint(array $items): string
    {
        $booking = new NasFreightsFreightExportBooking(['export_booking_no' => 'EXP-0001']);
        $booking->setRelation('customer', null);
        $booking->setRelation('overseasAgent', null);
        $booking->setRelation('items', collect($items)->map(fn ($attributes) => new NasFreightsFreightExportBookingItem($attributes)));
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

    public function test_container_shown_after_cbm_as_number_slash_size(): void
    {
        $html = $this->renderPrint([
            ['item_type' => 'container', 'container_no' => '112233', 'container_size' => '40GP', 'volume_cbm' => 12],
        ]);

        $this->assertStringContainsString('112233/40GP', $html);
        $this->assertGreaterThan(strpos($html, '>CBM<'), strpos($html, '>CONTAINER<'));
    }

    public function test_multiple_containers_are_comma_separated(): void
    {
        $html = $this->renderPrint([
            ['item_type' => 'container', 'container_no' => '112233', 'container_size' => '40GP'],
            ['item_type' => 'container', 'container_no' => '445566', 'container_size' => '20GP'],
        ]);

        $this->assertStringContainsString('112233/40GP, 445566/20GP', $html);
    }

    public function test_container_without_size_shows_number_only(): void
    {
        $html = $this->renderPrint([
            ['item_type' => 'container', 'container_no' => '112233', 'container_size' => null],
        ]);

        $this->assertStringContainsString('<td>112233</td>', $html);
        $this->assertStringNotContainsString('112233/', $html);
    }

    public function test_container_row_hidden_without_container_numbers(): void
    {
        $html = $this->renderPrint([
            ['item_type' => 'container', 'container_no' => null, 'container_size' => '40GP'],
            ['item_type' => 'package', 'container_no' => '999', 'package_type' => 'BAG'],
        ]);

        $this->assertStringNotContainsString('>CONTAINER<', $html);
    }

    public function test_duplicate_containers_listed_once(): void
    {
        $html = $this->renderPrint([
            ['item_type' => 'container', 'container_no' => '112233', 'container_size' => '40GP'],
            ['item_type' => 'container', 'container_no' => '112233', 'container_size' => '40GP'],
        ]);

        $this->assertStringContainsString('<td>112233/40GP</td>', $html);
    }
}

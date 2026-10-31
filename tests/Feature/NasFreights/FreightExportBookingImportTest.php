<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsCustomer;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsOverseasAgent;
use App\Models\NasFreights\NasFreightsShippingCarrier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FreightExportBookingImportTest extends TestCase
{
    private const HEADER = [
        'Srl.', 'Booking No', 'Booking Date', 'Salesperson', 'Customer (Exporter)', 'Overseas Agent', 'Shipping Carrier',
        'Service', 'Incoterms', 'Currency', 'POL', 'POD', 'Place of Receipt', 'Place of Delivery', 'Vessel', 'Voyage No',
        'Export B/L No', 'Booking Note No', 'ETD', 'ETA', 'Type', 'Size / Package', 'Container No', 'Seal No', 'HS Code',
        'Commodity', 'Qty', 'Weight', 'CBM', 'Origin', 'DG', 'Special', 'Status', 'Notes', 'Commodity2', 'Record Info',
        'Created', 'Last Updated', 'Cover Van No', 'Challan No', 'Capacity', 'Supplier', 'Qty3', 'Sup. Rate', 'Cus. Rate',
        'Demrr. Days', 'Cus. Demurrage', 'Sup. Demurrage', 'Amount', 'From', 'To',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('nas_freights_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('nas_freights_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('id_prefix');
            $table->string('customer_id');
            $table->string('name');
            $table->string('phone')->default('');
            $table->string('status')->default('Active');
            $table->text('address')->nullable();
            $table->timestamps();
        });
        Schema::create('nas_freights_overseas_agents', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code');
            $table->string('name');
            $table->string('country');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('nas_freights_shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('carrier_code');
            $table->string('name');
            $table->string('scac_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('nas_freights_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('company_name');
            $table->timestamps();
        });
        foreach (['nas_freights_package_types', 'nas_freights_container_types'] as $master) {
            Schema::create($master, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('export_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
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
            $table->json('hs_codes')->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('voyage_no')->nullable();
            $table->string('export_bl_no')->nullable();
            $table->string('booking_note_no')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->decimal('transport_amount', 15, 2)->default(0);
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
            $table->unsignedInteger('package_qty')->nullable();
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
        Schema::create('nas_freights_freight_export_booking_transport_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('export_booking_id');
            $table->string('cover_van_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('capacity')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('supplier_rate', 15, 2)->default(0);
            $table->decimal('customer_rate', 15, 2)->default(0);
            $table->integer('demurrage_days')->default(0);
            $table->decimal('cus_demurrage_charge', 15, 2)->default(0);
            $table->decimal('sup_demurrage_charge', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('location_from')->nullable();
            $table->string('location_to')->nullable();
            $table->timestamps();
        });

        DB::table('nas_freights_branches')->insert(['id' => 1, 'name' => 'Head Office']);
        DB::table('nas_freights_package_types')->insert([['name' => 'Carton'], ['name' => 'Pallet']]);
        DB::table('nas_freights_container_types')->insert([['name' => '20GP'], ['name' => '40HC']]);
        DB::table('nas_freights_suppliers')->insert(['code' => 'SUP-000001', 'company_name' => 'New Hoque Transport']);
        DB::table('employees')->insert([['id' => 5, 'name' => 'Mr. Sajjadul Islam Jahir'], ['id' => 6, 'name' => 'Mr. Kazi Tofiqul Islam']]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows  keyed by header label
     */
    private function workbook(array $rows, string $sheet = 'JAHIR'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $ws = $spreadsheet->getActiveSheet()->setTitle($sheet);
        $ws->setCellValue('B2', 'Booking Information');
        $ws->fromArray(self::HEADER, null, 'A3');

        foreach ($rows as $i => $row) {
            $ws->fromArray(array_map(fn ($h) => $row[$h] ?? null, self::HEADER), null, 'A'.(4 + $i));
        }

        $path = tempnam(sys_get_temp_dir(), 'feb').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'bookings.xlsx', null, null, true);
    }

    private function sampleRow(array $overrides = []): array
    {
        return array_merge([
            'Booking No'          => 'NASEXP26038',
            'Booking Date'        => '8/12/2026',
            'Customer (Exporter)' => 'ZISKA PHARMA ',
            'Overseas Agent'      => 'DEREK OFORI',
            'Shipping Carrier'    => 'ETHIOPIAN AIRLINES',
            'Service'             => 'AIR',
            'Incoterms'           => 'cfr',
            'Currency'            => '  101,286.81 ',
            'POL'                 => 'DHAKA',
            'POD'                 => 'GHANA',
            'Export B/L No'       => '071-6006-1732',
            'ETD'                 => '19-Aug-26',
            'ETA'                 => '—',
            'Type'                => 'CARTOON',
            'Size / Package'      => '3',
            'HS Code'             => '3004.90, 3003.20',
            'Commodity'           => 'MEDICINE',
            'Qty'                 => '12',
            'Weight'              => '1,423.00 KGS',
            'CBM'                 => '2.85',
            'Origin'              => 'BANGLADESH',
        ], $overrides);
    }

    private function callImport(string $uri, array $data)
    {
        return $this->postJson('/api/nas-freights/freight-export-bookings/'.$uri, $data);
    }

    public function test_preview_maps_rows_without_writing(): void
    {
        $response = $this->callImport('import/preview', [
            'branch_id' => 1,
            'file'      => $this->workbook([$this->sampleRow()]),
        ]);

        $response->assertOk()
            ->assertJsonPath('summary', ['total' => 1, 'new' => 1, 'existing' => 0, 'duplicate' => 0, 'invalid' => 0])
            ->assertJsonPath('preview.0.export_booking_no', 'NASEXP26038')
            ->assertJsonPath('preview.0.booking_date', '2026-08-12')
            ->assertJsonPath('preview.0.etd', '2026-08-19')
            ->assertJsonPath('preview.0.eta', null)
            ->assertJsonPath('preview.0.service_type', 'Air')
            ->assertJsonPath('preview.0.incoterms', 'CFR')
            ->assertJsonPath('preview.0.currency', 'BDT')
            ->assertJsonPath('preview.0.hs_codes', ['3004.90', '3003.20'])
            ->assertJsonPath('preview.0.items.0.package_type', 'Carton')
            ->assertJsonPath('preview.0.items.0.quantity', 12)
            ->assertJsonPath('preview.0.items.0.gross_weight', 1423)
            ->assertJsonPath('preview.0.items.0.weight_unit', 'KG');

        $this->assertStringContainsString('Customer \'ZISKA PHARMA\' will be created.', implode('|', $response->json('preview.0.warnings')));
        $this->assertSame(0, NasFreightsFreightExportBooking::count());
        $this->assertSame(0, NasFreightsCustomer::count());
    }

    public function test_import_creates_booking_item_and_missing_masters(): void
    {
        $response = $this->callImport('import', [
            'branch_id' => 1,
            'file'      => $this->workbook([
                $this->sampleRow(),
                $this->sampleRow(['Booking No' => 'NASEXP26059', 'Booking Date' => '8/23/2026']),
            ]),
        ]);

        $response->assertOk()->assertJsonPath('inserted', 2);

        $booking = NasFreightsFreightExportBooking::where('export_booking_no', 'NASEXP26038')->firstOrFail();
        $this->assertSame(1, $booking->branch_id);
        $this->assertSame('2026-08-12', $booking->booking_date->toDateString());
        $this->assertSame('Draft', $booking->status);
        $this->assertSame(['3004.90', '3003.20'], $booking->hs_codes);
        $this->assertSame('ZISKA PHARMA', $booking->customer->name);
        $this->assertSame('GHANA', $booking->overseasAgent->country);
        $this->assertCount(1, $booking->items);
        $this->assertEquals(1423, $booking->items->first()->gross_weight);

        $this->assertSame(1, NasFreightsCustomer::count());
        $this->assertSame(1, NasFreightsOverseasAgent::count());
        $this->assertSame(1, NasFreightsShippingCarrier::count());
    }

    public function test_import_skips_existing_and_in_file_duplicates(): void
    {
        $file = fn () => $this->workbook([$this->sampleRow(), $this->sampleRow()]);

        $first = $this->callImport('import', ['branch_id' => 1, 'file' => $file()]);
        $first->assertOk()->assertJsonPath('inserted', 1)->assertJsonPath('skipped.duplicate', 1);

        $second = $this->callImport('import', ['branch_id' => 1, 'file' => $file()]);
        $second->assertOk()->assertJsonPath('inserted', 0)->assertJsonPath('skipped.existing', 2);

        $this->assertSame(1, NasFreightsFreightExportBooking::count());
    }

    public function test_transport_columns_create_transport_item_and_total(): void
    {
        $this->callImport('import', [
            'branch_id' => 1,
            'file'      => $this->workbook([$this->sampleRow([
                'Cover Van No' => 'BSL/TA-11-0197',
                'Capacity'     => 'Dry Cover Van',
                'Supplier'     => 'New Hoque Transport',
                'Qty3'         => '2',
                'Sup. Rate'    => '1,700.00',
                'Cus. Rate'    => '1,800.00',
            ])]),
        ])->assertOk();

        $booking = NasFreightsFreightExportBooking::with('transportItems')->firstOrFail();
        $transport = $booking->transportItems->first();

        $this->assertSame(1, $transport->supplier_id);
        $this->assertEquals(3600, $transport->amount);
        $this->assertEquals(3600, $booking->transport_amount);
    }

    public function test_salesperson_resolves_from_central_employees_by_sheet_name(): void
    {
        $this->callImport('import', ['branch_id' => 1, 'file' => $this->workbook([$this->sampleRow()], 'JAHIR')])->assertOk();
        $this->assertSame(5, NasFreightsFreightExportBooking::firstOrFail()->salesperson_id);

        $this->callImport('import/preview', ['branch_id' => 1, 'file' => $this->workbook([$this->sampleRow(['Booking No' => 'X2'])], 'TOFIQ')])
            ->assertJsonPath('preview.0.salesperson_id', 6);
    }

    public function test_salesperson_cell_wins_over_sheet_name(): void
    {
        $this->callImport('import/preview', [
            'branch_id' => 1,
            'file'      => $this->workbook([$this->sampleRow(['Salesperson' => 'Mr. Kazi Tofiqul Islam'])], 'JAHIR'),
        ])->assertJsonPath('preview.0.salesperson_id', 6);
    }

    public function test_unknown_salesperson_is_left_blank_with_warning(): void
    {
        $response = $this->callImport('import/preview', ['branch_id' => 1, 'file' => $this->workbook([$this->sampleRow()], 'NAEEM')]);

        $response->assertJsonPath('preview.0.salesperson_id', null);
        $this->assertStringContainsString("Salesperson 'NAEEM' not found", implode('|', $response->json('preview.0.warnings')));
    }

    public function test_row_with_invalid_booking_date_is_reported_and_skipped(): void
    {
        $response = $this->callImport('import', [
            'branch_id' => 1,
            'file'      => $this->workbook([
                $this->sampleRow(['Booking Date' => 'next week']),
                $this->sampleRow(['Booking No' => 'NASEXP26059']),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('skipped.invalid.0.export_booking_no', 'NASEXP26038');
    }

    public function test_import_confirms_previewed_rows(): void
    {
        $preview = $this->callImport('import/preview', ['branch_id' => 1, 'file' => $this->workbook([$this->sampleRow()])])->json('preview');

        $this->callImport('import', ['branch_id' => 1, 'rows' => $preview])
            ->assertOk()
            ->assertJsonPath('inserted', 1);

        $this->assertSame(1, NasFreightsFreightExportBooking::count());
        $this->assertSame(1, NasFreightsFreightExportBooking::firstOrFail()->items()->count());
    }

    public function test_requests_are_validated(): void
    {
        $this->callImport('import/preview', ['branch_id' => 1])->assertStatus(422)->assertJsonValidationErrors('file');
        $this->callImport('import/preview', ['file' => $this->workbook([])])->assertStatus(422)->assertJsonValidationErrors('branch_id');
        $this->callImport('import', ['branch_id' => 99, 'file' => $this->workbook([])])->assertStatus(422)->assertJsonValidationErrors('branch_id');
        $this->callImport('import', ['branch_id' => 1, 'rows' => [['export_booking_no' => 'X']]])->assertStatus(422);
    }
}

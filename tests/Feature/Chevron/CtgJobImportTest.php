<?php

namespace Tests\Feature\Chevron;

use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobType;
use App\Models\Chevron\ChevronPort;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CtgJobImportTest extends TestCase
{
    private const IMPORT_URL = '/api/chevron/jobs/ctg/import';

    private const PREVIEW_URL = '/api/chevron/jobs/ctg/import/preview';

    private const HEADER = [
        'Sl', 'Action', 'Job No', 'Job Date', 'Job Type', 'Type', 'Port', 'Customer', 'Goods Name',
        'B/E', 'B/E Date', 'B/L', 'Lc', 'Invoice', 'M B L/M A W B', 'Gross Weight', 'Qty', 'Unit',
        'Assessable Value', 'Invoice Value', 'Currency Name', 'Currency Rate', 'Invoice Value B D T',
        'Total Payable', 'Remarks', 'Status', 'Bill Status',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        ChevronPort::create(['name' => 'CTG', 'code' => 'CTG', 'branch_id' => 6, 'is_active' => true]);
        ChevronJobType::create(['name' => 'Import', 'is_active' => true]);
        ChevronJobType::create(['name' => 'Export', 'is_active' => true]);
    }

    private function createSchema(): void
    {
        Schema::create('chevron_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->string('prefix', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_job_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_customers', function (Blueprint $table) {
            $table->id();
            $table->string('id_prefix', 20)->nullable();
            $table->string('customer_id', 30)->unique();
            $table->string('name');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->unique();
            $table->unsignedBigInteger('parent_job_id')->nullable();
            $table->unsignedSmallInteger('partial_seq')->nullable();
            $table->unique(['parent_job_id', 'partial_seq']);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('job_type_id')->nullable();
            $table->unsignedBigInteger('port_id')->nullable();
            $table->date('job_date')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('party_name')->nullable();
            $table->string('goods_name')->nullable();
            $table->string('be_no')->nullable();
            $table->date('be_date')->nullable();
            $table->string('bl_no')->nullable();
            $table->string('lc_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('mbl_mawb_no')->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->decimal('pack_quantity', 15, 3)->nullable();
            $table->string('pack_unit')->nullable();
            $table->decimal('assessable_value', 15, 2)->nullable();
            $table->string('currency_type')->nullable();
            $table->decimal('currency_rate', 15, 4)->nullable();
            $table->decimal('assessable_value_bdt', 15, 2)->nullable();
            $table->decimal('total_payable_1', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Active', 'Pending', 'Closed'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Column order of HEADER: 2 job no, 3 job date, 5 type, 6 port, 7 customer, 10 B/E date, 25 status.
     *
     * @param  array<int, mixed>  $overrides
     * @return array<int, mixed>
     */
    private function makeRow(array $overrides = []): array
    {
        return array_replace([
            '1', null, 'CF_IMCTG-2026-000001', '17 Nov 2025', null, 'Import', 'CTG', 'RENATA PLC.', 'GOODS',
            'BE-123', '22 Dec 2025', 'BL-9', 'LC-007', 'INV-1', 'MWB-1', 1500.5, 12, 'PCS',
            250000, null, 'USD', 110.5, 27500000, 28500000, 'SOME REMARK', 'Submitted', null,
        ], $overrides);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function makeXlsx(array $rows, string $headerCell = 'A1', string $sheetTitle = 'JOB INFO'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);
        $sheet->fromArray([self::HEADER], null, $headerCell);

        $headerRow = (int) preg_replace('/\D/', '', $headerCell);
        $firstColumn = preg_replace('/\d/', '', $headerCell);
        $sheet->fromArray($rows, null, $firstColumn.($headerRow + 1));

        $path = tempnam(sys_get_temp_dir(), 'ctgjob').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'CTG JOB INFO.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_import_reads_header_below_blank_rows_and_after_leading_columns(): void
    {
        $file = $this->makeXlsx([
            $this->makeRow(),
            $this->makeRow([2 => 'CF_EXCTG-2026-000002', 5 => 'Export']),
        ], 'M3', 'Sheet1');

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted', 2)
            ->assertJsonPath('skipped_existing', 0);

        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'     => 'CF_IMCTG-2026-000001',
            'branch_id'  => 6,
            'service_id' => 1,
            'port_id'    => 1,
            'party_name' => 'RENATA PLC.',
            'goods_name' => 'GOODS',
            'status'     => 'Pending',
        ]);
        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_EXCTG-2026-000002', 'service_id' => 2, 'job_type_id' => 2]);

        $job = ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->first();
        $this->assertSame('2025-11-17', $job->job_date->format('Y-m-d'));
        $this->assertSame('2025-12-22', $job->be_date->format('Y-m-d'));
        $this->assertEquals(1500.5, $job->gross_weight);
        $this->assertEquals(27500000, $job->assessable_value_bdt);
    }

    public function test_import_still_reads_the_original_a1_layout(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([$this->makeRow()])])
            ->assertOk()
            ->assertJsonPath('inserted', 1);

        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6]);
    }

    public function test_job_that_already_exists_is_never_duplicated(): void
    {
        ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6, 'service_id' => 1, 'remarks' => 'keep me']);

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => ' cf_imctg-2026-000001 ']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000002']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('skipped_existing', 1);

        $this->assertSame(2, ChevronJob::count());
        $this->assertSame('keep me', ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->value('remarks'));
    }

    public function test_uploading_the_same_file_twice_imports_nothing_the_second_time(): void
    {
        $rows = [$this->makeRow(), $this->makeRow([2 => 'CF_IMCTG-2026-000002'])];

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx($rows)])->assertOk()->assertJsonPath('inserted', 2);

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx($rows)])
            ->assertOk()
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('skipped_existing', 2)
            ->assertJsonPath('message', 'No new jobs to import. All records already exist.');

        $this->assertSame(2, ChevronJob::count());
    }

    public function test_job_repeated_inside_the_file_is_imported_once_and_reported(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow(['8' => 'FIRST']),
            $this->makeRow(['8' => 'SECOND', 2 => 'cf_imctg-2026-000001']),
            $this->makeRow(['8' => 'THIRD', 2 => 'CF_IMCTG-2026-000003']),
            $this->makeRow(['8' => 'FOURTH', 2 => 'CF_IMCTG-2026-000003 ']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 2)
            ->assertJsonPath('skipped_rows.duplicate_in_file.count', 2)
            ->assertJsonPath('skipped_rows.duplicate_in_file.rows', [3, 5]);

        $this->assertSame(2, ChevronJob::count());
        $this->assertSame('FIRST', ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->value('goods_name'));
    }

    public function test_row_with_data_but_no_job_no_is_reported_and_blank_rows_are_ignored(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => null]),
            array_fill(0, 27, null),
            $this->makeRow(),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('skipped_rows.missing_job_no.count', 1)
            ->assertJsonPath('skipped_rows.missing_job_no.rows', [2]);
    }

    public function test_partial_jobs_are_linked_to_their_parent_whatever_the_file_order(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => 'CF_IMCTG-2026-000001_PJ1']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000001_PJ']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000001']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 3)
            ->assertJsonPath('linked_partials', 2)
            ->assertJsonPath('unlinked_partials', 0);

        $parent = ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->first();
        $this->assertNull($parent->parent_job_id);
        $this->assertSame(1, ChevronJob::where('job_no', 'CF_IMCTG-2026-000001_PJ')->value('partial_seq'));
        $this->assertSame(2, ChevronJob::where('job_no', 'CF_IMCTG-2026-000001_PJ1')->value('partial_seq'));
        $this->assertSame(2, ChevronJob::where('parent_job_id', $parent->id)->count());
    }

    public function test_partial_job_links_to_a_parent_imported_earlier(): void
    {
        $parent = ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6, 'service_id' => 1]);

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => 'CF_IMCTG-2026-000001_PJ']),
        ])])->assertOk()->assertJsonPath('linked_partials', 1);

        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_IMCTG-2026-000001_PJ', 'parent_job_id' => $parent->id, 'partial_seq' => 1]);
    }

    public function test_partial_job_without_a_known_parent_is_imported_unlinked(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => 'CF_IMCTG-2026-000009_PJ']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('linked_partials', 0)
            ->assertJsonPath('unlinked_partials', 1);

        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_IMCTG-2026-000009_PJ', 'parent_job_id' => null, 'partial_seq' => null]);
    }

    public function test_partial_seq_already_taken_leaves_the_new_partial_unlinked(): void
    {
        $parent = ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6, 'service_id' => 1]);
        ChevronJob::create(['job_no' => 'OTHER-PJ', 'branch_id' => 6, 'service_id' => 1])
            ->forceFill(['parent_job_id' => $parent->id, 'partial_seq' => 1])->save();

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([2 => 'CF_IMCTG-2026-000001_PJ']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('unlinked_partials', 1);

        $this->assertSame(1, ChevronJob::where('parent_job_id', $parent->id)->count());
    }

    public function test_dates_accept_excel_serials_and_text_and_reject_nonsense(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([3 => 46074, 10 => '27/8/2026']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000002', 3 => '08/31/026', 10 => '0914/2026']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000003', 3 => null, 10 => '31/2/2026']),
        ])])->assertOk()->assertJsonPath('inserted', 3);

        $first = ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->first();
        $this->assertSame('2026-02-21', $first->job_date->format('Y-m-d'));
        $this->assertSame('2026-08-27', $first->be_date->format('Y-m-d'));

        $second = ChevronJob::where('job_no', 'CF_IMCTG-2026-000002')->first();
        $this->assertNull($second->job_date);
        $this->assertNull($second->be_date);

        $this->assertNull(ChevronJob::where('job_no', 'CF_IMCTG-2026-000003')->first()->be_date);
    }

    public function test_status_port_job_type_and_customer_are_mapped(): void
    {
        ChevronCustomer::create(['customer_id' => 'CUS-000001', 'name' => 'Renata Plc.', 'status' => 'Active']);

        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([
            $this->makeRow([25 => 'Confirmed']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000002', 25 => 'Closed', 6 => 'New Port', 5 => 'Transit', 7 => 'UNKNOWN LTD']),
        ])])
            ->assertOk()
            ->assertJsonPath('inserted', 2)
            ->assertJsonPath('new_ports', 1)
            ->assertJsonPath('new_job_types', 1);

        $first = ChevronJob::where('job_no', 'CF_IMCTG-2026-000001')->first();
        $this->assertSame('Active', $first->status);
        $this->assertSame(ChevronCustomer::value('id'), $first->customer_id);
        $this->assertSame('Renata Plc.', $first->party_name);

        $second = ChevronJob::where('job_no', 'CF_IMCTG-2026-000002')->first();
        $this->assertSame('Closed', $second->status);
        $this->assertNull($second->customer_id);
        $this->assertSame('UNKNOWN LTD', $second->party_name);
        $this->assertSame('NEW PORT', ChevronPort::find($second->port_id)->code);
        $this->assertSame('Transit', ChevronJobType::find($second->job_type_id)->name);
    }

    public function test_preview_reports_duplicates_and_writes_nothing(): void
    {
        ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6, 'service_id' => 1]);

        $this->postJson(self::PREVIEW_URL, ['file' => $this->makeXlsx([
            $this->makeRow(),
            $this->makeRow([2 => 'CF_IMCTG-2026-000002', 7 => 'UNKNOWN LTD']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000002']),
            $this->makeRow([2 => 'CF_IMCTG-2026-000007_PJ']),
        ])])
            ->assertOk()
            ->assertJsonPath('summary.rows_in_file', 4)
            ->assertJsonPath('summary.existing_jobs', 1)
            ->assertJsonPath('summary.new_jobs', 2)
            ->assertJsonPath('summary.duplicate_in_file', 1)
            ->assertJsonPath('summary.partial_jobs', 1)
            ->assertJsonPath('summary.partial_jobs_without_parent', 1)
            ->assertJsonPath('summary.unmatched_customers', 2)
            ->assertJsonPath('skipped_rows.duplicate_in_file.rows', [4])
            ->assertJsonPath('preview.0.exists', true)
            ->assertJsonPath('preview.1.exists', false)
            ->assertJsonPath('preview.1.warnings.0', "Customer 'UNKNOWN LTD' not found; saved as party name only.");

        $this->assertSame(1, ChevronJob::count());
    }

    public function test_import_requires_a_file(): void
    {
        $this->postJson(self::IMPORT_URL)->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->postJson(self::PREVIEW_URL)->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_file_without_a_job_no_header_is_rejected(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['Sl', 'Customer'], ['1', 'RENATA PLC.']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'ctgjob').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $file = new UploadedFile($path, 'bad.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Could not read the file: Header row with a "Job No" column was not found in the first 30 rows.');

        $this->assertSame(0, ChevronJob::count());
    }
}

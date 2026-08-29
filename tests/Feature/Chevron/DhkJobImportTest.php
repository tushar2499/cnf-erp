<?php

namespace Tests\Feature\Chevron;

use App\Models\Chevron\ChevronBranch;
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

class DhkJobImportTest extends TestCase
{
    private const HEADER = [
        'Sl', 'Action', 'Job No', 'Job Date', 'Job Type', 'Type', 'Port', 'Customer', 'Goods Name',
        'B/E', 'B/E Date', 'B/L', 'Lc', 'Invoice', 'M B/L/M A W/B', 'Gross Weight', 'Qty', 'Unit',
        'Assessable Value', 'Invoice Value', 'Currency Name', 'Currency Rate', 'Invoice Value B D T',
        'Total Payable', 'Remarks', 'Status', 'Bill Status',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedBranches();
        $this->seedReferences();
    }

    private function createSchema(): void
    {
        Schema::create('chevron_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

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

    private function seedBranches(): void
    {
        ChevronBranch::create(['id' => 5, 'name' => 'Head Office', 'code' => 'DK', 'is_active' => true]);
        ChevronBranch::create(['id' => 6, 'name' => 'Chittagong', 'code' => 'CTG', 'is_active' => true]);
    }

    private function seedReferences(): void
    {
        ChevronPort::create(['name' => 'DK', 'code' => 'DK', 'branch_id' => 6, 'is_active' => true]);
        ChevronPort::create(['name' => 'CTG', 'code' => 'CTG', 'branch_id' => 6, 'is_active' => true]);
        ChevronJobType::create(['name' => 'Import', 'is_active' => true]);
        ChevronJobType::create(['name' => 'Export', 'is_active' => true]);
    }

    private function makeRow(array $overrides = []): array
    {
        return array_replace([
            '1', null, 'CF_IMDK-2026-000001', '1/1/2026', null, 'Import', 'DK', 'RENATA PLC.', 'GOODS',
            'BE-123', '1/5/2026', 'BL-9', 'LC-007', 'INV-1', 'MWB-1', '1500.5', '12', 'PCS',
            '250000.00', '300000.00', 'USD', '110.5', '27500000.00', '28500000.00', 'SOME REMARK', 'Active', null,
        ], $overrides);
    }

    private function makeXlsx(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DHK JOB INFORMATION');
        $sheet->fromArray([self::HEADER], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'dhkjob').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'DHK JOB INFO.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_preview_lists_rows_and_marks_existing_jobs(): void
    {
        ChevronJob::create(['job_no' => 'CF_IMDK-2026-000001', 'branch_id' => 6, 'service_id' => 1, 'status' => 'Active']);

        $file = $this->makeXlsx([
            $this->makeRow(),
            $this->makeRow(['2' => 'CF_IMDK-2026-000002', '7' => 'PADMA LAMI TUBE LTD.']),
        ]);

        $this->postJson('/api/chevron/jobs/dhk/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('preview.0.job_no', 'CF_IMDK-2026-000001')
            ->assertJsonPath('preview.0.exists', true)
            ->assertJsonPath('preview.0.port_id', 1)
            ->assertJsonPath('preview.0.job_type_id', 1)
            ->assertJsonPath('preview.1.exists', false)
            ->assertJsonPath('preview.1.warnings.0', "Customer 'PADMA LAMI TUBE LTD.' not found; saved as party name only.");
    }

    public function test_import_creates_new_jobs_under_head_office_and_skips_existing(): void
    {
        ChevronJob::create(['job_no' => 'CF_IMDK-2026-000001', 'branch_id' => 6, 'service_id' => 1, 'status' => 'Active']);

        $file = $this->makeXlsx([
            $this->makeRow(),
            $this->makeRow(['2' => 'CF_IMDK-2026-000002', '7' => 'PADMA LAMI TUBE LTD.']),
            $this->makeRow(['2' => 'CF_IMDK-2026-000003', '5' => 'Export', '6' => 'CTG']),
        ]);

        $this->postJson('/api/chevron/jobs/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted', 2);

        $this->assertSame(1, ChevronJob::where('job_no', 'CF_IMDK-2026-000001')->count(), 'Existing job must not be duplicated');
        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'     => 'CF_IMDK-2026-000001',
            'branch_id'  => 6,
        ]);
        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'     => 'CF_IMDK-2026-000002',
            'branch_id'  => 5,
            'party_name' => 'PADMA LAMI TUBE LTD.',
        ]);
        $jobTwo = ChevronJob::where('job_no', 'CF_IMDK-2026-000002')->first();
        $this->assertSame('2026-01-01', $jobTwo->job_date->format('Y-m-d'));
        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'    => 'CF_IMDK-2026-000003',
            'branch_id' => 5,
            'status'    => 'Active',
        ]);
    }

    public function test_import_remaps_existing_customer(): void
    {
        ChevronCustomer::create([
            'id_prefix'   => 'CUS-',
            'customer_id' => 'CUS-000001',
            'name'        => 'GENERAL ELECTRONICS INDUSTRIES',
            'status'      => 'Active',
        ]);

        $file = $this->makeXlsx([
            $this->makeRow(['2' => 'CF_IMDK-2026-000010', '7' => 'GENERAL ELECTRONICS INDUSTRIES']),
        ]);

        $this->postJson('/api/chevron/jobs/dhk/import', ['file' => $file])->assertOk();

        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'     => 'CF_IMDK-2026-000010',
            'party_name' => 'GENERAL ELECTRONICS INDUSTRIES',
        ]);

        $job = ChevronJob::where('job_no', 'CF_IMDK-2026-000010')->first();
        $this->assertSame(ChevronCustomer::where('name', 'GENERAL ELECTRONICS INDUSTRIES')->value('id'), $job->customer_id);
    }

    public function test_import_requires_file(): void
    {
        $this->postJson('/api/chevron/jobs/dhk/import')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_existing_chittagong_job_endpoint_is_untouched(): void
    {
        $this->postJson('/api/chevron/jobs/import')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rows');
    }
}

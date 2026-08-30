<?php

namespace Tests\Feature\Chevron;

use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronJob;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DhkJobExpenseImportTest extends TestCase
{
    private const HEADER = [
        'SL NO', 'Job NO:', 'Column1', 'EXP DATE', 'EXP. BY', 'EXPENSE HEAD',
        'RECEIPTABLE', 'AMOUNT', 'Complition', 'Complition Date', 'Remarks',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedBranches();
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
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('party_name')->nullable();
            $table->enum('status', ['Active', 'Pending', 'Closed'])->default('Active');
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->enum('type', ['team_leader', 'prepare'])->default('prepare');
            $table->enum('current_status', ['Active', 'Inactive', 'Resigned', 'Terminated'])->default('Active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_bill')->default(false);
            $table->boolean('is_job')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->enum('type', ['External', 'Internal'])->default('External');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_head_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_head_id');
            $table->unsignedBigInteger('employee_id');
        });

        Schema::create('chevron_job_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date');
            $table->decimal('total_expense_amount', 15, 2)->default(0);
            $table->decimal('total_approved_amount', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('chevron_job_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_expense_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->enum('receiptable', ['Yes', 'No'])->default('No');
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->default(0);
            $table->date('expense_date');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    private function seedBranches(): void
    {
        ChevronBranch::create(['id' => 5, 'name' => 'Head Office', 'code' => 'DK', 'is_active' => true]);
        ChevronBranch::create(['id' => 6, 'name' => 'Chittagong', 'code' => 'CTG', 'is_active' => true]);
    }

    private function seedEmployee(string $code, string $name): Employee
    {
        return Employee::create([
            'code'           => $code,
            'name'           => $name,
            'type'           => 'prepare',
            'current_status' => 'Active',
            'is_active'      => true,
        ]);
    }

    private function makeXlsx(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([self::HEADER], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'dhk').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'DHK JOB INFO_EXP.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_preview_groups_rows_and_reports_new_entities(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', 5000, null, null, null],
            ['2', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'EXPLOSIVE NOC', 'YES', 1045.36, null, null, null],
            ['3', 'CF_IMDK-2026-000002', 'PADMA LAMI TUBE LTD.', '1/25/2026', 'CLCNFDK17_Mr. Md. Riazul Islam Ranju_Sr. Marketing Executive_C&F (Marketing)', 'COO PURPOSE', 'NO', 5300, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('summary.total_groups', 2)
            ->assertJsonPath('summary.total_items', 3)
            ->assertJsonPath('summary.new_jobs', 2)
            ->assertJsonPath('summary.new_customers', 2)
            ->assertJsonPath('summary.new_heads', 3)
            ->assertJsonPath('summary.new_employees', 1)
            ->assertJsonPath('preview.0.new_employee', false)
            ->assertJsonPath('preview.0.total_amount', 6045.36);
    }

    public function test_import_creates_expense_under_head_office_branch(): void
    {
        $employee = $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', 5000, null, null, 'Outpocket'],
            ['2', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'EXPLOSIVE NOC', 'YES', 1045.36, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('inserted_items', 2)
            ->assertJsonPath('skipped', 0)
            ->assertJsonPath('new_jobs', 1)
            ->assertJsonPath('new_customers', 1)
            ->assertJsonPath('new_heads', 2)
            ->assertJsonPath('new_employees', 0);

        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'     => 'CF_IMDK-2026-000001',
            'branch_id'  => 5,
            'party_name' => 'RENATA PLC.',
        ]);
        $this->assertDatabaseHas('chevron_customers', ['name' => 'RENATA PLC.']);
        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'                => 'CF_IMDK-2026-000001',
            'branch_id'             => 5,
            'employee_id'           => $employee->id,
            'date'                  => '2026-01-04',
            'total_expense_amount'  => 6045.36,
            'total_approved_amount' => 6045.36,
            'status'                => 'Approved',
        ]);

        $expenseId = DB::table('chevron_job_expenses')->where('job_no', 'CF_IMDK-2026-000001')->value('id');
        $this->assertSame(2, DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->count());
        $this->assertSame(1, DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->where('receiptable', 'Yes')->count());
    }

    public function test_import_reuses_existing_job_and_new_head_uses_general_category(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');
        ChevronJob::create(['job_no' => 'CF_IMDK-2026-000001', 'branch_id' => 6, 'service_id' => 1, 'status' => 'Active']);
        ChevronExpenseCategory::create(['name' => 'General', 'is_bill' => false, 'is_job' => true, 'is_active' => true]);

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'BRAND NEW HEAD', 'NO', 100, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('new_jobs', 0)
            ->assertJsonPath('new_heads', 1)
            ->assertJsonPath('new_categories', 0);

        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'    => 'CF_IMDK-2026-000001',
            'branch_id' => 6,
        ]);
        $this->assertSame(1, ChevronJob::where('job_no', 'CF_IMDK-2026-000001')->count(), 'Existing job must not be duplicated');
        $this->assertDatabaseHas('chevron_expense_heads', ['name' => 'BRAND NEW HEAD']);
        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'    => 'CF_IMDK-2026-000001',
            'branch_id' => 5,
        ]);
    }

    public function test_import_skips_duplicate_expense_group_on_second_run(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', 5000, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 0)
            ->assertJsonPath('skipped', 1);

        $this->assertSame(1, DB::table('chevron_job_expenses')->count());
    }

    public function test_employee_without_code_is_matched_by_normalized_name(): void
    {
        $employee = $this->seedEmployee('CLCNFDK24', 'Mr. Md. Mahpujur Rahman');

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000010', 'XCLUSIVE CAN LIMITED', '3/4/2026', 'MD. Mahpujur Rahman', 'MISC EXP', 'NO', 2000, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('new_employees', 0);

        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'      => 'CF_IMDK-2026-000010',
            'employee_id' => $employee->id,
        ]);
    }

    public function test_unknown_employee_is_created(): void
    {
        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000010', 'XCLUSIVE CAN LIMITED', '3/4/2026', 'CLCNFDK99_Mr. Brand New Staff_chowkidar_Front Desk', 'MISC EXP', 'NO', 2000, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('new_employees', 1);

        $this->assertDatabaseHas('employees', [
            'code'      => 'CLCNFDK99',
            'name'      => 'Mr. Brand New Staff',
            'type'      => 'prepare',
            'is_active' => true,
        ]);
    }

    public function test_preview_parses_m_d_y_date(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');

        $file = $this->makeXlsx([
            ['11', 'CF_IMDK-2026-000001', 'RENATA PLC.', '8/18/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', 400, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('preview.0.date', '2026-08-18');
    }

    public function test_same_person_with_and_without_code_creates_single_employee(): void
    {
        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000020', 'XCLUSIVE CAN LIMITED', '6/2/2026', 'CLCNFDK27_Mr. Reday Chandra Das _Marketing Executive_C&F (Marketing)', 'MISC EXP', 'NO', 100, null, null, null],
            ['2', 'CF_IMDK-2026-000020', 'XCLUSIVE CAN LIMITED', '6/2/2026', 'Reday Chandra Das', 'EXTRA EXP', 'NO', 50, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('new_employees', 1);

        $this->assertSame(1, Employee::where('name', 'Mr. Reday Chandra Das')->count(), 'Coded and name-only rows must map to one employee');
        $employee = Employee::where('code', 'CLCNFDK27')->first();
        $this->assertNotNull($employee);
        $this->assertSame(1, DB::table('chevron_job_expenses')->where('employee_id', $employee->id)->count());
    }

    public function test_import_reuses_jobs_uploaded_by_job_information_import(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');
        ChevronExpenseCategory::create(['name' => 'General', 'is_bill' => false, 'is_job' => true, 'is_active' => true]);
        ChevronJob::create([
            'job_no'     => 'CF_IMDK-2026-000001',
            'branch_id'  => 5,
            'service_id' => 1,
            'status'     => 'Active',
            'party_name' => 'RENATA PLC.',
        ]);

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', 5000, null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('new_jobs', 0)
            ->assertJsonPath('new_categories', 0);

        $this->assertSame(1, ChevronJob::where('job_no', 'CF_IMDK-2026-000001')->count(), 'Jobs must not be duplicated by expense import');
        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'    => 'CF_IMDK-2026-000001',
            'branch_id' => 5,
        ]);
    }

    public function test_amount_with_thousand_separator_and_spaces_is_parsed(): void
    {
        $this->seedEmployee('CLCNFDK04', 'Mr. Md. Abdul Halim');
        ChevronExpenseCategory::create(['name' => 'General', 'is_bill' => false, 'is_job' => true, 'is_active' => true]);

        $file = $this->makeXlsx([
            ['1', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'MISC EXP', 'NO', '  5,000.00 ', null, null, null],
            ['2', 'CF_IMDK-2026-000001', 'RENATA PLC.', '1/4/2026', 'CLCNFDK04_Mr. Md. Abdul Halim_Assistant Manager_C&F (Marketing)', 'EXPLOSIVE NOC', 'YES', '  1,045.36 ', null, null, null],
        ]);

        $this->postJson('/api/chevron/job-expenses/dhk/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('inserted_items', 2);

        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'                => 'CF_IMDK-2026-000001',
            'total_expense_amount'  => 6045.36,
            'total_approved_amount' => 6045.36,
        ]);
        $expenseId = DB::table('chevron_job_expenses')->where('job_no', 'CF_IMDK-2026-000001')->value('id');
        $this->assertEqualsWithDelta(6045.36, (float) DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->sum('expense_amount'), 0.001);
    }

    public function test_import_requires_file(): void
    {
        $this->postJson('/api/chevron/job-expenses/dhk/import')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_existing_chittagong_endpoint_still_requires_file(): void
    {
        $this->postJson('/api/chevron/job-expenses/import')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }
}

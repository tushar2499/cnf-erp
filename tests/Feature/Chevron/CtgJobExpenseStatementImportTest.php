<?php

namespace Tests\Feature\Chevron;

use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CtgJobExpenseStatementImportTest extends TestCase
{
    private const IMPORT_URL = '/api/chevron/job-expenses/ctg/statement/import';

    private const PREVIEW_URL = '/api/chevron/job-expenses/ctg/statement/import/preview';

    private const HEADER = [
        'Source_Name', 'SL NO', 'Job NO:', 'IMPORTER NAME', 'EXP DATE', 'EXP. BY', 'CATEGORY',
        'EXPENSE HEAD', 'RECEIPTABLE', 'AMOUNT', 'Complition', 'Complition Date', 'Remarks',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    private function createSchema(): void
    {
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

        Schema::create('chevron_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 30)->nullable();
            $table->string('name');
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
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(false);
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

    /**
     * @return array<int, mixed>
     */
    private function row(string $jobNo, string $importer, string $date, string $employee, string $category, string $head, string $receiptable, float|string $amount, ?string $note = null): array
    {
        $serial = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? ExcelDate::PHPToExcel(new \DateTime($date)) : $date;

        return [null, 1, $jobNo, $importer, $serial, $employee, $category, $head, $receiptable, $amount, null, null, $note];
    }

    /**
     * Mirrors the real workbook: a form sheet first, junk rows above the header, header on row 3.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function makeXlsx(array $rows, bool $withStatementSheet = true, ?string $formulaForImporterOfFirstRow = null): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $first = $spreadsheet->getActiveSheet();
        $first->setTitle('Job_Response');
        $first->fromArray([['Id', 'Name'], [1, 'anonymous']], null, 'A1');

        if ($withStatementSheet) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Job_Exp_Statement');
            $sheet->setCellValue('J2', '=1+1');
            $sheet->fromArray([self::HEADER], null, 'A3');
            $sheet->fromArray($rows, null, 'A4');

            if ($formulaForImporterOfFirstRow !== null) {
                $sheet->setCellValue('D4', $formulaForImporterOfFirstRow);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'ctg').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'Job Report CTG JOB EXP.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function seedEmployee(string $name, ?string $code = null): Employee
    {
        return Employee::create(['name' => $name, 'code' => $code, 'type' => 'prepare', 'current_status' => 'Active', 'is_active' => true]);
    }

    public function test_preview_reports_new_and_existing_entities(): void
    {
        $this->seedEmployee('Liton Sen');
        ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000001', 'branch_id' => 6, 'service_id' => 1, 'status' => 'Active']);

        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000001', 'ACME LTD.', '2026-04-19', 'LITON SEN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000001', 'ACME LTD.', '2026-04-19', 'LITON SEN', 'DELIVERY SECTION', 'LABOUR BILL', 'YES', 2531),
            $this->row('CF_EXCTG-2026-800001', 'RENATA PLC.', '2026-04-20', 'mezbah uddin', 'CUSTOMS SECTION', 'SEAL', 'NO', 100),
        ]);

        $this->postJson(self::PREVIEW_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('summary.total_groups', 2)
            ->assertJsonPath('summary.total_items', 3)
            ->assertJsonPath('summary.expenses_to_insert', 2)
            ->assertJsonPath('summary.existing_expenses', 0)
            ->assertJsonPath('summary.new_jobs', 1)
            ->assertJsonPath('summary.new_customers', 2)
            ->assertJsonPath('summary.new_heads', 2)
            ->assertJsonPath('summary.new_employees', 1)
            ->assertJsonPath('preview.0.new_job', false)
            ->assertJsonPath('preview.0.new_employee', false)
            ->assertJsonPath('preview.0.total_amount', 2571)
            ->assertJsonPath('preview.1.new_job', true);

        $this->assertSame(1, ChevronJob::count(), 'Preview must not write.');
    }

    public function test_import_creates_missing_job_with_basic_data_and_adds_expenses(): void
    {
        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'NIGHT ALLOWANCE', 'NO', 550),
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'LABOUR BILL', 'YES', 2531.5, 'cash memo'),
            $this->row('CF_EXCTG-2026-800001', 'RENATA PLC.', '2026-04-20', 'MEZBAH UDDIN', '', 'MISC EXP', 'NO', 100),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 2)
            ->assertJsonPath('inserted_items', 3)
            ->assertJsonPath('skipped_existing', 0)
            ->assertJsonPath('new_jobs', 2)
            ->assertJsonPath('new_customers', 2)
            ->assertJsonPath('new_heads', 3)
            ->assertJsonPath('new_employees', 1);

        $customerId = ChevronCustomer::where('name', 'ODESSA FASHIONS LTD.')->value('id');
        $this->assertDatabaseHas('chevron_jobs', [
            'job_no'      => 'CF_IMCTG-2026-000612',
            'branch_id'   => 6,
            'service_id'  => 1,
            'party_name'  => 'ODESSA FASHIONS LTD.',
            'customer_id' => $customerId,
            'status'      => 'Active',
        ]);
        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_EXCTG-2026-800001', 'branch_id' => 6, 'service_id' => 2]);

        $employeeId = Employee::where('name', 'MEZBAH UDDIN')->value('id');
        $jobId = ChevronJob::where('job_no', 'CF_IMCTG-2026-000612')->value('id');
        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'                => 'CF_IMCTG-2026-000612',
            'job_id'                => $jobId,
            'branch_id'             => 6,
            'employee_id'           => $employeeId,
            'date'                  => '2026-04-19',
            'total_expense_amount'  => 3081.5,
            'total_approved_amount' => 3081.5,
            'status'                => 'Approved',
        ]);

        $expenseId = DB::table('chevron_job_expenses')->where('job_no', 'CF_IMCTG-2026-000612')->value('id');
        $this->assertSame(2, DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->count());
        $this->assertSame(1, DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->where('receiptable', 'Yes')->count());
        $this->assertSame('cash memo', DB::table('chevron_job_expense_items')->where('job_expense_id', $expenseId)->where('receiptable', 'Yes')->value('note'));

        $this->assertDatabaseHas('chevron_expense_categories', ['name' => 'DELIVERY SECTION']);
        $this->assertDatabaseHas('chevron_expense_categories', ['name' => 'General']);
        $this->assertSame(3, DB::table('chevron_expense_head_employees')->where('employee_id', $employeeId)->count());
    }

    public function test_import_reuses_existing_job(): void
    {
        $job = ChevronJob::create(['job_no' => 'CF_IMCTG-2026-000612', 'branch_id' => 6, 'service_id' => 1, 'status' => 'Active']);

        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('new_jobs', 0);

        $this->assertSame(1, ChevronJob::where('job_no', 'CF_IMCTG-2026-000612')->count());
        $this->assertDatabaseHas('chevron_job_expenses', ['job_no' => 'CF_IMCTG-2026-000612', 'job_id' => $job->id]);
    }

    public function test_second_upload_of_same_file_adds_nothing(): void
    {
        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', '', 'DELIVERY SECTION', 'PHOTOCOPY', 'NO', 30),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 2);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 0)
            ->assertJsonPath('skipped_existing', 2)
            ->assertJsonPath('new_jobs', 0);

        $this->assertSame(2, DB::table('chevron_job_expenses')->count());
        $this->assertSame(2, DB::table('chevron_job_expense_items')->count());
    }

    public function test_expense_saved_earlier_with_legacy_employee_id_is_not_duplicated(): void
    {
        DB::table('chevron_employees')->insert(['id' => 7, 'employee_id' => 'EMP-0007', 'name' => 'Mezbah Uddin']);
        DB::table('chevron_job_expenses')->insert([
            'expense_no'  => 'EXP000001',
            'branch_id'   => 6,
            'job_no'      => 'CF_IMCTG-2026-000612',
            'employee_id' => 7,
            'date'        => '2026-04-19',
            'status'      => 'Approved',
        ]);

        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ODESSA FASHIONS LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 0)
            ->assertJsonPath('skipped_existing', 1)
            ->assertJsonPath('new_employees', 0);

        $this->assertSame(1, DB::table('chevron_job_expenses')->count());
        $this->assertSame(0, Employee::count());
    }

    public function test_employee_name_variants_merge_and_dk_suffix_matches_existing_employee(): void
    {
        $existing = $this->seedEmployee('Al Imran');

        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'mr.nurul huda', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'Nurul Huda', 'DELIVERY SECTION', 'PHOTOCOPY', 'NO', 30),
            $this->row('CF_IMCTG-2026-000613', 'ACME LTD.', '2026-04-19', 'al imran_dk', 'MARKETING_DHK', 'LUNCH', 'NO', 500),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 2)
            ->assertJsonPath('inserted_items', 3)
            ->assertJsonPath('new_employees', 1);

        $this->assertSame(2, Employee::count());
        $this->assertDatabaseHas('chevron_job_expenses', ['job_no' => 'CF_IMCTG-2026-000613', 'employee_id' => $existing->id]);
    }

    public function test_new_employee_reuses_code_from_legacy_chevron_employee(): void
    {
        DB::table('chevron_employees')->insert(['id' => 1, 'employee_id' => 'EMP-0100', 'name' => 'Rahul Deb']);
        $this->seedEmployee('R. Deb Central', 'EMP-0200');
        DB::table('chevron_employees')->insert(['id' => 2, 'employee_id' => 'EMP-0200', 'name' => 'Sujal Kanti']);

        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'rahul deb', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000613', 'ACME LTD.', '2026-04-19', 'sujal kanti', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 2)
            ->assertJsonPath('new_employees', 1);

        $this->assertDatabaseHas('employees', ['name' => 'rahul deb', 'code' => 'EMP-0100']);
        $this->assertSame(1, Employee::where('code', 'EMP-0200')->count());
        $this->assertDatabaseHas('chevron_job_expenses', [
            'job_no'      => 'CF_IMCTG-2026-000613',
            'employee_id' => Employee::where('code', 'EMP-0200')->value('id'),
        ]);
    }

    public function test_bad_rows_are_skipped_and_reported_by_reason(): void
    {
        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', 'not a date', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', '', 'NO', 40),
            $this->row('CF_IMCTG-2026-000612', 'ACME LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 'abc'),
            $this->row('', 'ACME LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            [null, null, null, null, null, null, null, null, null, null, null, null, null],
        ]);

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 1)
            ->assertJsonPath('inserted_items', 1)
            ->assertJsonPath('skipped_rows.invalid_date.count', 1)
            ->assertJsonPath('skipped_rows.invalid_date.rows', [5])
            ->assertJsonPath('skipped_rows.missing_expense_head.count', 1)
            ->assertJsonPath('skipped_rows.invalid_amount.count', 1)
            ->assertJsonPath('skipped_rows.missing_job_no.count', 1)
            ->assertJsonPath('skipped_rows.missing_job_no.rows', [8]);
    }

    public function test_formula_importer_uses_cached_value_and_blank_importer_falls_back_to_same_job(): void
    {
        $file = $this->makeXlsx([
            $this->row('CF_IMCTG-2026-000612', '', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000613', '', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'SEAL', 'NO', 40),
            $this->row('CF_IMCTG-2026-000613', 'BETA LTD.', '2026-04-19', 'MEZBAH UDDIN', 'DELIVERY SECTION', 'LUNCH', 'NO', 40),
        ], true, '="RENATA PLC."');

        $this->postJson(self::IMPORT_URL, ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted_expenses', 2);

        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_IMCTG-2026-000612', 'party_name' => 'RENATA PLC.']);
        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'CF_IMCTG-2026-000613', 'party_name' => 'BETA LTD.']);
    }

    public function test_workbook_without_statement_sheet_is_rejected(): void
    {
        $this->postJson(self::IMPORT_URL, ['file' => $this->makeXlsx([], false)])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Could not read the file: Sheet "Job_Exp_Statement" was not found in the workbook.');
    }

    public function test_file_is_required(): void
    {
        $this->postJson(self::IMPORT_URL, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }
}

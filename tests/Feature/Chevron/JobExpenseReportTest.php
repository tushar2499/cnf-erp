<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\ReportController;
use App\Http\Requests\Chevron\Report\JobExpenseRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Chevron\ChevronJobExpenseItem;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobExpenseReportTest extends TestCase
{
    private Employee $employee;

    private ChevronExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->createExpenseReportView();

        $this->employee = Employee::create([
            'name'      => 'Mr. Expense Employee',
            'code'      => 'EMP-EXP',
            'is_active' => true,
        ]);

        DB::table('chevron_branches')->insert([
            ['id' => 5, 'name' => 'Dhaka Branch'],
            ['id' => 6, 'name' => 'Chattogram Branch'],
        ]);

        $this->employee->branchAccess()->create(['branch_id' => 5]);

        $this->category = ChevronExpenseCategory::create([
            'name'      => 'Job Cost',
            'is_job'    => true,
            'is_active' => true,
        ]);

        session(['active_branch_id' => 5]);
    }

    protected function tearDown(): void
    {
        DB::statement('DROP VIEW IF EXISTS chevron_job_expense_view');
        Schema::dropIfExists('chevron_job_expense_items');
        Schema::dropIfExists('chevron_job_expenses');
        Schema::dropIfExists('chevron_bills');
        Schema::dropIfExists('chevron_jobs');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('chevron_expense_categories');
        Schema::dropIfExists('employee_branch_access');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('chevron_branches');

        parent::tearDown();
    }

    #[Test]
    public function flat_rows_show_expense_details(): void
    {
        $job = $this->createJob('JOB-001', 'ACME TRADING');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 150],
        ]);
        $this->createBill($job, 500);

        $json = $this->dataTable(['job_no' => 'JOB-001']);

        $this->assertSame(1, $json['recordsTotal']);
        $this->assertCount(1, $json['data']);

        $row = $json['data'][0];

        $this->assertSame('Dhaka Branch', $row['branch_name']);
        $this->assertSame('JOB-001', $row['job_no']);
        $this->assertSame('ACME TRADING', $row['party_name']);
        $this->assertSame('01 Oct 2026', $row['expense_date']);
        $this->assertSame('EXP000001', $row['expense_no']);
        $this->assertSame('Mr. Expense Employee', $row['expense_by']);
        $this->assertSame('Job Cost', $row['expense_category']);
        $this->assertSame('Port Handling', $row['expense_head']);
        $this->assertStringContainsString('Yes', $row['receiptable_badge']);
        $this->assertSame('150.00', $row['expense_amount']);
        $this->assertSame('150.00', $json['total_expense']);
    }

    #[Test]
    public function report_ignores_bills_and_exposes_no_bill_fields(): void
    {
        $job = $this->createJob('JOB-002', 'ACME TRADING');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
            ['receiptable' => 'No', 'expense_amount' => 50],
        ]);
        $this->createBill($job, 400);
        $this->createBill($job, 100);

        $json = $this->dataTable(['job_no' => 'JOB-002']);

        $this->assertCount(2, $json['data']);

        foreach ($json['data'] as $row) {
            $this->assertArrayNotHasKey('bill_total', $row);
        }

        $this->assertSame('150.00', $json['total_expense']);
        $this->assertArrayNotHasKey('total_bill', $json);
        $this->assertArrayNotHasKey('difference', $json);
        $this->assertSame(1, $json['total_jobs']);
        $this->assertSame(2, $json['total_rows']);
    }

    #[Test]
    public function report_only_loads_expenses_for_the_active_branch(): void
    {
        $activeJob = $this->createJob('JOB-003', 'ACTIVE PARTY', 5);
        $this->createExpense($activeJob, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $otherJob = $this->createJob('JOB-004', 'OTHER PARTY', 6);
        $this->createExpense($otherJob, [
            ['receiptable' => 'Yes', 'expense_amount' => 900],
        ]);

        $json = $this->dataTable(['from_date' => '2026-01-01']);

        $this->assertCount(1, $json['data']);
        $this->assertSame('JOB-003', $json['data'][0]['job_no']);
        $this->assertSame('100.00', $json['total_expense']);
    }

    #[Test]
    public function date_and_job_filters_narrow_the_expense_rows(): void
    {
        $jobA = $this->createJob('JOB-005', 'PARTY A');
        $jobB = $this->createJob('JOB-006', 'PARTY B');

        $this->createExpense($jobA, [
            ['receiptable' => 'Yes', 'expense_amount' => 100, 'expense_date' => '2026-03-10'],
        ]);
        $this->createExpense($jobB, [
            ['receiptable' => 'Yes', 'expense_amount' => 200, 'expense_date' => '2026-08-15'],
        ]);

        $byJob = $this->dataTable(['job_no' => 'JOB-005']);
        $this->assertCount(1, $byJob['data']);
        $this->assertSame('JOB-005', $byJob['data'][0]['job_no']);

        $byDate = $this->dataTable([
            'from_date' => '2026-08-01',
            'to_date'   => '2026-08-31',
        ]);
        $this->assertCount(1, $byDate['data']);
        $this->assertSame('JOB-006', $byDate['data'][0]['job_no']);
    }

    #[Test]
    public function print_view_renders_expense_totals_without_bill_data(): void
    {
        $job = $this->createJob('JOB-PRINT', 'ACME PRINT');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 150],
        ]);
        $this->createBill($job, 500);

        $html = $this->renderController('jobExpensePrint', ['job_no' => 'JOB-PRINT'])->render();

        $this->assertStringContainsString('Job Expense Report', $html);
        $this->assertStringNotContainsString('Vs Bill', $html);
        $this->assertStringContainsString('Branch', $html);
        $this->assertStringContainsString('Dhaka Branch', $html);
        $this->assertStringContainsString('ACME PRINT', $html);
        $this->assertStringContainsString('Expense No', $html);
        $this->assertStringContainsString('EXP000001', $html);
        $this->assertStringContainsString('Total Job Expense Amount', $html);
        $this->assertStringContainsString('150.00', $html);
        $this->assertStringNotContainsString('Bill Amount', $html);
        $this->assertStringNotContainsString('Difference', $html);
        $this->assertStringNotContainsString('500.00', $html);
    }

    #[Test]
    public function job_search_returns_matching_job_numbers_for_the_dropdown(): void
    {
        $this->createJob('JOB-SEARCH-001', 'SEARCHABLE PARTY');
        $this->createJob('JOB-OTHER-002', 'ANOTHER PARTY');

        $request = JobExpenseRequest::create(
            route('chevron.reports.job-expense.search-jobs'),
            'GET',
            ['q' => 'SEARCHABLE']
        );

        $response = app(ReportController::class)->searchJobs($request)->getData(true);

        $this->assertCount(1, $response);
        $this->assertSame('JOB-SEARCH-001', $response[0]['text']);
        $this->assertSame('JOB-SEARCH-001', $response[0]['id']);
    }

    #[Test]
    public function blank_filters_do_not_load_every_expense(): void
    {
        $job = $this->createJob('JOB-007', 'PARTY');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $otherJob = $this->createJob('JOB-008', 'OTHER PARTY', 6);
        $this->createExpense($otherJob, [
            ['receiptable' => 'Yes', 'expense_amount' => 900],
        ]);

        $json = $this->dataTable();

        $this->assertCount(0, $json['data']);
        $this->assertSame('0.00', $json['total_expense']);
        $this->assertSame(0, $json['total_rows']);
        $this->assertSame(0, $json['total_jobs']);
    }

    #[Test]
    public function column_filters_narrow_the_rows_and_totals(): void
    {
        $jobA = $this->createJob('JOB-C1', 'PARTY ONE');
        $this->createExpense($jobA, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $jobB = $this->createJob('JOB-C2', 'PARTY TWO');
        $this->createExpense($jobB, [
            ['receiptable' => 'No', 'expense_amount' => 200],
        ]);

        $byParty = $this->dataTable(['from_date' => '2026-01-01'], ['party_name' => 'PARTY TWO']);
        $this->assertCount(1, $byParty['data']);
        $this->assertSame('JOB-C2', $byParty['data'][0]['job_no']);
        $this->assertSame('200.00', $byParty['total_expense']);
        $this->assertSame(1, $byParty['total_rows']);
        $this->assertSame(1, $byParty['total_jobs']);

        $byAmount = $this->dataTable(['from_date' => '2026-01-01'], ['expense_amount' => '100']);
        $this->assertCount(1, $byAmount['data']);
        $this->assertSame('JOB-C1', $byAmount['data'][0]['job_no']);
        $this->assertSame('100.00', $byAmount['total_expense']);

        $byReceiptable = $this->dataTable(['from_date' => '2026-01-01'], ['receiptable_badge' => 'No']);
        $this->assertCount(1, $byReceiptable['data']);
        $this->assertSame('JOB-C2', $byReceiptable['data'][0]['job_no']);
    }

    #[Test]
    public function branch_name_is_searchable_by_column_and_globally(): void
    {
        $job = $this->createJob('JOB-B1', 'PARTY BRANCH');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $byColumn = $this->dataTable(['from_date' => '2026-01-01'], ['branch_name' => 'Dhaka']);
        $this->assertCount(1, $byColumn['data']);
        $this->assertSame('Dhaka Branch', $byColumn['data'][0]['branch_name']);

        $noMatch = $this->dataTable(['from_date' => '2026-01-01'], ['branch_name' => 'Chattogram']);
        $this->assertCount(0, $noMatch['data']);
        $this->assertSame('0.00', $noMatch['total_expense']);

        $global = $this->dataTable(
            ['from_date' => '2026-01-01', 'search' => ['value' => 'Dhaka Branch', 'regex' => 'false']]
        );
        $this->assertCount(1, $global['data']);
    }

    #[Test]
    public function global_search_narrows_the_rows_and_totals(): void
    {
        $jobA = $this->createJob('JOB-G1', 'PARTY ONE');
        $this->createExpense($jobA, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $jobB = $this->createJob('JOB-G2', 'PARTY TWO');
        $this->createExpense($jobB, [
            ['receiptable' => 'No', 'expense_amount' => 200],
        ]);

        $byParty = $this->dataTable(
            ['from_date' => '2026-01-01', 'search' => ['value' => 'PARTY TWO', 'regex' => 'false']]
        );

        $this->assertCount(1, $byParty['data']);
        $this->assertSame('JOB-G2', $byParty['data'][0]['job_no']);
        $this->assertSame('200.00', $byParty['total_expense']);
        $this->assertSame(1, $byParty['total_jobs']);
    }

    #[Test]
    public function expense_rows_without_a_job_still_show(): void
    {
        $expense = ChevronJobExpense::create([
            'expense_no'  => 'EXP-NOJOB',
            'branch_id'   => 5,
            'job_id'      => null,
            'job_no'      => 'JOB-ORPHAN',
            'employee_id' => $this->employee->id,
            'date'        => '2026-10-01',
            'status'      => 'Draft',
        ]);

        ChevronJobExpenseItem::create([
            'job_expense_id'  => $expense->id,
            'expense_head_id' => $this->expenseHead()->id,
            'receiptable'     => 'No',
            'expense_amount'  => 75,
            'expense_date'    => '2026-10-01',
        ]);

        $json = $this->dataTable(['job_no' => 'JOB-ORPHAN']);

        $this->assertCount(1, $json['data']);
        $this->assertSame('—', $json['data'][0]['party_name']);
        $this->assertSame('75.00', $json['total_expense']);
        $this->assertSame(0, $json['total_jobs']);
    }

    private function createExpenseReportView(): void
    {
        $migration = require glob(database_path('migrations/*_create_chevron_job_expense_view.php'))[0];

        $migration->up();
    }

    private function dataTable(array $filters = [], array $columnSearches = []): array
    {
        $request = JobExpenseRequest::create(
            route('chevron.reports.job-expense'),
            'GET',
            array_merge([
                'draw'    => 1,
                'start'   => 0,
                'length'  => 100,
                'search'  => ['value' => '', 'regex' => 'false'],
                'columns' => $this->dataTableColumns($columnSearches),
                'order'   => [['column' => 2, 'dir' => 'asc']],
            ], $filters)
        );

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->app->instance('request', $request);

        return app(ReportController::class)
            ->jobExpense($request)
            ->getData(true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function dataTableColumns(array $columnSearches = []): array
    {
        $columns = [
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'searchable' => false, 'orderable' => false],
            ['data' => 'branch_name', 'name' => 'branch_name'],
            ['data' => 'job_no', 'name' => 'job_no'],
            ['data' => 'party_name', 'name' => 'party_name'],
            ['data' => 'expense_date', 'name' => 'expense_date'],
            ['data' => 'expense_no', 'name' => 'expense_no'],
            ['data' => 'expense_by', 'name' => 'expense_by'],
            ['data' => 'expense_category', 'name' => 'expense_category'],
            ['data' => 'expense_head', 'name' => 'expense_head'],
            ['data' => 'receiptable_badge', 'name' => 'receiptable_badge'],
            ['data' => 'expense_amount', 'name' => 'expense_amount'],
        ];

        return array_map(function (array $column) use ($columnSearches) {
            $column += [
                'searchable' => true,
                'orderable'  => true,
                'search'     => ['value' => '', 'regex' => 'false'],
            ];

            $column['search']['value'] = $columnSearches[$column['data']] ?? '';

            return $column;
        }, $columns);
    }

    private function renderController(string $method, array $filters = [])
    {
        $request = JobExpenseRequest::create(
            route('chevron.reports.job-expense'),
            'GET',
            $filters
        );

        return app(ReportController::class)->{$method}($request);
    }

    private function createJob(string $jobNo, string $partyName, int $branchId = 5): ChevronJob
    {
        return ChevronJob::create([
            'job_no'     => $jobNo,
            'party_name' => $partyName,
            'branch_id'  => $branchId,
            'status'     => 'Active',
        ]);
    }

    private function createExpense(ChevronJob $job, array $items): ChevronJobExpense
    {
        $expense = ChevronJobExpense::create([
            'expense_no'  => 'EXP'.str_pad((string) (ChevronJobExpense::count() + 1), 6, '0', STR_PAD_LEFT),
            'branch_id'   => $job->branch_id,
            'job_id'      => $job->id,
            'job_no'      => $job->job_no,
            'employee_id' => $this->employee->id,
            'date'        => '2026-10-01',
            'status'      => 'Draft',
        ]);

        foreach ($items as $item) {
            ChevronJobExpenseItem::create([
                'job_expense_id'  => $expense->id,
                'expense_head_id' => $this->expenseHead()->id,
                'receiptable'     => $item['receiptable'],
                'expense_amount'  => $item['expense_amount'],
                'expense_date'    => $item['expense_date'] ?? '2026-10-01',
            ]);
        }

        return $expense;
    }

    private function createBill(ChevronJob $job, float $netPayable): ChevronBill
    {
        return ChevronBill::create([
            'bill_no'     => 'BIL'.str_pad((string) (ChevronBill::count() + 1), 6, '0', STR_PAD_LEFT),
            'branch_id'   => $job->branch_id,
            'job_id'      => $job->id,
            'job_no'      => $job->job_no,
            'party_name'  => $job->party_name,
            'bill_date'   => '2026-10-01',
            'net_payable' => $netPayable,
            'status'      => 'Active',
        ]);
    }

    private function expenseHead(): ChevronExpenseHead
    {
        return ChevronExpenseHead::query()->firstOrCreate(
            ['name' => 'Port Handling'],
            ['expense_category_id' => $this->category->id, 'type' => 'External', 'is_active' => true]
        );
    }

    private function createSchema(): void
    {
        Schema::create('chevron_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_branch_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();
        });

        Schema::create('chevron_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_bill')->default(false);
            $table->boolean('is_job')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(true);
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('job_no')->nullable();
            $table->string('party_name')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bill_no')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->string('party_name')->nullable();
            $table->date('bill_date')->nullable();
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_job_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('expense_no');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date')->nullable();
            $table->string('status')->default('Draft');
            $table->timestamps();
        });

        Schema::create('chevron_job_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_expense_id');
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->string('receiptable')->default('No');
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->date('expense_date')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }
}

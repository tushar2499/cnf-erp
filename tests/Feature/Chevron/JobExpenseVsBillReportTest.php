<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\ReportController;
use App\Http\Requests\Chevron\Report\JobExpenseVsBillRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Chevron\ChevronJobExpenseItem;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobExpenseVsBillReportTest extends TestCase
{
    private Employee $employee;

    private ChevronExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        $this->employee = Employee::create([
            'name'      => 'Mr. Vs Bill Employee',
            'code'      => 'EMP-VS',
            'is_active' => true,
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
        Schema::dropIfExists('chevron_job_expense_items');
        Schema::dropIfExists('chevron_job_expenses');
        Schema::dropIfExists('chevron_bills');
        Schema::dropIfExists('chevron_jobs');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('chevron_expense_categories');
        Schema::dropIfExists('employee_branch_access');
        Schema::dropIfExists('employees');

        parent::tearDown();
    }

    #[Test]
    public function flat_rows_show_expense_details_and_the_job_bill_amount(): void
    {
        $job = $this->createJob('JOB-001', 'ACME TRADING');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 150],
        ]);
        $this->createBill($job, 500);

        $rows = $this->renderController('jobExpenseVsBill', ['job_no' => 'JOB-001'])->getData()['rows'];

        $this->assertCount(1, $rows);

        $row = $rows->first();

        $this->assertSame('JOB-001', $row->jobExpense->job_no);
        $this->assertSame('ACME TRADING', $row->jobExpense->job->party_name);
        $this->assertSame('Job Cost', $row->expenseHead->expenseCategory->name);
        $this->assertSame('Port Handling', $row->expenseHead->name);
        $this->assertSame('Yes', $row->receiptable);
        $this->assertSame(150.0, (float) $row->expense_amount);
    }

    #[Test]
    public function bill_total_is_summed_per_job_not_per_expense_row(): void
    {
        $job = $this->createJob('JOB-002', 'ACME TRADING');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
            ['receiptable' => 'No', 'expense_amount' => 50],
        ]);
        $this->createBill($job, 400);
        $this->createBill($job, 100);

        $view = $this->renderController('jobExpenseVsBill', ['job_no' => 'JOB-002']);
        $billTotals = $view->getData()['billTotals'];

        $this->assertSame(500.0, (float) $billTotals[$job->id]);
        $this->assertSame(150.0, (float) $view->getData()['totalExpense']);
        $this->assertSame(500.0, (float) $view->getData()['totalBill']);
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

        $rows = $this->renderController('jobExpenseVsBill', ['from_date' => '2026-01-01'])->getData()['rows'];

        $this->assertCount(1, $rows);
        $this->assertSame('JOB-003', $rows->first()->jobExpense->job_no);
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

        $byJob = $this->renderController('jobExpenseVsBill', ['job_no' => 'JOB-005'])->getData()['rows'];
        $this->assertCount(1, $byJob);
        $this->assertSame('JOB-005', $byJob->first()->jobExpense->job_no);

        $byDate = $this->renderController('jobExpenseVsBill', [
            'from_date' => '2026-08-01',
            'to_date'   => '2026-08-31',
        ])->getData()['rows'];
        $this->assertCount(1, $byDate);
        $this->assertSame('JOB-006', $byDate->first()->jobExpense->job_no);
    }

    #[Test]
    public function print_view_renders_expense_and_bill_totals(): void
    {
        $job = $this->createJob('JOB-PRINT', 'ACME PRINT');
        $this->createExpense($job, [
            ['receiptable' => 'Yes', 'expense_amount' => 150],
        ]);
        $this->createBill($job, 500);

        $html = $this->renderController('jobExpenseVsBillPrint', ['job_no' => 'JOB-PRINT'])->render();

        $this->assertStringContainsString('Job Expense Vs Bill Report', $html);
        $this->assertStringContainsString('ACME PRINT', $html);
        $this->assertStringContainsString('Expense No', $html);
        $this->assertStringContainsString('EXP000001', $html);
        $this->assertStringContainsString('Bill Amount', $html);
        $this->assertStringContainsString('150.00', $html);
        $this->assertStringContainsString('500.00', $html);
        $this->assertStringContainsString('Difference (Expense - Bill)', $html);
        $this->assertStringContainsString('-350.00', $html);
    }

    #[Test]
    public function job_search_returns_matching_job_numbers_for_the_dropdown(): void
    {
        $this->createJob('JOB-SEARCH-001', 'SEARCHABLE PARTY');
        $this->createJob('JOB-OTHER-002', 'ANOTHER PARTY');

        $request = JobExpenseVsBillRequest::create(
            route('chevron.reports.job-expense-vs-bill.search-jobs'),
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

        $request = JobExpenseVsBillRequest::create(
            route('chevron.reports.job-expense-vs-bill'),
            'GET',
            ['from_date' => '', 'to_date' => '', 'job_no' => '']
        );

        $view = app(ReportController::class)->jobExpenseVsBill($request);

        $this->assertCount(0, $view->getData()['rows']);
    }

    private function renderController(string $method, array $filters = [])
    {
        $request = JobExpenseVsBillRequest::create(
            route('chevron.reports.job-expense-vs-bill'),
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

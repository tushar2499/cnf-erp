<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\ReportController;
use App\Http\Requests\Chevron\Report\JobExpenseSummaryRequest;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJobExpense;
use App\Models\Chevron\ChevronJobExpenseItem;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobExpenseSummaryReportTest extends TestCase
{
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();

        $this->employee = Employee::create([
            'name'      => 'Mr. Report Employee',
            'code'      => 'EMP-REPORT',
            'is_active' => true,
        ]);

        $this->employee->branchAccess()->create(['branch_id' => 5]);

        session(['active_branch_id' => 5]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chevron_job_expense_items');
        Schema::dropIfExists('chevron_job_expenses');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('employee_branch_access');
        Schema::dropIfExists('employees');

        parent::tearDown();
    }

    #[Test]
    public function receiptable_totals_are_calculated_from_yes_no_values(): void
    {
        $this->createExpense('EXP000001', [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
            ['receiptable' => 'No', 'expense_amount' => 40],
            ['receiptable' => 'Yes', 'expense_amount' => 60],
        ]);

        $html = $this->renderController('jobExpenseSummaryPrint')->render();

        $this->assertStringContainsString('Total Receiptable Amount', $html);
        $this->assertStringContainsString('160.00', $html);
        $this->assertStringContainsString('Total Non-Receiptable Amount', $html);
        $this->assertStringContainsString('40.00', $html);
        $this->assertStringContainsString('200.00', $html);
    }

    #[Test]
    public function report_only_loads_expenses_for_the_active_branch(): void
    {
        $this->createExpense('EXP000001', [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ], branchId: 5);

        $this->createExpense('EXP000002', [
            ['receiptable' => 'Yes', 'expense_amount' => 900],
        ], branchId: 6);

        $view = $this->renderController('jobExpenseSummary', ['from_date' => '2026-01-01']);

        $expenses = $view->getData()['expenses'];

        $this->assertCount(1, $expenses);
        $this->assertSame('EXP000001', $expenses->first()->expense_no);
    }

    #[Test]
    public function employee_filter_matches_the_employee_relation(): void
    {
        $this->createExpense('EXP000001', [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $other = Employee::create([
            'name'      => 'Ms. Other Employee',
            'code'      => 'EMP-OTHER',
            'is_active' => true,
        ]);

        $this->createExpense('EXP000002', [
            ['receiptable' => 'No', 'expense_amount' => 50],
        ], employeeId: $other->id);

        $view = $this->renderController('jobExpenseSummary', ['employee_id' => $this->employee->id]);

        $expenses = $view->getData()['expenses'];

        $this->assertCount(1, $expenses);
        $this->assertSame($this->employee->id, $expenses->first()->employee_id);
    }

    #[Test]
    public function employee_dropdown_is_branch_scoped_and_uses_employee_codes(): void
    {
        Employee::create([
            'name'      => 'Mr. Other Branch',
            'code'      => 'EMP-ELSEWHERE',
            'is_active' => true,
        ])->branchAccess()->create(['branch_id' => 6]);

        $view = $this->renderController('jobExpenseSummary');

        $employees = $view->getData()['employees'];

        $this->assertCount(1, $employees);
        $this->assertSame('EMP-REPORT', $employees->first()->code);
    }

    #[Test]
    public function blank_filters_do_not_load_every_expense(): void
    {
        $this->createExpense('EXP000001', [
            ['receiptable' => 'Yes', 'expense_amount' => 100],
        ]);

        $request = JobExpenseSummaryRequest::create(
            route('chevron.reports.job-expense-summary'),
            'GET',
            ['from_date' => '', 'to_date' => '', 'job_no' => '', 'employee_id' => '']
        );

        $view = app(ReportController::class)->jobExpenseSummary($request);

        $this->assertCount(0, $view->getData()['expenses']);
    }

    private function renderController(string $method, array $filters = [])
    {
        $request = JobExpenseSummaryRequest::create(
            route('chevron.reports.job-expense-summary'),
            'GET',
            $filters
        );

        return app(ReportController::class)->{$method}($request);
    }

    private function createExpense(
        string $expenseNo,
        array $items,
        int $branchId = 5,
        ?int $employeeId = null
    ): ChevronJobExpense {
        $expense = ChevronJobExpense::create([
            'expense_no'  => $expenseNo,
            'branch_id'   => $branchId,
            'job_no'      => 'JOB-'.str_replace('EXP', '', $expenseNo),
            'employee_id' => $employeeId ?? $this->employee->id,
            'date'        => '2026-10-01',
            'status'      => 'Draft',
        ]);

        foreach ($items as $item) {
            ChevronJobExpenseItem::create([
                'job_expense_id'  => $expense->id,
                'expense_head_id' => $this->expenseHead()->id,
                'receiptable'     => $item['receiptable'],
                'expense_amount'  => $item['expense_amount'],
                'expense_date'    => '2026-10-01',
            ]);
        }

        return $expense;
    }

    private function expenseHead(): ChevronExpenseHead
    {
        return ChevronExpenseHead::query()->firstOrCreate(
            ['name' => 'Port Handling'],
            ['type' => 'External', 'is_active' => true]
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

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_job_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('expense_no');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('be_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value_usd', 15, 2)->nullable();
            $table->string('bl_no')->nullable();
            $table->date('date')->nullable();
            $table->decimal('total_expense_amount', 15, 2)->nullable();
            $table->decimal('total_approved_amount', 15, 2)->nullable();
            $table->text('remarks')->nullable();
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

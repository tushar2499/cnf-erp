<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\ReportController;
use App\Http\Requests\Chevron\Report\JobBillRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronBillItem;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Chevron\ChevronJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobBillReportTest extends TestCase
{
    private ChevronExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->createJobBillView();

        DB::table('chevron_branches')->insert([
            ['id' => 5, 'name' => 'Dhaka Branch'],
            ['id' => 6, 'name' => 'Chattogram Branch'],
        ]);

        $this->category = ChevronExpenseCategory::create([
            'name'      => 'Clearing Charge',
            'is_bill'   => true,
            'is_active' => true,
        ]);

        session(['active_branch_id' => 5]);
    }

    protected function tearDown(): void
    {
        DB::statement('DROP VIEW IF EXISTS chevron_job_bill_view');
        Schema::dropIfExists('chevron_bill_items');
        Schema::dropIfExists('chevron_bills');
        Schema::dropIfExists('chevron_jobs');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('chevron_expense_categories');
        Schema::dropIfExists('chevron_branches');

        parent::tearDown();
    }

    #[Test]
    public function flat_rows_show_bill_details(): void
    {
        $job = $this->createJob('JOB-001', 'ACME TRADING');
        $this->createBill($job, [150]);

        $json = $this->dataTable(['job_no' => 'JOB-001']);

        $this->assertSame(1, $json['recordsTotal']);
        $this->assertCount(1, $json['data']);

        $row = $json['data'][0];

        $this->assertSame('Dhaka Branch', $row['branch_name']);
        $this->assertSame('JOB-001', $row['job_no']);
        $this->assertSame('ACME TRADING', $row['party_name']);
        $this->assertSame('01 Oct 2026', $row['bill_date']);
        $this->assertSame('BIL000001', $row['bill_no']);
        $this->assertSame('C&amp;F', $row['bill_type']);
        $this->assertSame('Clearing Charge', $row['expense_category']);
        $this->assertSame('Port Handling', $row['expense_head']);
        $this->assertSame('150.00', $row['bill_amount']);
        $this->assertSame('150.00', $json['total_bill']);
    }

    #[Test]
    public function totals_sum_every_bill_item_row(): void
    {
        $job = $this->createJob('JOB-002', 'ACME TRADING');
        $this->createBill($job, [100, 50]);
        $this->createBill($job, [25]);

        $json = $this->dataTable(['job_no' => 'JOB-002']);

        $this->assertCount(3, $json['data']);
        $this->assertSame('175.00', $json['total_bill']);
        $this->assertSame(1, $json['total_jobs']);
        $this->assertSame(3, $json['total_rows']);
    }

    #[Test]
    public function report_only_loads_bills_for_the_active_branch(): void
    {
        $activeJob = $this->createJob('JOB-003', 'ACTIVE PARTY', 5);
        $this->createBill($activeJob, [100]);

        $otherJob = $this->createJob('JOB-004', 'OTHER PARTY', 6);
        $this->createBill($otherJob, [900]);

        $json = $this->dataTable(['from_date' => '2026-01-01']);

        $this->assertCount(1, $json['data']);
        $this->assertSame('JOB-003', $json['data'][0]['job_no']);
        $this->assertSame('100.00', $json['total_bill']);
    }

    #[Test]
    public function date_and_job_filters_narrow_the_bill_rows(): void
    {
        $jobA = $this->createJob('JOB-005', 'PARTY A');
        $jobB = $this->createJob('JOB-006', 'PARTY B');

        $this->createBill($jobA, [100], '2026-03-10');
        $this->createBill($jobB, [200], '2026-08-15');

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
    public function print_view_renders_bill_rows_and_totals(): void
    {
        $job = $this->createJob('JOB-PRINT', 'ACME PRINT');
        $this->createBill($job, [150]);

        $html = $this->renderController('jobBillPrint', ['job_no' => 'JOB-PRINT'])->render();

        $this->assertStringContainsString('Job Bill Report', $html);
        $this->assertStringContainsString('Branch', $html);
        $this->assertStringContainsString('Dhaka Branch', $html);
        $this->assertStringContainsString('ACME PRINT', $html);
        $this->assertStringContainsString('Bill No', $html);
        $this->assertStringContainsString('BIL000001', $html);
        $this->assertStringContainsString('Total Job Bill Amount', $html);
        $this->assertStringContainsString('150.00', $html);
        $this->assertStringNotContainsString('Expense No', $html);
    }

    #[Test]
    public function job_search_returns_matching_job_numbers_for_the_dropdown(): void
    {
        $this->createJob('JOB-SEARCH-001', 'SEARCHABLE PARTY');
        $this->createJob('JOB-OTHER-002', 'ANOTHER PARTY');

        $request = JobBillRequest::create(
            route('chevron.reports.job-bill.search-jobs'),
            'GET',
            ['q' => 'SEARCHABLE']
        );

        $response = app(ReportController::class)->searchBillJobs($request)->getData(true);

        $this->assertCount(1, $response);
        $this->assertSame('JOB-SEARCH-001', $response[0]['text']);
        $this->assertSame('JOB-SEARCH-001', $response[0]['id']);
    }

    #[Test]
    public function blank_filters_do_not_load_every_bill(): void
    {
        $job = $this->createJob('JOB-007', 'PARTY');
        $this->createBill($job, [100]);

        $otherJob = $this->createJob('JOB-008', 'OTHER PARTY', 6);
        $this->createBill($otherJob, [900]);

        $json = $this->dataTable();

        $this->assertCount(0, $json['data']);
        $this->assertSame('0.00', $json['total_bill']);
        $this->assertSame(0, $json['total_rows']);
        $this->assertSame(0, $json['total_jobs']);
    }

    #[Test]
    public function column_filters_narrow_the_rows_and_totals(): void
    {
        $jobA = $this->createJob('JOB-C1', 'PARTY ONE');
        $this->createBill($jobA, [100]);

        $jobB = $this->createJob('JOB-C2', 'PARTY TWO');
        $this->createBill($jobB, [200]);

        $byParty = $this->dataTable(['from_date' => '2026-01-01'], ['party_name' => 'PARTY TWO']);
        $this->assertCount(1, $byParty['data']);
        $this->assertSame('JOB-C2', $byParty['data'][0]['job_no']);
        $this->assertSame('200.00', $byParty['total_bill']);
        $this->assertSame(1, $byParty['total_rows']);
        $this->assertSame(1, $byParty['total_jobs']);

        $byAmount = $this->dataTable(['from_date' => '2026-01-01'], ['bill_amount' => '100']);
        $this->assertCount(1, $byAmount['data']);
        $this->assertSame('JOB-C1', $byAmount['data'][0]['job_no']);
        $this->assertSame('100.00', $byAmount['total_bill']);
    }

    #[Test]
    public function branch_name_is_searchable_by_column_and_globally(): void
    {
        $job = $this->createJob('JOB-B1', 'PARTY BRANCH');
        $this->createBill($job, [100]);

        $byColumn = $this->dataTable(['from_date' => '2026-01-01'], ['branch_name' => 'Dhaka']);
        $this->assertCount(1, $byColumn['data']);
        $this->assertSame('Dhaka Branch', $byColumn['data'][0]['branch_name']);

        $noMatch = $this->dataTable(['from_date' => '2026-01-01'], ['branch_name' => 'Chattogram']);
        $this->assertCount(0, $noMatch['data']);
        $this->assertSame('0.00', $noMatch['total_bill']);

        $global = $this->dataTable(
            ['from_date' => '2026-01-01', 'search' => ['value' => 'Dhaka Branch', 'regex' => 'false']]
        );
        $this->assertCount(1, $global['data']);
    }

    #[Test]
    public function global_search_narrows_the_rows_and_totals(): void
    {
        $jobA = $this->createJob('JOB-G1', 'PARTY ONE');
        $this->createBill($jobA, [100]);

        $jobB = $this->createJob('JOB-G2', 'PARTY TWO');
        $this->createBill($jobB, [200]);

        $byParty = $this->dataTable(
            ['from_date' => '2026-01-01', 'search' => ['value' => 'PARTY TWO', 'regex' => 'false']]
        );

        $this->assertCount(1, $byParty['data']);
        $this->assertSame('JOB-G2', $byParty['data'][0]['job_no']);
        $this->assertSame('200.00', $byParty['total_bill']);
        $this->assertSame(1, $byParty['total_jobs']);
    }

    #[Test]
    public function bill_rows_without_a_job_still_show(): void
    {
        $bill = ChevronBill::create([
            'bill_no'     => 'BIL-NOJOB',
            'branch_id'   => 5,
            'bill_type'   => 'Advance',
            'bill_date'   => '2026-10-01',
            'job_id'      => null,
            'job_no'      => 'JOB-ORPHAN',
            'party_name'  => 'ORPHAN PARTY',
            'net_payable' => 75,
            'status'      => 'Active',
        ]);

        ChevronBillItem::create([
            'bill_id'             => $bill->id,
            'expense_category_id' => $this->category->id,
            'expense_head_id'     => $this->expenseHead()->id,
            'amount'              => 75,
        ]);

        $json = $this->dataTable(['job_no' => 'JOB-ORPHAN']);

        $this->assertCount(1, $json['data']);
        $this->assertSame('ORPHAN PARTY', $json['data'][0]['party_name']);
        $this->assertSame('Advance', $json['data'][0]['bill_type']);
        $this->assertSame('75.00', $json['total_bill']);
        $this->assertSame(0, $json['total_jobs']);
    }

    private function createJobBillView(): void
    {
        $migration = require glob(database_path('migrations/*_create_chevron_job_bill_view.php'))[0];

        $migration->up();
    }

    private function dataTable(array $filters = [], array $columnSearches = []): array
    {
        $request = JobBillRequest::create(
            route('chevron.reports.job-bill'),
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
            ->jobBill($request)
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
            ['data' => 'bill_date', 'name' => 'bill_date'],
            ['data' => 'bill_no', 'name' => 'bill_no'],
            ['data' => 'bill_type', 'name' => 'bill_type'],
            ['data' => 'expense_category', 'name' => 'expense_category'],
            ['data' => 'expense_head', 'name' => 'expense_head'],
            ['data' => 'bill_amount', 'name' => 'bill_amount'],
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
        $request = JobBillRequest::create(
            route('chevron.reports.job-bill'),
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

    /**
     * @param  array<int, float|int>  $itemAmounts
     */
    private function createBill(ChevronJob $job, array $itemAmounts, string $billDate = '2026-10-01'): ChevronBill
    {
        $bill = ChevronBill::create([
            'bill_no'     => 'BIL'.str_pad((string) (ChevronBill::count() + 1), 6, '0', STR_PAD_LEFT),
            'branch_id'   => $job->branch_id,
            'bill_type'   => 'C&F',
            'bill_date'   => $billDate,
            'job_id'      => $job->id,
            'job_no'      => $job->job_no,
            'party_name'  => $job->party_name,
            'net_payable' => array_sum($itemAmounts),
            'status'      => 'Active',
        ]);

        foreach ($itemAmounts as $amount) {
            ChevronBillItem::create([
                'bill_id'             => $bill->id,
                'expense_category_id' => $this->category->id,
                'expense_head_id'     => $this->expenseHead()->id,
                'amount'              => $amount,
            ]);
        }

        return $bill;
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
            $table->string('bill_type')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->string('party_name')->nullable();
            $table->date('bill_date')->nullable();
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('qty', 15, 3)->nullable();
            $table->timestamps();
        });
    }
}

<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\BillController;
use App\Http\Requests\Chevron\Bill\EditBillRequest;
use App\Http\Requests\Chevron\Bill\IndexBillRequest;
use App\Http\Requests\Chevron\Bill\PrintBillRequest;
use App\Http\Requests\Chevron\Bill\UpdateBillRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Yajra\DataTables\Facades\DataTables;

class BillControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createJobSchema();
        $this->createBillSchema();
        $this->createBillItemSchema();
        $this->createExpenseReferenceSchemas();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chevron_bill_items');
        Schema::dropIfExists('chevron_bills');
        Schema::dropIfExists('chevron_jobs');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('chevron_expense_categories');

        parent::tearDown();
    }

    public function test_job_search_uses_the_original_assessable_value(): void
    {
        ChevronJob::create([
            'job_no'               => 'JOB-001',
            'party_name'           => 'Example Party',
            'assessable_value'     => 123.45,
            'assessable_value_bdt' => 13579.50,
            'currency_rate'        => 110,
        ]);

        $request = IndexBillRequest::create('/', 'GET', ['q' => 'JOB-001']);
        $response = (new BillController)->searchJobs($request);

        $this->assertEquals(123.45, $response->getData(true)[0]['assessable_value']);
    }

    public function test_bill_list_defaults_to_newest_first(): void
    {
        $dataTable = \Mockery::mock();
        $dataTable->shouldReceive('addIndexColumn')->once()->andReturnSelf();
        $dataTable->shouldReceive('editColumn')->twice()->andReturnSelf();
        $dataTable->shouldReceive('addColumn')->times(5)->andReturnSelf();
        $dataTable->shouldReceive('rawColumns')->once()->andReturnSelf();
        $dataTable->shouldReceive('make')->once()->with(true)->andReturn(response()->json([]));
        DataTables::shouldReceive('of')
            ->once()
            ->withArgs(function ($query): bool {
                $this->assertSame('desc', $query->getQuery()->orders[0]['direction']);

                return true;
            })
            ->andReturn($dataTable);

        $request = IndexBillRequest::create('/', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        session(['active_branch_id' => 5]);

        $this->assertSame(200, (new BillController)->index($request)->getStatusCode());
    }

    public function test_edit_uses_the_linked_jobs_original_assessable_value(): void
    {
        $job = ChevronJob::create([
            'job_no'               => 'JOB-001',
            'assessable_value'     => 123.45,
            'assessable_value_bdt' => 13579.50,
        ]);
        $bill = ChevronBill::create([
            'bill_no'          => 'BIL000001',
            'job_id'           => $job->id,
            'assessable_value' => 13579.50,
        ]);

        $view = (new BillController)->edit(EditBillRequest::create('/', 'GET'), $bill);

        $this->assertEquals(123.45, $view->getData()['assessableValue']);
    }

    public function test_updating_a_bill_redirects_to_the_bill_list(): void
    {
        $bill = ChevronBill::create(['bill_no' => 'BIL000001', 'branch_id' => 5]);
        $request = UpdateBillRequest::create('/', 'PUT', [
            'bill_date' => '2026-01-01',
            'job_no'    => 'JOB-001',
            'rows'      => [['expense_category_id' => null, 'expense_head_id' => null, 'amount' => 0]],
        ]);

        $response = (new BillController)->update($request, $bill);

        $this->assertSame(route('chevron.cnf.bills.index'), $response->getTargetUrl());
    }

    public function test_print_shows_commission_under_miscellaneous_and_hides_empty_sections(): void
    {
        $bill = ChevronBill::create([
            'bill_no'               => 'BIL000001',
            'commission_on'         => 'ASSESSABLE',
            'commission_rate'       => 2,
            'commission_amount'     => 500,
            'total_payable'         => 500,
            'less_customs_duty_tax' => 0,
            'income_tax_cnf_com'    => 0,
            'due_amount'            => 500,
        ]);

        $html = (new BillController)->print(PrintBillRequest::create('/', 'GET'), $bill)->render();

        $this->assertStringContainsString('MISCELLANEOUS', $html);
        $this->assertStringContainsString('Commission on Assessable Value', $html);
        $this->assertStringNotContainsString('LESS CUSTOMS DUTY', $html);
        $this->assertStringNotContainsString(' Rate', $html);
        $this->assertStringNotContainsString('TOTAL', $html);
    }

    public function test_print_uses_job_currency_and_shows_customs_duty_when_present(): void
    {
        $job = ChevronJob::create([
            'job_no'        => 'JOB-001',
            'currency_type' => 'USD',
            'currency_rate' => 110,
        ]);
        $bill = ChevronBill::create([
            'bill_no'               => 'BIL000001',
            'job_id'                => $job->id,
            'currency_type'         => 'BDT',
            'currency_rate'         => 999,
            'commission_amount'     => 0,
            'total_payable'         => 1000,
            'less_customs_duty_tax' => 250,
            'income_tax_cnf_com'    => 0,
            'due_amount'            => 750,
        ]);

        $html = (new BillController)->print(PrintBillRequest::create('/', 'GET'), $bill)->render();

        $this->assertStringContainsString('USD Rate', $html);
        $this->assertStringContainsString('110.00', $html);
        $this->assertStringNotContainsString('999.00', $html);
        $this->assertStringContainsString('LESS CUSTOMS DUTY', $html);
        $this->assertStringContainsString('TOTAL', $html);
        $this->assertStringNotContainsString('MISCELLANEOUS', $html);
    }

    public function test_print_hides_currency_section_when_no_currency_rate_exists(): void
    {
        $bill = ChevronBill::create([
            'bill_no'           => 'BIL000001',
            'currency_type'     => 'USD',
            'currency_rate'     => 150,
            'commission_amount' => 0,
            'total_payable'     => 100,
            'due_amount'        => 100,
        ]);

        $html = (new BillController)->print(PrintBillRequest::create('/', 'GET'), $bill)->render();

        $this->assertStringNotContainsString('USD Rate', $html);
        $this->assertStringNotContainsString('Rate', $html);
    }

    private function createJobSchema(): void
    {
        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();

            foreach ([
                'job_no', 'party_name', 'party_address', 'goods_name', 'mate_code', 'po_no',
                'pack_unit', 'gross_weight_unit', 'lc_no', 'lca_no', 'be_no', 'hbi_hawb_no',
                'invoice_no', 'mbl_mawb_no', 'bl_no', 'currency_type',
            ] as $column) {
                $table->string($column)->nullable();
            }

            foreach (['pack_quantity', 'gross_weight', 'assessable_value', 'assessable_value_bdt', 'currency_rate', 'invoice_value_1', 'invoice_value_2'] as $column) {
                $table->decimal($column, 15, 2)->nullable();
            }

            foreach (['be_date', 'invoice_date', 'bl_date', 'delivery_date'] as $column) {
                $table->date($column)->nullable();
            }

            $table->timestamps();
        });
    }

    private function createBillSchema(): void
    {
        Schema::create('chevron_bills', function (Blueprint $table) {
            $table->id();

            foreach ([
                'bill_no', 'bill_type', 'bill_date', 'delivery_date', 'job_id', 'job_no',
                'party_name', 'party_address', 'goods_description', 'mate_code', 'po_no',
                'quantity', 'quantity_unit', 'quantity_remark', 'gross_weight', 'gross_weight_unit',
                'lc_no', 'lc_ref', 'be_no', 'be_date', 'invoice_no', 'invoice_ref', 'invoice_date',
                'bl_no', 'bl_ref', 'bl_date', 'assessable_value', 'invoice_value_bdt', 'invoice_value',
                'currency_type', 'currency_rate', 'remarks', 'sub_total', 'commission_on',
                'commission_rate', 'commission_amount', 'total_payable', 'less_customs_duty_tax',
                'income_tax_cnf_com', 'net_payable', 'advance_amount', 'due_amount', 'status',
            ] as $column) {
                $table->text($column)->nullable();
            }

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });
    }

    private function createBillItemSchema(): void
    {
        Schema::create('chevron_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->unsignedBigInteger('expense_head_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('note')->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('qty', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    private function createExpenseReferenceSchemas(): void
    {
        Schema::create('chevron_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active');
            $table->boolean('is_bill');
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(false);
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_active');
        });
    }
}

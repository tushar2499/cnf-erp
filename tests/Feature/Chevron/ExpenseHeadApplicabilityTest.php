<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\BillController;
use App\Http\Controllers\Chevron\ExpenseHeadController;
use App\Http\Requests\Chevron\Bill\CreateBillRequest;
use App\Http\Requests\Chevron\ExpenseHead\StoreExpenseHeadRequest;
use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseHeadApplicabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id');
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(false);
            $table->string('type');
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_head_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_head_id');
            $table->unsignedBigInteger('employee_id');
            $table->primary(['expense_head_id', 'employee_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chevron_expense_head_employees');
        Schema::dropIfExists('chevron_expense_heads');
        Schema::dropIfExists('chevron_expense_categories');
        Schema::dropIfExists('employees');

        parent::tearDown();
    }

    #[Test]
    public function job_only_category_forces_the_head_to_be_for_jobs(): void
    {
        $category = $this->category(['name' => 'JOB CATEGORY', 'is_bill' => false, 'is_job' => true]);

        (new ExpenseHeadController)->store($this->storeRequest([
            'name'                => 'Job Head',
            'expense_category_id' => $category->id,
            'type'                => 'External',
            'is_bill'             => 1,
            'is_job'              => 0,
        ]));

        $this->assertDatabaseHas('chevron_expense_heads', [
            'name'    => 'Job Head',
            'is_bill' => 0,
            'is_job'  => 1,
        ]);
    }

    #[Test]
    public function bill_only_category_forces_the_head_to_be_for_bills(): void
    {
        $category = $this->category(['name' => 'BILL CATEGORY', 'is_bill' => true, 'is_job' => false]);

        (new ExpenseHeadController)->store($this->storeRequest([
            'name'                => 'Bill Head',
            'expense_category_id' => $category->id,
            'type'                => 'External',
            'is_bill'             => 0,
            'is_job'              => 1,
        ]));

        $this->assertDatabaseHas('chevron_expense_heads', [
            'name'    => 'Bill Head',
            'is_bill' => 1,
            'is_job'  => 0,
        ]);
    }

    #[Test]
    public function both_category_allows_the_head_to_be_narrowed_to_jobs_only(): void
    {
        $category = $this->category(['name' => 'BOTH CATEGORY', 'is_bill' => true, 'is_job' => true]);

        (new ExpenseHeadController)->store($this->storeRequest([
            'name'                => 'Shared Head',
            'expense_category_id' => $category->id,
            'type'                => 'Internal',
            'is_bill'             => 0,
            'is_job'              => 1,
        ]));

        $this->assertDatabaseHas('chevron_expense_heads', [
            'name'    => 'Shared Head',
            'is_bill' => 0,
            'is_job'  => 1,
        ]);
    }

    #[Test]
    public function both_category_requires_at_least_one_applicability(): void
    {
        $category = $this->category(['name' => 'BOTH CATEGORY', 'is_bill' => true, 'is_job' => true]);

        $this->expectException(ValidationException::class);

        (new ExpenseHeadController)->store($this->storeRequest([
            'name'                => 'Invalid Head',
            'expense_category_id' => $category->id,
            'type'                => 'External',
            'is_bill'             => 0,
            'is_job'              => 0,
        ]));
    }

    #[Test]
    public function bill_forms_only_list_heads_applicable_to_bills(): void
    {
        $billCategory = $this->category(['name' => 'BILL CATEGORY', 'is_bill' => true, 'is_job' => false]);
        $jobCategory = $this->category(['name' => 'JOB CATEGORY', 'is_bill' => false, 'is_job' => true]);

        ChevronExpenseHead::create([
            'name'                => 'Bill Head',
            'expense_category_id' => $billCategory->id,
            'is_bill'             => true,
            'is_job'              => false,
            'type'                => 'External',
            'is_active'           => true,
        ]);

        ChevronExpenseHead::create([
            'name'                => 'Job Head',
            'expense_category_id' => $jobCategory->id,
            'is_bill'             => false,
            'is_job'              => true,
            'type'                => 'External',
            'is_active'           => true,
        ]);

        $view = (new BillController)->create(CreateBillRequest::create('/chevron/cnf/bills/create', 'GET'));
        $headNames = collect($view->getData()['expenseHeads'])->pluck('name')->all();

        $this->assertContains('Bill Head', $headNames);
        $this->assertNotContains('Job Head', $headNames);
    }

    #[Test]
    public function head_created_without_flags_inherits_its_category_applicability(): void
    {
        $category = $this->category(['name' => 'API CATEGORY', 'is_bill' => true, 'is_job' => true]);

        $head = ChevronExpenseHead::create([
            'name'                => 'API Head',
            'expense_category_id' => $category->id,
            'type'                => 'External',
            'is_active'           => true,
        ]);

        $this->assertTrue($head->is_bill);
        $this->assertTrue($head->is_job);
        $this->assertStringContainsString('Bill & Job', $head->applicabilityBadge());
    }

    private function category(array $attributes): ChevronExpenseCategory
    {
        return ChevronExpenseCategory::create(array_merge([
            'is_active' => true,
        ], $attributes));
    }

    private function storeRequest(array $data): StoreExpenseHeadRequest
    {
        $request = StoreExpenseHeadRequest::create('/chevron/settings/expense-heads', 'POST', $data);
        $request->setUserResolver(fn () => $this->superUser());

        return $request;
    }

    private function superUser(): User
    {
        $user = new User;
        $user->is_super = true;

        return $user;
    }
}

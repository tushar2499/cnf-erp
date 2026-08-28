<?php

namespace Tests\Feature\Chevron;

use App\Models\Chevron\ChevronExpenseCategory;
use App\Models\Chevron\ChevronExpenseHead;
use App\Models\Employee;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseHeadEmployeesRelationshipTest extends TestCase
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('expense_category_id')->constrained('chevron_expense_categories')->onDelete('restrict');
            $table->string('type');
            $table->decimal('amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_head_employees', function (Blueprint $table) {
            $table->foreignId('expense_head_id')->constrained('chevron_expense_heads')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->primary(['expense_head_id', 'employee_id']);
        });
    }

    #[Test]
    public function expense_head_employees_relation_resolves_to_employee(): void
    {
        $head = $this->makeHead();
        $employee = Employee::create(['name' => 'Mr. John Doe', 'code' => 'EMP-0001', 'is_active' => true]);

        $head->employees()->attach($employee->id);

        $this->assertInstanceOf(Employee::class, $head->employees->first());
        $this->assertSame($employee->id, $head->employees->first()->id);
        $this->assertDatabaseHas('chevron_expense_head_employees', [
            'expense_head_id' => $head->id,
            'employee_id'     => $employee->id,
        ]);
    }

    #[Test]
    public function inactive_or_unknown_employee_is_not_attached_to_expense_head(): void
    {
        $head = $this->makeHead();

        $active = Employee::create(['name' => 'Mr. Active', 'code' => 'EMP-0002', 'is_active' => true]);
        $inactive = Employee::create(['name' => 'Mr. Inactive', 'code' => 'EMP-0003', 'is_active' => false]);

        $matched = Employee::whereIn('id', [$active->id, $inactive->id, 99999])
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $head->employees()->sync($matched);

        $this->assertDatabaseHas('chevron_expense_head_employees', ['expense_head_id' => $head->id, 'employee_id' => $active->id]);
        $this->assertDatabaseMissing('chevron_expense_head_employees', ['expense_head_id' => $head->id, 'employee_id' => $inactive->id]);
        $this->assertSame([$active->id], $head->employees()->pluck('employees.id')->all());
    }

    private function makeHead(): ChevronExpenseHead
    {
        return ChevronExpenseHead::create([
            'name'                => 'Port Handling Fee',
            'expense_category_id' => ChevronExpenseCategory::create(['name' => 'CUSTOMS', 'is_active' => true])->id,
            'type'                => 'External',
            'amount'              => 100,
            'is_active'           => true,
        ]);
    }
}

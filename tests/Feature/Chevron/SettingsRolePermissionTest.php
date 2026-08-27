<?php

namespace Tests\Feature\Chevron;

use App\Http\Requests\Chevron\Account\DestroyAccountRequest;
use App\Http\Requests\Chevron\Account\IndexAccountRequest;
use App\Http\Requests\Chevron\Account\StoreAccountRequest;
use App\Http\Requests\Chevron\Account\UpdateAccountRequest;
use App\Http\Requests\Chevron\Branch\DestroyBranchRequest;
use App\Http\Requests\Chevron\Branch\IndexBranchRequest;
use App\Http\Requests\Chevron\Branch\StoreBranchRequest;
use App\Http\Requests\Chevron\Branch\UpdateBranchRequest;
use App\Http\Requests\Chevron\Customer\DestroyCustomerRequest;
use App\Http\Requests\Chevron\Customer\IndexCustomerRequest;
use App\Http\Requests\Chevron\Customer\NextIdCustomerRequest;
use App\Http\Requests\Chevron\Customer\StoreCustomerRequest;
use App\Http\Requests\Chevron\Customer\UpdateCustomerRequest;
use App\Http\Requests\Chevron\ExpenseCategory\DestroyExpenseCategoryRequest;
use App\Http\Requests\Chevron\ExpenseCategory\ImportExpenseCategoryRequest;
use App\Http\Requests\Chevron\ExpenseCategory\IndexExpenseCategoryRequest;
use App\Http\Requests\Chevron\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\Chevron\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Http\Requests\Chevron\ExpenseHead\DestroyExpenseHeadRequest;
use App\Http\Requests\Chevron\ExpenseHead\ImportExpenseHeadRequest;
use App\Http\Requests\Chevron\ExpenseHead\IndexExpenseHeadRequest;
use App\Http\Requests\Chevron\ExpenseHead\StoreExpenseHeadRequest;
use App\Http\Requests\Chevron\ExpenseHead\UpdateExpenseHeadRequest;
use App\Http\Requests\Chevron\Item\DestroyItemRequest;
use App\Http\Requests\Chevron\Item\IndexItemRequest;
use App\Http\Requests\Chevron\Item\QuickStoreItemRequest;
use App\Http\Requests\Chevron\Item\StoreItemRequest;
use App\Http\Requests\Chevron\Item\UpdateItemRequest;
use App\Http\Requests\Chevron\JobType\DestroyJobTypeRequest;
use App\Http\Requests\Chevron\JobType\IndexJobTypeRequest;
use App\Http\Requests\Chevron\JobType\StoreJobTypeRequest;
use App\Http\Requests\Chevron\JobType\UpdateJobTypeRequest;
use App\Http\Requests\Chevron\Port\DestroyPortRequest;
use App\Http\Requests\Chevron\Port\IndexPortRequest;
use App\Http\Requests\Chevron\Port\StorePortRequest;
use App\Http\Requests\Chevron\Port\UpdatePortRequest;
use App\Http\Requests\Chevron\Service\DestroyServiceRequest;
use App\Http\Requests\Chevron\Service\IndexServiceRequest;
use App\Http\Requests\Chevron\Service\StoreServiceRequest;
use App\Http\Requests\Chevron\Service\UpdateServiceRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingsRolePermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('module')->nullable();
            $table->integer('sorting_order')->default(0);
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super')->default(false);
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public static function requestProvider(): array
    {
        return [
            // Services
            [IndexServiceRequest::class, 'cnf.service.list'],
            [StoreServiceRequest::class, 'cnf.service.create'],
            [UpdateServiceRequest::class, 'cnf.service.edit'],
            [DestroyServiceRequest::class, 'cnf.service.delete'],
            // Job Types
            [IndexJobTypeRequest::class, 'cnf.job-type.list'],
            [StoreJobTypeRequest::class, 'cnf.job-type.create'],
            [UpdateJobTypeRequest::class, 'cnf.job-type.edit'],
            [DestroyJobTypeRequest::class, 'cnf.job-type.delete'],
            // Ports
            [IndexPortRequest::class, 'cnf.port.list'],
            [StorePortRequest::class, 'cnf.port.create'],
            [UpdatePortRequest::class, 'cnf.port.edit'],
            [DestroyPortRequest::class, 'cnf.port.delete'],
            // Branches
            [IndexBranchRequest::class, 'cnf.branch.list'],
            [StoreBranchRequest::class, 'cnf.branch.create'],
            [UpdateBranchRequest::class, 'cnf.branch.edit'],
            [DestroyBranchRequest::class, 'cnf.branch.delete'],
            // Accounts
            [IndexAccountRequest::class, 'cnf.account.list'],
            [StoreAccountRequest::class, 'cnf.account.create'],
            [UpdateAccountRequest::class, 'cnf.account.edit'],
            [DestroyAccountRequest::class, 'cnf.account.delete'],
            // Items
            [IndexItemRequest::class, 'cnf.item.list'],
            [StoreItemRequest::class, 'cnf.item.create'],
            [UpdateItemRequest::class, 'cnf.item.edit'],
            [DestroyItemRequest::class, 'cnf.item.delete'],
            [QuickStoreItemRequest::class, 'cnf.item.create'],
            // Expense Categories
            [IndexExpenseCategoryRequest::class, 'cnf.expense-category.list'],
            [StoreExpenseCategoryRequest::class, 'cnf.expense-category.create'],
            [UpdateExpenseCategoryRequest::class, 'cnf.expense-category.edit'],
            [DestroyExpenseCategoryRequest::class, 'cnf.expense-category.delete'],
            [ImportExpenseCategoryRequest::class, 'cnf.expense-category.create'],
            // Expense Heads
            [IndexExpenseHeadRequest::class, 'cnf.expense-head.list'],
            [StoreExpenseHeadRequest::class, 'cnf.expense-head.create'],
            [UpdateExpenseHeadRequest::class, 'cnf.expense-head.edit'],
            [DestroyExpenseHeadRequest::class, 'cnf.expense-head.delete'],
            [ImportExpenseHeadRequest::class, 'cnf.expense-head.create'],
            // Customers
            [IndexCustomerRequest::class, 'cnf.customer.list'],
            [StoreCustomerRequest::class, 'cnf.customer.create'],
            [UpdateCustomerRequest::class, 'cnf.customer.edit'],
            [DestroyCustomerRequest::class, 'cnf.customer.delete'],
            [NextIdCustomerRequest::class, 'cnf.customer.create'],
        ];
    }

    private function makeUser(bool $authorized, string $permission): User
    {
        $perm = Permission::create([
            'name'       => $permission,
            'guard_name' => 'web',
            'company_id' => null,
            'module'     => 'Settings',
        ]);

        $authorizedRole = Role::create(['name' => 'Authorized Role', 'guard_name' => 'web']);
        $publicRole = Role::create(['name' => 'Public Role', 'guard_name' => 'web']);
        $authorizedRole->permissions()->attach($perm->id);

        $user = User::create([
            'name'      => 'Test User',
            'email'     => fake()->unique()->safeEmail(),
            'password'  => 'password',
            'is_super'  => false,
            'role_id'   => $authorized ? $authorizedRole->id : $publicRole->id,
        ]);

        return $user;
    }

    private function bind(FormRequest $request, User $user): FormRequest
    {
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    #[DataProvider('requestProvider')]
    public function test_request_enforces_permission(string $class, string $permission): void
    {
        $authorized = $this->bind(new $class, $this->makeUser(true, $permission));
        $this->assertTrue($authorized->authorize());

        $unauthorized = $this->bind(new $class, $this->makeUser(false, $permission));
        $this->assertFalse($unauthorized->authorize());
    }
}

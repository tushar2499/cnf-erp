<?php

namespace Tests\Feature\Chevron;

use App\Http\Requests\Chevron\Report\JobBillRequest;
use App\Http\Requests\Chevron\Report\JobExpenseRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobExpenseReportPermissionTest extends TestCase
{
    private const OLD_NAME = 'cnf.report.job-expense-vs-bill';

    private const NEW_NAME = 'cnf.report.job-expense';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('module')->default('Reports');
            $table->integer('sorting_order')->default(0);
            $table->timestamps();
            $table->unique(['name', 'company_id']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->primary(['role_id', 'permission_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');

        parent::tearDown();
    }

    #[Test]
    public function migration_renames_the_permission_and_keeps_role_assignments(): void
    {
        $oldId = $this->insertPermission(self::OLD_NAME);
        DB::table('role_has_permissions')->insert(['role_id' => 7, 'permission_id' => $oldId]);

        $this->permissionMigration()->up();

        $this->assertDatabaseMissing('permissions', ['name' => self::OLD_NAME]);
        $this->assertDatabaseHas('permissions', ['id' => $oldId, 'name' => self::NEW_NAME]);
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => 7, 'permission_id' => $oldId]);
    }

    #[Test]
    public function migration_merges_into_an_already_seeded_permission(): void
    {
        $oldId = $this->insertPermission(self::OLD_NAME);
        $newId = $this->insertPermission(self::NEW_NAME);

        DB::table('role_has_permissions')->insert([
            ['role_id' => 1, 'permission_id' => $oldId],
            ['role_id' => 2, 'permission_id' => $oldId],
            ['role_id' => 2, 'permission_id' => $newId],
        ]);

        $this->permissionMigration()->up();

        $this->assertDatabaseMissing('permissions', ['name' => self::OLD_NAME]);
        $this->assertSame(1, DB::table('permissions')->where('name', self::NEW_NAME)->count());
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => 1, 'permission_id' => $newId]);
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => 2, 'permission_id' => $newId]);
    }

    #[Test]
    public function migration_does_nothing_when_the_old_permission_is_absent(): void
    {
        $newId = $this->insertPermission(self::NEW_NAME);

        $this->permissionMigration()->up();

        $this->assertDatabaseHas('permissions', ['id' => $newId, 'name' => self::NEW_NAME]);
        $this->assertSame(1, DB::table('permissions')->count());
    }

    #[Test]
    public function migration_down_restores_the_old_permission_name(): void
    {
        $id = $this->insertPermission(self::NEW_NAME);

        $this->permissionMigration()->down();

        $this->assertDatabaseHas('permissions', ['id' => $id, 'name' => self::OLD_NAME]);
    }

    #[Test]
    public function job_bill_migration_creates_the_permission_once(): void
    {
        $migration = $this->jobBillPermissionMigration();

        $migration->up();
        $migration->up();

        $this->assertSame(1, DB::table('permissions')->where('name', 'cnf.report.job-bill')->count());
        $this->assertDatabaseHas('permissions', [
            'name'          => 'cnf.report.job-bill',
            'company_id'    => 1,
            'module'        => 'Reports',
            'guard_name'    => 'web',
            'sorting_order' => 142,
        ]);
    }

    #[Test]
    public function job_bill_migration_keeps_an_already_seeded_permission(): void
    {
        $id = DB::table('permissions')->insertGetId([
            'name'          => 'cnf.report.job-bill',
            'company_id'    => 1,
            'sorting_order' => 142,
        ]);

        $this->jobBillPermissionMigration()->up();

        $this->assertSame(1, DB::table('permissions')->where('name', 'cnf.report.job-bill')->count());
        $this->assertDatabaseHas('permissions', ['id' => $id, 'name' => 'cnf.report.job-bill']);
    }

    #[Test]
    public function job_bill_migration_down_removes_the_permission(): void
    {
        $migration = $this->jobBillPermissionMigration();

        $migration->up();
        $migration->down();

        $this->assertDatabaseMissing('permissions', ['name' => 'cnf.report.job-bill']);
    }

    #[Test]
    public function request_is_authorized_only_by_the_job_expense_permission(): void
    {
        $this->assertTrue($this->authorizeWith(self::NEW_NAME));
        $this->assertFalse($this->authorizeWith(self::OLD_NAME));
        $this->assertFalse($this->authorizeWith('cnf.report.job-expense-summary'));
    }

    #[Test]
    public function job_bill_request_is_authorized_only_by_the_job_bill_permission(): void
    {
        $authorize = function (string $granted): bool {
            $user = new class($granted)
            {
                public function __construct(private string $granted) {}

                public function hasPermission(string $permission): bool
                {
                    return $permission === $this->granted;
                }
            };

            $request = JobBillRequest::create('/chevron/reports/job-bill', 'GET');
            $request->setUserResolver(fn () => $user);

            return $request->authorize();
        };

        $this->assertTrue($authorize('cnf.report.job-bill'));
        $this->assertFalse($authorize(self::NEW_NAME));
        $this->assertFalse($authorize('cnf.report.job-expense-summary'));
    }

    #[Test]
    public function job_bill_report_routes_exist(): void
    {
        $this->assertTrue(Route::has('chevron.reports.job-bill'));
        $this->assertTrue(Route::has('chevron.reports.job-bill.print'));
        $this->assertTrue(Route::has('chevron.reports.job-bill.search-jobs'));
    }

    #[Test]
    public function only_the_job_expense_report_routes_exist(): void
    {
        $this->assertTrue(Route::has('chevron.reports.job-expense'));
        $this->assertTrue(Route::has('chevron.reports.job-expense.print'));
        $this->assertTrue(Route::has('chevron.reports.job-expense.search-jobs'));
        $this->assertFalse(Route::has('chevron.reports.job-expense-vs-bill'));
        $this->assertFalse(Route::has('chevron.reports.job-expense-vs-bill.print'));
        $this->assertFalse(Route::has('chevron.reports.job-expense-vs-bill.search-jobs'));
    }

    private function authorizeWith(string $grantedPermission): bool
    {
        $user = new class($grantedPermission)
        {
            public function __construct(private string $granted) {}

            public function hasPermission(string $permission): bool
            {
                return $permission === $this->granted;
            }
        };

        $request = JobExpenseRequest::create('/chevron/reports/job-expense', 'GET');
        $request->setUserResolver(fn () => $user);

        return $request->authorize();
    }

    private function insertPermission(string $name): int
    {
        return DB::table('permissions')->insertGetId([
            'name'          => $name,
            'company_id'    => 1,
            'sorting_order' => 141,
        ]);
    }

    private function jobBillPermissionMigration(): object
    {
        return require glob(database_path('migrations/*_add_job_bill_report_permission.php'))[0];
    }

    private function permissionMigration(): object
    {
        return require glob(database_path('migrations/*_rename_job_expense_vs_bill_report_permission.php'))[0];
    }
}

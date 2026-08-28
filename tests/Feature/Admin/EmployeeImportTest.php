<?php

namespace Tests\Feature\Admin;

use App\Models\Chevron\ChevronBranch;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBranchAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class EmployeeImportTest extends TestCase
{
    use RefreshDatabase;

    protected function makeXlsx(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_merge([['Company', 'Emp. ID', 'Name', 'Designation', 'J. Date']], $rows), null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'emp').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'employees.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_preview_returns_parsed_employees(): void
    {
        Employee::create(['name' => 'Existing One', 'current_status' => 'Active']);

        $file = $this->makeXlsx([
            ['Chevron', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
        ]);

        $this->postJson('/api/admin/employees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('preview.0.name', 'Mr. Test User')
            ->assertJsonPath('preview.0.code', 'CL001')
            ->assertJsonPath('preview.0.designation', 'Accountant')
            ->assertJsonPath('preview.0.joining_date', '2025-12-01')
            ->assertJsonPath('preview.0.exists', false);
    }

    public function test_preview_marks_existing_employee(): void
    {
        Employee::create(['name' => 'Mr. Test User', 'code' => 'CL001', 'current_status' => 'Active']);

        $file = $this->makeXlsx([
            ['Chevron', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
        ]);

        $this->postJson('/api/admin/employees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('preview.0.exists', true);
    }

    public function test_import_wipes_previous_and_inserts_employees_and_designations(): void
    {
        Employee::create(['name' => 'Old Employee', 'current_status' => 'Active']);
        Designation::create(['name' => 'Old Designation']);

        $this->seedCompanyAndBranches();

        $file = $this->makeXlsx([
            ['Chevron Lines (C&F) Ltd.', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
            ['Chevron Lines (C&F) Ltd.', 'CL002', 'Mrs. Second User', 'Accountant', '1/Mar/2026'],
            ['Chevron Lines (C&F) Ltd.', 'CL003', 'Mr. Third User', 'Manager', '1/Jan/2026'],
        ]);

        $this->postJson('/api/admin/employees/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('inserted.employees', 3)
            ->assertJsonPath('inserted.designations', 2)
            ->assertJsonPath('inserted.branch_access', 6);

        $this->assertDatabaseCount('employees', 3);
        $this->assertDatabaseCount('designations', 2);
        $this->assertDatabaseCount('employee_branch_access', 6);

        $this->assertFalse(Employee::where('name', 'Old Employee')->exists());
        $this->assertFalse(Designation::where('name', 'Old Designation')->exists());
        $this->assertSame(1, Employee::min('id'), 'TRUNCATE should reset auto-increment IDs');

        $accountant = Designation::where('name', 'Accountant')->first();
        $this->assertNotNull($accountant);
        $this->assertDatabaseHas('employees', [
            'name'           => 'Mr. Test User',
            'code'           => 'CL001',
            'designation_id' => $accountant->id,
            'joining_date'   => '2025-12-01',
        ]);

        $employee = Employee::where('code', 'CL001')->first();
        $this->assertSame(2, EmployeeBranchAccess::where('employee_id', $employee->id)->count());
        $this->assertSame(1, EmployeeBranchAccess::where('employee_id', $employee->id)->where('company_id', 1)->count());
    }

    public function test_preview_reports_company_branch_access(): void
    {
        $this->seedCompanyAndBranches();

        $file = $this->makeXlsx([
            ['Chevron Lines (C&F) Ltd.', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
        ]);

        $this->postJson('/api/admin/employees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('preview.0.company_id', 1)
            ->assertJsonCount(2, 'preview.0.branch_ids')
            ->assertJsonPath('companies.0.name', 'Chevron Lines (C&F) Ltd.');
    }

    public function test_preview_reports_unmatched_company(): void
    {
        $file = $this->makeXlsx([
            ['Unknown Company', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
        ]);

        $this->postJson('/api/admin/employees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('preview.0.company_id', null)
            ->assertJsonCount(0, 'preview.0.branch_ids');
    }

    public function test_import_nulls_linked_users_employee_id(): void
    {
        $oldEmp = Employee::create(['name' => 'Old Linked', 'current_status' => 'Active']);
        User::create([
            'name'        => 'Himal',
            'username'    => 'himal',
            'password'    => bcrypt('secret'),
            'employee_id' => $oldEmp->id,
            'is_active'   => true,
        ]);

        $this->seedCompanyAndBranches();

        $file = $this->makeXlsx([
            ['Chevron Lines (C&F) Ltd.', 'CL001', 'Mr. Test User', 'Accountant', '1/Dec/2025'],
        ]);

        $this->postJson('/api/admin/employees/import', ['file' => $file])
            ->assertOk();

        $this->assertNull(User::where('username', 'himal')->first()->employee_id);
    }

    protected function seedCompanyAndBranches(): void
    {
        Company::create(['id' => 1, 'name' => 'Chevron Lines (C&F) Ltd.', 'slug' => 'chevron-lines', 'type' => 'cnf', 'is_active' => true]);
        ChevronBranch::create(['id' => 5, 'name' => 'Head Office', 'code' => 'DK', 'is_active' => true]);
        ChevronBranch::create(['id' => 6, 'name' => 'Chittagong', 'code' => 'CTG', 'is_active' => true]);
    }

    public function test_import_requires_file(): void
    {
        $this->postJson('/api/admin/employees/import')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }
}

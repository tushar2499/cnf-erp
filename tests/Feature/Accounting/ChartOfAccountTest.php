<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\AccAccount;
use App\Models\User;
use App\Services\Accounting\ChartOfAccountImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * The full migration chain is MySQL-only, so only the tables these tests touch are built here.
 */
class ChartOfAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        (require database_path('migrations/2026_11_16_115343_create_acc_accounts_table.php'))->up();
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: string}>  $rows
     */
    private function makeWorkbook(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Notes');
        $sheet->setCellValue('A1', 'This sheet has no chart of accounts');

        $coa = $spreadsheet->createSheet();
        $coa->setTitle('All_CHEV_COA');
        $coa->fromArray(array_merge(
            [['Index_SL', 'Code_Master', 'Code2_Sub-1', 'Code3_Sub-2', 'Code4_Ledger', 'Type']],
            array_map(fn ($r) => array_merge([''], $r), $rows),
        ), null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'coa').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function sampleWorkbook(): string
    {
        return $this->makeWorkbook([
            ['03_Equity Capital', '_', '_', '_', 'Cr'],                                                                    // row 2: master only
            ['01_Asset', '0102_Current Assets', '010201_Cash at Office', '0102010001_Cash In Hand at Head Office', 'Dr'],   // row 3
            ['01_Asset', '0102_Current Assets', '010202_Cash At Bank', '0102020001_1111-001336-301_MBL Tejgaon', 'Dr'],     // row 4
            ['01_Asset', '0102_Current Assets', '010204_Accounts Receivable', '0102040001_ABC Garments Ltd', 'Dr'],         // row 5
            ['01_Asset', '0102_Current Assets', '010204_Accounts Receivable', '0102040002_XYZ Textiles', 'Dr'],             // row 6
            ['02_Liability', '0201_Current Liabilities', '020101_Accounts Payable', '0201010001_Linkers Travels', 'Cr'],    // row 7
            ['02_Liability', '0201_Current Liabilities', '020104_Income Tax Payable', '0201040001_Income Tax Payable', 'Cr'], // row 8
            ['02_Liability', '0201_Current Liabilities', '020104_Interest Payable', '0201040001_Interest Payable', 'Cr'],   // row 9: dup code + 2nd name
            ['02_Liability', '0201_Current Liabilities', '020102_Bank Current Liabilities', '020102001_SJIBPLC TR', 'Cr'],  // row 10: 9 digits
            ['02_Liability', '0201_Current Liabilities', '020102_Bank Current Liabilities', 'Acceptance Loan For Ab Bank', 'Cr'], // row 11: no code
            ['03_Equity Capital', '0303_Owners Equity', '030301_Directors Equity', '0303020004_A.H.M. Abdullah', 'Cr'],     // row 12: wrong parent
            ['05_Expense', '0502_Indirect Expenses', '050203_Administrative Expenses', '0502030020_0502020026News Papers Expense', 'Dr'], // row 13: junk prefix
            ['06_Asset', '0601_Contra Assets', '060101_Contra Assets', '0601010001_MBL Tejgaon', 'Dr'],                      // row 14: skipped master
        ]);
    }

    public function test_parse_builds_hierarchy_and_reports_issues(): void
    {
        $result = app(ChartOfAccountImporter::class)->parse($this->sampleWorkbook());
        $accounts = collect($result['accounts'])->keyBy('code');

        $this->assertSame('asset', $accounts['01']['type']);
        $this->assertTrue($accounts['0102']['is_group']);
        $this->assertSame('0102', $accounts['010201']['parent_code']);

        $cash = $accounts['0102010001'];
        $this->assertFalse($cash['is_group']);
        $this->assertTrue($cash['is_cash_bank']);
        $this->assertSame('dr', $cash['nature']);
        $this->assertTrue($accounts['0102020001']['is_cash_bank']);

        $this->assertSame('News Papers Expense', $accounts['0502030020']['name']);
        $this->assertSame('cr', $accounts['03']['nature']);

        $this->assertFalse($accounts->has('06'));
        $this->assertFalse($accounts->has('0102040001'));
        $this->assertFalse($accounts->has('0303020004'));
        $this->assertFalse($accounts->has('020102001'));

        $this->assertSame(4, $result['stats']['errors']);
        $this->assertSame(1, $result['stats']['warnings']);
        $this->assertSame(3, $result['stats']['party_ledgers_collapsed']);
        $this->assertSame(1, $result['stats']['skipped_master_rows']);

        $messages = collect($result['issues'])->pluck('message', 'row');
        $this->assertStringContainsString('Duplicate ledger code 0201040001', $messages[9]);
        $this->assertStringContainsString('must be 10 digits', $messages[10]);
        $this->assertStringContainsString('has no code', $messages[11]);
        $this->assertStringContainsString('does not belong to parent 030301', $messages[12]);
    }

    public function test_party_ledgers_are_collapsed_into_control_accounts(): void
    {
        $accounts = collect(app(ChartOfAccountImporter::class)->parse($this->sampleWorkbook())['accounts'])->keyBy('code');

        $receivable = $accounts['0102040000'];
        $this->assertSame('Accounts Receivable', $receivable['name']);
        $this->assertSame('customer', $receivable['party_type']);
        $this->assertSame('010204', $receivable['parent_code']);
        $this->assertFalse($receivable['is_group']);

        $this->assertSame('supplier', $accounts['0201010000']['party_type']);
    }

    public function test_import_persists_and_reimport_updates_without_duplicates(): void
    {
        $importer = app(ChartOfAccountImporter::class);
        $path = $this->sampleWorkbook();

        $first = $importer->import($path);
        $this->assertSame(0, $first['updated']);
        $total = AccAccount::count();
        $this->assertSame($first['created'], $total);

        $ledger = AccAccount::where('code', '0102010001')->first();
        $this->assertSame(AccAccount::where('code', '010201')->value('id'), $ledger->parent_id);
        $this->assertSame(4, $ledger->level);

        AccAccount::where('code', '0102010001')->update(['name' => 'Old name']);
        $second = $importer->import($path);

        $this->assertSame(0, $second['created']);
        $this->assertSame($total, $second['updated']);
        $this->assertSame($total, AccAccount::count());
        $this->assertSame('Cash In Hand at Head Office', AccAccount::where('code', '0102010001')->value('name'));
    }

    public function test_file_without_coa_headers_is_rejected(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['Name', 'Amount']]);
        $path = tempnam(sys_get_temp_dir(), 'coa').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $this->actingAs($this->superUser())
            ->post(route('admin.accounting.accounts.import.preview'), [
                'file' => new UploadedFile($path, 'other.xlsx', null, null, true),
            ], ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No sheet with headers Code_Master, Code2_Sub-1, Code3_Sub-2, Code4_Ledger, Type was found.');
    }

    public function test_import_endpoint_imports_workbook(): void
    {
        $this->actingAs($this->superUser())
            ->post(route('admin.accounting.accounts.import'), [
                'file' => new UploadedFile($this->sampleWorkbook(), 'coa.xlsx', null, null, true),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('updated', 0);

        $this->assertDatabaseHas('acc_accounts', ['code' => '0102040000', 'party_type' => 'customer']);
    }

    public function test_store_child_inherits_type_and_validates_code(): void
    {
        $user = $this->superUser();
        $group = AccAccount::create(['code' => '01', 'name' => 'Asset', 'level' => 1, 'type' => 'asset', 'nature' => 'dr', 'is_group' => true]);

        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [
            'parent_id' => $group->id, 'code' => '0201', 'name' => 'Wrong prefix', 'nature' => 'dr',
        ])->assertStatus(422)->assertJsonValidationErrors(['code']);

        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [
            'parent_id' => $group->id, 'code' => '010', 'name' => 'Too short', 'nature' => 'dr',
        ])->assertStatus(422)->assertJsonValidationErrors(['code']);

        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [
            'parent_id' => $group->id, 'code' => '0102', 'name' => 'Current Assets', 'nature' => 'dr',
        ])->assertOk();

        $child = AccAccount::where('code', '0102')->first();
        $this->assertSame('asset', $child->type);
        $this->assertSame(2, $child->level);
        $this->assertTrue($child->is_group);
        $this->assertSame($user->id, $child->created_by);
    }

    public function test_master_requires_type_and_ledger_cannot_have_children(): void
    {
        $user = $this->superUser();

        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [
            'code' => '01', 'name' => 'Asset', 'nature' => 'dr',
        ])->assertStatus(422)->assertJsonValidationErrors(['type']);

        $ledger = AccAccount::create(['code' => '0102010001', 'name' => 'Cash', 'level' => 4, 'type' => 'asset', 'nature' => 'dr', 'is_group' => false]);

        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [
            'parent_id' => $ledger->id, 'code' => '010201000101', 'name' => 'Child', 'nature' => 'dr',
        ])->assertStatus(422)->assertJsonValidationErrors(['parent_id']);
    }

    public function test_next_code_suggests_following_ledger_code(): void
    {
        $group = AccAccount::create(['code' => '010202', 'name' => 'Cash At Bank', 'level' => 3, 'type' => 'asset', 'nature' => 'dr', 'is_group' => true]);
        AccAccount::create(['parent_id' => $group->id, 'code' => '0102020014', 'name' => 'Bank 14', 'level' => 4, 'type' => 'asset', 'nature' => 'dr']);

        $this->actingAs($this->superUser())
            ->getJson(route('admin.accounting.accounts.next-code', ['parent_id' => $group->id]))
            ->assertOk()
            ->assertJson(['code' => '0102020015', 'level' => 4, 'is_group' => false, 'type' => 'asset']);
    }

    public function test_update_keeps_code_and_destroy_blocks_groups_with_children(): void
    {
        $user = $this->superUser();
        $group = AccAccount::create(['code' => '010202', 'name' => 'Cash At Bank', 'level' => 3, 'type' => 'asset', 'nature' => 'dr', 'is_group' => true]);
        $ledger = AccAccount::create(['parent_id' => $group->id, 'code' => '0102020001', 'name' => 'Bank', 'level' => 4, 'type' => 'asset', 'nature' => 'dr']);

        $this->actingAs($user)->putJson(route('admin.accounting.accounts.update', $ledger), [
            'code' => '9999999999', 'name' => 'MBL Tejgaon', 'nature' => 'dr', 'is_cash_bank' => 1, 'is_active' => 1,
        ])->assertOk();

        $ledger->refresh();
        $this->assertSame('0102020001', $ledger->code);
        $this->assertSame('MBL Tejgaon', $ledger->name);
        $this->assertTrue($ledger->is_cash_bank);

        $this->actingAs($user)->deleteJson(route('admin.accounting.accounts.destroy', $group))
            ->assertStatus(422);
        $this->actingAs($user)->deleteJson(route('admin.accounting.accounts.destroy', $ledger))->assertOk();
        $this->assertDatabaseMissing('acc_accounts', ['id' => $ledger->id]);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('admin.accounting.accounts.index'))->assertForbidden();
        $this->actingAs($user)->postJson(route('admin.accounting.accounts.store'), [])->assertForbidden();
    }

    private function superUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_super' => true])->save();

        return $user;
    }
}

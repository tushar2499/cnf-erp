<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasFreights\NasFreightsExpenseHead;
use App\Models\NasFreights\NasFreightsFreightBookingExpenseItem;
use App\Models\NasFreights\NasFreightsFreightExportBooking;
use App\Models\NasFreights\NasFreightsFreightExportBookingExpense;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FreightExportBookingExpenseImportTest extends TestCase
{
    private const AMRAN = 'NFFFDK07_Mr. Mohammed Amran Hossain_Sr. Customer Relation Officer_Freight Forwarding';

    private const SAIDUR = 'NFFFDK05_Mr. Md. Saidur Rahman_Assistant Manager_Freight Forwarding';

    private const HEADER = ['Booking No', 'Date', 'Employee', 'Expense Head', 'Receiptable', 'Amount', 'Completion', 'Note'];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('nas_freights_freight_export_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('export_booking_no')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->date('booking_date');
            $table->string('service_type');
            $table->string('invoice_no')->nullable();
            $table->string('export_bl_no')->nullable();
            $table->timestamps();
        });
        Schema::create('nas_freights_freight_booking_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->string('booking_type')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('booking_no')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value_usd', 15, 2)->nullable();
            $table->string('bl_no')->nullable();
            $table->decimal('total_expense_amount', 15, 2)->default(0);
            $table->decimal('total_approved_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('branch_id');
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->string('status')->default('Draft');
            $table->unsignedBigInteger('entry_by')->nullable();
            $table->timestamps();
        });
        Schema::create('nas_freights_freight_booking_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freight_booking_expense_id');
            $table->unsignedBigInteger('expense_head_id');
            $table->string('receiptable')->default('No');
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->default(0);
            $table->date('expense_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('nas_freights_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
        Schema::create('nas_freights_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('status')->default('Active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('nas_freights_designations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('nas_freights_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        $this->booking('NASEXP26038', ['invoice_no' => 'INV-38', 'export_bl_no' => 'BL-38']);
        $this->booking('NASEXP26059');
        $this->booking('NASEXP26017_PJ');
        $this->booking('NAS EXP26018');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function booking(string $no, array $attributes = [], int $branchId = 1): NasFreightsFreightExportBooking
    {
        return NasFreightsFreightExportBooking::create($attributes + [
            'export_booking_no' => $no,
            'branch_id'         => $branchId,
            'booking_date'      => '2026-08-01',
            'service_type'      => 'Air',
        ]);
    }

    private function serial(string $date): float
    {
        return Date::dateTimeToExcel(new \DateTime($date));
    }

    /**
     * Row of the header-less accounts workbook: SL, Booking No, Date, Employee, Category, Expense Head, Receiptable, Amount, Completion.
     *
     * @return array<int, mixed>
     */
    private function row(string $booking, string $date, ?string $employee, ?string $head, float|int|string $amount, string $receiptable = 'YES', ?string $completion = 'DONE'): array
    {
        return [1, $booking, $this->serial($date), $employee, null, $head, $receiptable, $amount, $completion];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function headerlessWorkbook(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->setTitle('Sheet1')->fromArray($rows, null, 'A1');

        return $this->save($spreadsheet);
    }

    /**
     * Title in row 1, header in row 3, data from row 4.
     *
     * @param  array<int, string>  $header
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function headedWorkbook(array $rows, array $header = self::HEADER): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Export Job Expenses');
        $sheet->fromArray($header, null, 'A3');
        $sheet->fromArray($rows, null, 'A4');

        return $this->save($spreadsheet);
    }

    private function save(Spreadsheet $spreadsheet): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'fee').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'expenses.xlsx', null, null, true);
    }

    private function callImport(string $uri, array $data)
    {
        return $this->postJson('/api/nas-freights/freight-export-booking-expenses/'.$uri, $data);
    }

    public function test_preview_reads_headerless_workbook_and_writes_nothing(): void
    {
        $response = $this->callImport('import/preview', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NF_EXDHK-2026-800038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 1000),
                $this->row('NF_EXDHK-2026-800038', '2026-08-14', self::AMRAN, 'PO COMMISSION', '2,500.50', 'NO', 'PENDING'),
                [3, null, null, null, null, null, null, null, null],
                $this->row('NF_EXDHK-2026-800099', '2026-08-15', self::AMRAN, 'KB/C', 300),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.rows', 3)
            ->assertJsonPath('summary.bookings', 2)
            ->assertJsonPath('summary.new', 1)
            ->assertJsonPath('summary.not_found', 1)
            ->assertJsonPath('summary.importable_lines', 2)
            ->assertJsonPath('summary.new_heads', 2)
            ->assertJsonPath('summary.new_employees', 1)
            ->assertJsonPath('preview.0.status', 'new')
            ->assertJsonPath('preview.0.matched_booking_no', 'NASEXP26038')
            ->assertJsonPath('preview.0.matched_by', 'converted')
            ->assertJsonPath('preview.0.date', '2026-08-12')
            ->assertJsonPath('preview.0.employee_action', 'create')
            ->assertJsonPath('preview.0.lines.1.receiptable', 'No')
            ->assertJsonPath('preview.1.status', 'not_found')
            ->assertJsonPath('preview.1.reason', 'Booking not found.');

        $this->assertEquals(3500.5, $response->json('summary.importable_amount'));
        $this->assertEquals(3500.5, $response->json('preview.0.total_expense_amount'));
        $this->assertEquals(1000, $response->json('preview.0.total_approved_amount'));
        $this->assertEquals(2500.5, $response->json('preview.0.lines.1.expense_amount'));
        $this->assertEquals(0, $response->json('preview.0.lines.1.approved_amount'));

        $this->assertSame(0, NasFreightsFreightExportBookingExpense::count());
        $this->assertSame(0, NasFreightsExpenseHead::count());
        $this->assertSame(0, NasFreightsEmployee::count());
    }

    public function test_preview_reads_workbook_with_header_row_in_any_column_order(): void
    {
        $response = $this->callImport('import/preview', [
            'file'      => $this->headedWorkbook(
                [[500, 'Trucking Dry', 'NASEXP26038', '2026-08-12', 450, 'Yes', 'by truck']],
                ['Amount', 'Expense Head', 'Booking No', 'Date', 'Approved Amount', 'Receiptable', 'Remarks']
            ),
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.new', 1)
            ->assertJsonPath('preview.0.matched_by', 'exact')
            ->assertJsonPath('preview.0.lines.0.row', 4)
            ->assertJsonPath('preview.0.lines.0.expense_head', 'Trucking Dry')
            ->assertJsonPath('preview.0.lines.0.date', '2026-08-12')
            ->assertJsonPath('preview.0.lines.0.receiptable', 'Yes')
            ->assertJsonPath('preview.0.lines.0.note', 'by truck');

        $this->assertEquals(500, $response->json('preview.0.lines.0.expense_amount'));
        $this->assertEquals(450, $response->json('preview.0.lines.0.approved_amount'));
    }

    public function test_header_row_missing_a_required_column_is_reported(): void
    {
        $this->callImport('import/preview', [
            'file'      => $this->headedWorkbook([['NASEXP26038', '2026-08-12', 500]], ['Booking No', 'Date', 'Amount']),
        ])->assertStatus(422)->assertJsonPath('message', 'Could not read the file: Header row 3 is missing column(s): Expense Head.');
    }

    public function test_import_creates_one_expense_per_booking_with_items_and_totals(): void
    {
        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NF_EXDHK-2026-800038', '2026-08-14', self::AMRAN, 'Sea Freight Charge', 1000),
                $this->row('NF_EXDHK-2026-800038', '2026-08-12', self::AMRAN, 'PO COMMISSION', 58, 'NO', 'PENDING'),
                $this->row('NF_EXDHK-2026-800059', '2026-08-20', self::AMRAN, 'KB/C', '2,19,480.00', 'NO', 'PENDING'),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('inserted', 2)
            ->assertJsonPath('inserted_items', 3)
            ->assertJsonPath('new_heads', 3)
            ->assertJsonPath('new_employees', 1);
        $this->assertEquals(220538, $response->json('total_expense_amount'));

        $expense = NasFreightsFreightExportBookingExpense::with('items.expenseHead')->where('booking_no', 'NASEXP26038')->firstOrFail();
        $this->assertSame('FBE000001', $expense->expense_no);
        $this->assertSame('export', $expense->booking_type);
        $this->assertSame('INV-38', $expense->invoice_no);
        $this->assertSame('BL-38', $expense->bl_no);
        $this->assertSame(1, $expense->branch_id);
        $this->assertSame('2026-08-12', $expense->date->toDateString());
        $this->assertEquals(1058, $expense->total_expense_amount);
        $this->assertEquals(1000, $expense->total_approved_amount);
        $this->assertSame('Approved', $expense->status);
        $this->assertSame('NFFFDK07', $expense->employee->code);
        $this->assertCount(2, $expense->items);

        $first = $expense->items->first();
        $this->assertSame('Sea Freight Charge', $first->expenseHead->name);
        $this->assertSame('Yes', $first->receiptable);
        $this->assertSame('2026-08-14', $first->expense_date->toDateString());
        $this->assertEquals(1000, $first->approved_amount);
        $this->assertSame('No', $expense->items->last()->receiptable);
        $this->assertEquals(0, $expense->items->last()->approved_amount);

        $second = NasFreightsFreightExportBookingExpense::where('booking_no', 'NASEXP26059')->firstOrFail();
        $this->assertSame('FBE000002', $second->expense_no);
        $this->assertSame('Draft', $second->status);
        $this->assertEquals(219480, $second->total_expense_amount);
    }

    public function test_only_existing_bookings_get_expenses(): void
    {
        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 1000),
                $this->row('NF_EXDHK-2026-800111', '2026-08-12', 'GHO01_Mr. Ghost_Clerk_Ops', 'GHOST HEAD', 5),
                $this->row('UNKNOWN-1', '2026-08-12', 'GHO01_Mr. Ghost_Clerk_Ops', 'GHOST HEAD', 5),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonCount(2, 'skipped.not_found')
            ->assertJsonPath('skipped.not_found.0.booking_no', 'NF_EXDHK-2026-800111')
            ->assertJsonPath('skipped.not_found.0.rows', [2])
            ->assertJsonPath('skipped.not_found.1.booking_no', 'UNKNOWN-1');

        $this->assertSame(1, NasFreightsFreightExportBookingExpense::count());
        $this->assertFalse(NasFreightsExpenseHead::where('name', 'GHOST HEAD')->exists());
        $this->assertFalse(NasFreightsEmployee::where('code', 'GHO01')->exists());
    }

    public function test_nf_exdhk_numbers_convert_keep_their_suffix_and_ignore_spaces(): void
    {
        $response = $this->callImport('import/preview', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NF_EXDHK-2026-800017_PJ', '2026-08-12', null, 'Sea Freight Charge', 10),
                $this->row('NF_EXDHK-2026-800018', '2026-08-12', null, 'Sea Freight Charge', 10),
                $this->row('NF_EXDHK-2026-80036_PJ', '2026-08-12', null, 'Sea Freight Charge', 10),
                $this->row('NASEXP26059', '2026-08-12', null, 'Sea Freight Charge', 10),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.new', 3)
            ->assertJsonPath('summary.not_found', 1)
            ->assertJsonPath('preview.0.matched_booking_no', 'NASEXP26017_PJ')
            ->assertJsonPath('preview.1.matched_booking_no', 'NAS EXP26018')
            ->assertJsonPath('preview.2.status', 'not_found')
            ->assertJsonPath('preview.3.matched_by', 'exact');
    }

    public function test_lines_of_one_booking_written_with_both_numberings_land_in_one_expense(): void
    {
        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', null, 'Sea Freight Charge', 10),
                $this->row('NF_EXDHK-2026-800038', '2026-08-13', null, 'KB/C', 20),
            ]),
        ]);

        $response->assertOk()->assertJsonPath('inserted', 1)->assertJsonPath('inserted_items', 2);
        $this->assertSame(1, NasFreightsFreightExportBookingExpense::count());
    }

    public function test_same_file_uploaded_twice_never_creates_duplicate_expenses(): void
    {
        $file = fn () => $this->headerlessWorkbook([
            $this->row('NF_EXDHK-2026-800038', '2026-08-14', self::AMRAN, 'Sea Freight Charge', 1000),
            $this->row('NF_EXDHK-2026-800038', '2026-08-12', self::AMRAN, 'PO COMMISSION', 58),
            $this->row('NF_EXDHK-2026-800059', '2026-08-20', self::AMRAN, 'KB/C', 300),
        ]);

        $this->callImport('import', ['file' => $file()])->assertOk()->assertJsonPath('inserted', 2);

        $second = $this->callImport('import', ['file' => $file()]);
        $second->assertOk()
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('message', 'No new export booking expenses to import.')
            ->assertJsonCount(2, 'skipped.existing')
            ->assertJsonPath('skipped.existing.0.expense_no', 'FBE000001')
            ->assertJsonPath('summary.existing', 2);

        $this->assertSame(2, NasFreightsFreightExportBookingExpense::count());
        $this->assertSame(3, NasFreightsFreightBookingExpenseItem::count());
        $this->assertSame(3, NasFreightsExpenseHead::count());
        $this->assertSame(1, NasFreightsEmployee::count());
    }

    public function test_booking_that_already_has_an_expense_is_left_untouched(): void
    {
        $head = NasFreightsExpenseHead::create(['name' => 'Port Handling', 'status' => 'Active']);
        $expense = NasFreightsFreightExportBookingExpense::create([
            'expense_no'           => 'FBE000001',
            'booking_id'           => NasFreightsFreightExportBooking::where('export_booking_no', 'NASEXP26038')->value('id'),
            'booking_no'           => 'NASEXP26038',
            'branch_id'            => 1,
            'date'                 => '2026-08-01',
            'total_expense_amount' => 100,
        ]);
        $expense->items()->create(['expense_head_id' => $head->id, 'expense_amount' => 100, 'expense_date' => '2026-08-01']);

        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([$this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 1000)]),
        ]);

        $response->assertOk()
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('skipped.existing.0.booking_no', 'NASEXP26038')
            ->assertJsonPath('skipped.existing.0.expense_no', 'FBE000001')
            ->assertJsonPath('skipped.existing.0.reason', 'Booking already has expense FBE000001.');

        $this->assertSame(1, NasFreightsFreightExportBookingExpense::count());
        $this->assertSame(1, $expense->items()->count());
        $this->assertEquals(100, $expense->fresh()->total_expense_amount);
        $this->assertFalse(NasFreightsEmployee::where('code', 'NFFFDK07')->exists());
    }

    public function test_identical_lines_in_the_file_are_imported_once(): void
    {
        $line = ['NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 'YES', 1000, 'DONE'];
        $rows = [
            [...$line, 'x'],
            [...$line, 'x'],
            [...$line, 'y'],
            ['NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 'YES', 1001, 'DONE', 'x'],
        ];

        $response = $this->callImport('import', ['file' => $this->headedWorkbook($rows)]);

        $response->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('inserted_items', 3)
            ->assertJsonPath('summary.duplicate_lines', 1)
            ->assertJsonPath('skipped.duplicate_lines.0.row', 5)
            ->assertJsonPath('skipped.duplicate_lines.0.duplicate_of', 4);

        $this->assertSame(3, NasFreightsFreightBookingExpenseItem::count());
        $this->assertEquals(3001, NasFreightsFreightExportBookingExpense::firstOrFail()->total_expense_amount);
    }

    public function test_booking_with_a_bad_row_is_skipped_whole_until_the_file_is_fixed(): void
    {
        $broken = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NF_EXDHK-2026-800038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 1000),
                $this->row('NF_EXDHK-2026-800038', '2026-08-13', self::AMRAN, null, 200),
                $this->row('NF_EXDHK-2026-800059', '2026-08-20', self::AMRAN, 'KB/C', 300),
            ]),
        ]);

        $broken->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('skipped.invalid.0.booking_no', 'NF_EXDHK-2026-800038')
            ->assertJsonPath('skipped.invalid.0.errors', ['Row 2: Expense Head is empty.']);
        $this->assertFalse(NasFreightsFreightExportBookingExpense::where('booking_no', 'NASEXP26038')->exists());

        $fixed = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NF_EXDHK-2026-800038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 1000),
                $this->row('NF_EXDHK-2026-800038', '2026-08-13', self::AMRAN, 'KB/C', 200),
                $this->row('NF_EXDHK-2026-800059', '2026-08-20', self::AMRAN, 'KB/C', 300),
            ]),
        ]);

        $fixed->assertOk()->assertJsonPath('inserted', 1)->assertJsonCount(1, 'skipped.existing');
        $this->assertSame(2, NasFreightsFreightExportBookingExpense::count());
        $this->assertSame(2, NasFreightsFreightExportBookingExpense::where('booking_no', 'NASEXP26038')->firstOrFail()->items()->count());
    }

    public function test_each_expense_takes_the_branch_of_its_booking(): void
    {
        $this->booking('NASEXP26200', [], 2);

        $response = $this->callImport('import', [
            'file' => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 10),
                $this->row('NASEXP26200', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 10),
            ]),
        ]);

        $response->assertOk()->assertJsonPath('inserted', 2);
        $this->assertSame(1, NasFreightsFreightExportBookingExpense::where('booking_no', 'NASEXP26038')->firstOrFail()->branch_id);
        $this->assertSame(2, NasFreightsFreightExportBookingExpense::where('booking_no', 'NASEXP26200')->firstOrFail()->branch_id);
    }

    public function test_masters_are_reused_or_created_only_for_imported_bookings(): void
    {
        NasFreightsExpenseHead::create(['name' => 'Sea Freight Charge', 'status' => 'Active']);
        $designationId = DB::table('nas_freights_designations')->insertGetId(['name' => 'Sr. Customer Relation Officer']);
        $departmentId = DB::table('nas_freights_departments')->insertGetId(['name' => 'Freight Forwarding']);

        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'SEA FREIGHT CHARGE', 1000),
                $this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'KB/C', 10),
                $this->row('NASEXP26059', '2026-08-12', self::AMRAN, 'sea freight charge', 20),
            ]),
        ]);

        $response->assertOk()->assertJsonPath('new_heads', 1)->assertJsonPath('new_employees', 1);
        $this->assertSame(2, NasFreightsExpenseHead::count());
        $this->assertSame('External', NasFreightsExpenseHead::where('name', 'KB/C')->firstOrFail()->type);

        $employee = NasFreightsEmployee::where('code', 'NFFFDK07')->firstOrFail();
        $this->assertSame('Mr. Mohammed Amran Hossain', $employee->name);
        $this->assertSame('Sr. Customer Relation Officer', $employee->designation);
        $this->assertSame($designationId, $employee->designation_id);
        $this->assertSame($departmentId, $employee->department_id);
        $this->assertSame(1, $employee->branch_id);
        $this->assertSame(1, NasFreightsEmployee::count());
    }

    public function test_employee_resolves_by_code_then_by_name(): void
    {
        $saidur = NasFreightsEmployee::create(['branch_id' => 1, 'code' => 'NFFFDK05', 'name' => 'Mr. Md. Saidur Rahman']);

        $response = $this->callImport('import/preview', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', self::SAIDUR, 'Sea Freight Charge', 10),
                $this->row('NASEXP26059', '2026-08-12', 'XYZ99_Mr. Md. Saidur Rahman_Manager_Ops', 'Sea Freight Charge', 10),
                $this->row('NASEXP26017_PJ', '2026-08-12', 'Saidur Rahman', 'Sea Freight Charge', 10),
                $this->row('NAS EXP26018', '2026-08-12', 'Unknown Person', 'Sea Freight Charge', 10),
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('preview.0.employee_id', $saidur->id)
            ->assertJsonPath('preview.1.employee_id', $saidur->id)
            ->assertJsonPath('preview.2.employee_id', $saidur->id)
            ->assertJsonPath('preview.3.employee_id', null)
            ->assertJsonPath('preview.3.employee_action', 'none')
            ->assertJsonPath('preview.3.warnings.0', "Employee 'Unknown Person' not found and has no code; left blank.")
            ->assertJsonPath('summary.new_employees', 0);
    }

    public function test_amounts_dates_and_flags_are_parsed_and_bad_rows_are_reported(): void
    {
        $response = $this->callImport('import/preview', [
            'file'      => $this->headedWorkbook([
                ['NASEXP26038', '12-Aug-2026', null, 'sea freight charge', 'yes', '2,19,480.00', 'done'],
                ['NASEXP26059', '2026-08-12', null, 'KB/C', 'no', '0', null],
                ['NASEXP26017_PJ', 'someday', null, 'KB/C', 'no', 10, null],
                [null, '2026-08-12', null, 'KB/C', 'no', 10, null],
            ]),
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.new', 1)
            ->assertJsonPath('summary.invalid', 3)
            ->assertJsonPath('preview.0.date', '2026-08-12')
            ->assertJsonPath('preview.0.lines.0.receiptable', 'Yes')
            ->assertJsonPath('preview.1.errors', ["Row 5: Amount '0' is not a positive number."])
            ->assertJsonPath('preview.2.errors', ["Row 6: Date 'someday' is not a valid date."])
            ->assertJsonPath('preview.3.booking_no', null)
            ->assertJsonPath('preview.3.errors', ['Row 7: Booking No is empty.']);

        $this->assertEquals(219480, $response->json('preview.0.lines.0.expense_amount'));
        $this->assertEquals(219480, $response->json('preview.0.lines.0.approved_amount'));
    }

    public function test_other_employees_on_a_booking_are_noted_on_their_lines(): void
    {
        $response = $this->callImport('import', [
            'file'      => $this->headerlessWorkbook([
                $this->row('NASEXP26038', '2026-08-12', self::AMRAN, 'Sea Freight Charge', 10),
                $this->row('NASEXP26038', '2026-08-13', self::SAIDUR, 'KB/C', 20),
            ]),
        ]);

        $response->assertOk()->assertJsonPath('inserted', 1)->assertJsonPath('new_employees', 1);

        $expense = NasFreightsFreightExportBookingExpense::with('items', 'employee')->firstOrFail();
        $this->assertSame('NFFFDK07', $expense->employee->code);
        $this->assertNull($expense->items->first()->note);
        $this->assertSame('Exp. by: Mr. Md. Saidur Rahman (NFFFDK05)', $expense->items->last()->note);
    }

    public function test_workbook_without_rows_imports_nothing(): void
    {
        $this->callImport('import', ['file' => $this->headerlessWorkbook([])])
            ->assertOk()
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('summary.rows', 0);
    }

    public function test_requests_are_validated(): void
    {
        $file = fn () => $this->headerlessWorkbook([]);

        $this->callImport('import/preview', [])->assertStatus(422)->assertJsonValidationErrors('file');
        $this->callImport('import', [])->assertStatus(422)->assertJsonValidationErrors('file');
        $this->callImport('import', ['file' => UploadedFile::fake()->create('expenses.txt', 1, 'text/plain')])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');

        $this->callImport('import/preview', ['file' => $file()])->assertOk();
        $this->callImport('import/preview', ['branch_id' => 99, 'file' => $file()])->assertOk();
    }
}

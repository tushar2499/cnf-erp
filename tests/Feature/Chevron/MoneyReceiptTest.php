<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\AdvanceBalanceController;
use App\Http\Controllers\Chevron\BillController;
use App\Http\Controllers\Chevron\CustomerController;
use App\Http\Controllers\Chevron\MoneyReceiptController;
use App\Http\Requests\Chevron\Bill\DestroyBillRequest;
use App\Http\Requests\Chevron\Bill\UpdateBillRequest;
use App\Http\Requests\Chevron\Customer\IndexCustomerRequest;
use App\Http\Requests\Chevron\MoneyReceipt\DestroyMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\IndexMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\PrintMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\ShowMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\StoreMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\UpdateMoneyReceiptRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronMoneyReceipt;
use App\Models\Chevron\ChevronMoneyReceiptAllocation;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MoneyReceiptTest extends TestCase
{
    private const BRANCH = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        session(['active_branch_id' => self::BRANCH]);
    }

    protected function tearDown(): void
    {
        foreach (['chevron_money_receipt_allocations', 'chevron_money_receipt_items', 'chevron_money_receipts', 'chevron_bill_items', 'chevron_bills', 'chevron_jobs', 'chevron_accounts', 'chevron_branches', 'chevron_customers', 'companies'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_bill_receipt_collects_up_to_the_balance_and_updates_the_received_amount(): void
    {
        $bill = $this->makeBill(1000);

        $this->storeReceipt($this->billPayload([[$bill->id, 1000]]));

        $receipt = ChevronMoneyReceipt::firstOrFail();
        $this->assertSame('Bill', $receipt->receipt_type);
        $this->assertSame('MR000001', $receipt->receipt_no);
        $this->assertSame(self::BRANCH, (int) $receipt->branch_id);
        $this->assertEquals(1000, $receipt->total_amount);
        $this->assertEquals(1000, $receipt->payable_amount);
        $this->assertEquals(1000, $bill->fresh()->received_amount);
        $this->assertEquals(0, $bill->fresh()->balance);
        $this->assertSame(1, $receipt->allocations()->where('bill_id', $bill->id)->count());
    }

    public function test_bill_receipt_cannot_collect_more_than_the_bill_balance(): void
    {
        $bill = $this->makeBill(1000);

        $errors = $this->storeExpectingErrors($this->billPayload([[$bill->id, 1000.01]], 1000.01));

        $this->assertArrayHasKey("allocations.{$bill->id}.amount", $errors);
        $this->assertSame(0, ChevronMoneyReceipt::count());
        $this->assertSame(0, ChevronMoneyReceiptAllocation::count());
        $this->assertEquals(0, $bill->fresh()->received_amount);
    }

    public function test_partial_receipts_reduce_the_balance_until_nothing_is_left(): void
    {
        $bill = $this->makeBill(1000);

        $this->storeReceipt($this->billPayload([[$bill->id, 400]]));
        $this->storeReceipt($this->billPayload([[$bill->id, 600]]));

        $this->assertEquals(1000, $bill->fresh()->received_amount);

        $errors = $this->storeExpectingErrors($this->billPayload([[$bill->id, 1]]));

        $this->assertArrayHasKey("allocations.{$bill->id}.amount", $errors);
        $this->assertSame(2, ChevronMoneyReceipt::count());
    }

    public function test_one_receipt_can_collect_against_several_bills(): void
    {
        $first = $this->makeBill(1000, 'BIL000001');
        $second = $this->makeBill(500, 'BIL000002');

        $this->storeReceipt($this->billPayload([[$first->id, 1000], [$second->id, 200]], 1200));

        $this->assertEquals(1000, $first->fresh()->received_amount);
        $this->assertEquals(200, $second->fresh()->received_amount);
        $this->assertEquals(300, $second->fresh()->balance);
        $this->assertEquals(1500, ChevronMoneyReceipt::firstOrFail()->payable_amount);
    }

    public function test_collected_total_must_equal_the_allocated_total(): void
    {
        $bill = $this->makeBill(1000);

        $errors = $this->storeExpectingErrors($this->billPayload([[$bill->id, 500]], 800));

        $this->assertArrayHasKey('allocations', $errors);
        $this->assertSame(0, ChevronMoneyReceipt::count());
    }

    public function test_bills_of_another_party_or_branch_are_rejected(): void
    {
        $otherParty = $this->makeBill(1000, 'BIL000001', ['party_name' => 'Someone Else']);
        $otherBranch = $this->makeBill(1000, 'BIL000002', ['branch_id' => 99]);

        $this->assertArrayHasKey("allocations.{$otherParty->id}.bill_id", $this->storeExpectingErrors($this->billPayload([[$otherParty->id, 100]], 100)));
        $this->assertArrayHasKey("allocations.{$otherBranch->id}.bill_id", $this->storeExpectingErrors($this->billPayload([[$otherBranch->id, 100]], 100)));
        $this->assertSame(0, ChevronMoneyReceipt::count());
    }

    public function test_bill_is_matched_to_the_party_through_its_job_customer(): void
    {
        $job = ChevronJob::create(['job_no' => 'JOB-001', 'customer_id' => 7, 'party_name' => 'Acme', 'branch_id' => self::BRANCH]);
        $bill = $this->makeBill(300, 'BIL000001', ['party_name' => 'Acme Ltd (old name)', 'job_id' => $job->id]);

        $this->storeReceipt($this->billPayload([[$bill->id, 300]], 300, ['party_id' => 7]));

        $this->assertEquals(300, $bill->fresh()->received_amount);
    }

    public function test_editing_a_receipt_does_not_count_its_own_payment_twice(): void
    {
        $bill = $this->makeBill(1000);
        $this->storeReceipt($this->billPayload([[$bill->id, 1000]]));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $this->updateReceipt($receipt, $this->billPayload([[$bill->id, 700]], 700));
        $this->assertEquals(700, $bill->fresh()->received_amount);

        $errors = $this->updateExpectingErrors($receipt->fresh(), $this->billPayload([[$bill->id, 1200]], 1200));
        $this->assertArrayHasKey("allocations.{$bill->id}.amount", $errors);
        $this->assertEquals(700, $bill->fresh()->received_amount);
        $this->assertEquals(700, $receipt->fresh()->total_amount);
    }

    public function test_changing_a_receipt_to_advance_releases_its_bills(): void
    {
        $bill = $this->makeBill(1000);
        $this->storeReceipt($this->billPayload([[$bill->id, 1000]]));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $this->updateReceipt($receipt, $this->advancePayload(1000));

        $this->assertEquals(0, $bill->fresh()->received_amount);
        $this->assertSame('Advance', $receipt->fresh()->receipt_type);
        $this->assertSame(1, $receipt->allocations()->count());
    }

    public function test_deleting_a_receipt_restores_the_bill_balance(): void
    {
        $bill = $this->makeBill(1000);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]], 400));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $response = (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), $receipt);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, ChevronMoneyReceipt::count());
        $this->assertEquals(0, $bill->fresh()->received_amount);
        $this->assertEquals(1000, $bill->fresh()->balance);
    }

    public function test_advance_receipt_accepts_any_amount_without_a_job(): void
    {
        $this->storeReceipt($this->advancePayload(987654.32));

        $receipt = ChevronMoneyReceipt::firstOrFail();
        $this->assertSame('Advance', $receipt->receipt_type);
        $this->assertEquals(987654.32, $receipt->total_amount);
        $this->assertEquals(0, $receipt->payable_amount);

        $allocation = $receipt->allocations()->firstOrFail();
        $this->assertNull($allocation->bill_id);
        $this->assertNull($allocation->job_id);
        $this->assertEquals(987654.32, $allocation->amount);
    }

    public function test_advance_receipt_can_be_linked_to_a_job_of_the_party(): void
    {
        $job = $this->makeJob('JOB-001');

        $this->storeReceipt($this->advancePayload(500, $this->jobSplit([[$job->id, 500]])));

        $allocations = ChevronMoneyReceiptAllocation::all();
        $this->assertCount(1, $allocations);
        $this->assertSame($job->id, (int) $allocations->first()->job_id);
        $this->assertNull($allocations->first()->bill_id);
        $this->assertEquals(500, $allocations->first()->amount);
    }

    public function test_advance_receipt_can_be_split_across_several_jobs_with_the_rest_kept_general(): void
    {
        $first = $this->makeJob('JOB-001');
        $second = $this->makeJob('JOB-002');

        $this->storeReceipt($this->advancePayload(1000, $this->jobSplit([[$first->id, 300], [$second->id, 200]])));

        $receipt = ChevronMoneyReceipt::firstOrFail();
        $this->assertEquals(1000, $receipt->total_amount);
        $this->assertCount(3, $receipt->allocations);
        $this->assertEquals(300, $receipt->allocations->firstWhere('job_id', $first->id)->amount);
        $this->assertEquals(200, $receipt->allocations->firstWhere('job_id', $second->id)->amount);
        $this->assertEquals(500, $receipt->allocations->first(fn ($a) => $a->job_id === null)->amount);
        $this->assertEquals(1000, $receipt->allocations->sum('amount'));
    }

    public function test_general_advance_mode_keeps_the_whole_amount_without_a_job_even_with_stray_job_rows(): void
    {
        $job = $this->makeJob('JOB-001');

        $this->storeReceipt($this->advancePayload(1500, ['advance_mode' => 'general'] + $this->jobSplit([[$job->id, 500]])));

        $allocations = ChevronMoneyReceiptAllocation::all();
        $this->assertCount(1, $allocations);
        $this->assertNull($allocations->first()->job_id);
        $this->assertNull($allocations->first()->bill_id);
        $this->assertEquals(1500, $allocations->first()->amount);
    }

    public function test_job_mode_advance_still_assigns_job_rows(): void
    {
        $job = $this->makeJob('JOB-001');

        $this->storeReceipt($this->advancePayload(1500, ['advance_mode' => 'job'] + $this->jobSplit([[$job->id, 500]])));

        $receipt = ChevronMoneyReceipt::firstOrFail();
        $this->assertEquals(500, $receipt->allocations->firstWhere('job_id', $job->id)->amount);
        $this->assertEquals(1000, $receipt->allocations->first(fn ($a) => $a->job_id === null)->amount);
    }

    public function test_editing_a_job_advance_into_a_general_advance_drops_the_job_rows(): void
    {
        $job = $this->makeJob('JOB-001');
        $this->storeReceipt($this->advancePayload(1000, $this->jobSplit([[$job->id, 1000]])));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $this->updateReceipt($receipt, $this->advancePayload(1000, ['advance_mode' => 'general']));

        $allocations = $receipt->fresh()->allocations;
        $this->assertCount(1, $allocations);
        $this->assertNull($allocations->first()->job_id);
    }

    public function test_advance_split_that_uses_the_whole_amount_has_no_general_row(): void
    {
        $first = $this->makeJob('JOB-001');
        $second = $this->makeJob('JOB-002');

        $this->storeReceipt($this->advancePayload(1000, $this->jobSplit([[$first->id, 400], [$second->id, 600]])));

        $allocations = ChevronMoneyReceiptAllocation::all();
        $this->assertCount(2, $allocations);
        $this->assertNull($allocations->first(fn ($a) => $a->job_id === null));
    }

    public function test_advance_job_amounts_cannot_exceed_the_collected_total(): void
    {
        $first = $this->makeJob('JOB-001');
        $second = $this->makeJob('JOB-002');

        $errors = $this->storeExpectingErrors($this->advancePayload(500, $this->jobSplit([[$first->id, 300], [$second->id, 300]])));

        $this->assertArrayHasKey('job_allocations', $errors);
        $this->assertSame(0, ChevronMoneyReceipt::count());
        $this->assertSame(0, ChevronMoneyReceiptAllocation::count());
    }

    public function test_advance_rejects_a_job_of_another_party_or_branch(): void
    {
        $otherParty = $this->makeJob('JOB-002', ['party_name' => 'Someone Else']);
        $otherBranch = $this->makeJob('JOB-003', ['branch_id' => 99]);

        $this->assertArrayHasKey("job_allocations.{$otherParty->id}.job_id", $this->storeExpectingErrors($this->advancePayload(500, $this->jobSplit([[$otherParty->id, 500]]))));
        $this->assertArrayHasKey("job_allocations.{$otherBranch->id}.job_id", $this->storeExpectingErrors($this->advancePayload(500, $this->jobSplit([[$otherBranch->id, 500]]))));
        $this->assertSame(0, ChevronMoneyReceipt::count());
    }

    public function test_editing_an_advance_receipt_replaces_its_job_split(): void
    {
        $first = $this->makeJob('JOB-001');
        $second = $this->makeJob('JOB-002');
        $this->storeReceipt($this->advancePayload(1000, $this->jobSplit([[$first->id, 1000]])));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $this->updateReceipt($receipt, $this->advancePayload(800, $this->jobSplit([[$second->id, 500]])));

        $receipt = $receipt->fresh();
        $this->assertEquals(800, $receipt->total_amount);
        $this->assertCount(2, $receipt->allocations);
        $this->assertNull($receipt->allocations->firstWhere('job_id', $first->id));
        $this->assertEquals(500, $receipt->allocations->firstWhere('job_id', $second->id)->amount);
        $this->assertEquals(300, $receipt->allocations->first(fn ($a) => $a->job_id === null)->amount);
    }

    public function test_one_receipt_collects_across_several_bills_of_different_jobs(): void
    {
        $jobOne = $this->makeJob('JOB-001');
        $jobTwo = $this->makeJob('JOB-002');
        $billOne = $this->makeBill(1000, 'BIL000001', ['job_id' => $jobOne->id]);
        $billTwo = $this->makeBill(600, 'BIL000002', ['job_id' => $jobTwo->id]);
        $billThree = $this->makeBill(300, 'BIL000003', ['job_id' => $jobTwo->id]);

        $this->storeReceipt($this->billPayload([[$billOne->id, 250], [$billTwo->id, 600], [$billThree->id, 100]]));

        $receipt = ChevronMoneyReceipt::firstOrFail();
        $this->assertEquals(950, $receipt->total_amount);
        $this->assertEquals(1900, $receipt->payable_amount);
        $this->assertCount(3, $receipt->allocations);
        $this->assertEquals(250, $billOne->fresh()->received_amount);
        $this->assertEquals(600, $billTwo->fresh()->received_amount);
        $this->assertEquals(100, $billThree->fresh()->received_amount);
        $this->assertSame($jobTwo->id, (int) $receipt->allocations->firstWhere('bill_id', $billThree->id)->job_id);

        $this->storeReceipt($this->billPayload([[$billOne->id, 750], [$billThree->id, 200]]));

        $this->assertEquals(1000, $billOne->fresh()->received_amount);
        $this->assertEquals(300, $billThree->fresh()->received_amount);
        $this->assertEquals(0, $billOne->fresh()->balance);
    }

    public function test_one_overcollected_bill_rejects_the_whole_multi_bill_receipt(): void
    {
        $first = $this->makeBill(1000, 'BIL000001');
        $second = $this->makeBill(300, 'BIL000002');

        $errors = $this->storeExpectingErrors($this->billPayload([[$first->id, 1000], [$second->id, 300.01]]));

        $this->assertArrayHasKey("allocations.{$second->id}.amount", $errors);
        $this->assertArrayNotHasKey("allocations.{$first->id}.amount", $errors);
        $this->assertSame(0, ChevronMoneyReceipt::count());
        $this->assertEquals(0, $first->fresh()->received_amount);
        $this->assertEquals(0, $second->fresh()->received_amount);
    }

    public function test_editing_a_bill_receipt_can_swap_the_bills_it_covers(): void
    {
        $first = $this->makeBill(1000, 'BIL000001');
        $second = $this->makeBill(500, 'BIL000002');
        $third = $this->makeBill(200, 'BIL000003');
        $this->storeReceipt($this->billPayload([[$first->id, 1000], [$second->id, 100]]));
        $receipt = ChevronMoneyReceipt::firstOrFail();

        $this->updateReceipt($receipt, $this->billPayload([[$second->id, 500], [$third->id, 200]]));

        $this->assertEquals(0, $first->fresh()->received_amount);
        $this->assertEquals(500, $second->fresh()->received_amount);
        $this->assertEquals(200, $third->fresh()->received_amount);
        $this->assertCount(2, $receipt->fresh()->allocations);
        $this->assertEquals(700, $receipt->fresh()->total_amount);
    }

    public function test_party_jobs_search_filters_by_job_number_and_pages_results(): void
    {
        foreach (range(1, 35) as $n) {
            $this->makeJob('JOB-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT));
        }
        $this->makeJob('JOB-ZZZ', ['party_name' => 'Someone Else']);
        $this->makeJob('JOB-YYY', ['branch_id' => 99]);

        $controller = new MoneyReceiptController;
        $search = fn (array $query) => $controller->partyJobs(IndexMoneyReceiptRequest::create('/', 'GET', array_merge(['party_name' => 'Acme'], $query)))->getData(true);

        $firstPage = $search([]);
        $this->assertCount(30, $firstPage['results']);
        $this->assertTrue($firstPage['pagination']['more']);
        $this->assertSame('JOB-035', $firstPage['results'][0]['text']);

        $secondPage = $search(['page' => 2]);
        $this->assertCount(5, $secondPage['results']);
        $this->assertFalse($secondPage['pagination']['more']);

        $this->assertSame(['JOB-007'], array_column($search(['q' => 'JOB-007'])['results'], 'text'));
        $this->assertSame(['JOB-007'], array_column($search(['q' => '007'])['results'], 'text'));
        $this->assertSame([], $search(['q' => 'ZZZ'])['results']);
        $this->assertSame([], $search(['q' => 'YYY'])['results']);
    }

    public function test_receipt_list_reference_column_filters_by_bill_no_and_job_no(): void
    {
        $job = $this->makeJob('JOB-777');
        $bill = $this->makeBill(500, 'BIL000042', ['job_id' => $job->id]);
        $this->storeReceipt($this->billPayload([[$bill->id, 100]]));
        $this->storeReceipt($this->advancePayload(300, $this->jobSplit([[$job->id, 300]])));
        $this->storeReceipt($this->advancePayload(50));

        $this->assertSame(['BIL000042 (JOB-777)'], $this->listReferences('BIL0000'));
        $this->assertEqualsCanonicalizing(['BIL000042 (JOB-777)', 'JOB-777'], $this->listReferences('JOB-777'));
        $this->assertSame([], $this->listReferences('JOB-999'));
        $this->assertEqualsCanonicalizing(['BIL000042 (JOB-777)', 'JOB-777', 'General advance'], $this->listReferences(''));
    }

    public function test_receipt_list_shows_the_newest_receipt_first(): void
    {
        foreach ([100, 200, 300] as $amount) {
            $this->storeReceipt($this->advancePayload($amount));
        }

        $this->assertSame(['MR000003', 'MR000002', 'MR000001'], array_column($this->listReceipts(''), 'receipt_no'));
    }

    public function test_receipt_list_offers_view_and_print_for_every_status_and_edit_only_when_active(): void
    {
        $this->storeReceipt($this->advancePayload(100));
        $this->storeReceipt($this->advancePayload(200));
        ChevronMoneyReceipt::where('receipt_no', 'MR000002')->update(['status' => 'Approved']);

        $rows = collect($this->listReceipts(''))->keyBy('receipt_no');

        foreach (['MR000001', 'MR000002'] as $no) {
            $id = ChevronMoneyReceipt::where('receipt_no', $no)->value('id');
            $this->assertStringContainsString(route('chevron.cnf.money-receipts.show', $id), $rows[$no]['action']);
            $this->assertStringContainsString(route('chevron.cnf.money-receipts.print', $id), $rows[$no]['action']);
        }
        $this->assertStringContainsString('/edit', $rows['MR000001']['action']);
        $this->assertStringNotContainsString('/edit', $rows['MR000002']['action']);
        $this->assertStringContainsString('fa-lock', $rows['MR000002']['action']);
    }

    public function test_show_page_lists_bills_with_balance_and_settled_status(): void
    {
        $job = $this->makeJob('JOB-001');
        $bill = $this->makeBill(1000, 'BIL000042', ['job_id' => $job->id, 'bill_date' => '2026-11-01']);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]]));
        $first = ChevronMoneyReceipt::firstOrFail();

        $html = $this->renderShow($first);

        $this->assertStringContainsString('MR000001', $html);
        $this->assertStringContainsString('BIL000042', $html);
        $this->assertStringContainsString('JOB-001', $html);
        $this->assertStringContainsString('01-Nov-2026', $html);
        $this->assertStringContainsString('BDT 400.00', $html);
        $this->assertStringContainsString('BDT FOUR HUNDRED TAKA ONLY', $html);
        $this->assertStringContainsString('Partially paid', $html);
        $this->assertStringContainsString('600.00', $html);
        $this->assertStringNotContainsString('Settled', $html);

        $this->storeReceipt($this->billPayload([[$bill->id, 600]]));

        $this->assertStringContainsString('Settled', $this->renderShow($first->fresh()));
    }

    public function test_show_page_lists_job_wise_advance_and_the_general_part(): void
    {
        $first = $this->makeJob('JOB-001');
        $second = $this->makeJob('JOB-002');
        $this->storeReceipt($this->advancePayload(1000, $this->jobSplit([[$first->id, 300], [$second->id, 200]])));

        $html = $this->renderShow(ChevronMoneyReceipt::firstOrFail());

        $this->assertStringContainsString('Advance Allocation', $html);
        $this->assertStringContainsString('JOB-001', $html);
        $this->assertStringContainsString('JOB-002', $html);
        $this->assertStringContainsString('General advance (no job)', $html);
        $this->assertStringContainsString('500.00', $html);
        $this->assertStringContainsString('1,000.00', $html);
    }

    public function test_show_page_explains_legacy_receipts(): void
    {
        $receipt = ChevronMoneyReceipt::create([
            'receipt_no' => 'MR000099', 'branch_id' => self::BRANCH, 'receipt_date' => '2026-01-05',
            'party_name' => 'Old Party', 'pay_type' => 'Cash', 'total_amount' => 250, 'status' => 'Active',
        ]);
        $receipt->items()->create(['payment_type' => 'Cash', 'amount' => 250]);

        $html = $this->renderShow($receipt);

        $this->assertStringContainsString('Legacy receipt.', $html);
        $this->assertStringContainsString('Old Party', $html);
        $this->assertStringNotContainsString('Collected Against Bills', $html);
        $this->assertStringNotContainsString('Advance Allocation', $html);
    }

    public function test_show_page_offers_edit_only_for_active_receipts(): void
    {
        $this->storeReceipt($this->advancePayload(100));
        $receipt = ChevronMoneyReceipt::firstOrFail();
        $controller = new MoneyReceiptController;
        $request = $this->requestAs(ShowMoneyReceiptRequest::class);

        $this->assertTrue($controller->show($request, $receipt)->getData()['canEdit']);

        $receipt->update(['status' => 'Approved']);
        $this->assertFalse($controller->show($request, $receipt->fresh())->getData()['canEdit']);
    }

    public function test_print_page_has_letterhead_allocations_and_cheque_note(): void
    {
        Company::create(['name' => 'Chevron Lines (C&F) Ltd.', 'slug' => 'chevron-lines', 'type' => 'cnf', 'email' => 'info@example.test']);
        (new ChevronBranch(['name' => 'Head Office', 'code' => 'DK', 'address' => 'Dhaka, Bangladesh', 'phone' => '+88 02 9830600']))
            ->forceFill(['id' => self::BRANCH])
            ->save();
        $job = $this->makeJob('JOB-001');
        $bill = $this->makeBill(1000, 'BIL000042', ['job_id' => $job->id]);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]], 400, ['items' => [['payment_type' => 'Cheque', 'amount' => 400, 'cheque_card_no' => 'CHQ-7788', 'cheque_card_holder' => 'A. Rahman']]]));

        $html = (new MoneyReceiptController)
            ->printView($this->requestAs(PrintMoneyReceiptRequest::class), ChevronMoneyReceipt::firstOrFail())
            ->render();

        foreach (['MONEY RECEIPT' => false, 'Money Receipt' => true, 'Chevron Lines (C&amp;F) Ltd.' => true, 'Head Office' => true, 'Dhaka, Bangladesh' => true, '+88 02 9830600' => true,
            'MR000001'            => true, 'Acme' => true, 'BIL000042' => true, 'JOB-001' => true, 'CHQ-7788' => true, 'A. Rahman' => true,
            'BDT 400.00'          => true, 'BDT FOUR HUNDRED TAKA ONLY' => true, 'Cheque payments are subject to realization.' => true,
            'Received By (Party)' => true, 'Authorized Signature' => true] as $needle => $expected) {
            $expected
                ? $this->assertStringContainsString($needle, $html, "missing {$needle}")
                : $this->assertStringNotContainsString($needle, $html);
        }
    }

    public function test_print_page_for_general_advance_has_no_cheque_note(): void
    {
        $this->storeReceipt($this->advancePayload(750));

        $html = (new MoneyReceiptController)
            ->printView($this->requestAs(PrintMoneyReceiptRequest::class), ChevronMoneyReceipt::firstOrFail())
            ->render();

        $this->assertStringContainsString('General advance (no job)', $html);
        $this->assertStringContainsString('Advance Payment', $html);
        $this->assertStringNotContainsString('subject to realization', $html);
    }

    public function test_receipt_of_another_branch_cannot_be_viewed_or_printed(): void
    {
        $this->storeReceipt($this->advancePayload(100));
        $receipt = ChevronMoneyReceipt::firstOrFail();
        $receipt->update(['branch_id' => 99]);
        $controller = new MoneyReceiptController;

        try {
            $controller->show($this->requestAs(ShowMoneyReceiptRequest::class), $receipt);
            $this->fail('Expected 404 for show.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }

        $this->expectException(HttpException::class);
        $controller->printView($this->requestAs(PrintMoneyReceiptRequest::class), $receipt);
    }

    public function test_advance_receipts_build_the_customer_wise_advance_balance_per_branch(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $this->storeReceipt($this->advancePayload(500, ['party_id' => 7]));
        $this->storeReceipt($this->advancePayload(200, ['party_id' => 8, 'party_name' => 'Beta']));
        $this->storeReceipt($this->advancePayload(9999, ['party_id' => 7]));
        ChevronMoneyReceipt::where('total_amount', 9999)->update(['branch_id' => 99]);

        $this->assertEquals(1500, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
        $this->assertEquals(200, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 8));
        $this->assertEquals(9999, ChevronMoneyReceipt::advanceBalance(99, 7));
        $this->assertEquals(0, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 123));
    }

    public function test_a_bill_can_be_paid_from_the_customers_advance_balance(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $bill = $this->makeBill(600);

        $this->storeReceipt($this->billPayload([[$bill->id, 600]], 600, [
            'party_id' => 7,
            'pay_type' => 'Mixed',
            'items'    => [
                ['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 400, 'account_id' => 3, 'cheque_card_no' => 'X1'],
                ['payment_type' => 'Cash', 'amount' => 200],
            ],
        ]));

        $this->assertEquals(0, $bill->fresh()->balance);
        $this->assertEquals(400, ChevronMoneyReceipt::advanceAdjusted(self::BRANCH, 7));
        $this->assertEquals(600, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $adjustment = ChevronMoneyReceipt::where('receipt_type', 'Bill')->firstOrFail()->items->firstWhere('payment_type', ChevronMoneyReceipt::ADVANCE_ADJUSTMENT);
        $this->assertNull($adjustment->account_id);
        $this->assertNull($adjustment->cheque_card_no);
    }

    public function test_advance_adjustment_cannot_exceed_the_available_balance(): void
    {
        $this->storeReceipt($this->advancePayload(300, ['party_id' => 7]));
        $bill = $this->makeBill(1000);

        $errors = $this->storeExpectingErrors($this->billPayload([[$bill->id, 400]], 400, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 400]],
        ]));

        $this->assertArrayHasKey('items', $errors);
        $this->assertStringContainsString('available advance balance (300.00)', $errors['items'][0]);
        $this->assertSame(1, ChevronMoneyReceipt::count());
        $this->assertEquals(0, $bill->fresh()->received_amount);
    }

    public function test_advance_adjustment_needs_a_party_chosen_from_the_list(): void
    {
        $bill = $this->makeBill(1000);

        $errors = $this->storeExpectingErrors($this->billPayload([[$bill->id, 100]], 100, [
            'items' => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 100]],
        ]));

        $this->assertArrayHasKey('party_name', $errors);
        $this->assertSame(0, ChevronMoneyReceipt::count());
    }

    public function test_advance_adjustment_is_not_allowed_on_an_advance_receipt(): void
    {
        $this->storeReceipt($this->advancePayload(500, ['party_id' => 7]));

        $errors = $this->storeExpectingErrors($this->advancePayload(100, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 100]],
        ]));

        $this->assertArrayHasKey('items', $errors);
        $this->assertSame(1, ChevronMoneyReceipt::count());
    }

    public function test_editing_a_bill_receipt_gets_its_own_advance_adjustment_back(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $bill = $this->makeBill(1000);
        $adjust = fn (float $amount) => $this->billPayload([[$bill->id, $amount]], $amount, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => $amount]],
        ]);
        $this->storeReceipt($adjust(800));
        $receipt = ChevronMoneyReceipt::where('receipt_type', 'Bill')->firstOrFail();

        $this->updateReceipt($receipt, $adjust(1000));
        $this->assertEquals(0, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $this->updateReceipt($receipt->fresh(), $adjust(300));
        $this->assertEquals(700, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $errors = $this->updateExpectingErrors($receipt->fresh(), $adjust(1200));
        $this->assertArrayHasKey('items', $errors);
        $this->assertEquals(700, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
    }

    public function test_deleting_a_bill_receipt_gives_the_advance_balance_back(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $bill = $this->makeBill(600);
        $this->storeReceipt($this->billPayload([[$bill->id, 600]], 600, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 600]],
        ]));
        $this->assertEquals(400, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $receipt = ChevronMoneyReceipt::where('receipt_type', 'Bill')->firstOrFail();
        $response = (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), $receipt);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(1000, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
        $this->assertEquals(600, $bill->fresh()->balance);
    }

    public function test_an_advance_that_was_already_adjusted_cannot_be_deleted_or_reduced_below_the_used_amount(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $advance = ChevronMoneyReceipt::firstOrFail();
        $bill = $this->makeBill(700);
        $this->storeReceipt($this->billPayload([[$bill->id, 700]], 700, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 700]],
        ]));

        $deleted = (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), $advance);
        $this->assertSame(422, $deleted->getStatusCode());
        $this->assertTrue(ChevronMoneyReceipt::whereKey($advance->id)->exists());
        $this->assertEquals(300, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $advance = ChevronMoneyReceipt::findOrFail($advance->id);
        $errors = $this->updateExpectingErrors($advance, $this->advancePayload(600, ['party_id' => 7]));
        $this->assertArrayHasKey('items', $errors);
        $this->assertEquals(1000, ChevronMoneyReceipt::findOrFail($advance->id)->total_amount);

        $this->updateReceipt(ChevronMoneyReceipt::findOrFail($advance->id), $this->advancePayload(800, ['party_id' => 7]));
        $this->assertEquals(100, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
    }

    public function test_moving_an_adjusted_advance_to_another_customer_is_rejected(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $advance = ChevronMoneyReceipt::firstOrFail();
        $bill = $this->makeBill(700);
        $this->storeReceipt($this->billPayload([[$bill->id, 700]], 700, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 700]],
        ]));

        $errors = $this->updateExpectingErrors($advance->fresh(), $this->advancePayload(1000, ['party_id' => 8, 'party_name' => 'Beta']));

        $this->assertArrayHasKey('items', $errors);
        $this->assertSame(7, (int) $advance->fresh()->party_id);
    }

    public function test_party_advance_endpoint_reports_the_balance_and_gives_back_the_edited_receipts_own_adjustment(): void
    {
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $bill = $this->makeBill(600);
        $this->storeReceipt($this->billPayload([[$bill->id, 600]], 600, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 600]],
        ]));
        $billReceipt = ChevronMoneyReceipt::where('receipt_type', 'Bill')->firstOrFail();
        $controller = new MoneyReceiptController;

        $plain = $controller->partyAdvance(IndexMoneyReceiptRequest::create('/', 'GET', ['party_id' => 7]))->getData(true);
        $editing = $controller->partyAdvance(IndexMoneyReceiptRequest::create('/', 'GET', ['party_id' => 7, 'receipt_id' => $billReceipt->id]))->getData(true);
        $none = $controller->partyAdvance(IndexMoneyReceiptRequest::create('/', 'GET'))->getData(true);

        $this->assertEquals(1000, $plain['received']);
        $this->assertEquals(600, $plain['adjusted']);
        $this->assertEquals(400, $plain['balance']);
        $this->assertSame(route('chevron.cnf.advance-balances.show', 7), $plain['statement_url']);
        $this->assertEquals(1000, $editing['balance']);
        $this->assertEquals(0, $none['balance']);
    }

    public function test_advance_balance_list_shows_each_customers_received_adjusted_and_balance(): void
    {
        $this->makeCustomer(7, 'Acme');
        $this->makeCustomer(8, 'Beta');
        $this->makeCustomer(9, 'No Advance');
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $this->storeReceipt($this->advancePayload(250, ['party_id' => 8, 'party_name' => 'Beta']));
        $bill = $this->makeBill(600);
        $this->storeReceipt($this->billPayload([[$bill->id, 600]], 600, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 600]],
        ]));

        $request = IndexMoneyReceiptRequest::create('/', 'GET', ['draw' => 1, 'start' => 0, 'length' => 50, 'search' => ['value' => '', 'regex' => 'false']]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->app->instance('request', $request);
        $this->app->forgetInstance('datatables.request');
        $rows = collect(json_decode((new AdvanceBalanceController)->index($request)->getContent(), true)['data'])->keyBy('name');

        $this->assertSame(['Acme', 'Beta'], $rows->keys()->sort()->values()->all());
        $this->assertSame(['1,000.00', '600.00', '400.00'], [$rows['Acme']['advance_received'], $rows['Acme']['advance_adjusted'], $rows['Acme']['balance']]);
        $this->assertSame(['250.00', '0.00', '250.00'], [$rows['Beta']['advance_received'], $rows['Beta']['advance_adjusted'], $rows['Beta']['balance']]);
        $this->assertStringContainsString(route('chevron.cnf.advance-balances.show', 7), $rows['Acme']['action']);
    }

    public function test_advance_statement_lists_receipts_and_adjustments_with_a_running_balance(): void
    {
        $customer = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJob('JOB-001');
        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7] + $this->jobSplit([[$job->id, 1000]])));
        $bill = $this->makeBill(600, 'BIL000042');
        $this->storeReceipt($this->billPayload([[$bill->id, 600]], 600, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 600]],
        ]));
        $this->actingAs($this->userWithAllPermissions());

        $view = (new AdvanceBalanceController)->show($this->requestAs(IndexMoneyReceiptRequest::class), $customer);
        $data = $view->getData();
        $html = html_entity_decode($view->render());

        $this->assertEquals(1000, $data['received']);
        $this->assertEquals(600, $data['adjusted']);
        $this->assertEquals(400, $data['balance']);
        $this->assertSame([1000.0, 400.0], $data['entries']->pluck('balance')->all());
        foreach (['Acme', 'MR000001', 'MR000002', 'Advance received', 'Adjusted to bill', 'JOB-001', 'BIL000042', '1,000.00', '600.00', '400.00'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing {$needle}");
        }
    }

    public function test_customer_advance_balance_column_follows_every_receipt_change(): void
    {
        $acme = $this->makeCustomer(7, 'Acme');
        $beta = $this->makeCustomer(8, 'Beta');
        $total = fn (ChevronCustomer $c) => (float) ChevronCustomer::findOrFail($c->id)->advance_balance;

        $this->storeReceipt($this->advancePayload(1000, ['party_id' => 7]));
        $this->assertEquals(1000, $total($acme));
        $this->assertEquals(0, $total($beta));

        // The column is the customer's total across branches; the branch balance stays branch-only.
        session(['active_branch_id' => 99]);
        $this->storeReceipt($this->advancePayload(500, ['party_id' => 7]));
        session(['active_branch_id' => self::BRANCH]);
        $this->assertEquals(1500, $total($acme));
        $this->assertEquals(1000, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $bill = $this->makeBill(400);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]], 400, [
            'party_id' => 7,
            'items'    => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 400]],
        ]));
        $this->assertEquals(1100, $total($acme));

        $billReceipt = ChevronMoneyReceipt::where('receipt_type', 'Bill')->firstOrFail();
        $this->updateReceipt($billReceipt, $this->billPayload([[$bill->id, 400]], 400, [
            'party_id' => 7,
            'items'    => [['payment_type' => 'Cash', 'amount' => 400]],
        ]));
        $this->assertEquals(1500, $total($acme));

        $unused = ChevronMoneyReceipt::where('branch_id', self::BRANCH)->where('receipt_type', 'Advance')->firstOrFail();
        $this->updateReceipt($unused, $this->advancePayload(1000, ['party_id' => 8, 'party_name' => 'Beta']));
        $this->assertEquals(500, $total($acme));
        $this->assertEquals(1000, $total($beta));

        (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), ChevronMoneyReceipt::findOrFail($unused->id));
        $this->assertEquals(0, $total($beta));
        $this->assertEquals(500, $total($acme));
    }

    public function test_customer_advance_balance_is_not_mass_assignable(): void
    {
        $customer = $this->makeCustomer(7, 'Acme');

        $customer->update(['name' => 'Acme Ltd', 'advance_balance' => 999999]);

        $this->assertEquals(0, (float) ChevronCustomer::findOrFail(7)->advance_balance);
        $this->assertSame('Acme Ltd', ChevronCustomer::findOrFail(7)->name);
    }

    public function test_customer_list_shows_the_stored_advance_balance(): void
    {
        $this->makeCustomer(7, 'Acme');
        $this->makeCustomer(8, 'Beta');
        $this->storeReceipt($this->advancePayload(1234.5, ['party_id' => 7]));

        $request = IndexCustomerRequest::create('/', 'GET', ['draw' => 1, 'start' => 0, 'length' => 50, 'search' => ['value' => '', 'regex' => 'false']]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->app->instance('request', $request);
        $this->app->forgetInstance('datatables.request');
        $user = $this->userWithAllPermissions();
        $request->setUserResolver(fn () => $user);

        $rows = collect(json_decode((new CustomerController)->index($request)->getContent(), true)['data'])->keyBy('name');

        $this->assertSame('1,234.50', $rows['Acme']['advance_balance']);
        $this->assertSame('0.00', $rows['Beta']['advance_balance']);
    }

    public function test_party_jobs_include_jobs_linked_by_customer_id(): void
    {
        $this->makeJob('JOB-001', ['party_name' => 'Acme Ltd (old name)', 'customer_id' => 7]);

        $request = IndexMoneyReceiptRequest::create('/', 'GET', ['party_id' => 7, 'party_name' => 'Acme']);
        $results = (new MoneyReceiptController)->partyJobs($request)->getData(true)['results'];

        $this->assertSame(['JOB-001'], array_column($results, 'text'));
    }

    public function test_only_active_receipts_can_be_edited_or_deleted(): void
    {
        $this->storeReceipt($this->advancePayload(100));
        $receipt = ChevronMoneyReceipt::firstOrFail();
        $receipt->update(['status' => 'Approved']);

        $deleteResponse = (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), $receipt);
        $this->assertSame(422, $deleteResponse->getStatusCode());
        $this->assertSame(1, ChevronMoneyReceipt::count());

        $this->expectException(HttpException::class);
        $this->updateReceipt($receipt, $this->advancePayload(200));
    }

    public function test_receipts_of_another_branch_cannot_be_deleted(): void
    {
        $this->storeReceipt($this->advancePayload(100));
        $receipt = ChevronMoneyReceipt::firstOrFail();
        $receipt->update(['branch_id' => 99]);

        $this->expectException(HttpException::class);
        (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), $receipt);
    }

    public function test_party_bills_lists_only_outstanding_bills_and_adds_back_the_edited_receipt(): void
    {
        $open = $this->makeBill(1000, 'BIL000001');
        $paid = $this->makeBill(500, 'BIL000002');
        $this->makeBill(700, 'BIL000003', ['party_name' => 'Someone Else']);
        $this->storeReceipt($this->billPayload([[$paid->id, 500]], 500));
        $this->storeReceipt($this->billPayload([[$open->id, 250]], 250));
        $editing = ChevronMoneyReceipt::where('receipt_no', 'MR000002')->firstOrFail();

        $controller = new MoneyReceiptController;
        $plain = $controller->partyBills(IndexMoneyReceiptRequest::create('/', 'GET', ['party_name' => 'Acme']))->getData(true)['bills'];
        $whileEditing = $controller->partyBills(IndexMoneyReceiptRequest::create('/', 'GET', ['party_name' => 'Acme', 'receipt_id' => $editing->id]))->getData(true)['bills'];

        $this->assertSame(['BIL000001'], array_column($plain, 'bill_no'));
        $this->assertEquals(750, $plain[0]['balance']);
        $this->assertEquals(250, $plain[0]['received_amount']);
        $this->assertEquals(1000, $whileEditing[0]['balance']);
        $this->assertEquals(0, $whileEditing[0]['received_amount']);
    }

    public function test_bill_due_cannot_drop_below_the_amount_already_received(): void
    {
        $bill = $this->makeBill(1000);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]], 400));

        $request = UpdateBillRequest::create('/', 'PUT', [
            'bill_date' => '2026-01-01',
            'rows'      => [['expense_category_id' => null, 'expense_head_id' => null, 'amount' => 0]],
        ]);

        try {
            (new BillController)->update($request, $bill->fresh());
            $this->fail('Expected a validation error.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('advance_amount', $e->errors());
        }

        $this->assertEquals(1000, $bill->fresh()->due_amount);
    }

    public function test_bill_with_money_receipts_cannot_be_deleted(): void
    {
        $bill = $this->makeBill(1000);
        $this->storeReceipt($this->billPayload([[$bill->id, 400]], 400));

        $response = (new BillController)->destroy(DestroyBillRequest::create('/', 'DELETE'), $bill->fresh());

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(1, ChevronBill::count());
    }

    public function test_request_rules_require_a_known_type_and_bill_allocations(): void
    {
        $rules = (new StoreMoneyReceiptRequest)->rules();
        $base = [
            'receipt_date' => '2026-11-18',
            'party_name'   => 'Acme',
            'pay_type'     => 'Cash',
            'items'        => [['payment_type' => 'Cash', 'amount' => 100]],
        ];

        $this->assertTrue(Validator::make($base + ['receipt_type' => 'Advance'], $rules)->passes());
        $this->assertArrayHasKey('receipt_type', Validator::make($base + ['receipt_type' => 'Other'], $rules)->errors()->toArray());
        $this->assertArrayHasKey('allocations', Validator::make($base + ['receipt_type' => 'Bill'], $rules)->errors()->toArray());
        $this->assertArrayHasKey('allocations.1.bill_id', Validator::make($base + [
            'receipt_type' => 'Bill',
            'allocations'  => [['bill_id' => 4, 'amount' => 10], ['bill_id' => 4, 'amount' => 10]],
        ], $rules)->errors()->toArray());
        $this->assertArrayHasKey('allocations.0.amount', Validator::make($base + [
            'receipt_type' => 'Bill',
            'allocations'  => [['bill_id' => 4, 'amount' => 0]],
        ], $rules)->errors()->toArray());
        $this->assertTrue(Validator::make($base + [
            'receipt_type'    => 'Advance',
            'job_allocations' => [['job_id' => 4, 'amount' => 10], ['job_id' => 5, 'amount' => 10]],
        ], $rules)->passes());
        $this->assertArrayHasKey('job_allocations.1.job_id', Validator::make($base + [
            'receipt_type'    => 'Advance',
            'job_allocations' => [['job_id' => 4, 'amount' => 10], ['job_id' => 4, 'amount' => 10]],
        ], $rules)->errors()->toArray());
        $this->assertArrayHasKey('job_allocations.0.amount', Validator::make($base + [
            'receipt_type'    => 'Advance',
            'job_allocations' => [['job_id' => 4, 'amount' => 0]],
        ], $rules)->errors()->toArray());
    }

    /**
     * Run the receipt list DataTable with a Reference column search and return the Reference values.
     *
     * @return array<int, string>
     */
    private function listReferences(string $search): array
    {
        return array_column($this->listReceipts($search), 'reference');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listReceipts(string $search): array
    {
        $user = $this->userWithAllPermissions();

        $columns = [['data' => 'reference', 'name' => 'reference', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => $search, 'regex' => 'false']]];
        $request = IndexMoneyReceiptRequest::create('/', 'GET', ['draw' => 1, 'start' => 0, 'length' => 50, 'columns' => $columns, 'search' => ['value' => '', 'regex' => 'false']]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->app->instance('request', $request);
        $this->app->forgetInstance('datatables.request');
        $request->setUserResolver(fn () => $user);

        $payload = json_decode((new MoneyReceiptController)->index($request)->getContent(), true);

        return $payload['data'];
    }

    /**
     * Render the full show page inside the app layout as a logged-in user.
     */
    private function renderShow(ChevronMoneyReceipt $receipt): string
    {
        $this->actingAs($this->userWithAllPermissions());

        $view = (new MoneyReceiptController)->show($this->requestAs(ShowMoneyReceiptRequest::class), $receipt);

        return html_entity_decode($view->render());
    }

    private function userWithAllPermissions(): User
    {
        return new class extends User
        {
            public function hasPermission(string $permission): bool
            {
                return true;
            }
        };
    }

    /**
     * @param  class-string<FormRequest>  $class
     */
    private function requestAs(string $class): FormRequest
    {
        $request = $class::create('/', 'GET');
        $user = $this->userWithAllPermissions();
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function makeCustomer(int $id, string $name): ChevronCustomer
    {
        $customer = new ChevronCustomer(['name' => $name, 'customer_id' => 'C'.$id]);
        $customer->forceFill(['id' => $id])->save();

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeJob(string $jobNo, array $overrides = []): ChevronJob
    {
        return ChevronJob::create(array_merge([
            'job_no'     => $jobNo,
            'party_name' => 'Acme',
            'branch_id'  => self::BRANCH,
        ], $overrides));
    }

    /**
     * @param  array<int, array{0: int, 1: float|int}>  $jobs
     * @return array<string, mixed>
     */
    private function jobSplit(array $jobs): array
    {
        return [
            'job_allocations' => collect($jobs)->mapWithKeys(fn ($j) => [$j[0] => ['job_id' => $j[0], 'amount' => $j[1]]])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeBill(float $due, string $billNo = 'BIL000001', array $overrides = []): ChevronBill
    {
        return ChevronBill::create(array_merge([
            'bill_no'         => $billNo,
            'branch_id'       => self::BRANCH,
            'party_name'      => 'Acme',
            'net_payable'     => $due,
            'due_amount'      => $due,
            'received_amount' => 0,
        ], $overrides));
    }

    /**
     * @param  array<int, array{0: int, 1: float|int}>  $allocations
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billPayload(array $allocations, float|int|null $collected = null, array $overrides = []): array
    {
        $collected ??= array_sum(array_column($allocations, 1));

        return array_merge([
            'receipt_date' => '2026-11-18',
            'receipt_type' => 'Bill',
            'party_id'     => null,
            'party_name'   => 'Acme',
            'pay_type'     => 'Cash',
            'allocations'  => collect($allocations)->mapWithKeys(fn ($a) => [$a[0] => ['bill_id' => $a[0], 'amount' => $a[1]]])->all(),
            'items'        => [['payment_type' => 'Cash', 'amount' => $collected]],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function advancePayload(float|int $amount, array $overrides = []): array
    {
        return array_merge([
            'receipt_date' => '2026-11-18',
            'receipt_type' => 'Advance',
            'party_id'     => null,
            'party_name'   => 'Acme',
            'pay_type'     => 'Cash',
            'items'        => [['payment_type' => 'Cash', 'amount' => $amount]],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeReceipt(array $payload): void
    {
        (new MoneyReceiptController)->store(StoreMoneyReceiptRequest::create('/', 'POST', $payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateReceipt(ChevronMoneyReceipt $receipt, array $payload): void
    {
        (new MoneyReceiptController)->update(UpdateMoneyReceiptRequest::create('/', 'PUT', $payload), $receipt);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function storeExpectingErrors(array $payload): array
    {
        try {
            $this->storeReceipt($payload);
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $this->fail('Expected a validation error.');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function updateExpectingErrors(ChevronMoneyReceipt $receipt, array $payload): array
    {
        try {
            $this->updateReceipt($receipt, $payload);
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $this->fail('Expected a validation error.');
    }

    private function createSchema(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->decimal('advance_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('chevron_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->nullable();
            $table->string('party_name')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });

        Schema::create('chevron_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->nullable();
            $table->string('bill_date')->nullable();
            $table->string('job_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('job_no')->nullable();
            $table->string('party_name')->nullable();
            $table->text('sub_total')->nullable();
            $table->text('commission_on')->nullable();
            $table->text('commission_rate')->nullable();
            $table->text('commission_amount')->nullable();
            $table->text('assessable_value')->nullable();
            $table->text('invoice_value_bdt')->nullable();
            $table->text('total_payable')->nullable();
            $table->text('less_customs_duty_tax')->nullable();
            $table->text('income_tax_cnf_com')->nullable();
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->text('advance_amount')->nullable();
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0);
            $table->text('status')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
            $table->decimal('qty', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('chevron_money_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20)->unique();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('receipt_date');
            $table->string('receipt_type', 10)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name');
            $table->string('pay_type', 50);
            $table->decimal('payable_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_money_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id');
            $table->string('payment_type', 50);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account_no', 100)->nullable();
            $table->string('cheque_card_holder')->nullable();
            $table->string('cheque_card_no', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('cheque_date')->nullable();
            $table->timestamps();
        });

        Schema::create('chevron_money_receipt_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id');
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }
}

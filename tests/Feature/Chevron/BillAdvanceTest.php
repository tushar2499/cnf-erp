<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\AdvanceBalanceController;
use App\Http\Controllers\Chevron\BillController;
use App\Http\Controllers\Chevron\MoneyReceiptController;
use App\Http\Requests\Chevron\Bill\DestroyBillRequest;
use App\Http\Requests\Chevron\Bill\IndexBillRequest;
use App\Http\Requests\Chevron\Bill\PrintBillRequest;
use App\Http\Requests\Chevron\Bill\ShowBillRequest;
use App\Http\Requests\Chevron\Bill\StoreBillRequest;
use App\Http\Requests\Chevron\Bill\UpdateBillRequest;
use App\Http\Requests\Chevron\MoneyReceipt\DestroyMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\IndexMoneyReceiptRequest;
use App\Http\Requests\Chevron\MoneyReceipt\StoreMoneyReceiptRequest;
use App\Models\Chevron\ChevronBill;
use App\Models\Chevron\ChevronCustomer;
use App\Models\Chevron\ChevronJob;
use App\Models\Chevron\ChevronMoneyReceipt;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class BillAdvanceTest extends TestCase
{
    private const BRANCH = 5;

    private const TABLES = [
        'chevron_money_receipt_allocations', 'chevron_money_receipt_items', 'chevron_money_receipts',
        'chevron_bill_items', 'chevron_bills', 'chevron_jobs', 'chevron_customers',
        'chevron_expense_heads', 'chevron_expense_categories', 'chevron_branches',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        session(['active_branch_id' => self::BRANCH]);
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_a_bill_draws_its_advance_from_the_customers_balance(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);

        $this->storeBill($this->billPayload($job, 1200, 500));

        $bill = ChevronBill::firstOrFail();
        $this->assertSame(7, (int) $bill->customer_id);
        $this->assertEquals(500, $bill->advance_amount);
        $this->assertEquals(700, $bill->due_amount);
        $this->assertSame('Submitted', $bill->status);
        $this->assertEquals(500, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
        $this->assertEquals(500, ChevronCustomer::findOrFail(7)->advance_balance);
    }

    public function test_advance_covering_the_whole_bill_approves_it(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(2000, 7);

        $this->storeBill($this->billPayload($job, 1200, 1200));

        $this->assertEquals(0, ChevronBill::firstOrFail()->due_amount);
        $this->assertSame('Approved', ChevronBill::firstOrFail()->status);
        $this->assertEquals(800, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
    }

    public function test_advance_cannot_exceed_the_bills_net_payable(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(5000, 7);

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, 1200.01));

        $this->assertStringContainsString('cannot exceed the bill\'s net payable amount (1,200.00)', $errors['advance_amount'][0]);
        $this->assertSame(0, ChevronBill::count());
        $this->assertEquals(5000, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
    }

    public function test_advance_cannot_exceed_the_customers_available_balance(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(400, 7);

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, 500));

        $this->assertStringContainsString('exceeds the available advance balance of Acme (400.00)', $errors['advance_amount'][0]);
        $this->assertSame(0, ChevronBill::count());
    }

    public function test_advance_of_another_branch_is_not_available(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        session(['active_branch_id' => 99]);
        $this->receiveAdvance(1000, 7);
        session(['active_branch_id' => self::BRANCH]);

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, 100));

        $this->assertArrayHasKey('advance_amount', $errors);
    }

    public function test_negative_advance_is_rejected(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, -5));

        $this->assertSame('Advance amount cannot be negative.', $errors['advance_amount'][0]);
    }

    public function test_a_job_without_a_customer_cannot_use_advance_but_can_still_be_billed(): void
    {
        $job = $this->makeJobFor(null, 'Unknown Party');

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, 100));
        $this->assertStringContainsString('no linked customer', $errors['advance_amount'][0]);

        $this->storeBill($this->billPayload($job, 1200, 0));
        $this->assertNull(ChevronBill::firstOrFail()->customer_id);
    }

    public function test_customer_is_found_by_party_name_when_the_job_has_no_customer_link(): void
    {
        $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor(null, 'Acme');
        $this->receiveAdvance(1000, 7);

        $this->storeBill($this->billPayload($job, 1200, 300));

        $this->assertSame(7, (int) ChevronBill::firstOrFail()->customer_id);
        $this->assertEquals(700, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
    }

    public function test_an_ambiguous_party_name_does_not_link_a_customer(): void
    {
        $this->makeCustomer(7, 'Acme');
        $this->makeCustomer(8, 'Acme');
        $job = $this->makeJobFor(null, 'Acme');

        $errors = $this->storeBillExpectingErrors($this->billPayload($job, 1200, 100));

        $this->assertStringContainsString('no linked customer', $errors['advance_amount'][0]);
    }

    public function test_editing_a_bill_gets_its_own_advance_back(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 800));
        $bill = ChevronBill::firstOrFail();

        $this->updateBill($bill, $this->billPayload($job, 1200, 1000));
        $this->assertEquals(1000, $bill->fresh()->advance_amount);
        $this->assertEquals(0, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));

        $errors = $this->updateBillExpectingErrors($bill->fresh(), $this->billPayload($job, 1200, 1000.01));
        $this->assertArrayHasKey('advance_amount', $errors);
        $this->assertEquals(1000, $bill->fresh()->advance_amount);

        $this->updateBill($bill->fresh(), $this->billPayload($job, 1200, 100));
        $this->assertEquals(900, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
        $this->assertEquals(900, ChevronCustomer::findOrFail(7)->advance_balance);
    }

    public function test_moving_a_bill_to_another_customers_job_moves_the_advance(): void
    {
        $acmeJob = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $betaJob = $this->makeJobFor($this->makeCustomer(8, 'Beta'), 'Beta', 'JOB-002');
        $this->receiveAdvance(1000, 7);
        $this->receiveAdvance(1000, 8, 'Beta');
        $this->storeBill($this->billPayload($acmeJob, 1200, 600));
        $bill = ChevronBill::firstOrFail();

        $this->updateBill($bill, $this->billPayload($betaJob, 1200, 300));

        $this->assertSame(8, (int) $bill->fresh()->customer_id);
        $this->assertEquals(1000, ChevronCustomer::findOrFail(7)->advance_balance);
        $this->assertEquals(700, ChevronCustomer::findOrFail(8)->advance_balance);
    }

    public function test_deleting_a_bill_returns_its_advance_to_the_customer(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 600));
        $this->assertEquals(400, ChevronCustomer::findOrFail(7)->advance_balance);

        $response = (new BillController)->destroy(DestroyBillRequest::create('/', 'DELETE'), ChevronBill::firstOrFail());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(1000, ChevronMoneyReceipt::advanceBalance(self::BRANCH, 7));
        $this->assertEquals(1000, ChevronCustomer::findOrFail(7)->advance_balance);
    }

    public function test_advance_used_on_a_bill_is_not_available_to_money_receipt_adjustments(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 800));
        $other = ChevronBill::create(['bill_no' => 'BIL000900', 'branch_id' => self::BRANCH, 'party_name' => 'Acme', 'net_payable' => 500, 'due_amount' => 500]);

        try {
            $this->storeReceipt([
                'receipt_date' => '2026-11-18',
                'receipt_type' => 'Bill',
                'party_id'     => 7,
                'party_name'   => 'Acme',
                'pay_type'     => 'Mixed',
                'allocations'  => [$other->id => ['bill_id' => $other->id, 'amount' => 300]],
                'items'        => [['payment_type' => ChevronMoneyReceipt::ADVANCE_ADJUSTMENT, 'amount' => 300]],
            ]);
            $this->fail('Expected a validation error.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('available advance balance (200.00)', $e->errors()['items'][0]);
        }
    }

    public function test_an_advance_receipt_already_consumed_by_a_bill_cannot_be_deleted(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 800));

        $response = (new MoneyReceiptController)->destroy(DestroyMoneyReceiptRequest::create('/', 'DELETE'), ChevronMoneyReceipt::firstOrFail());

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(1, ChevronMoneyReceipt::count());
    }

    public function test_advance_info_reports_the_job_collection_total_balance_and_limit(): void
    {
        $acme = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor($acme);
        $other = $this->makeJobFor($acme, 'Acme', 'JOB-002');
        $this->receiveAdvance(1000, 7, 'Acme', [[$job->id, 300], [$other->id, 100]]);
        $this->storeBill($this->billPayload($job, 1200, 250));
        $bill = ChevronBill::firstOrFail();

        $new = $this->advanceInfo(['job_id' => $job->id]);
        $editing = $this->advanceInfo(['job_id' => $job->id, 'bill_id' => $bill->id]);

        $this->assertSame(7, $new['customer_id']);
        $this->assertSame('Acme', $new['customer_name']);
        $this->assertEquals(300, $new['job_advance']);
        $this->assertEquals(750, $new['total_advance']);
        $this->assertEquals(750, $new['available']);
        $this->assertEquals(1000, $editing['total_advance']);
        $this->assertEquals(1000, $editing['available']);
        $this->assertSame(route('chevron.cnf.advance-balances.show', 7), $new['statement_url']);
    }

    public function test_advance_info_lists_the_job_collections_date_wise_oldest_first(): void
    {
        $acme = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor($acme);
        $other = $this->makeJobFor($acme, 'Acme', 'JOB-002');
        $this->receiveAdvance(500, 7, 'Acme', [[$job->id, 200], [$other->id, 100]], '2026-11-18');
        $this->receiveAdvance(400, 7, 'Acme', [[$job->id, 400]], '2026-11-10');
        $this->receiveAdvance(900, 7);

        $info = $this->advanceInfo(['job_id' => $job->id]);

        $this->assertSame(['10-Nov-2026', '18-Nov-2026'], array_column($info['job_collections'], 'date'));
        $this->assertSame(['MR000002', 'MR000001'], array_column($info['job_collections'], 'receipt_no'));
        $this->assertEquals([400, 200], array_column($info['job_collections'], 'amount'));
        $this->assertSame(
            route('chevron.cnf.money-receipts.show', ChevronMoneyReceipt::where('receipt_no', 'MR000002')->firstOrFail()->id),
            $info['job_collections'][0]['receipt_url']
        );
        $this->assertEquals(600, $info['job_advance']);
        $this->assertEquals(1800, $info['total_advance']);
    }

    public function test_advance_info_gives_no_receipt_links_without_receipt_permission(): void
    {
        $acme = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor($acme);
        $this->receiveAdvance(500, 7, 'Acme', [[$job->id, 500]]);

        $info = $this->advanceInfo(['job_id' => $job->id], false);

        $this->assertCount(1, $info['job_collections']);
        $this->assertSame('MR000001', $info['job_collections'][0]['receipt_no']);
        $this->assertNull($info['job_collections'][0]['receipt_url']);
    }

    public function test_advance_info_has_no_job_collections_when_the_job_received_no_advance(): void
    {
        $acme = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor($acme);
        $other = $this->makeJobFor($acme, 'Acme', 'JOB-002');
        $this->receiveAdvance(500, 7, 'Acme', [[$other->id, 500]]);
        $this->receiveAdvance(300, 7);

        $info = $this->advanceInfo(['job_id' => $job->id]);

        $this->assertSame([], $info['job_collections']);
        $this->assertEquals(0, $info['job_advance']);
        $this->assertEquals(800, $info['total_advance']);
    }

    public function test_advance_info_for_a_job_without_a_customer_has_nothing_to_apply(): void
    {
        $job = $this->makeJobFor(null, 'Unknown Party');

        $info = $this->advanceInfo(['job_id' => $job->id]);

        $this->assertNull($info['customer_id']);
        $this->assertSame([], $info['job_collections']);
        $this->assertEquals(0, $info['available']);
        $this->assertNull($info['statement_url']);
        $this->assertEquals(0, $this->advanceInfo([])['available']);
    }

    public function test_a_hand_entered_advance_on_an_old_unlinked_bill_stays_valid(): void
    {
        $job = $this->makeJobFor(null, 'Unknown Party');
        $legacy = ChevronBill::create([
            'bill_no'     => 'BIL000100', 'branch_id' => self::BRANCH, 'job_id' => $job->id,
            'net_payable' => 1200, 'advance_amount' => 400, 'due_amount' => 800, 'received_amount' => 0,
        ]);

        $this->updateBill($legacy, $this->billPayload($job, 1200, 400));
        $this->assertEquals(400, $legacy->fresh()->advance_amount);

        $errors = $this->updateBillExpectingErrors($legacy->fresh(), $this->billPayload($job, 1200, 401));
        $this->assertArrayHasKey('advance_amount', $errors);
        $this->assertEquals(400, $this->advanceInfo(['job_id' => $job->id, 'bill_id' => $legacy->id])['available']);
    }

    public function test_advance_statement_lists_the_advance_used_on_a_bill(): void
    {
        $customer = $this->makeCustomer(7, 'Acme');
        $job = $this->makeJobFor($customer);
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 600));
        $request = IndexMoneyReceiptRequest::create('/', 'GET');
        $user = $this->userWithAllPermissions();
        $request->setUserResolver(fn () => $user);
        $this->actingAs($user);

        $view = (new AdvanceBalanceController)->show($request, $customer);
        $html = $view->render();

        $this->assertEquals(600, $view->getData()['adjusted']);
        $this->assertEquals(400, $view->getData()['balance']);
        $this->assertSame([1000.0, 400.0], $view->getData()['entries']->pluck('balance')->all());
        $this->assertStringContainsString('BIL000001', $html);
        $this->assertStringContainsString('JOB-001', $html);
        $this->assertStringContainsString('Adjusted to bill', $html);
    }

    public function test_advance_balance_list_counts_advance_used_on_bills(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 600));

        $request = IndexMoneyReceiptRequest::create('/', 'GET', ['draw' => 1, 'start' => 0, 'length' => 50, 'search' => ['value' => '', 'regex' => 'false']]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->app->instance('request', $request);
        $this->app->forgetInstance('datatables.request');
        $row = json_decode((new AdvanceBalanceController)->index($request)->getContent(), true)['data'][0];

        $this->assertSame(['1,000.00', '600.00', '400.00'], [$row['advance_received'], $row['advance_adjusted'], $row['balance']]);
    }

    public function test_request_rules_reject_a_negative_or_non_numeric_advance(): void
    {
        foreach ([StoreBillRequest::class, UpdateBillRequest::class] as $class) {
            $rules = (new $class)->rules();

            $this->assertTrue(validator(['advance_amount' => '-1'], ['advance_amount' => $rules['advance_amount']])->fails());
            $this->assertTrue(validator(['advance_amount' => 'abc'], ['advance_amount' => $rules['advance_amount']])->fails());
            $this->assertFalse(validator(['advance_amount' => '250.50'], ['advance_amount' => $rules['advance_amount']])->fails());
            $this->assertFalse(validator(['advance_amount' => null], ['advance_amount' => $rules['advance_amount']])->fails());
        }
    }

    public function test_print_shows_the_advance_payment_with_the_receipts_collected_for_the_job(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7, 'Acme', [[$job->id, 1000]], '2026-11-10');
        $this->storeBill($this->billPayload($job, 1200, 500));

        $html = $this->printBill(ChevronBill::firstOrFail());

        $this->assertStringContainsString('LESS ADVANCE PAYMENT', $html);
        $this->assertStringContainsString('Received 10 Nov 2026', $html);
        $this->assertStringContainsString('MR000001: 1,000.00', $html);
        $this->assertStringContainsString('500.00', $html);
        $this->assertStringContainsString('700.00', $html);
        $this->assertStringContainsString('TOTAL', $html);
    }

    public function test_print_shows_the_advance_payment_line_without_receipts_when_it_came_from_the_general_balance(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1200, 400));

        $html = $this->printBill(ChevronBill::firstOrFail());

        $this->assertStringContainsString('LESS ADVANCE PAYMENT', $html);
        $this->assertStringContainsString('400.00', $html);
        $this->assertStringNotContainsString('Received ', $html);
    }

    public function test_print_without_advance_keeps_the_original_layout(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7, 'Acme', [[$job->id, 1000]]);
        $this->storeBill($this->billPayload($job, 1200, 0));

        $html = $this->printBill(ChevronBill::firstOrFail());

        $this->assertStringNotContainsString('ADVANCE', $html);
        $this->assertStringNotContainsString('MR000001', $html);
        $this->assertStringContainsString('BALANCE', $html);
    }

    public function test_print_opens_as_a_preview_with_a_print_button_and_does_not_print_on_load(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->storeBill($this->billPayload($job, 1200, 0));

        $html = $this->printBill(ChevronBill::firstOrFail());

        $this->assertStringContainsString('<button type="button" class="btn-print" onclick="window.print()">', $html);
        $this->assertStringContainsString('onclick="window.close()"', $html);
        $this->assertStringContainsString('.no-print { display: none !important; }', $html);
        $this->assertStringNotContainsString('window.onload', $html);
        $this->assertStringNotContainsString('afterprint', $html);
    }

    public function test_print_shows_the_advance_after_the_tax_deductions(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7);
        $this->storeBill($this->billPayload($job, 1000, 300, ['sub_total' => 1200, 'less_customs_duty_tax' => 200]));

        $html = $this->printBill(ChevronBill::firstOrFail());

        $this->assertLessThan(strpos($html, 'LESS ADVANCE PAYMENT'), strpos($html, 'LESS CUSTOMS DUTY'));
        $this->assertLessThan(strpos($html, 'BALANCE'), strpos($html, 'LESS ADVANCE PAYMENT'));
    }

    public function test_show_lists_payments_received_and_the_outstanding_balance(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->storeBill($this->billPayload($job, 1000, 0));
        $bill = ChevronBill::firstOrFail();
        $this->collectOnBill($bill, 300);

        $data = $this->showData($bill->fresh());

        $this->assertCount(1, $data['receiptCollections']);
        $this->assertEquals(300, $data['receiptCollections']->first()['amount']);
        $this->assertSame('Cash', $data['receiptCollections']->first()['pay_type']);
    }

    public function test_show_hands_the_view_the_job_advance_collections_and_permission_flags(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7, 'Acme', [[$job->id, 600]]);
        $this->storeBill($this->billPayload($job, 1200, 500));

        $view = $this->showView(ChevronBill::firstOrFail(), true);
        $denied = $this->showView(ChevronBill::firstOrFail(), false);

        $this->assertEquals(600, $view->getData()['advanceCollections']->sum('amount'));
        $this->assertTrue($view->getData()['canEdit']);
        $this->assertTrue($view->getData()['canPrint']);
        $this->assertTrue($view->getData()['canOpenReceipts']);
        $this->assertFalse($denied->getData()['canEdit']);
        $this->assertFalse($denied->getData()['canPrint']);
        $this->assertFalse($denied->getData()['canOpenReceipts']);
    }

    public function test_show_renders_the_advance_collection_and_links_the_receipts(): void
    {
        $job = $this->makeJobFor($this->makeCustomer(7, 'Acme'));
        $this->receiveAdvance(1000, 7, 'Acme', [[$job->id, 600]]);
        $this->storeBill($this->billPayload($job, 1200, 500));
        $bill = ChevronBill::firstOrFail();
        $this->collectOnBill($bill, 200);

        $this->actingAs($this->userWithAllPermissions());
        $html = $this->showView($bill->fresh(), true)->render();

        $this->assertStringContainsString('Advance Payment Collection', $html);
        $this->assertStringContainsString('Payments Received Against This Bill', $html);
        $this->assertStringContainsString('Less advance payment adjusted', $html);
        $this->assertStringContainsString(route('chevron.cnf.money-receipts.show', ChevronMoneyReceipt::where('receipt_type', 'Advance')->value('id')), $html);
        $this->assertStringContainsString(route('chevron.cnf.bills.print', $bill->id), $html);
        $this->assertStringContainsString('Partially paid', $html);
    }

    public function test_show_refuses_a_bill_of_another_branch(): void
    {
        $bill = ChevronBill::create(['bill_no' => 'BIL000777', 'branch_id' => self::BRANCH + 1]);

        $this->expectException(NotFoundHttpException::class);

        $this->showView($bill, true);
    }

    public function test_show_needs_the_bill_list_permission(): void
    {
        $request = ShowBillRequest::create('/', 'GET');

        $request->setUserResolver(fn () => $this->userWithPermissions(false));
        $this->assertFalse($request->authorize());

        $request->setUserResolver(fn () => $this->userWithPermissions(true));
        $this->assertTrue($request->authorize());
    }

    public function test_the_bill_list_action_column_links_to_the_view_page(): void
    {
        ChevronBill::create(['bill_no' => 'BIL000001', 'branch_id' => self::BRANCH]);
        $request = IndexBillRequest::create('/', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $user = $this->userWithAllPermissions();
        $request->setUserResolver(fn () => $user);

        $rows = (new BillController)->index($request)->getData(true)['data'];

        $this->assertStringContainsString(route('chevron.cnf.bills.show', $rows[0]['id']), $rows[0]['action']);
        $this->assertStringContainsString(route('chevron.cnf.bills.print', $rows[0]['id']), $rows[0]['action']);
    }

    private function printBill(ChevronBill $bill): string
    {
        return (new BillController)->print(PrintBillRequest::create('/', 'GET'), $bill)->render();
    }

    /**
     * @return array<string, mixed>
     */
    private function showData(ChevronBill $bill): array
    {
        return $this->showView($bill, true)->getData();
    }

    private function showView(ChevronBill $bill, bool $permissions): View
    {
        $request = ShowBillRequest::create('/', 'GET');
        $user = $this->userWithPermissions($permissions);
        $request->setUserResolver(fn () => $user);

        return (new BillController)->show($request, $bill);
    }

    private function collectOnBill(ChevronBill $bill, float|int $amount): void
    {
        $this->storeReceipt([
            'receipt_date' => '2026-11-18',
            'receipt_type' => 'Bill',
            'party_id'     => $bill->customer_id,
            'party_name'   => $bill->party_name,
            'pay_type'     => 'Cash',
            'allocations'  => [$bill->id => ['bill_id' => $bill->id, 'amount' => $amount]],
            'items'        => [['payment_type' => 'Cash', 'amount' => $amount]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function advanceInfo(array $query, bool $permissions = true): array
    {
        $request = IndexBillRequest::create('/', 'GET', $query);
        $user = $this->userWithPermissions($permissions);
        $request->setUserResolver(fn () => $user);

        return (new BillController)->advanceInfo($request)->getData(true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeBill(array $payload): void
    {
        (new BillController)->store(StoreBillRequest::create('/', 'POST', $payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateBill(ChevronBill $bill, array $payload): void
    {
        (new BillController)->update(UpdateBillRequest::create('/', 'PUT', $payload), $bill);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function storeBillExpectingErrors(array $payload): array
    {
        try {
            $this->storeBill($payload);
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $this->fail('Expected a validation error.');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function updateBillExpectingErrors(ChevronBill $bill, array $payload): array
    {
        try {
            $this->updateBill($bill, $payload);
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $this->fail('Expected a validation error.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeReceipt(array $payload): void
    {
        (new MoneyReceiptController)->store(StoreMoneyReceiptRequest::create('/', 'POST', $payload));
    }

    /**
     * @param  array<int, array{0: int, 1: float|int}>  $jobs
     */
    private function receiveAdvance(float|int $amount, int $partyId, string $partyName = 'Acme', array $jobs = [], string $date = '2026-11-18'): void
    {
        $this->storeReceipt([
            'receipt_date'    => $date,
            'receipt_type'    => 'Advance',
            'party_id'        => $partyId,
            'party_name'      => $partyName,
            'pay_type'        => 'Cash',
            'items'           => [['payment_type' => 'Cash', 'amount' => $amount]],
            'job_allocations' => collect($jobs)->mapWithKeys(fn ($j) => [$j[0] => ['job_id' => $j[0], 'amount' => $j[1]]])->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function billPayload(ChevronJob $job, float|int $netPayable, float|int $advance, array $overrides = []): array
    {
        return array_merge([
            'bill_date'        => '2026-11-18',
            'job_id'           => $job->id,
            'job_no'           => $job->job_no,
            'party_name'       => $job->party_name,
            'sub_total'        => $netPayable,
            'commission_on'    => 'ASSESSABLE',
            'commission_rate'  => 0,
            'assessable_value' => 0,
            'advance_amount'   => $advance,
            'rows'             => [['expense_category_id' => null, 'expense_head_id' => null, 'amount' => $netPayable]],
        ], $overrides);
    }

    private function makeCustomer(int $id, string $name): ChevronCustomer
    {
        $customer = new ChevronCustomer(['name' => $name, 'customer_id' => 'C'.$id]);
        $customer->forceFill(['id' => $id])->save();

        return $customer;
    }

    private function makeJobFor(?ChevronCustomer $customer, string $partyName = 'Acme', string $jobNo = 'JOB-001'): ChevronJob
    {
        return ChevronJob::create([
            'job_no'      => $jobNo,
            'party_name'  => $customer?->name ?? $partyName,
            'customer_id' => $customer?->id,
            'branch_id'   => self::BRANCH,
        ]);
    }

    private function userWithAllPermissions(): User
    {
        return $this->userWithPermissions(true);
    }

    private function userWithPermissions(bool $granted): User
    {
        $user = new class extends User
        {
            public bool $granted = true;

            public function hasPermission(string $permission): bool
            {
                return $this->granted;
            }
        };
        $user->granted = $granted;

        return $user;
    }

    private function createSchema(): void
    {
        Schema::create('chevron_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chevron_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_bill')->default(true);
        });

        Schema::create('chevron_expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->boolean('is_bill')->default(true);
            $table->boolean('is_job')->default(false);
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('chevron_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->decimal('advance_balance', 15, 2)->default(0);
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

            foreach ([
                'bill_no', 'bill_type', 'bill_date', 'delivery_date', 'job_no', 'party_name', 'party_address',
                'goods_description', 'mate_code', 'po_no', 'quantity', 'quantity_unit', 'quantity_remark',
                'gross_weight', 'gross_weight_unit', 'lc_no', 'lc_ref', 'be_no', 'be_date', 'invoice_no',
                'invoice_ref', 'invoice_date', 'bl_no', 'bl_ref', 'bl_date', 'currency_type', 'remarks',
                'commission_on', 'status',
            ] as $column) {
                $table->string($column)->nullable();
            }

            foreach ([
                'assessable_value', 'invoice_value_bdt', 'invoice_value', 'currency_rate', 'sub_total',
                'commission_rate', 'commission_amount', 'total_payable', 'less_customs_duty_tax',
                'income_tax_cnf_com', 'net_payable', 'advance_amount', 'due_amount', 'received_amount',
            ] as $column) {
                $table->decimal($column, 15, 2)->nullable()->default(0);
            }

            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
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

<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\AccVoucher;
use App\Models\Company;
use App\Services\Accounting\ReportService;
use App\Services\Accounting\VoucherService;
use Illuminate\Validation\ValidationException;

class VoucherAndReportTest extends AccountingTestCase
{
    private function voucher(string $type, string $date, array $lines, ?int $companyId = null): AccVoucher
    {
        return app(VoucherService::class)->create([
            'company_id' => $companyId ?? $this->freights->id, 'voucher_type' => $type, 'voucher_date' => $date,
            'narration'  => 'Test', 'lines' => $lines,
        ], null);
    }

    private function line(string $account, float $debit, float $credit, array $extra = []): array
    {
        return ['account_id' => $this->acc[$account]->id, 'debit' => $debit, 'credit' => $credit] + $extra;
    }

    /** Opening capital, a sale with VAT, a receipt and an expense. */
    private function sampleBooks(): void
    {
        $this->voucher('journal', '2026-09-30', [$this->line('bank', 100000, 0), $this->line('capital', 0, 100000)]);
        $this->voucher('journal', '2026-10-05', [
            $this->line('ar', 52500, 0, ['party_type' => 'customer', 'party_id' => 7, 'party_name' => 'ABC Garments']),
            $this->line('sales', 0, 50000), $this->line('vat', 0, 2500),
        ]);
        $this->voucher('receipt', '2026-10-09', [
            $this->line('bank', 52500, 0),
            $this->line('ar', 0, 52500, ['party_type' => 'customer', 'party_id' => 7, 'party_name' => 'ABC Garments']),
        ]);
        $this->voucher('payment', '2026-10-15', [$this->line('rent', 20000, 0), $this->line('bank', 0, 20000)]);
    }

    public function test_voucher_validation_rules(): void
    {
        $cases = [
            'unbalanced'    => [$this->line('bank', 100, 0), $this->line('capital', 0, 90)],
            'single line'   => [$this->line('bank', 100, 0)],
            'both sides'    => [['account_id' => $this->acc['bank']->id, 'debit' => 100, 'credit' => 100], $this->line('capital', 0, 100)],
            'group account' => [['account_id' => $this->acc['bank']->parent_id, 'debit' => 100, 'credit' => 0], $this->line('capital', 0, 100)],
        ];

        foreach ($cases as $name => $lines) {
            try {
                $this->voucher('journal', '2026-10-01', $lines);
                $this->fail("{$name} should be rejected");
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame(0, AccVoucher::count());
    }

    public function test_numbering_is_per_company_type_and_year(): void
    {
        $chevronId = Company::where('slug', 'chevron-lines')->value('id');
        $lines = [$this->line('bank', 100, 0), $this->line('capital', 0, 100)];

        $this->assertSame('JV-2026-00001', $this->voucher('journal', '2026-10-01', $lines)->voucher_no);
        $this->assertSame('JV-2026-00002', $this->voucher('journal', '2026-10-02', $lines)->voucher_no);
        $this->assertSame('PV-2026-00001', $this->voucher('payment', '2026-10-02', $lines)->voucher_no);
        $this->assertSame('JV-2027-00001', $this->voucher('journal', '2027-01-02', $lines)->voucher_no);
        $this->assertSame('JV-2026-00001', $this->voucher('journal', '2026-10-02', $lines, $chevronId)->voucher_no);
    }

    public function test_ledger_running_balance_and_cancelled_vouchers_are_excluded(): void
    {
        $this->sampleBooks();
        $cancelled = $this->voucher('payment', '2026-10-20', [$this->line('rent', 999, 0), $this->line('bank', 0, 999)]);
        app(VoucherService::class)->cancel($cancelled, 'Wrong amount', null);

        $ledger = app(ReportService::class)->ledger($this->acc['bank'], $this->freights->id, '2026-10-01', '2026-10-31');

        $this->assertSame(100000.0, $ledger['opening']);
        $this->assertCount(2, $ledger['rows']);
        $this->assertSame(152500.0, $ledger['rows'][0]['balance']);
        $this->assertSame('Accounts Receivable', $ledger['rows'][0]['particulars']);
        $this->assertSame(132500.0, $ledger['closing']);
    }

    public function test_party_ledger_filters_by_customer(): void
    {
        $this->sampleBooks();
        $this->voucher('journal', '2026-10-06', [
            $this->line('ar', 1000, 0, ['party_type' => 'customer', 'party_id' => 8, 'party_name' => 'XYZ Textiles']),
            $this->line('sales', 0, 1000),
        ]);

        $ledger = app(ReportService::class)->ledger($this->acc['ar'], null, '2026-10-01', '2026-10-31', 'customer', 8);

        $this->assertCount(1, $ledger['rows']);
        $this->assertSame(1000.0, $ledger['closing']);
    }

    public function test_trial_balance_profit_loss_and_balance_sheet_agree(): void
    {
        $this->sampleBooks();
        $reports = app(ReportService::class);

        $tb = $reports->trialBalance($this->freights->id, '2026-10-01', '2026-10-31');
        $this->assertSame($tb['totals']['closing_dr'], $tb['totals']['closing_cr']);
        $this->assertSame(100000.0, $tb['totals']['opening_dr']);

        $level1 = collect($reports->trialBalance($this->freights->id, '2026-10-01', '2026-10-31', 1)['rows'])->keyBy('code');
        $this->assertSame(132500.0, $level1['01']['closing']);

        $pl = $reports->profitLoss($this->freights->id, '2026-10-01', '2026-10-31');
        $this->assertSame(50000.0, $pl['income']['total']);
        $this->assertSame(20000.0, $pl['expense']['total']);
        $this->assertSame(30000.0, $pl['net_profit']);
        $this->assertSame('Indirect Expenses', $pl['expense']['groups'][0]['name']);

        $bs = $reports->balanceSheet($this->freights->id, '2026-10-31');
        $this->assertSame(132500.0, $bs['total_assets']);
        $this->assertSame(30000.0, $bs['profit']);
        $this->assertSame(0.0, $bs['difference']);

        $this->assertSame(0.0, $reports->balanceSheet(Company::where('slug', 'chevron-lines')->value('id'), '2026-10-31')['total_assets']);
    }

    public function test_cash_book_and_day_book(): void
    {
        $this->sampleBooks();
        $reports = app(ReportService::class);

        $books = $reports->cashBook(null, '2026-10-01', '2026-10-31');
        $this->assertCount(1, $books);
        $this->assertSame('0102020001', $books[0]['account']->code);
        $this->assertSame(52500.0, $books[0]['total_debit']);
        $this->assertSame(20000.0, $books[0]['total_credit']);

        $this->assertCount(3, $reports->dayBook($this->freights->id, '2026-10-01', '2026-10-31'));
        $this->assertCount(1, $reports->dayBook($this->freights->id, '2026-10-01', '2026-10-31', 'payment'));
    }

    public function test_manual_voucher_http_create_edit_cancel_and_auto_voucher_is_not_editable(): void
    {
        $user = $this->superUser();

        $response = $this->actingAs($user)->postJson(route('admin.accounting.vouchers.store'), [
            'company_id' => $this->freights->id, 'voucher_type' => 'payment', 'voucher_date' => '2026-10-15', 'narration' => 'Rent',
            'lines'      => [$this->line('rent', 20000, 0), $this->line('bank', 0, 20000)],
        ])->assertOk();
        $voucher = AccVoucher::first();
        $response->assertJsonPath('redirect', route('admin.accounting.vouchers.show', $voucher));
        $this->assertSame($user->id, $voucher->created_by);

        $this->actingAs($user)->putJson(route('admin.accounting.vouchers.update', $voucher), [
            'company_id' => $this->freights->id, 'voucher_type' => 'payment', 'voucher_date' => '2026-10-16', 'narration' => 'Rent Oct',
            'lines'      => [$this->line('rent', 25000, 0), $this->line('cash', 0, 25000)],
        ])->assertOk();
        $voucher->refresh();
        $this->assertSame('PV-2026-00001', $voucher->voucher_no);
        $this->assertSame('25000.00', $voucher->total_amount);
        $this->assertSame($this->acc['cash']->id, $voucher->lines()->get()[1]->account_id);

        $this->actingAs($user)->postJson(route('admin.accounting.vouchers.cancel', $voucher), ['reason' => 'Duplicate'])->assertOk();
        $this->assertSame('cancelled', $voucher->fresh()->status);
        $this->actingAs($user)->postJson(route('admin.accounting.vouchers.cancel', $voucher), ['reason' => 'Again'])->assertStatus(422);

        $auto = $this->voucher('journal', '2026-10-01', [$this->line('bank', 100, 0), $this->line('capital', 0, 100)]);
        $auto->update(['is_auto' => true]);
        $this->actingAs($user)->putJson(route('admin.accounting.vouchers.update', $auto), [
            'company_id' => $this->freights->id, 'voucher_type' => 'journal', 'voucher_date' => '2026-10-01',
            'lines'      => [$this->line('bank', 200, 0), $this->line('capital', 0, 200)],
        ])->assertStatus(422);
    }

    public function test_report_and_voucher_pages_render(): void
    {
        $this->sampleBooks();
        $user = $this->superUser();
        $period = ['company_id' => $this->freights->id, 'from' => '2026-10-01', 'to' => '2026-10-31'];

        $this->actingAs($user)->get(route('admin.accounting.reports.ledger', $period + ['account_id' => $this->acc['bank']->id]))
            ->assertOk()->assertSee('132,500.00 Dr');
        $this->actingAs($user)->get(route('admin.accounting.reports.trial-balance', $period))->assertOk()->assertSee('Trial Balance');
        $this->actingAs($user)->get(route('admin.accounting.reports.profit-loss', $period))->assertOk()->assertSee('Net Profit');
        $this->actingAs($user)->get(route('admin.accounting.reports.balance-sheet', ['as_on' => '2026-10-31']))->assertOk()->assertSee('132,500.00');
        $this->actingAs($user)->get(route('admin.accounting.reports.day-book', $period))->assertOk()->assertSee('JV-2026-00002');
        $this->actingAs($user)->get(route('admin.accounting.reports.cash-book', $period))->assertOk()->assertSee('MBL Tejgaon');
        $this->actingAs($user)->get(route('admin.accounting.vouchers.show', AccVoucher::first()))->assertOk()->assertSee('JV-2026-00001');
        $this->actingAs($user)->get(route('admin.accounting.vouchers.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.accounting.vouchers.edit', AccVoucher::first()))->assertOk()->assertSee('Edit Voucher JV-2026-00001');
        $this->actingAs($user)->get(route('admin.accounting.vouchers.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.accounting.accounts.index'))->assertOk()->assertSee('Chart of Accounts');
        $this->actingAs($user)->get(route('admin.accounting.posting.index'))->assertOk()->assertSee('Load Entries');

        $this->map('freights.money_receipt', 'received', 'cash', 'ar', 'Cash');
        $this->actingAs($user)->get(route('admin.accounting.mappings.index'))->assertOk()
            ->assertSee('Local Logistics — Money Receipt')->assertSee('0102010001 – Cash In Hand')->assertSee('No rule yet');
    }

    public function test_mapping_rules_are_validated(): void
    {
        $user = $this->superUser();
        $rule = ['source_type' => 'freights.money_receipt', 'component' => 'received', 'match_key' => 'Cash',
            'debit_account_id' => $this->acc['cash']->id, 'credit_account_id' => $this->acc['ar']->id];

        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), $rule)->assertOk();
        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), $rule)->assertStatus(422)->assertJsonValidationErrors(['match_key']);
        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), ['match_key' => 'Bitcoin'] + $rule)->assertStatus(422)->assertJsonValidationErrors(['match_key']);
        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), ['component' => 'nope'] + $rule)->assertStatus(422)->assertJsonValidationErrors(['component']);
        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), ['credit_account_id' => $this->acc['cash']->id] + $rule)->assertStatus(422)->assertJsonValidationErrors(['credit_account_id']);
        $this->actingAs($user)->postJson(route('admin.accounting.mappings.store'), ['debit_account_id' => $this->acc['cash']->parent_id] + $rule)->assertStatus(422)->assertJsonValidationErrors(['debit_account_id']);
    }
}

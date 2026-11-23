<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\AccVoucher;
use App\Models\Company;
use App\Models\User;
use App\Services\Accounting\PostingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostingTest extends AccountingTestCase
{
    private function entries(): array
    {
        return collect(app(PostingService::class)->entries($this->freights, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31')))
            ->keyBy('ref')->all();
    }

    public function test_unmapped_parts_are_reported(): void
    {
        $this->customerBill();
        $this->map('freights.customer_bill', 'revenue', 'ar', 'sales');

        $entry = $this->entries()['freights.customer_bill:1'];

        $this->assertSame('unmapped', $entry['status']);
        $this->assertSame([['label' => 'VAT', 'amount' => 2500.0]], $entry['unmapped']);
    }

    public function test_mapping_builds_merged_lines_with_party_on_control_account(): void
    {
        $this->customerBill();
        $this->map('freights.customer_bill', 'revenue', 'ar', 'sales');
        $this->map('freights.customer_bill', 'vat', 'ar', 'vat');

        $entry = $this->entries()['freights.customer_bill:1'];
        $lines = collect($entry['lines'])->keyBy('account_code');

        $this->assertSame('ready', $entry['status']);
        $this->assertSame(52500.0, $lines['0102040000']['debit']);
        $this->assertSame('ABC Garments', $lines['0102040000']['party_name']);
        $this->assertSame(50000.0, $lines['0401010001']['credit']);
        $this->assertNull($lines['0401010001']['party_id']);
        $this->assertSame(2500.0, $lines['0201070001']['credit']);
    }

    public function test_specific_match_key_overrides_default_rule(): void
    {
        $this->moneyReceipt();
        $this->moneyReceipt(['receipt_no' => 'MR-0002', 'payment_mode' => 'Cash', 'amount_received' => 1000]);
        $this->map('freights.money_receipt', 'received', 'bank', 'ar');
        $this->map('freights.money_receipt', 'received', 'cash', 'ar', 'Cash');

        $entries = $this->entries();

        $this->assertSame('0102020001', $entries['freights.money_receipt:1']['lines'][0]['account_code']);
        $this->assertSame('0102010001', $entries['freights.money_receipt:2']['lines'][0]['account_code']);
    }

    public function test_post_creates_numbered_voucher_and_blocks_double_posting(): void
    {
        $this->moneyReceipt();
        $this->moneyReceipt(['receipt_no' => 'MR-0002', 'amount_received' => 1000]);
        $this->map('freights.money_receipt', 'received', 'bank', 'ar');
        $user = $this->superUser();
        $service = app(PostingService::class);
        [$from, $to] = [Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31')];

        $voucher = $service->post($this->freights, $from, $to, 'freights.money_receipt', 'freights.money_receipt:1', null, null, $user->id);
        $second = $service->post($this->freights, $from, $to, 'freights.money_receipt', 'freights.money_receipt:2', null, null, $user->id);

        $this->assertSame('RV-2026-00001', $voucher->voucher_no);
        $this->assertSame('RV-2026-00002', $second->voucher_no);
        $this->assertTrue($voucher->is_auto);
        $this->assertSame('receipt', $voucher->voucher_type);
        $this->assertSame('52500.00', $voucher->total_amount);
        $this->assertSame('posted', $this->entries()['freights.money_receipt:1']['status']);

        $this->expectException(ValidationException::class);
        $service->post($this->freights, $from, $to, 'freights.money_receipt', 'freights.money_receipt:1', null, null, $user->id);
    }

    public function test_changed_document_can_be_reposted_and_old_voucher_is_cancelled(): void
    {
        $id = $this->moneyReceipt();
        $this->map('freights.money_receipt', 'received', 'bank', 'ar');
        $service = app(PostingService::class);
        [$from, $to] = [Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31')];
        $old = $service->post($this->freights, $from, $to, 'freights.money_receipt', 'freights.money_receipt:1', null, null, null);

        DB::table('nas_freights_money_receipts')->where('id', $id)->update(['amount_received' => 50000, 'updated_at' => '2026-10-12 09:00:00']);
        $this->assertSame('changed', $this->entries()['freights.money_receipt:1']['status']);

        $new = $service->post($this->freights, $from, $to, 'freights.money_receipt', 'freights.money_receipt:1', null, null, null);

        $this->assertSame('cancelled', $old->fresh()->status);
        $this->assertNull($old->fresh()->source_ref);
        $this->assertSame('50000.00', $new->total_amount);
        $this->assertSame('posted', $this->entries()['freights.money_receipt:1']['status']);
    }

    public function test_edited_lines_must_balance(): void
    {
        $this->moneyReceipt();
        $this->map('freights.money_receipt', 'received', 'bank', 'ar');

        $this->expectException(ValidationException::class);
        app(PostingService::class)->post($this->freights, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'),
            'freights.money_receipt', 'freights.money_receipt:1', [
                ['account_id' => $this->acc['bank']->id, 'debit' => 52500, 'credit' => 0],
                ['account_id' => $this->acc['ar']->id, 'debit' => 0, 'credit' => 50000],
            ], null, null);
    }

    public function test_source_of_another_company_is_rejected(): void
    {
        $chevron = Company::where('slug', 'chevron-lines')->first();

        $this->expectException(ValidationException::class);
        app(PostingService::class)->entries($chevron, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'), 'freights.money_receipt');
    }

    public function test_http_post_with_edited_lines_and_bulk_post(): void
    {
        $this->moneyReceipt();
        $this->moneyReceipt(['receipt_no' => 'MR-0002', 'amount_received' => 1000]);
        $this->customerBill();
        $this->map('freights.money_receipt', 'received', 'bank', 'ar');
        $user = $this->superUser();
        $filter = ['company_id' => $this->freights->id, 'from' => '2026-10-01', 'to' => '2026-10-31'];

        $this->actingAs($user)->getJson(route('admin.accounting.posting.entries', $filter))
            ->assertOk()->assertJsonCount(3, 'entries');

        $this->actingAs($user)->postJson(route('admin.accounting.posting.post'), $filter + [
            'source_type' => 'freights.money_receipt', 'ref' => 'freights.money_receipt:1', 'narration' => 'Edited',
            'lines'       => [
                ['account_id' => $this->acc['cash']->id, 'debit' => 52500, 'credit' => 0],
                ['account_id' => $this->acc['ar']->id, 'debit' => 0, 'credit' => 52500, 'party_type' => 'customer', 'party_id' => 7, 'party_name' => 'ABC Garments'],
            ],
        ])->assertOk()->assertJsonPath('voucher_no', 'RV-2026-00001');

        $voucher = AccVoucher::with('lines')->first();
        $this->assertSame('Edited', $voucher->narration);
        $this->assertSame($this->acc['cash']->id, $voucher->lines[0]->account_id);

        // Bulk: receipt 2 posts, the unmapped bill is skipped.
        $this->actingAs($user)->postJson(route('admin.accounting.posting.post-many'), $filter + ['items' => [
            ['source_type' => 'freights.money_receipt', 'ref' => 'freights.money_receipt:2'],
            ['source_type' => 'freights.customer_bill', 'ref' => 'freights.customer_bill:1'],
        ]])->assertOk()->assertJsonPath('posted', 1)->assertJsonCount(1, 'skipped');
    }

    public function test_posting_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.accounting.posting.entries', ['company_id' => $this->freights->id, 'from' => '2026-10-01', 'to' => '2026-10-31']))
            ->assertForbidden();
    }
}

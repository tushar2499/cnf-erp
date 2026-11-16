<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\ItemController;
use App\Http\Requests\Chevron\Item\NextCodeItemRequest;
use App\Http\Requests\Chevron\Item\QuickStoreItemRequest;
use App\Http\Requests\Chevron\Item\StoreItemRequest;
use App\Models\Chevron\ChevronItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemCodeGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('chevron_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name')->nullable();
            $table->string('supplier')->nullable();
            $table->string('purchase_unit');
            $table->text('description')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->default('Active');
            $table->decimal('item_price', 15, 2)->default(0);
            $table->boolean('availability_in_po')->default(true);
            $table->boolean('availability_in_so')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chevron_items');

        parent::tearDown();
    }

    private function createItem(string $code): ChevronItem
    {
        return ChevronItem::create([
            'item_code'     => $code,
            'item_name'     => 'Item '.$code,
            'purchase_unit' => 'PCS',
        ]);
    }

    public function test_first_item_code_starts_at_1001(): void
    {
        $this->assertSame('ITEM-1001', ChevronItem::generateItemCode());
    }

    public function test_next_code_uses_numeric_suffix_not_string_order(): void
    {
        $this->createItem('ITEM-001');
        $this->createItem('ITEM-0002');

        $this->assertSame('ITEM-0003', ChevronItem::generateItemCode());
    }

    public function test_next_code_continues_past_a_wider_code(): void
    {
        $this->createItem('ITEM-9999');
        $this->createItem('ITEM-10000');

        $this->assertSame('ITEM-10001', ChevronItem::generateItemCode());
    }

    public function test_next_code_ignores_codes_outside_the_item_pattern(): void
    {
        $this->createItem('ITEM-0002');
        $this->createItem('ABCDE9999');
        $this->createItem('ITEM-XYZ');

        $this->assertSame('ITEM-0003', ChevronItem::generateItemCode());
    }

    public function test_next_code_endpoint_returns_the_generated_code(): void
    {
        $this->createItem('ITEM-001');
        $this->createItem('ITEM-0002');

        $response = (new ItemController)->nextCode(NextCodeItemRequest::create('/', 'GET'));

        $this->assertSame('ITEM-0003', $response->getData(true)['next_code']);
    }

    public function test_quick_store_creates_unique_codes_when_legacy_codes_exist(): void
    {
        $this->createItem('ITEM-001');
        $this->createItem('ITEM-0002');

        $first = (new ItemController)->quickStore(QuickStoreItemRequest::create('/', 'POST', [
            'item_name' => 'my good',
        ]))->getData(true);
        $second = (new ItemController)->quickStore(QuickStoreItemRequest::create('/', 'POST', [
            'item_name'     => 'another good',
            'purchase_unit' => 'KG',
            'item_price'    => 12.5,
        ]))->getData(true);

        $this->assertSame('ITEM-0003 — my good', $first['text']);
        $this->assertSame('ITEM-0004 — another good', $second['text']);
        $this->assertSame('KG', $second['purchase_unit']);
        $this->assertSame(4, ChevronItem::count());
        $this->assertSame('ITEM-0004', ChevronItem::find($second['id'])->item_code);
    }

    public function test_store_creates_item_with_the_next_code(): void
    {
        $this->createItem('ITEM-001');
        $this->createItem('ITEM-0002');

        (new ItemController)->store(StoreItemRequest::create('/', 'POST', [
            'item_name'     => 'Widget',
            'purchase_unit' => 'PCS',
        ]));

        $this->assertDatabaseHas('chevron_items', [
            'item_code' => 'ITEM-0003',
            'item_name' => 'Widget',
        ]);
    }
}

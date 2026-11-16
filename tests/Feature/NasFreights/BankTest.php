<?php

namespace Tests\Feature\NasFreights;

use App\Models\NasFreights\NasFreightsBank;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BankTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->actingAsSuperUser();
        $this->withSession($this->sessionWithBranch());
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super')->default(false);
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('cnf');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nas_freights_banks', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('account_type', 50);
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->string('routing_number', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function actingAsSuperUser(): void
    {
        $user = User::create([
            'name'      => 'Super User',
            'username'  => 'super',
            'email'     => 'super@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]);
        $user->forceFill(['is_super' => true])->save();

        $this->actingAs($user);
    }

    /**
     * @return array<string, int|string>
     */
    private function sessionWithBranch(): array
    {
        return [
            'active_company_id'        => 1,
            'active_company_slug'      => 'nas-freights',
            'active_company_name'      => 'NAS Freights',
            'active_company_type'      => 'freight',
            'nas_freights_branch_id'   => 1,
            'nas_freights_branch_name' => 'Corporate',
            'nas_freights_branch_code' => 'COR',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'account_name'   => 'NAS Freights Ltd.',
            'account_number' => '1234567890123',
            'account_type'   => 'Current',
            'bank_name'      => 'Islami Bank Bangladesh Ltd.',
            'bank_branch'    => 'Motijheel',
            'routing_number' => '125261234',
        ], $overrides);
    }

    public function test_index_page_renders_all_columns(): void
    {
        $this->get(route('nas-freights.settings.banks.index'))
            ->assertOk()
            ->assertSee('Account Name')
            ->assertSee('Account Number')
            ->assertSee('Account Type')
            ->assertSee('Bank Name')
            ->assertSee('Bank Branch')
            ->assertSee('Routing Number')
            ->assertSee(route('nas-freights.settings.banks.store'), false);
    }

    public function test_index_lists_banks_for_datatable_without_branch_scoping(): void
    {
        NasFreightsBank::factory()->create(['bank_name' => 'Alpha Bank', 'account_number' => '111']);
        NasFreightsBank::factory()->create(['bank_name' => 'Beta Bank', 'account_number' => '222']);

        $this->get(route('nas-freights.settings.banks.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['bank_name' => 'Alpha Bank'])
            ->assertJsonFragment(['bank_name' => 'Beta Bank']);
    }

    public function test_store_creates_bank_and_trims_values(): void
    {
        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload([
            'account_name'   => '  NAS Freights Ltd.  ',
            'account_number' => ' 1234567890123 ',
        ]))->assertOk()->assertJsonPath('message', 'Bank created.');

        $this->assertDatabaseHas('nas_freights_banks', [
            'account_name'   => 'NAS Freights Ltd.',
            'account_number' => '1234567890123',
            'account_type'   => 'Current',
            'bank_name'      => 'Islami Bank Bangladesh Ltd.',
            'bank_branch'    => 'Motijheel',
            'routing_number' => '125261234',
            'is_active'      => 1,
        ]);
    }

    public function test_store_allows_blank_branch_and_routing_number(): void
    {
        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload([
            'bank_branch'    => '',
            'routing_number' => '',
        ]))->assertOk();

        $this->assertDatabaseHas('nas_freights_banks', [
            'account_number' => '1234567890123',
            'bank_branch'    => null,
            'routing_number' => null,
        ]);
    }

    public function test_store_requires_mandatory_fields(): void
    {
        $this->postJson(route('nas-freights.settings.banks.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account_name', 'account_number', 'account_type', 'bank_name']);

        $this->assertSame(0, NasFreightsBank::count());
    }

    public function test_store_rejects_duplicate_account_number(): void
    {
        NasFreightsBank::factory()->create(['account_number' => '1234567890123']);

        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload())
            ->assertStatus(422)
            ->assertJsonPath('errors.account_number.0', 'This account number already exists.');
    }

    public function test_account_type_is_free_text_and_rejects_overlong_value(): void
    {
        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload(['account_type' => '  Student Savings  ']))
            ->assertOk();

        $this->assertDatabaseHas('nas_freights_banks', ['account_type' => 'Student Savings']);

        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload([
            'account_number' => '999',
            'account_type'   => str_repeat('A', 51),
        ]))->assertStatus(422)->assertJsonValidationErrors('account_type');
    }

    public function test_index_page_renders_account_type_as_text_input_not_dropdown(): void
    {
        $this->get(route('nas-freights.settings.banks.index'))
            ->assertOk()
            ->assertSee('<input type="text" id="fieldAccountType"', false)
            ->assertDontSee('<select id="fieldAccountType"', false);
    }

    public function test_store_can_create_inactive_bank(): void
    {
        $this->postJson(route('nas-freights.settings.banks.store'), $this->payload(['is_active' => 0]))->assertOk();

        $this->assertDatabaseHas('nas_freights_banks', ['account_number' => '1234567890123', 'is_active' => 0]);
    }

    public function test_update_changes_bank_and_allows_keeping_own_account_number(): void
    {
        $bank = NasFreightsBank::factory()->create(['account_number' => '1234567890123']);

        $this->putJson(route('nas-freights.settings.banks.update', $bank->id), $this->payload([
            'bank_name' => 'Sonali Bank',
            'is_active' => 0,
        ]))->assertOk()->assertJsonPath('message', 'Bank updated.');

        $this->assertDatabaseHas('nas_freights_banks', [
            'id'        => $bank->id,
            'bank_name' => 'Sonali Bank',
            'is_active' => 0,
        ]);
    }

    public function test_update_rejects_account_number_used_by_another_bank(): void
    {
        NasFreightsBank::factory()->create(['account_number' => '1234567890123']);
        $other = NasFreightsBank::factory()->create(['account_number' => '555']);

        $this->putJson(route('nas-freights.settings.banks.update', $other->id), $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('account_number');
    }

    public function test_destroy_deletes_bank(): void
    {
        $bank = NasFreightsBank::factory()->create();

        $this->deleteJson(route('nas-freights.settings.banks.destroy', $bank->id))
            ->assertOk()
            ->assertJsonPath('message', 'Bank deleted.');

        $this->assertDatabaseMissing('nas_freights_banks', ['id' => $bank->id]);
    }

    public function test_banks_require_permission(): void
    {
        $bank = NasFreightsBank::factory()->create();
        $this->actingAs(User::create([
            'name'      => 'No Perms',
            'username'  => 'noperms',
            'email'     => 'noperms@example.com',
            'password'  => bcrypt('secret'),
            'is_active' => true,
        ]));

        $this->get(route('nas-freights.settings.banks.index'))->assertForbidden();
        $this->post(route('nas-freights.settings.banks.store'), $this->payload())->assertForbidden();
        $this->put(route('nas-freights.settings.banks.update', $bank->id), $this->payload())->assertForbidden();
        $this->delete(route('nas-freights.settings.banks.destroy', $bank->id))->assertForbidden();

        $this->assertDatabaseHas('nas_freights_banks', ['id' => $bank->id]);
    }

    public function test_active_scope_returns_only_active_banks_ordered_by_bank_name(): void
    {
        NasFreightsBank::factory()->create(['bank_name' => 'Zeta Bank', 'account_number' => '1']);
        NasFreightsBank::factory()->create(['bank_name' => 'Alpha Bank', 'account_number' => '2']);
        NasFreightsBank::factory()->inactive()->create(['bank_name' => 'Hidden Bank', 'account_number' => '3']);

        $this->assertSame(['Alpha Bank', 'Zeta Bank'], NasFreightsBank::active()->pluck('bank_name')->all());
    }
}

<?php

namespace Tests\Feature\Chevron;

use App\Http\Controllers\Chevron\CnfJobController;
use App\Http\Requests\Chevron\Job\DestroyJobRequest;
use App\Http\Requests\Chevron\Job\EditJobRequest;
use App\Http\Requests\Chevron\Job\ShowJobRequest;
use App\Http\Requests\Chevron\Job\StorePartialJobRequest;
use App\Http\Requests\Chevron\Job\UpdateJobRequest;
use App\Models\Chevron\ChevronJob;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PartialJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('chevron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->unique();
            $table->foreignId('parent_job_id')->nullable()->constrained('chevron_jobs')->restrictOnDelete();
            $table->unsignedSmallInteger('partial_seq')->nullable();
            $table->unique(['parent_job_id', 'partial_seq']);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('job_type_id')->nullable();
            $table->string('party_name')->nullable();
            $table->string('goods_name')->nullable();
            $table->string('hbi_hawb_no')->nullable();
            $table->date('job_date')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('assessable_value', 15, 2)->nullable();
            $table->decimal('assessable_value_bdt', 15, 2)->nullable();
            $table->decimal('cnf_charge_1', 15, 2)->nullable();
            $table->decimal('duty_amount', 15, 2)->nullable();
            $table->decimal('net_payable_1', 15, 2)->nullable();
            $table->decimal('net_payable_2', 15, 2)->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('chevron_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->timestamps();
        });

        Schema::create('chevron_job_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('job_no')->nullable();
            $table->date('date')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_expense_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        foreach (['chevron_services', 'chevron_job_types'] as $lookup) {
            Schema::create($lookup, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::create('chevron_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        session(['active_branch_id' => 5]);
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('chevron_ports');
        Schema::dropIfExists('chevron_job_types');
        Schema::dropIfExists('chevron_services');
        Schema::dropIfExists('chevron_job_expenses');
        Schema::dropIfExists('chevron_bills');
        Schema::dropIfExists('chevron_jobs');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    #[Test]
    public function partial_jobs_follow_the_parent_number_serial(): void
    {
        $parent = $this->createJob('CF_IMDHK-2026-000012');

        $first = $parent->createPartialJob();
        $second = $parent->createPartialJob();
        $third = $parent->createPartialJob();

        $this->assertSame('CF_IMDHK-2026-000012_PJ', $first->job_no);
        $this->assertSame('CF_IMDHK-2026-000012_PJ1', $second->job_no);
        $this->assertSame('CF_IMDHK-2026-000012_PJ2', $third->job_no);
        $this->assertSame([1, 2, 3], [$first->partial_seq, $second->partial_seq, $third->partial_seq]);
        $this->assertSame($parent->id, $third->parent_job_id);
        $this->assertSame(3, $parent->partials()->count());
    }

    #[Test]
    public function the_next_partial_number_continues_after_a_deleted_one(): void
    {
        $parent = $this->createJob('CF_IMDHK-2026-000012');
        $parent->createPartialJob();
        $second = $parent->createPartialJob();
        $second->delete();

        $this->assertSame('CF_IMDHK-2026-000012_PJ1', $parent->createPartialJob()->job_no);
    }

    #[Test]
    public function the_next_partial_number_continues_after_existing_unlinked_partial_numbers(): void
    {
        $parent = $this->createJob('JOB-A');
        $this->createJob('JOB-A_PJ');
        $this->createJob('JOB-A_PJ1');

        $partial = $parent->createPartialJob();

        $this->assertSame('JOB-A_PJ2', $partial->job_no);
        $this->assertSame(3, $partial->partial_seq);
        $this->assertSame('JOB-A_PJ3', $parent->nextPartialJobNo());
    }

    #[Test]
    public function the_next_partial_number_goes_after_the_highest_existing_number(): void
    {
        $parent = $this->createJob('JOB-A');
        $this->createJob('JOB-A_PJ');
        $this->createJob('JOB-A_PJ3');

        $this->assertSame('JOB-A_PJ4', $parent->createPartialJob()->job_no);
    }

    #[Test]
    public function partial_numbers_of_other_jobs_do_not_change_the_serial(): void
    {
        $parent = $this->createJob('JOB-A');
        $this->createJob('JOB-AB_PJ');
        $this->createJob('JOB-A_PJ_X');

        $this->assertSame('JOB-A_PJ', $parent->createPartialJob()->job_no);
    }

    #[Test]
    public function migration_links_existing_partial_numbers_to_their_parent_job(): void
    {
        $parent = $this->createJob('JOB-A');
        $first = $this->createJob('JOB-A_PJ');
        $second = $this->createJob('JOB-A_PJ1');
        $third = $this->createJob('JOB-A_PJ2');
        $orphan = $this->createJob('JOB-GONE_PJ');
        $other = $this->createJob('JOB-B');

        $migration = require glob(database_path('migrations/*_link_existing_partial_jobs_to_parent.php'))[0];
        $migration->up();

        $this->assertSame([$parent->id, 1], [$first->fresh()->parent_job_id, $first->fresh()->partial_seq]);
        $this->assertSame([$parent->id, 2], [$second->fresh()->parent_job_id, $second->fresh()->partial_seq]);
        $this->assertSame([$parent->id, 3], [$third->fresh()->parent_job_id, $third->fresh()->partial_seq]);
        $this->assertNull($orphan->fresh()->parent_job_id);
        $this->assertNull($parent->fresh()->parent_job_id);
        $this->assertNull($other->fresh()->parent_job_id);
        $this->assertSame(3, $parent->partials()->count());
        $this->assertSame('JOB-A_PJ3', $parent->nextPartialJobNo());

        $migration->up();

        $this->assertSame(3, $parent->partials()->count());
    }

    #[Test]
    public function partial_job_copies_the_parent_job_fields(): void
    {
        $parent = $this->createJob('JOB-A', [
            'party_name'    => 'ACME TRADING',
            'goods_name'    => 'Cotton',
            'hbi_hawb_no'   => 'HAWB-1',
            'job_date'      => '2026-10-01',
            'cnf_charge_1'  => 120.5,
            'duty_amount'   => 300,
            'net_payable_1' => 1000,
            'net_payable_2' => 110000,
            'remarks'       => 'main job',
        ]);

        $partial = $parent->createPartialJob()->fresh();

        $this->assertNotSame($parent->id, $partial->id);
        $this->assertNotSame($parent->job_no, $partial->job_no);
        $this->assertSame(5, (int) $partial->branch_id);
        $this->assertSame('ACME TRADING', $partial->party_name);
        $this->assertSame('Cotton', $partial->goods_name);
        $this->assertSame('HAWB-1', $partial->hbi_hawb_no);
        $this->assertSame('2026-10-01', $partial->job_date->format('Y-m-d'));
        $this->assertEquals(120.5, $partial->cnf_charge_1);
        $this->assertEquals(300, $partial->duty_amount);
        $this->assertEquals(1000, $partial->net_payable_1);
        $this->assertEquals(110000, $partial->net_payable_2);
        $this->assertSame('main job', $partial->remarks);
        $this->assertTrue($partial->isPartial());
        $this->assertFalse($parent->isPartial());
        $this->assertSame($parent->id, $partial->parent->id);
    }

    #[Test]
    public function partial_job_does_not_copy_bills_or_expenses(): void
    {
        $parent = $this->createJob('JOB-A');
        DB::table('chevron_bills')->insert(['bill_no' => 'BIL000001', 'job_id' => $parent->id, 'job_no' => 'JOB-A']);
        DB::table('chevron_job_expenses')->insert(['expense_no' => 'EXP000001', 'job_id' => $parent->id]);

        $parent->createPartialJob();

        $this->assertSame(1, DB::table('chevron_bills')->count());
        $this->assertSame(1, DB::table('chevron_job_expenses')->count());
        $this->assertSame($parent->id, (int) DB::table('chevron_bills')->value('job_id'));
    }

    #[Test]
    public function a_partial_job_cannot_have_its_own_partial_jobs(): void
    {
        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();

        $this->expectException(\LogicException::class);

        $partial->createPartialJob();
    }

    #[Test]
    public function store_partial_redirects_to_the_new_partial_edit_page(): void
    {
        $parent = $this->createJob('JOB-A');

        $response = (new CnfJobController)->storePartial(StorePartialJobRequest::create('/', 'POST'), $parent);

        $partial = ChevronJob::where('parent_job_id', $parent->id)->firstOrFail();

        $this->assertSame('JOB-A_PJ', $partial->job_no);
        $this->assertSame(route('chevron.cnf.jobs.edit', $partial->id), $response->getTargetUrl());
        $this->assertStringContainsString('JOB-A_PJ', session('success'));
        $this->assertStringContainsString('JOB-A now has 1 partial job.', session('success'));

        (new CnfJobController)->storePartial(StorePartialJobRequest::create('/', 'POST'), $parent);

        $this->assertStringContainsString('JOB-A now has 2 partial jobs.', session('success'));
    }

    #[Test]
    public function store_partial_refuses_a_job_that_is_already_partial(): void
    {
        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();

        $response = (new CnfJobController)->storePartial(StorePartialJobRequest::create('/', 'POST'), $partial);

        $this->assertSame(route('chevron.cnf.jobs.show', $partial->id), $response->getTargetUrl());
        $this->assertNotNull(session('error'));
        $this->assertSame(1, ChevronJob::whereNotNull('parent_job_id')->count());
    }

    #[Test]
    public function store_partial_returns_not_found_for_another_branch(): void
    {
        $parent = $this->createJob('JOB-A', ['branch_id' => 6]);

        $this->expectException(NotFoundHttpException::class);

        (new CnfJobController)->storePartial(StorePartialJobRequest::create('/', 'POST'), $parent);
    }

    #[Test]
    public function only_users_who_can_create_jobs_may_create_partial_jobs(): void
    {
        $this->assertTrue($this->partialRequestFor($this->userWithPermission(true))->authorize());
        $this->assertFalse($this->partialRequestFor($this->userWithPermission(false))->authorize());
        $this->assertFalse($this->partialRequestFor(null)->authorize());
    }

    #[Test]
    public function partial_job_may_share_the_hawb_number_with_its_family_only(): void
    {
        $parent = $this->createJob('JOB-A', ['hbi_hawb_no' => 'HAWB-1']);
        $partialOne = $parent->createPartialJob();
        $partialTwo = $parent->createPartialJob();
        $other = $this->createJob('JOB-B', ['hbi_hawb_no' => 'HAWB-2']);

        $this->assertTrue($this->hawbPasses($partialOne, 'HAWB-1'));
        $this->assertTrue($this->hawbPasses($parent, 'HAWB-1'));
        $this->assertTrue($this->hawbPasses($partialTwo, 'HAWB-1'));
        $this->assertFalse($this->hawbPasses($partialOne, 'HAWB-2'));
        $this->assertFalse($this->hawbPasses($other, 'HAWB-1'));
        $this->assertTrue($this->hawbPasses($other, 'HAWB-2'));
    }

    #[Test]
    public function update_cannot_change_the_job_number_or_parent_reference(): void
    {
        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();
        $other = $this->createJob('JOB-B');

        (new CnfJobController)->update(UpdateJobRequest::create('/', 'PUT', [
            'party_name'    => 'RENAMED PARTY',
            'job_no'        => 'HACKED',
            'parent_job_id' => $other->id,
            'partial_seq'   => 9,
        ]), $partial);

        $partial->refresh();

        $this->assertSame('RENAMED PARTY', $partial->party_name);
        $this->assertSame('JOB-A_PJ', $partial->job_no);
        $this->assertSame($parent->id, $partial->parent_job_id);
        $this->assertSame(1, $partial->partial_seq);
    }

    #[Test]
    public function a_parent_with_partial_jobs_cannot_be_deleted_but_a_partial_can(): void
    {
        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();

        $blocked = (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $parent);

        $this->assertSame(422, $blocked->getStatusCode());
        $this->assertStringContainsString('JOB-A', $blocked->getData(true)['message']);
        $this->assertDatabaseHas('chevron_jobs', ['id' => $parent->id]);

        $blockers = $blocked->getData(true)['blockers'];
        $this->assertCount(1, $blockers);
        $this->assertSame('partial_jobs', $blockers[0]['type']);
        $this->assertSame(1, $blockers[0]['total']);
        $this->assertSame('JOB-A_PJ', $blockers[0]['items'][0]['label']);

        $allowed = (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $partial);

        $this->assertSame(200, $allowed->getStatusCode());
        $this->assertDatabaseMissing('chevron_jobs', ['id' => $partial->id]);

        $parent->unsetRelation('partials');
        $this->assertSame(200, (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $parent)->getStatusCode());
    }

    #[Test]
    public function a_job_used_in_job_expenses_cannot_be_deleted_and_the_expenses_are_listed(): void
    {
        $job = $this->createJob('JOB-A');
        DB::table('chevron_job_expenses')->insert([
            ['expense_no' => 'EXP000001', 'job_id' => $job->id, 'job_no' => 'JOB-A', 'date' => '2026-10-05', 'status' => 'Approved', 'total_expense_amount' => 1500],
            ['expense_no' => 'EXP000002', 'job_id' => null, 'job_no' => 'JOB-A', 'date' => '2026-10-06', 'status' => 'Draft', 'total_expense_amount' => 20.5],
            ['expense_no' => 'EXP000003', 'job_id' => 999, 'job_no' => 'JOB-OTHER', 'date' => '2026-10-07', 'status' => 'Draft', 'total_expense_amount' => 1],
        ]);

        $response = (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $job);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertDatabaseHas('chevron_jobs', ['id' => $job->id]);

        $blockers = $response->getData(true)['blockers'];
        $this->assertCount(1, $blockers);
        $this->assertSame('job_expenses', $blockers[0]['type']);
        $this->assertSame(2, $blockers[0]['total']);
        $this->assertSame(['EXP000001', 'EXP000002'], array_column($blockers[0]['items'], 'label'));
        $this->assertSame('05 Oct 2026 · Approved · 1,500.00', $blockers[0]['items'][0]['detail']);
    }

    #[Test]
    public function every_blocker_is_reported_together_and_long_lists_are_capped(): void
    {
        $parent = $this->createJob('JOB-A');
        $parent->createPartialJob();

        foreach (range(1, 12) as $n) {
            DB::table('chevron_job_expenses')->insert(['expense_no' => 'EXP'.$n, 'job_id' => $parent->id, 'job_no' => 'JOB-A']);
        }

        $blockers = (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $parent)->getData(true)['blockers'];

        $this->assertSame(['partial_jobs', 'job_expenses'], array_column($blockers, 'type'));
        $this->assertSame(12, $blockers[1]['total']);
        $this->assertCount(10, $blockers[1]['items']);
    }

    #[Test]
    public function a_job_without_partials_or_expenses_is_deleted(): void
    {
        $job = $this->createJob('JOB-A');
        $this->createJob('JOB-AB');
        DB::table('chevron_job_expenses')->insert(['expense_no' => 'EXP000001', 'job_id' => 999, 'job_no' => 'JOB-B']);

        $response = (new CnfJobController)->destroy(DestroyJobRequest::create('/', 'DELETE'), $job);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseMissing('chevron_jobs', ['id' => $job->id]);
        $this->assertDatabaseHas('chevron_jobs', ['job_no' => 'JOB-AB']);
    }

    #[Test]
    public function partial_panel_lists_partials_on_the_parent_and_links_back_from_a_partial(): void
    {
        $parent = $this->createJob('JOB-A');

        $empty = view('chevron.cnf.jobs._partial-job-panel', ['job' => $parent->load(['parent', 'partials'])])->render();
        $this->assertStringContainsString('No partial jobs yet.', $empty);

        $partial = $parent->createPartialJob();

        $parentHtml = view('chevron.cnf.jobs._partial-job-panel', [
            'job' => $parent->fresh()->load(['parent', 'partials']),
        ])->render();
        $this->assertStringContainsString('Partial Jobs', $parentHtml);
        $this->assertStringContainsString('JOB-A_PJ', $parentHtml);
        $this->assertStringContainsString(route('chevron.cnf.jobs.show', $partial->id), $parentHtml);

        $partialHtml = view('chevron.cnf.jobs._partial-job-panel', [
            'job' => $partial->fresh()->load(['parent', 'partials']),
        ])->render();
        $this->assertStringContainsString('This is a partial job of', $partialHtml);
        $this->assertStringContainsString(route('chevron.cnf.jobs.show', $parent->id), $partialHtml);
        $this->assertStringContainsString('Partial #1', $partialHtml);
    }

    #[Test]
    public function partial_button_shows_for_a_parent_job_only(): void
    {
        Auth::setUser((new User)->forceFill(['is_super' => true]));

        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();

        $parentHtml = view('chevron.cnf.jobs._partial-job-button', ['job' => $parent])->render();
        $this->assertStringContainsString('Create Partial Job', $parentHtml);
        $this->assertStringContainsString(route('chevron.cnf.jobs.partial.store', $parent->id), $parentHtml);

        $editHtml = view('chevron.cnf.jobs._partial-job-button', ['job' => $parent, 'isEditPage' => true])->render();
        $this->assertStringContainsString('Unsaved changes on this page are not copied.', $editHtml);

        $partialHtml = view('chevron.cnf.jobs._partial-job-button', ['job' => $partial->fresh()])->render();
        $this->assertStringNotContainsString('Create Partial Job', $partialHtml);
    }

    #[Test]
    public function partial_button_dialog_data_reports_existing_partials_and_the_next_number(): void
    {
        Auth::setUser((new User)->forceFill(['is_super' => true]));

        $parent = $this->createJob('JOB-A');

        $none = view('chevron.cnf.jobs._partial-job-button', ['job' => $parent])->render();
        $this->assertStringContainsString('data-existing="[]"', $none);
        $this->assertStringContainsString('data-next="JOB-A_PJ"', $none);
        $this->assertStringNotContainsString('existing partial jobs', $none);

        $parent->createPartialJob();
        $parent->createPartialJob();

        $two = view('chevron.cnf.jobs._partial-job-button', ['job' => $parent->fresh()])->render();
        $this->assertStringContainsString('data-existing="[&quot;JOB-A_PJ&quot;,&quot;JOB-A_PJ1&quot;]"', $two);
        $this->assertStringContainsString('data-next="JOB-A_PJ2"', $two);
        $this->assertStringContainsString('2<span class="visually-hidden"> existing partial jobs</span>', $two);
    }

    #[Test]
    public function partial_button_next_number_skips_numbers_already_used_by_unlinked_jobs(): void
    {
        Auth::setUser((new User)->forceFill(['is_super' => true]));

        $parent = $this->createJob('JOB-A');
        $this->createJob('JOB-A_PJ');
        $this->createJob('JOB-A_PJ1');

        $html = view('chevron.cnf.jobs._partial-job-button', ['job' => $parent])->render();

        $this->assertStringContainsString('data-next="JOB-A_PJ2"', $html);
    }

    #[Test]
    public function show_and_edit_pages_render_the_partial_job_options(): void
    {
        Auth::setUser((new User)->forceFill(['is_super' => true]));
        view()->share('errors', new ViewErrorBag);

        $parent = $this->createJob('JOB-A');
        $partial = $parent->createPartialJob();
        $controller = new CnfJobController;

        $parentShow = $controller->show(ShowJobRequest::create('/', 'GET'), $parent)->render();
        $this->assertStringContainsString('Create Partial Job', $parentShow);
        $this->assertStringContainsString('JOB-A_PJ', $parentShow);

        $parentEdit = $controller->edit(EditJobRequest::create('/', 'GET'), $parent)->render();
        $this->assertStringContainsString('Create Partial Job', $parentEdit);
        $this->assertStringContainsString('JOB-A_PJ', $parentEdit);

        $partialShow = $controller->show(ShowJobRequest::create('/', 'GET'), $partial->fresh())->render();
        $this->assertStringNotContainsString('Create Partial Job', $partialShow);
        $this->assertStringContainsString('Parent JOB-A', $partialShow);

        $partialEdit = $controller->edit(EditJobRequest::create('/', 'GET'), $partial->fresh())->render();
        $this->assertStringNotContainsString('Create Partial Job', $partialEdit);
        $this->assertStringContainsString('Parent JOB-A', $partialEdit);
    }

    #[Test]
    public function partial_button_is_hidden_without_the_create_permission(): void
    {
        Auth::setUser($this->userWithPermission(false));

        $html = view('chevron.cnf.jobs._partial-job-button', ['job' => $this->createJob('JOB-A')])->render();

        $this->assertStringNotContainsString('Create Partial Job', $html);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createJob(string $jobNo, array $attributes = []): ChevronJob
    {
        return ChevronJob::create(array_merge([
            'job_no'    => $jobNo,
            'branch_id' => 5,
            'status'    => 'Active',
        ], $attributes));
    }

    private function hawbPasses(ChevronJob $job, string $hawb): bool
    {
        $request = UpdateJobRequest::create('/', 'PUT');

        $route = new Route('PUT', 'jobs/{job}', []);
        $route->bind($request);
        $route->setParameter('job', $job);
        $request->setRouteResolver(fn () => $route);

        $rules = ['hbi_hawb_no' => $request->rules()['hbi_hawb_no']];

        return Validator::make(['hbi_hawb_no' => $hawb], $rules)->passes();
    }

    private function partialRequestFor(?object $user): StorePartialJobRequest
    {
        $request = StorePartialJobRequest::create('/', 'POST');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function userWithPermission(bool $allowed): object
    {
        $user = new class extends User
        {
            public bool $allowed = false;

            public function hasPermission(string $permission): bool
            {
                return $this->allowed;
            }
        };
        $user->allowed = $allowed;

        return $user;
    }
}

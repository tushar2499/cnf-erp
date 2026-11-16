<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\Posting\EntriesPostingRequest;
use App\Http\Requests\Admin\Accounting\Posting\IndexPostingRequest;
use App\Http\Requests\Admin\Accounting\Posting\PostManyPostingRequest;
use App\Http\Requests\Admin\Accounting\Posting\PostPostingRequest;
use App\Models\Company;
use App\Services\Accounting\PostingService;
use App\Services\Accounting\Sources\SourceRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class PostingController extends Controller
{
    public function __construct(private PostingService $posting) {}

    public function index(IndexPostingRequest $request, SourceRegistry $registry)
    {
        $companies = Company::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']);
        $sources = collect($registry->all())->map(fn ($s) => ['key' => $s->key(), 'label' => $s->label(), 'company' => $s->companySlug()])->values();

        return view('admin.accounting.posting.index', compact('companies', 'sources'));
    }

    public function entries(EntriesPostingRequest $request): JsonResponse
    {
        $company = Company::findOrFail($request->integer('company_id'));

        return response()->json([
            'entries' => $this->posting->entries(
                $company,
                Carbon::parse($request->from),
                Carbon::parse($request->to),
                $request->source_type ?: null,
            ),
        ]);
    }

    public function post(PostPostingRequest $request): JsonResponse
    {
        $voucher = $this->posting->post(
            Company::findOrFail($request->integer('company_id')),
            Carbon::parse($request->from),
            Carbon::parse($request->to),
            $request->source_type,
            $request->ref,
            $request->has('lines') ? $request->input('lines') : null,
            $request->narration,
            $request->user()->id,
        );

        return response()->json([
            'message'    => "Posted as voucher {$voucher->voucher_no}.",
            'voucher_id' => $voucher->id,
            'voucher_no' => $voucher->voucher_no,
        ]);
    }

    public function postMany(PostManyPostingRequest $request): JsonResponse
    {
        $result = $this->posting->postMany(
            Company::findOrFail($request->integer('company_id')),
            Carbon::parse($request->from),
            Carbon::parse($request->to),
            $request->input('items'),
            $request->user()->id,
        );

        return response()->json([
            'message' => "{$result['posted']} voucher(s) posted".($result['skipped'] ? ', '.count($result['skipped']).' skipped.' : '.'),
        ] + $result);
    }
}

<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\Mapping\DestroyMappingRequest;
use App\Http\Requests\Admin\Accounting\Mapping\IndexMappingRequest;
use App\Http\Requests\Admin\Accounting\Mapping\StoreMappingRequest;
use App\Http\Requests\Admin\Accounting\Mapping\UpdateMappingRequest;
use App\Models\Accounting\AccTransactionMapping;
use App\Models\Company;
use App\Services\Accounting\Sources\MatchOptions;
use App\Services\Accounting\Sources\SourceRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MappingController extends Controller
{
    public function __construct(private SourceRegistry $registry) {}

    public function index(IndexMappingRequest $request)
    {
        $mappings = AccTransactionMapping::with(['debitAccount', 'creditAccount'])
            ->orderBy('source_type')->orderBy('component')->orderBy('match_key')
            ->get()
            ->groupBy('source_type');

        $companies = Company::whereIn('slug', collect($this->registry->all())->map->companySlug()->unique())
            ->orderBy('id')->get(['id', 'name', 'slug']);

        $sources = collect($this->registry->all())->map(fn ($s) => [
            'key'        => $s->key(),
            'label'      => $s->label(),
            'company'    => $s->companySlug(),
            'components' => $s->components(),
        ]);

        $matchOptions = $sources->flatMap(fn ($s) => collect($s['components'])->pluck('match')->filter())
            ->unique()
            ->mapWithKeys(fn ($type) => [$type => ['label' => MatchOptions::LABELS[$type] ?? $type, 'options' => MatchOptions::options($type)]]);

        return view('admin.accounting.mappings.index', compact('mappings', 'companies', 'sources', 'matchOptions'));
    }

    public function store(StoreMappingRequest $request): JsonResponse
    {
        $data = $this->validatedRule($request->validated());

        AccTransactionMapping::create($data + [
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Mapping saved.']);
    }

    public function update(UpdateMappingRequest $request, AccTransactionMapping $mapping): JsonResponse
    {
        $data = $this->validatedRule($request->validated(), $mapping->id);

        $mapping->update($data + ['is_active' => $request->boolean('is_active', true)]);

        return response()->json(['message' => 'Mapping updated.']);
    }

    public function destroy(DestroyMappingRequest $request, AccTransactionMapping $mapping): JsonResponse
    {
        $mapping->delete();

        return response()->json(['message' => 'Mapping deleted.']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validatedRule(array $data, ?int $ignoreId = null): array
    {
        if (! $this->registry->has($data['source_type'])) {
            throw ValidationException::withMessages(['source_type' => 'Unknown document type.']);
        }

        $components = $this->registry->get($data['source_type'])->components();
        $component = $components[$data['component']] ?? null;
        if (! $component) {
            throw ValidationException::withMessages(['component' => 'Unknown amount part for this document.']);
        }

        $matchKey = (string) ($data['match_key'] ?? '');
        if ($matchKey !== '' && ! array_key_exists($matchKey, MatchOptions::options($component['match'] ?? ''))) {
            throw ValidationException::withMessages(['match_key' => 'Invalid value for this amount part.']);
        }

        $duplicate = AccTransactionMapping::where([
            'source_type' => $data['source_type'],
            'component'   => $data['component'],
            'match_key'   => $matchKey,
        ])->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['match_key' => 'A rule for this amount part and value already exists — edit it instead.']);
        }

        return [
            'source_type'       => $data['source_type'],
            'component'         => $data['component'],
            'match_key'         => $matchKey,
            'debit_account_id'  => $data['debit_account_id'],
            'credit_account_id' => $data['credit_account_id'],
        ];
    }
}

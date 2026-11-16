<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\Account\DestroyAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\ImportAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\ImportPreviewAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\IndexAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\LedgerOptionsAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\OptionsAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\StoreAccountRequest;
use App\Http\Requests\Admin\Accounting\Account\UpdateAccountRequest;
use App\Models\Accounting\AccAccount;
use App\Services\Accounting\ChartOfAccountImporter;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    public function index(IndexAccountRequest $request)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $canEdit = $user->hasPermission('admin.accounting.coa.edit');
            $canDelete = $user->hasPermission('admin.accounting.coa.delete');

            $query = AccAccount::query()
                ->leftJoin('acc_accounts as parents', 'parents.id', '=', 'acc_accounts.parent_id')
                ->select('acc_accounts.*', 'parents.code as parent_code', 'parents.name as parent_name');

            if ($request->filled('type')) {
                $query->where('acc_accounts.type', $request->type);
            }
            if ($request->filled('level')) {
                $query->where('acc_accounts.level', $request->level);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn ($row) => '<span style="padding-left:'.(($row->level - 1) * 1.1).'rem" class="'.($row->is_group ? 'fw-semibold' : '').'">'.e($row->name).'</span>')
                ->addColumn('level_name', fn ($row) => AccAccount::LEVEL_NAMES[$row->level] ?? $row->level)
                ->editColumn('type', fn ($row) => ucfirst($row->type))
                ->editColumn('nature', fn ($row) => strtoupper($row->nature))
                ->addColumn('parent', fn ($row) => $row->parent_code ? e($row->parent_code.' – '.$row->parent_name) : '')
                ->addColumn('flags', function ($row) {
                    $flags = [];
                    if ($row->is_group) {
                        $flags[] = '<span class="badge bg-secondary">Group</span>';
                    }
                    if ($row->is_cash_bank) {
                        $flags[] = '<span class="badge bg-info text-dark">Cash/Bank</span>';
                    }
                    if ($row->party_type) {
                        $flags[] = '<span class="badge bg-warning text-dark">'.ucfirst($row->party_type).' control</span>';
                    }

                    return implode(' ', $flags);
                })
                ->addColumn('status_badge', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->filterColumn('parent', fn ($q, $k) => $q->where(fn ($w) => $w->where('parents.code', 'like', "%{$k}%")->orWhere('parents.name', 'like', "%{$k}%")))
                ->filterColumn('code', fn ($q, $k) => $q->where('acc_accounts.code', 'like', "{$k}%"))
                ->filterColumn('name', fn ($q, $k) => $q->where('acc_accounts.name', 'like', "%{$k}%"))
                ->orderColumn('code', 'acc_accounts.code $1')
                ->addColumn('action', function ($row) use ($canEdit, $canDelete) {
                    $html = '';
                    if ($canEdit) {
                        $html .= '<button class="btn btn-sm btn-outline-secondary btn-edit me-1"
                            data-id="'.$row->id.'"
                            data-code="'.e($row->code).'"
                            data-name="'.e($row->name).'"
                            data-parent="'.e($row->parent_code ? $row->parent_code.' – '.$row->parent_name : '—').'"
                            data-type="'.$row->type.'"
                            data-nature="'.$row->nature.'"
                            data-is_group="'.(int) $row->is_group.'"
                            data-is_cash_bank="'.(int) $row->is_cash_bank.'"
                            data-party_type="'.e($row->party_type).'"
                            data-is_active="'.(int) $row->is_active.'"
                            title="Edit" aria-label="Edit '.e($row->name).'"><i class="fa fa-edit"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('admin.accounting.accounts.destroy', $row->id).'"
                            data-name="'.e($row->code.' '.$row->name).'"
                            title="Delete" aria-label="Delete '.e($row->name).'"><i class="fa fa-trash"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['name', 'flags', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.accounting.accounts.index', [
            'types' => AccAccount::TYPES,
        ]);
    }

    /**
     * Group accounts for the parent picker (Select2).
     */
    public function parents(OptionsAccountRequest $request): JsonResponse
    {
        $term = trim((string) $request->q);

        $results = AccAccount::where('is_group', true)
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "{$term}%")->orWhere('name', 'like', "%{$term}%")))
            ->orderBy('code')
            ->limit(50)
            ->get(['id', 'code', 'name', 'level'])
            ->map(fn ($a) => ['id' => $a->id, 'text' => $a->code.' – '.$a->name.' ('.AccAccount::LEVEL_NAMES[$a->level].')']);

        return response()->json(['results' => $results]);
    }

    /**
     * Active postable ledgers for account pickers (Select2) in mappings, vouchers and reports.
     */
    public function ledgers(LedgerOptionsAccountRequest $request): JsonResponse
    {
        $term = trim((string) $request->q);

        $results = AccAccount::where('is_group', false)
            ->where('is_active', true)
            ->when($request->boolean('cash_bank'), fn ($q) => $q->where('is_cash_bank', true))
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "{$term}%")->orWhere('name', 'like', "%{$term}%")))
            ->orderBy('code')
            ->limit(50)
            ->get(['id', 'code', 'name', 'party_type', 'is_cash_bank'])
            ->map(fn ($a) => ['id' => $a->id, 'text' => $a->code.' – '.$a->name, 'party_type' => $a->party_type]);

        return response()->json(['results' => $results]);
    }

    /**
     * Suggested code and inherited type for a new child of the given parent.
     */
    public function nextCode(OptionsAccountRequest $request): JsonResponse
    {
        $parent = AccAccount::where('is_group', true)->findOrFail($request->integer('parent_id'));

        return response()->json([
            'code'     => $parent->nextChildCode(),
            'level'    => $parent->level + 1,
            'is_group' => $parent->level + 1 < AccAccount::LEVEL_LEDGER,
            'type'     => $parent->type,
            'nature'   => $parent->nature,
        ]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $parent = $request->parentAccount();
        $level = $parent ? $parent->level + 1 : AccAccount::LEVEL_MASTER;
        $isLedger = $level === AccAccount::LEVEL_LEDGER;

        AccAccount::create([
            'parent_id'    => $parent?->id,
            'code'         => $validated['code'],
            'name'         => $validated['name'],
            'level'        => $level,
            'type'         => $parent ? $parent->type : $validated['type'],
            'nature'       => $validated['nature'],
            'is_group'     => ! $isLedger,
            'is_cash_bank' => $isLedger && $request->boolean('is_cash_bank'),
            'party_type'   => $isLedger ? ($validated['party_type'] ?? null) : null,
            'is_active'    => $request->boolean('is_active', true),
            'created_by'   => $request->user()->id,
        ]);

        return response()->json(['message' => 'Account created successfully.']);
    }

    public function update(UpdateAccountRequest $request, AccAccount $account): JsonResponse
    {
        $validated = $request->validated();

        $account->update([
            'name'         => $validated['name'],
            'nature'       => $validated['nature'],
            'is_cash_bank' => ! $account->is_group && $request->boolean('is_cash_bank'),
            'party_type'   => $account->is_group ? null : ($validated['party_type'] ?? null),
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Account updated successfully.']);
    }

    public function destroy(DestroyAccountRequest $request, AccAccount $account): JsonResponse
    {
        if ($account->children()->exists()) {
            return response()->json(['message' => 'Remove or move its child accounts first.'], 422);
        }

        $account->delete();

        return response()->json(['message' => 'Account deleted.']);
    }

    public function importPreview(ImportPreviewAccountRequest $request, ChartOfAccountImporter $importer): JsonResponse
    {
        try {
            return response()->json($importer->parse($request->file('file')->getPathname()));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function import(ImportAccountRequest $request, ChartOfAccountImporter $importer): JsonResponse
    {
        try {
            $result = $importer->import($request->file('file')->getPathname(), $request->user()->id);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "Chart of accounts imported: {$result['created']} created, {$result['updated']} updated.",
        ] + $result);
    }
}

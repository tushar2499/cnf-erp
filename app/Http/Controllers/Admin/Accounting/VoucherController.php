<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounting\Voucher\CancelVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\CreateVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\EditVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\IndexVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\PartiesVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\ShowVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\StoreVoucherRequest;
use App\Http\Requests\Admin\Accounting\Voucher\UpdateVoucherRequest;
use App\Models\Accounting\AccVoucher;
use App\Models\Company;
use App\Services\Accounting\PartyDirectory;
use App\Services\Accounting\Sources\SourceRegistry;
use App\Services\Accounting\VoucherService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    public function __construct(private VoucherService $vouchers) {}

    public function index(IndexVoucherRequest $request, SourceRegistry $registry)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $canView = $user->hasPermission('admin.accounting.voucher.view');
            $canEdit = $user->hasPermission('admin.accounting.voucher.edit');
            $canCancel = $user->hasPermission('admin.accounting.voucher.cancel');
            $labels = collect($registry->all())->map->label();

            $query = AccVoucher::query()
                ->join('companies', 'companies.id', '=', 'acc_vouchers.company_id')
                ->select('acc_vouchers.*', 'companies.name as company_name')
                ->when($request->company_id, fn ($q, $id) => $q->where('acc_vouchers.company_id', $id))
                ->when($request->voucher_type, fn ($q, $t) => $q->where('acc_vouchers.voucher_type', $t))
                ->when($request->status, fn ($q, $s) => $q->where('acc_vouchers.status', $s))
                ->when($request->origin, fn ($q, $o) => $q->where('acc_vouchers.is_auto', $o === 'auto'))
                ->when($request->from, fn ($q, $d) => $q->where('acc_vouchers.voucher_date', '>=', $d))
                ->when($request->to, fn ($q, $d) => $q->where('acc_vouchers.voucher_date', '<=', $d));

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('voucher_date', fn ($v) => $v->voucher_date->format('d-m-Y'))
                ->editColumn('voucher_type', fn ($v) => AccVoucher::TYPES[$v->voucher_type])
                ->editColumn('total_amount', fn ($v) => number_format((float) $v->total_amount, 2))
                ->addColumn('origin', fn ($v) => $v->is_auto
                    ? '<span class="badge bg-info text-dark" title="'.e($labels[$v->source_type] ?? $v->source_type).'">Auto · '.e($v->source_doc_no).'</span>'
                    : '<span class="badge bg-secondary">Manual</span>')
                ->addColumn('status_badge', fn ($v) => $v->status === 'posted'
                    ? '<span class="badge bg-success">Posted</span>'
                    : '<span class="badge bg-danger" title="'.e($v->cancel_reason).'">Cancelled</span>')
                ->filterColumn('company_name', fn ($q, $k) => $q->where('companies.name', 'like', "%{$k}%"))
                ->filterColumn('voucher_no', fn ($q, $k) => $q->where('acc_vouchers.voucher_no', 'like', "%{$k}%"))
                ->filterColumn('narration', fn ($q, $k) => $q->where(fn ($w) => $w->where('acc_vouchers.narration', 'like', "%{$k}%")->orWhere('acc_vouchers.source_doc_no', 'like', "%{$k}%")))
                ->orderColumn('voucher_date', 'acc_vouchers.voucher_date $1, acc_vouchers.id $1')
                ->addColumn('action', function ($v) use ($canView, $canEdit, $canCancel) {
                    $html = '';
                    if ($canView) {
                        $html .= '<a href="'.route('admin.accounting.vouchers.show', $v->id).'" class="btn btn-sm btn-outline-primary me-1" title="View"><i class="fa fa-eye"></i></a>';
                    }
                    if ($canEdit && ! $v->is_auto && $v->status === 'posted') {
                        $html .= '<a href="'.route('admin.accounting.vouchers.edit', $v->id).'" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if ($canCancel && $v->status === 'posted') {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-cancel" data-url="'.route('admin.accounting.vouchers.cancel', $v->id).'" data-no="'.e($v->voucher_no).'" title="Cancel"><i class="fa fa-ban"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['origin', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.accounting.vouchers.index', [
            'companies' => Company::orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function create(CreateVoucherRequest $request, PartyDirectory $parties)
    {
        return view('admin.accounting.vouchers.form', [
            'voucher'       => null,
            'companies'     => Company::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']),
            'partyTypes'    => $this->partyTypesByCompany($parties),
            'existingLines' => [],
        ]);
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        $voucher = $this->vouchers->create($request->validated() + ['is_auto' => false], $request->user()->id);

        return response()->json([
            'message'  => "Voucher {$voucher->voucher_no} saved.",
            'redirect' => route('admin.accounting.vouchers.show', $voucher),
        ]);
    }

    public function show(ShowVoucherRequest $request, AccVoucher $voucher, SourceRegistry $registry)
    {
        $voucher->load(['lines.account', 'company', 'creator', 'canceller']);
        $sourceLabel = $voucher->source_type && $registry->has($voucher->source_type)
            ? $registry->get($voucher->source_type)->label()
            : null;

        return view('admin.accounting.vouchers.show', compact('voucher', 'sourceLabel'));
    }

    public function edit(EditVoucherRequest $request, AccVoucher $voucher, PartyDirectory $parties)
    {
        abort_if($voucher->is_auto || ! $voucher->isPosted(), 403, 'Only posted manual vouchers can be edited.');
        $voucher->load('lines.account');

        return view('admin.accounting.vouchers.form', [
            'voucher'       => $voucher,
            'companies'     => Company::orderBy('id')->get(['id', 'name', 'slug']),
            'partyTypes'    => $this->partyTypesByCompany($parties),
            'existingLines' => $voucher->lines->map(fn ($l) => [
                'account_id'    => $l->account_id,
                'account_text'  => $l->account->code.' – '.$l->account->name,
                'party_control' => $l->account->party_type,
                'party_type'    => $l->party_type,
                'party_id'      => $l->party_id,
                'party_name'    => $l->party_name,
                'narration'     => $l->narration,
                'debit'         => (float) $l->debit,
                'credit'        => (float) $l->credit,
            ])->values(),
        ]);
    }

    public function update(UpdateVoucherRequest $request, AccVoucher $voucher): JsonResponse
    {
        $this->vouchers->update($voucher, $request->validated());

        return response()->json([
            'message'  => "Voucher {$voucher->voucher_no} updated.",
            'redirect' => route('admin.accounting.vouchers.show', $voucher),
        ]);
    }

    public function cancel(CancelVoucherRequest $request, AccVoucher $voucher): JsonResponse
    {
        $this->vouchers->cancel($voucher, $request->reason, $request->user()->id);

        return response()->json(['message' => "Voucher {$voucher->voucher_no} cancelled."]);
    }

    public function parties(PartiesVoucherRequest $request, PartyDirectory $parties): JsonResponse
    {
        $company = Company::findOrFail($request->integer('company_id'));

        return response()->json(['results' => $parties->search($company->slug, $request->type, trim((string) $request->q))]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function partyTypesByCompany(PartyDirectory $parties): array
    {
        return Company::orderBy('id')->get(['id', 'slug'])
            ->mapWithKeys(fn ($c) => [$c->id => $parties->typesFor($c->slug)])
            ->all();
    }
}

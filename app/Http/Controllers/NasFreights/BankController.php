<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Http\Requests\NasFreights\Bank\DestroyBankRequest;
use App\Http\Requests\NasFreights\Bank\IndexBankRequest;
use App\Http\Requests\NasFreights\Bank\StoreBankRequest;
use App\Http\Requests\NasFreights\Bank\UpdateBankRequest;
use App\Models\NasFreights\NasFreightsBank;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function index(IndexBankRequest $request)
    {
        if ($request->ajax()) {
            return DataTables::of(NasFreightsBank::query()->orderBy('bank_name')->orderBy('account_name'))
                ->addIndexColumn()
                ->addColumn('status_badge', fn ($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>')
                ->addColumn('action', function ($r) use ($request) {
                    $html = '';

                    if ($request->user()->hasPermission('freight.bank.edit')) {
                        $html .= '<button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="'.$r->id.'"
                            data-account_name="'.e($r->account_name).'"
                            data-account_number="'.e($r->account_number).'"
                            data-account_type="'.e($r->account_type).'"
                            data-bank_name="'.e($r->bank_name).'"
                            data-bank_branch="'.e($r->bank_branch).'"
                            data-routing_number="'.e($r->routing_number).'"
                            data-is_active="'.(int) $r->is_active.'">
                            <i class="fa fa-edit"></i>
                        </button> ';
                    }

                    if ($request->user()->hasPermission('freight.bank.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                            data-url="'.route('nas-freights.settings.banks.destroy', $r->id).'"
                            data-name="'.e($r->bank_name).' — '.e($r->account_number).'">
                            <i class="fa fa-trash"></i>
                        </button>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('nas-freights.settings.banks.index');
    }

    public function store(StoreBankRequest $request)
    {
        NasFreightsBank::create($this->payload($request));

        return response()->json(['message' => 'Bank created.']);
    }

    public function update(UpdateBankRequest $request, NasFreightsBank $bank)
    {
        $bank->update($this->payload($request));

        return response()->json(['message' => 'Bank updated.']);
    }

    public function destroy(DestroyBankRequest $request, NasFreightsBank $bank)
    {
        $bank->delete();

        return response()->json(['message' => 'Bank deleted.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreBankRequest|UpdateBankRequest $request): array
    {
        return [
            'account_name'   => trim($request->account_name),
            'account_number' => trim($request->account_number),
            'account_type'   => trim($request->account_type),
            'bank_name'      => trim($request->bank_name),
            'bank_branch'    => filled($request->bank_branch) ? trim($request->bank_branch) : null,
            'routing_number' => filled($request->routing_number) ? trim($request->routing_number) : null,
            'is_active'      => $request->boolean('is_active', true),
        ];
    }
}

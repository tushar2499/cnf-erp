<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(ChevronAccount::query())
                ->addIndexColumn()
                ->addColumn('status_badge', fn($r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="'           . $r->id           . '"
                        data-account_no="'   . e($r->account_no)   . '"
                        data-account_name="' . e($r->account_name) . '"
                        data-bank_name="'    . e($r->bank_name)    . '"
                        data-branch_name="'  . e($r->branch_name)  . '"
                        data-account_type="' . e($r->account_type) . '"
                        data-is_active="'    . (int)$r->is_active  . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'  . route('chevron.settings.accounts.destroy', $r->id) . '"
                        data-name="' . e($r->account_no) . ' — ' . e($r->account_name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('chevron.settings.accounts.index', [
            'accountTypes' => ChevronAccount::accountTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_no'   => ['required', 'string', 'max:100', 'unique:chevron_accounts,account_no'],
            'account_name' => ['required', 'string', 'max:255'],
            'bank_name'    => ['nullable', 'string', 'max:255'],
            'branch_name'  => ['nullable', 'string', 'max:255'],
            'account_type' => ['required', 'string'],
        ]);

        ChevronAccount::create([
            'account_no'   => $request->account_no,
            'account_name' => $request->account_name,
            'bank_name'    => $request->bank_name,
            'branch_name'  => $request->branch_name,
            'account_type' => $request->account_type,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Account created successfully.']);
    }

    public function update(Request $request, ChevronAccount $account)
    {
        $request->validate([
            'account_no'   => ['required', 'string', 'max:100', 'unique:chevron_accounts,account_no,' . $account->id],
            'account_name' => ['required', 'string', 'max:255'],
            'bank_name'    => ['nullable', 'string', 'max:255'],
            'branch_name'  => ['nullable', 'string', 'max:255'],
            'account_type' => ['required', 'string'],
        ]);

        $account->update([
            'account_no'   => $request->account_no,
            'account_name' => $request->account_name,
            'bank_name'    => $request->bank_name,
            'branch_name'  => $request->branch_name,
            'account_type' => $request->account_type,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Account updated successfully.']);
    }

    public function destroy(ChevronAccount $account)
    {
        $account->delete();
        return response()->json(['message' => 'Account deleted.']);
    }
}

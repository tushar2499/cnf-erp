<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingBranch;
use App\Models\UserBranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = $this->allowedBranches();

        if ($branches->isEmpty()) {
            return redirect()->route('company.select')
                ->with('error', 'You do not have branch access for NAS Trading. Please contact your administrator.');
        }

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('nas-trading.select-branch', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = session('active_company_id') or abort(403, 'No active company in session.');
        $allowedIds = $user->is_super ? null : UserBranchAccess::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->pluck('branch_id')
            ->toArray();

        $request->validate([
            'branch_id' => [
                'required',
                'exists:nas_trading_branches,id',
                function ($attribute, $value, $fail) use ($allowedIds) {
                    if ($allowedIds !== null && ! in_array((int) $value, $allowedIds)) {
                        $fail('You do not have access to that branch.');
                    }
                },
            ],
        ]);

        return $this->setAndRedirect(NasTradingBranch::findOrFail($request->branch_id));
    }

    private function allowedBranches()
    {
        $companyId = session('active_company_id') or abort(403, 'No active company in session.');
        $user = Auth::user();

        $query = NasTradingBranch::where('is_active', true)->orderBy('name');

        if (! $user->is_super) {
            $allowedIds = UserBranchAccess::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->pluck('branch_id')
                ->toArray();

            $query->whereIn('id', $allowedIds);
        }

        return $query->get();
    }

    private function setAndRedirect(NasTradingBranch $branch)
    {
        session([
            'nas_trading_branch_id'   => $branch->id,
            'nas_trading_branch_name' => $branch->name,
            'nas_trading_branch_code' => $branch->code,
        ]);

        return redirect()->route('nas-trading.dashboard');
    }
}

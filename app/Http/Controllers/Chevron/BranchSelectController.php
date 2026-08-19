<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBranch;
use App\Models\EmployeeBranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = $this->allowedBranches();

        if ($branches->isEmpty()) {
            return redirect()->route('company.select')
                ->with('error', 'You do not have branch access for Chevron Lines. Please contact your administrator.');
        }

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('chevron.select-branch', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = session('active_company_id') or abort(403, 'No active company in session.');
        $allowedIds = $user->is_super ? null : EmployeeBranchAccess::where('employee_id', $user->employee_id)
            ->where('company_id', $companyId)
            ->pluck('branch_id')
            ->toArray();

        $request->validate([
            'branch_id' => [
                'required',
                'exists:chevron_branches,id',
                function ($attribute, $value, $fail) use ($allowedIds) {
                    if ($allowedIds !== null && ! in_array((int) $value, $allowedIds)) {
                        $fail('You do not have access to that branch.');
                    }
                },
            ],
        ]);

        return $this->setAndRedirect(ChevronBranch::findOrFail($request->branch_id));
    }

    private function allowedBranches()
    {
        $companyId = session('active_company_id') or abort(403, 'No active company in session.');
        $user = Auth::user();

        $query = ChevronBranch::where('is_active', true)->orderBy('name');

        if (! $user->is_super) {
            $allowedIds = EmployeeBranchAccess::where('employee_id', $user->employee_id)
                ->where('company_id', $companyId)
                ->pluck('branch_id')
                ->toArray();

            $query->whereIn('id', $allowedIds);
        }

        return $query->get();
    }

    private function setAndRedirect(ChevronBranch $branch)
    {
        session([
            'active_branch_id'   => $branch->id,
            'active_branch_name' => $branch->name,
            'active_branch_code' => $branch->code,
        ]);

        return redirect()->route('chevron.dashboard');
    }
}

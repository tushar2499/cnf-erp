<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBranchAccess;
use App\Models\NasFreights\NasFreightsBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = $this->allowedBranches();

        if ($branches->isEmpty()) {
            return redirect()->route('company.select')
                ->with('error', 'You do not have branch access for NAS Freights. Please contact your administrator.');
        }

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('nas-freights.select-branch', compact('branches'));
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
                'exists:nas_freights_branches,id',
                function ($attribute, $value, $fail) use ($allowedIds) {
                    if ($allowedIds !== null && ! in_array((int) $value, $allowedIds)) {
                        $fail('You do not have access to that branch.');
                    }
                },
            ],
        ]);

        return $this->setAndRedirect(NasFreightsBranch::findOrFail($request->branch_id));
    }

    private function allowedBranches()
    {
        $companyId = session('active_company_id') or abort(403, 'No active company in session.');
        $user = Auth::user();

        $query = NasFreightsBranch::where('is_active', true)->orderBy('name');

        if (! $user->is_super) {
            $allowedIds = EmployeeBranchAccess::where('employee_id', $user->employee_id)
                ->where('company_id', $companyId)
                ->pluck('branch_id')
                ->toArray();

            $query->whereIn('id', $allowedIds);
        }

        return $query->get();
    }

    private function setAndRedirect(NasFreightsBranch $branch)
    {
        session([
            'nas_freights_branch_id'   => $branch->id,
            'nas_freights_branch_name' => $branch->name,
            'nas_freights_branch_code' => $branch->code,
        ]);

        return redirect()->route('nas-freights.dashboard');
    }
}

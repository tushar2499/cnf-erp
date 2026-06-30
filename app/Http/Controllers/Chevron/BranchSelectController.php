<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronBranch;
use Illuminate\Http\Request;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = ChevronBranch::where('is_active', true)->orderBy('name')->get();

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('chevron.select-branch', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['branch_id' => ['required', 'exists:chevron_branches,id']]);
        $branch = ChevronBranch::findOrFail($request->branch_id);
        return $this->setAndRedirect($branch);
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

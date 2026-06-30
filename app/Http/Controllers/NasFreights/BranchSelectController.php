<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Controller;
use App\Models\NasFreights\NasFreightsBranch;
use Illuminate\Http\Request;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = NasFreightsBranch::where('is_active', true)->orderBy('name')->get();

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('nas-freights.select-branch', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:nas_freights_branches,id']);
        return $this->setAndRedirect(NasFreightsBranch::findOrFail($request->branch_id));
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

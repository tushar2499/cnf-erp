<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Controller;
use App\Models\NasTrading\NasTradingBranch;
use Illuminate\Http\Request;

class BranchSelectController extends Controller
{
    public function show()
    {
        $branches = NasTradingBranch::where('is_active', true)->orderBy('name')->get();

        if ($branches->count() === 1) {
            return $this->setAndRedirect($branches->first());
        }

        return view('nas-trading.select-branch', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:nas_trading_branches,id']);
        return $this->setAndRedirect(NasTradingBranch::findOrFail($request->branch_id));
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

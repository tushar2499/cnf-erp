<?php

namespace App\Http\Controllers;

use App\Models\Chevron\ChevronBranch;
use App\Models\Company;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasTrading\NasTradingBranch;
use App\Models\UserBranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function select()
    {
        $user = Auth::user();

        if ($user->is_super) {
            $companies = Company::where('is_active', true)->get();
        } else {
            $companies = $user->companies()
                ->where('companies.is_active', true)
                ->whereNotNull('company_user.role_id')
                ->get();
        }

        $companies = $companies->filter(fn ($company) => $this->companyHasBranchAccess($company, $user))->values();

        return view('company.select', compact('companies'));
    }

    private function companyHasBranchAccess(Company $company, $user): bool
    {
        if ($user->is_super) {
            return match ($company->slug) {
                'chevron-lines' => ChevronBranch::where('is_active', true)->exists(),
                'nas-freights'  => NasFreightsBranch::where('is_active', true)->exists(),
                'nas-trading'   => NasTradingBranch::where('is_active', true)->exists(),
                default         => false,
            };
        }

        return UserBranchAccess::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->exists();
    }

    public function switch(Request $request, string $slug)
    {
        $user = Auth::user();

        if ($user->is_super) {
            $company = Company::where('slug', $slug)->where('is_active', true)->firstOrFail();
        } else {
            $company = $user->companies()
                ->where('companies.slug', $slug)
                ->where('companies.is_active', true)
                ->whereNotNull('company_user.role_id')
                ->firstOrFail();
        }

        session([
            'active_company_id'   => $company->id,
            'active_company_slug' => $company->slug,
            'active_company_name' => $company->name,
            'active_company_type' => $company->type,
        ]);

        // Clear old branch when switching company
        session()->forget(['active_branch_id', 'active_branch_name', 'active_branch_code']);

        $dashboard = match ($company->slug) {
            'chevron-lines' => route('chevron.select-branch'),
            'nas-freights'  => route('nas-freights.dashboard'),
            'nas-trading'   => route('nas-trading.dashboard'),
            default         => route('company.select'),
        };

        return redirect($dashboard);
    }
}

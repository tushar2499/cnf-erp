<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function select()
    {
        $companies = Auth::user()
            ->companies()
            ->where('companies.is_active', true)
            ->get();

        return view('company.select', compact('companies'));
    }

    public function switch(Request $request, string $slug)
    {
        $company = Auth::user()
            ->companies()
            ->where('companies.slug', $slug)
            ->where('companies.is_active', true)
            ->firstOrFail();

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

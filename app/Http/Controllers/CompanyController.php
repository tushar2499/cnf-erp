<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmployeeBranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function select()
    {
        $user = Auth::user();

        if ($user->is_super) {
            $companies = Company::where('is_active', true)->get();
        } else {
            $employeeCompanyIds = EmployeeBranchAccess::where('employee_id', $user->employee_id)
                ->pluck('company_id')
                ->unique();

            $roleCompanyIds = DB::table('role_company')
                ->where('role_id', $user->role_id)
                ->pluck('company_id');

            $visibleCompanyIds = $employeeCompanyIds->intersect($roleCompanyIds);

            $companies = Company::whereIn('id', $visibleCompanyIds)
                ->where('is_active', true)
                ->get();
        }

        return view('company.select', compact('companies'));
    }

    public function switch(Request $request, string $slug)
    {
        $user = Auth::user();

        if ($user->is_super) {
            $company = Company::where('slug', $slug)->where('is_active', true)->firstOrFail();
        } else {
            $employeeCompanyIds = EmployeeBranchAccess::where('employee_id', $user->employee_id)
                ->pluck('company_id')
                ->unique();

            $roleCompanyIds = DB::table('role_company')
                ->where('role_id', $user->role_id)
                ->pluck('company_id');

            $visibleCompanyIds = $employeeCompanyIds->intersect($roleCompanyIds);

            $company = Company::where('slug', $slug)
                ->where('is_active', true)
                ->whereIn('id', $visibleCompanyIds)
                ->firstOrFail();
        }

        session([
            'active_company_id'   => $company->id,
            'active_company_slug' => $company->slug,
            'active_company_name' => $company->name,
            'active_company_type' => $company->type,
        ]);

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

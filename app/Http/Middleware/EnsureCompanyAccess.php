<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\EmployeeBranchAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $sessionSlug = session('active_company_slug');

        if ($sessionSlug !== $slug) {
            $user = $request->user();
            $company = Company::where('slug', $slug)->where('is_active', true)->first();

            if (! $company || ! $this->userCanAccessCompany($user, $company)) {
                return redirect()->route('company.select')
                    ->with('error', 'You do not have access to that company.');
            }

            session([
                'active_company_id'   => $company->id,
                'active_company_slug' => $company->slug,
                'active_company_name' => $company->name,
                'active_company_type' => $company->type,
            ]);
        }

        return $next($request);
    }

    private function userCanAccessCompany($user, Company $company): bool
    {
        if ($user->is_super) {
            return true;
        }

        $hasEmployeeAccess = EmployeeBranchAccess::where('employee_id', $user->employee_id)
            ->where('company_id', $company->id)
            ->exists();

        $hasRoleAccess = DB::table('role_company')
            ->where('role_id', $user->role_id)
            ->where('company_id', $company->id)
            ->exists();

        return $hasEmployeeAccess && $hasRoleAccess;
    }
}

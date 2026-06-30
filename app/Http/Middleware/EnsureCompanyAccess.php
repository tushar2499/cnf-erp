<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            // Try to auto-switch if user has access
            $company = $request->user()
                ->companies()
                ->where('companies.slug', $slug)
                ->where('companies.is_active', true)
                ->where('company_user.is_active', true)
                ->first();

            if (!$company) {
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
}
